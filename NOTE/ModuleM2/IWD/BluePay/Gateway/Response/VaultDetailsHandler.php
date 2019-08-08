<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePay\Gateway\Response;

use IWD\BluePay\Gateway\SubjectReader;
use IWD\BluePay\Gateway\Config\Config;
use IWD\BluePay\Api\AdapterResponseInterface;
use IWD\BluePay\Model\Ui\ConfigProvider;
use IWD\BluePay\Observer\DataAssignObserver;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use IWD\BluePay\Api\Data\PaymentTokenFactoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Vault Details Handler
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VaultDetailsHandler implements HandlerInterface
{
    /**
     * @var PaymentTokenFactoryInterface
     */
    protected $paymentTokenFactory;

    /**
     * @var OrderPaymentExtensionInterfaceFactory
     */
    protected $paymentExtensionFactory;

    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \IWD\BluePay\Helper\Json
     */
    private $serializer;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * VaultDetailsHandler constructor.
     * @param PaymentTokenFactoryInterface $paymentTokenFactory
     * @param OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory
     * @param Config $config
     * @param SubjectReader $subjectReader
     * @param ScopeConfigInterface $scopeConfig
     * @param \IWD\BluePay\Helper\Json|null $serializer
     */
    public function __construct(
        PaymentTokenFactoryInterface $paymentTokenFactory,
        OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory,
        Config $config,
        SubjectReader $subjectReader,
        ScopeConfigInterface $scopeConfig,
        \IWD\BluePay\Helper\Json $serializer = null
    ) {
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->paymentExtensionFactory = $paymentExtensionFactory;
        $this->config = $config;
        $this->subjectReader = $subjectReader;
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\IWD\BluePay\Helper\Json::class);
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $transactionId = $this->subjectReader->readTransaction($response);
        $payment = $paymentDO->getPayment();
        $responseObj = $response['object'];

        // add vault payment token entity to extension attributes
        $paymentToken = $this->getVaultPaymentToken($transactionId, $payment, $responseObj);
        if (null !== $paymentToken) {
            $extensionAttributes = $this->getExtensionAttributes($payment);
            $extensionAttributes->setVaultPaymentToken($paymentToken);
        }
    }

    /**
     * Get vault payment token entity
     *
     * @param int $transaction
     * @param InfoInterface $payment
     * @param AdapterResponseInterface $response
     * @return PaymentTokenInterface|null
     */
    protected function getVaultPaymentToken($transactionId, InfoInterface $payment, AdapterResponseInterface $response)
    {
        // Check token existing in gateway response
        if (empty($transactionId) || !($this->isVaultEnabled() || $this->canSaveCard($payment))) {
            return null;
        }
        try {
            /** @var PaymentTokenInterface $paymentToken */
            $paymentToken = $this->paymentTokenFactory->create();
            $paymentToken->setGatewayToken($transactionId);
            $details = [];
            if ($payment->getData(OrderPaymentInterface::ECHECK_TYPE) == 'CC') {
                $paymentToken->setType(PaymentTokenFactoryInterface::TOKEN_TYPE_CREDIT_CARD);
                $paymentToken->setExpiresAt($this->getExpirationDate($payment));
                $details = [
                    'type' => $payment->getData(OrderPaymentInterface::CC_TYPE),
                    'expirationDate' => $this->getExpirationDate($payment, 'm/Y')
                ];
            }
            else {
                $paymentToken->setType(PaymentTokenFactoryInterface::TOKEN_TYPE_ACCOUNT);
            }
            $details['maskedCC'] = $this->getMaskedAccount($response);
            $paymentToken->setTokenDetails($this->convertDetailsToJSON($details));
        } catch (\Exception $e) {
            $paymentToken = null;
        }

        return $paymentToken;
    }

    /**
     * @param InfoInterface $payment
     * @return bool
     */
    protected function canSaveCard(InfoInterface $payment)
    {
        $saveInfoKey = DataAssignObserver::SAVE_PAYMENT_INFO;

        return $payment->getAdditionalInformation($saveInfoKey);
    }

    /**
     * @return bool
     */
    private function isVaultEnabled()
    {
        $path = 'payment/' . ConfigProvider::CC_VAULT_CODE . '/active';
        return (bool) $this->scopeConfig->getValue($path);
    }

    /**
     * @param InfoInterface $payment
     * @return string
     * @throws \Exception
     */
    private function getExpirationDate(InfoInterface $payment, $format = 'Y-m-d 00:00:00')
    {
        $expDate = new \DateTime(
            $payment->getData(OrderPaymentInterface::CC_EXP_YEAR)
            . '-'
            . $payment->getData(OrderPaymentInterface::CC_EXP_MONTH)
            . '-'
            . '01'
            . ' '
            . '00:00:00',
            new \DateTimeZone('UTC')
        );
//        $expDate->add(new \DateInterval('P1M'));
        return $expDate->format($format);
    }

    /**
     * Convert payment token details to JSON
     * @param array $details
     * @return string
     */
    private function convertDetailsToJSON($details)
    {
        $json = $this->serializer->serialize($details);
        return $json ? $json : '{}';
    }

    /**
     * @param AdapterResponseInterface $response
     * @return bool|string
     */
    private function getMaskedAccount(AdapterResponseInterface $response)
    {
        $maskedAccount = $response->getData(AdapterResponseInterface::MASKED_ACCOUNT);
        return empty($maskedAccount) ? 'XXXX' : substr($maskedAccount, -4);
    }

    /**
     * Get payment extension attributes
     * @param InfoInterface $payment
     * @return OrderPaymentExtensionInterface
     */
    private function getExtensionAttributes(InfoInterface $payment)
    {
        $extensionAttributes = $payment->getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->paymentExtensionFactory->create();
            $payment->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }
}
