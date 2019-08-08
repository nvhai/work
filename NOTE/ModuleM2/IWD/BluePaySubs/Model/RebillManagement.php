<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Model;

use IWD\BluePaySubs\Api\RebillManagementInterface;
use IWD\BluePaySubs\Api\SubsAdapterInterface;
use IWD\BluePaySubs\Api\SubsAdapterResponseInterface;
use IWD\BluePaySubs\Model\Adapter\BluePaySubsAdapterFactory;
use IWD\BluePay\Gateway\Validator\ResponseValidator;
use IWD\BluePay\Gateway\Request;
use IWD\BluePaySubs\Api\Data\SubscriptionInterface;
use IWD\BluePaySubs\Model\Source\Period;
use IWD\BluePaySubs\Model\Source\Status;
use Magento\Sales\Api\Data\OrderItemInterface;

class RebillManagement implements RebillManagementInterface
{
    /**
     * @var Status
     */
    protected $statusSource;

    /**
     * @var \IWD\BluePaySubs\Helper\Data
     */
    protected $helper;

    /**
     * @var BluePaySubsAdapterFactory
     */
    protected $adapterFactory;

    /**
     * @var ResponseValidator
     */
    private $responseValidator;

    /**
     * @var Period
     */
    protected $periodSource;

    /**
     * RebillManagement constructor.
     * @param Status $statusSource
     * @param \IWD\BluePaySubs\Helper\Data $helper
     * @param BluePaySubsAdapterFactory $adapterFactory
     * @param ResponseValidator $responseValidator
     * @param Period $periodSource
     */
    public function __construct(
        Status $statusSource,
        \IWD\BluePaySubs\Helper\Data $helper,
        BluePaySubsAdapterFactory $adapterFactory,
        ResponseValidator $responseValidator,
        Period $periodSource
    )
    {
        $this->statusSource = $statusSource;
        $this->helper = $helper;
        $this->adapterFactory = $adapterFactory;
        $this->responseValidator = $responseValidator;
        $this->periodSource = $periodSource;
    }

    /**
     * @inheritdoc
     */
    public function createRebill(array $data, OrderItemInterface $item, $shippingAmount)
    {
        $rebill = $this->adapterFactory->create();
        $rebillAmount = '0.00';
        if (empty($rebillingInfo = $this->_buildRebillInfo($item, $shippingAmount))) {
            return null;
        }
        $rebill->setCustomerInformation($data[Request\CustomerDataBuilder::CUSTOMER])
            ->setRebillingInformation($rebillingInfo);
        $rebill->setOrderID(uniqid());
        // Previous transaction token checking
        if ($data[Request\PaymentDataBuilder::TRANS_ID]) {
            $rebill->auth($rebillAmount, $data[Request\PaymentDataBuilder::TRANS_ID]);
        } else {
            $rebill->setPaymentInformation($data[Request\PaymentDataBuilder::PAYMENT])
                ->auth($rebillAmount);
        }

        return $this->_process($rebill);
    }

    /**
     * @inheritdoc
     */
    public function createRebillPayment(array $data)
    {
        $updateRebillPaymentInformation = $this->adapterFactory->create();
        $updateRebillPaymentInformation->setCustomerInformation($data[Request\CustomerDataBuilder::CUSTOMER])
            ->setPaymentInformation($data[Request\PaymentDataBuilder::PAYMENT])
            ->auth("0.00");
        $response = $updateRebillPaymentInformation->process()->getResponse();
        if (strtoupper($response->getMessage()) == 'INFORMATION STORED') {
            return $response;
        }
        if (strtoupper($response->getMessage()) == 'DUPLICATE') {
            throw new \Exception(__('Sorry, Your card was declined. Please enter your information again or try a different card.'));
        }
        return null;
    }

    /**
     * @param SubscriptionInterface $subscription
     * @return SubsAdapterResponseInterface|SubsAdapterResponseInterface[]|null
     */
    public function updateRebill(SubscriptionInterface $subscription)
    {
        $rebill = $this->adapterFactory->create();
        $info = $this->_collectRebillInfo($subscription);
        $rebill->updateRebill($info);

        return $this->_process($rebill);
    }

    /**
     * @param SubscriptionInterface $subscription
     * @param $newStatus
     * @return SubsAdapterResponseInterface|SubsAdapterResponseInterface[]|null
     */
    public function updateRebillStatus(SubscriptionInterface $subscription, $newStatus)
    {
        if (!$this->statusSource->canSetStatus($subscription, $newStatus)) {
            return null;
        }
        $rebill = $this->adapterFactory->create();
        $rebillId = $subscription->getRebillId();
        $rebill->updateRebillStatus($rebillId, $newStatus);

        return $this->_process($rebill);
    }

    /**
     * @param SubscriptionInterface $subscription
     * @return SubsAdapterResponseInterface|null
     */
    public function getRebillStatus(SubscriptionInterface $subscription)
    {
        $rebill = $this->adapterFactory->create();
        $rebill->getRebillStatus($subscription->getRebillID());

        return $this->_process($rebill);
    }

