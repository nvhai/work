<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePaySubs\Controller\Bsubs\Edit;

use IWD\BluePaySubs\Model\Source\Agent;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use IWD\BluePaySubs\Api\Data\SubscriptionInterface;
use IWD\BluePaySubs\Api\RebillManagementInterface;
use IWD\BluePaySubs\Helper\Address as AddressHelper;

/**
 * BillingAddress Class
 */
class BillingAddress extends \IWD\BluePaySubs\Controller\Bsubs
{
    /**
     * @var \IWD\BluePaySubs\Model\Service\Subscription
     */
    protected $subscriptionService;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var AddressHelper
     */
    protected $addressHelper;

    /**
     * BillingAddress constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \IWD\BluePaySubs\Api\SubscriptionRepositoryInterface $subscriptionRepository
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \IWD\BluePaySubs\Model\Service\Subscription $subscriptionService
     * @param RebillManagementInterface $rebillManagement
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param AddressHelper $addressHelper
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        \IWD\BluePaySubs\Api\SubscriptionRepositoryInterface $subscriptionRepository,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \IWD\BluePaySubs\Model\Service\Subscription $subscriptionService,
        RebillManagementInterface $rebillManagement,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        AddressHelper $addressHelper
    ) {
        parent::__construct($context, $registry, $subscriptionRepository, $currentCustomer, $rebillManagement);
        $this->subscriptionService = $subscriptionService;
        $this->addressRepository = $addressRepository;
        $this->addressHelper = $addressHelper;
    }

    /**
     * Subscriptions edit page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $initialized = $this->_init();
        $params = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($initialized !== true) {
            $resultRedirect->setPath('*/bsubs/index');
            return $resultRedirect;
        }
        /** @var \IWD\BluePaySubs\Model\Subscription $subscription */
        $subscription = $this->registry->registry('current_subs');

        try {
            if (!isset($params['billing'])) {
                throw new LocalizedException(__('Error, billing payment not specified'));
            }
            $this->updateSubscriptionAddress($subscription, $params['billing']);
            $this->updateRebill($subscription);

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

        }
        $resultRedirect->setPath('*/bsubs/edit', ['id' => $subscription->getId()]);

        return $resultRedirect;
    }

    /**
     * @param SubscriptionInterface $subscription
     * @param array $paymentData
     * @param string $type
     * @throws LocalizedException
     * @throws \Exception
     * @return $this
     */
    protected function updateSubscriptionAddress(
        SubscriptionInterface $subscription,
        array $addressData,
        $type = 'Billing address'
    ) {
        if (empty($addressData['address_id'])) {
            // build new customer address
            $addressData['street'] = $this->getStreet($addressData);
            $customerAddress = $this->addressHelper->buildAddressFromInput($addressData, [], true);
            $customerAddress->setCustomerId($subscription->getCustomerId());
            $this->addressRepository->save($customerAddress);
            $this->changeAddress($subscription, ['address_id' => $customerAddress->getId()]);
            $this->messageManager->addSuccessMessage(__('%1 added.', $type));
        } else {
            $this->changeAddress($subscription, $addressData);
            $this->messageManager->addSuccessMessage(__('%1 changed.', $type));
        }

        return $this;
    }

    /**
     * @param SubscriptionInterface $subscription
     * @param array $address
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function changeAddress(SubscriptionInterface $subscription, array $address)
    {
        return  $this->subscriptionService->changeBillingAddress($subscription, $address, Agent::AGENT_CUSTOMER);
    }

    /**
     * @param array $data
     * @return array
     */
    protected function getStreet(array $data)
    {
        $result = [];
        foreach ($data as $k => $v) {
            if(strpos($k, 'street') !== false) {
                $result[] = $v;
            }
        }

        return $result;
    }
}
