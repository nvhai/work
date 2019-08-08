<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePay\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Framework\Exception\LocalizedException;
use IWD\BluePay\Gateway\Config\Config;
use IWD\BluePay\Api\Data\CardInterface;
use IWD\BluePay\Api\CardRepositoryInterface;

/**
 * Class DataAssignObserver
 */
class DataAssignObserver extends AbstractDataAssignObserver
{
    /**
     * Card number
     */
    const CARD_NUMBER = 'cc_number';

    /**
     * Save payment info key
     */
    const SAVE_PAYMENT_INFO = 'save_payment_info';

    /**
     * @var CardRepositoryInterface
     */
    private $cardRepository;

    /**
     * DataAssignObserver constructor.
     * @param CardRepositoryInterface $cardRepository
     */
    public function __construct(
        CardRepositoryInterface $cardRepository
    ) {
        $this->cardRepository = $cardRepository;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);
        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_array($additionalData)) {
            return;
        }

        $paymentInfo = $this->readPaymentModelArgument($observer);
        if ($paymentInfo->getMethod() != Config::CODE) {
            return;
        }

        $paymentInfo->addData($additionalData);
        $paymentInfo->setAdditionalInformation(
            self::SAVE_PAYMENT_INFO,
            $this->_isSaveCardInfo($additionalData)
        );
        $paymentInfo->setAdditionalInformation(
            OrderPaymentInterface::CC_TRANS_ID,
            $this->_getTransactionId($additionalData)
        );

    }

    /**
     * @param array $additionalData
     * @return bool
     */
    protected function _isSaveCardInfo(array $additionalData)
    {
        return !empty($additionalData[self::SAVE_PAYMENT_INFO]) && !empty($additionalData[self::CARD_NUMBER]);
    }

    /**
     * @param array $additionalData
     * @return int|null
     */
    protected function _getTransactionId(array $additionalData)
    {
        $transactionId = null;
        try {
            if (!empty($additionalData[CardInterface::HASH]) && empty($additionalData[self::CARD_NUMBER])) {
                $card = $this->cardRepository->getByHash($additionalData[CardInterface::HASH]);
                if ($card->getId()) {
                    $transactionId = $card->getTransId();
                }
            }
        } catch (LocalizedException $e) {
            $e->getMessage();
        }

        return $transactionId;
    }
}