    /**
     * @param $startDate
     * @param string $endDate
     * @return SubsAdapterResponseInterface|SubsAdapterResponseInterface[]|null
     * @throws \Exception
     */
    public function getRebillDailyReport($startDate, $endDate = '')
    {
        $rebillReport = $this->adapterFactory->create();

        $params = $this->_getReportIntervals($startDate, $endDate);
        $params += [
            'subaccountsSearched' => '1', // Also search subaccounts? Yes
            'doNotEscape' => '1', // Output response without commas? Yes
            'errors' => '1' // Do not include errored transactions? Yes
        ];
        $rebillReport->getRebillDailyReport($params);

        return $this->_process($rebillReport);
    }

    /**
     * @param $transId
     * @param $startDate
     * @param string $endDate
     * @return SubsAdapterResponseInterface|null
     * @throws \Exception
     */
    public function getRebillByTransaction($transId, $startDate, $endDate = '')
    {
        $rebill = $this->adapterFactory->create();

        $params = $this->_getReportIntervals($startDate, $endDate);
        $params += [
            'transID' => $transId,
            'errors' => '1' // Do not include errored transactions? Yes
        ];
        $rebill->getRebillByTransaction($params);

        return $this->_process($rebill);
    }

    /**
     * Create rebill info from order item
     *
     * @param OrderItemInterface $item
     * @param $shippingAmount
     * @return array|null
     * @throws \Exception
     */
    protected function _buildRebillInfo(OrderItemInterface $item, $shippingAmount)
    {
        if ($this->helper->isItemSubscription($item) !== true) {
            return null;
        }

        $subscription = $this->helper->getItemSubscription($item);
        if (empty($subscription)) {
            return null;
        }

        $interval = $this->helper->getItemSubscriptionInterval($item);
        // Period interval can't be <= zero
        if ($interval <= 0) {
            return null;
        }
        $rebilingItemAmount = $this->helper->getItemSubscriptionPrice($item) * $item->getQtyOrdered();
        if (!$item->getIsVirtual()) {
            $rebilingItemAmount += $shippingAmount;
        }
        $period = $this->_getRebillPeriod($subscription['period']);
        $rebillInfo = [
            'rebillFirstDate' => $this->helper->getDateInterval($interval, $period),
            'rebillExpression' => $interval . ' ' . $period,
            'rebillAmount' => $rebilingItemAmount
        ];
        if (!empty($subscription['cycles'])) {
            $rebillInfo['rebillCycles'] = $subscription['cycles'];
        }

        return $rebillInfo;
    }

    /**
     * Collect exist rebill info from subscription
     *
     * @param SubscriptionInterface $subscription
     * @return array
     */
    protected function _collectRebillInfo(\IWD\BluePaySubs\Api\Data\SubscriptionInterface $subscription)
    {
        $interval = $subscription->getPeriodInterval();
        $expr = $interval . ' ' . $this->_getRebillPeriod($subscription->getPeriod());

        $rebillInfo = [
            'rebillID' => $subscription->getRebillID(),
            'templateID' => $subscription->getTransactionId(),
            'rebNextDate' => $subscription->getNextDate(),
            'rebExpr' => $expr, // Rebill Frequency
            'rebAmount' => $subscription->getAmount(), // Rebill Amount
        ];

        if (!empty($subscription->getCycles())) {
            $rebillInfo['rebCycles'] = $subscription->getCycles();
        }

        return $rebillInfo;
    }

    /**
     * @param SubsAdapterInterface $adapter
     * @return SubsAdapterResponseInterface[]|SubsAdapterResponseInterface|null
     */
    protected function _process(SubsAdapterInterface $adapter)
    {
        try {
            $response = $adapter->process()->getResponse();
            // Don't validate report results array
            if (is_array($response) && !empty($response)) {
                return $response;
            }
            $validationResult = $this->responseValidator->validate(
                [
                    'response' => [
                        'object' => $response
                    ]
                ]
            );
            if ($validationResult->isValid()) {
                return $response;
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    /**
     * Retrieve formatted intervals
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     * @throws \Exception
     */
    protected function _getReportIntervals($startDate, $endDate)
    {
        $startDate = new \DateTime($startDate);
        if (empty($endDate)) {
            $endDate = new \DateTime();
            $endDate->add(new \DateInterval('P1D'));
        } else {
            $endDate = new \DateTime($endDate);
        }

        return [
            'reportStart' => $startDate->format(self::REBILL_DATE_FORMAT), // Report Start Date: YYYY-MM-DD
            'reportEnd' => $endDate->format(self::REBILL_DATE_FORMAT), // Report End Date: YYYY-MM-DD
        ];
    }

    /**
     * @param $subscriptionPeriod
     * @return string
     */
    protected function _getRebillPeriod($subscriptionPeriod)
    {
        return strtoupper($this->periodSource->getOptionText($subscriptionPeriod));
    }
}