<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePay\Gateway\Response;

use IWD\BluePay\Observer\DataAssignObserver;
use IWD\BluePay\Gateway\SubjectReader;
use IWD\BluePay\Api\CardRepositoryInterface;
use IWD\BluePay\Api\Data\CardInterface;
use IWD\BluePay\Api\Data\CardInterfaceFactory;
use IWD\BluePay\Api\AdapterResponseInterface;
use IWD\BluePay\Model\Ui\ConfigProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Payment Details Handler
 */
class PaymentDetailsHandler implements HandlerInterface
{
    const CREDIT_CARD_TYPE = 'CARD_TYPE';

    const PAYMENT_ACCOUNT = 'PAYMENT_ACCOUNT';

    const MASTER_ID = 'id';

    const AVS_RESPONSE_CODE = 'AVS_RESULT';

    const CVV_RESPONSE_CODE = 'CVV2_RESULT';

    const AUTHORIZATION_CODE = 'AUTH_CODE';

    const STATUS = 'Result';

    /**
     * List of additional details
     * @var array
     */
    protected $additionalInformationMapping = [
        self::CREDIT_CARD_TYPE,
        self::PAYMENT_ACCOUNT,
        self::MASTER_ID,
        self::AVS_RESPONSE_CODE,
        self::CVV_RESPONSE_CODE,
        self::AUTHORIZATION_CODE,
        self::STATUS,
    ];

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var CardRepositoryInterface
     */
    private $cardRepository;

    /**
     * @var CardInterfaceFactory
     */
    private $cardFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * System event manager
     *
     *
     * @var ManagerInterface
     */
    protected $_eventManager;

    /**
     * PaymentDetailsHandler constructor.
     * @param SubjectReader $subjectReader
     * @param CardRepositoryInterface $cardRepository
     * @param CardInterfaceFactory $cardFactory
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        SubjectReader $subjectReader,
        CardRepositoryInterface $cardRepository,
        CardInterfaceFactory $cardFactory,
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        ManagerInterface $eventManager
    ) {
        $this->subjectReader = $subjectReader;
        $this->cardRepository = $cardRepository;
        $this->cardFactory = $cardFactory;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->_eventManager = $eventManager;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $transactionId = $this->subjectReader->readTransaction($response);
        /** @var OrderPaymentInterface $payment */
        $payment = $paymentDO->getPayment();
        $responseObj = $response['object'];

        $payment->setCcTransId($transactionId);
        $payment->setLastTransId($transactionId);

        foreach ($this->additionalInformationMapping as $item) {
            if (empty($responseObj->getData($item))) {
                continue;
            }
            $payment->setAdditionalInformation($item, $responseObj->getData($item));
        }
        if($card = $this->_saveCardInfo($payment, $responseObj)) {
            $payment->setParentId($card->getId());
        }

        $this->_eventManager->dispatch(
            'iwd_bluepay_payment_handle_after',
            ['subject' => $handlingSubject, 'response' => $response]
        );
    }

    /**
     * @param OrderPaymentInterface $payment
     * @param AdapterResponseInterface $response
     * @return CardInterface
     */
    protected function _saveCardInfo(OrderPaymentInterface $payment, AdapterResponseInterface $response)
    {
        try {
            if (!$this->_canSaveCard($payment) || $response->getData('PAYMENT_TYPE') != 'CREDIT') {
                return null;
            }

            /**@var $card CardInterface */
            $card = $this->cardFactory->create();
            $card->setTransId($response->getTransactionId())
                ->setMaskedAccount($response->getPaymentAccount())
                ->setCustomerIp($response->getRemoteIp())
                ->setExpirationDate($response->getCardExpire())
                ->setPaymentId($payment->getId())
                ->setAdditionalData($payment->getAdditionalInformation());
            $order = $payment->getOrder();
            if ($order->getCustomerId()) {
                $card->setCustomerId($order->getCustomerId());
            }
            if ($order->getCustomerEmail()) {
                $card->setCustomerEmail($order->getCustomerEmail());
            }

            $this->cardRepository->save($card);
        } catch (LocalizedException $e) {
            $card = null;
            $message = __($e->getMessage() ?: 'Error processing saving BluePay credit card data.');
            $message .= ' Order Payment ID: ' . $payment->getId();
            $this->logger->critical($message);
        }

        return $card;
    }

    /**
     * @param OrderPaymentInterface $payment
     * @return bool
     */
    protected function _canSaveCard(OrderPaymentInterface $payment)
    {
        $saveInfoKey = DataAssignObserver::SAVE_PAYMENT_INFO;
        return $payment->getAdditionalInformation($saveInfoKey) && $this->_isInternalSaveCardEnabled();
    }

    /**
     * @return bool
     */
    protected function _isInternalSaveCardEnabled()
    {
        $path = 'payment/' . ConfigProvider::CC_VAULT_CODE . '/active';
        $pathInternal = 'payment/' . ConfigProvider::CODE . '/' . ConfigProvider::CC_VAULT_INTERNAL;
        return (bool) !$this->scopeConfig->getValue($path) && $this->scopeConfig->getValue($pathInternal);
    }
}
