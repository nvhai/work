<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Helper;

use IWD\BluePay\Gateway\Response\PaymentDetailsHandler;
use IWD\BluePay\Api\AdapterResponseInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use IWD\BluePay\Api\Data\PaymentTokenFactoryInterface;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Class Vault
 * @package IWD\BluePaySubs\Helper
 */
class Vault extends AbstractHelper
{
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Sales\Api\OrderPaymentRepositoryInterface
     */
    protected $orderPaymentRepository;

    /**
     * @var PaymentTokenFactoryInterface
     */
    protected $paymentTokenFactory;

    /**
     * @var PaymentTokenRepositoryInterface
     */
    protected $paymentTokenRepository;

    /**
     * @var OrderPaymentExtensionInterfaceFactory
     */
    protected $paymentExtensionFactory;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var \IWD\BluePay\Helper\Json
     */
    protected $serializer;

    /**
     * @var array
     */
    protected $tokenbasePaymentMethods = [
        'iwd_bluepay_cc_vault'
    ];

    /**
     * Vault constructor.
     * @param Context $context
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderPaymentRepositoryInterface $orderPaymentRepository
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository
     * @param PaymentTokenFactoryInterface $paymentTokenFactory
     * @param OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory
     * @param EncryptorInterface $encryptor
     * @param \IWD\BluePay\Helper\Json|null $serializer
     */
    public function __construct(
        Context $context,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderPaymentRepositoryInterface $orderPaymentRepository,
        PaymentTokenRepositoryInterface $paymentTokenRepository,
        OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory,
        EncryptorInterface $encryptor,
        PaymentTokenFactoryInterface $paymentTokenFactory,
        \IWD\BluePay\Helper\Json $serializer = null
    ) {
        parent::__construct($context);

        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderPaymentRepository = $orderPaymentRepository;
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->paymentExtensionFactory = $paymentExtensionFactory;
        $this->encryptor = $encryptor;
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\IWD\BluePay\Helper\Json::class);
    }

    /**
     * Determine whether the given quote is TokenBase or not.
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return bool
     */
    public function isQuoteTokenBase(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        if (in_array($quote->getPayment()->getMethod(), $this->tokenbasePaymentMethods)) {
            return true;
        }

        return false;
    }

    /**
     * Get the token for the given quote
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return PaymentTokenInterface
     */
    public function getQuoteToken(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        $token = null;
        $publicHash = $quote->getPayment()->getAdditionalInformation('public_hash');

        if (!empty($publicHash)) {
            $token = $this->getTokenByHash($publicHash);
        }

        return $token;
    }

    /**
     * @param array $params
     * @return PaymentTokenInterface[]
     */
    public function searchTokens(array $params)
    {
        foreach ($params as $field => $value) {
            $this->searchCriteriaBuilder->addFilter($field, $value);
        }
        $searchCriteria = $this->searchCriteriaBuilder->create();

        return $this->paymentTokenRepository->getList($searchCriteria)->getItems();
    }

    /**
     * @param $hash
     * @return PaymentTokenInterface|null
     */
    public function getTokenByHash($hash)
    {
        try {
            /** @var PaymentTokenInterface[] $tokens */
            $tokens = $this->searchTokens(['public_hash' => $hash]);
            if (!empty($tokens)) {
                $token = array_shift($tokens);
                if (!empty($token) && $token->getId()) {
                    return $token;
                }
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    /**
     * @param array $params
     * @return OrderPaymentInterface[]
     */
    public function searchOrderPayment(array $params)
    {
        foreach ($params as $field => $value) {
            $this->searchCriteriaBuilder->addFilter($field, $value);
        }
        $searchCriteria = $this->searchCriteriaBuilder->create();

        return $this->orderPaymentRepository->getList($searchCriteria)->getItems();
    }

    /**
     * Get label for the current token.
     *
     * @param PaymentTokenInterface $token
     * @return string
     */
    public function getTokenLabel(PaymentTokenInterface $token)
    {
        $details = $this->decodeDetailsFromJSON($token->getTokenDetails());
        return __(
            '%1 XXXX-%2',
            isset($details['type']) ? $details['type'] : '',
            isset($details['maskedCC']) ? $details['maskedCC'] : ''
        );
    }

    /**
     * @param $transactionId
     * @param InfoInterface $payment
     * @return PaymentTokenInterface|mixed|null
     */
    public function getVaultPaymentToken($transactionId, InfoInterface $payment)
    {
        // Check token existing in gateway response
        if (empty($transactionId)) {
            return null;
        }
        try {
            $paymentToken = $this->generateToken($transactionId, $payment);
            $extensionAttributes = $this->getExtensionAttributes($payment);
            $extensionAttributes->setVaultPaymentToken($paymentToken);
        } catch (\Exception $e) {
            $paymentToken = null;
        }

        return $paymentToken;
    }

    /**
     * @param $transactionId
     * @param InfoInterface $payment
     * @return PaymentTokenInterface|mixed
     * @throws LocalizedException
     * @throws \Exception
     */
    public function generateToken($transactionId, InfoInterface $payment)
    {
        /** @var PaymentTokenInterface[] $tokens */
        $tokens = $this->searchTokens([PaymentTokenInterface::GATEWAY_TOKEN => $transactionId]);
        if (!empty($tokens)) {
            $token = array_shift($tokens);
            if (!empty($token) && $token->getId()) {
                return $token;
            }
        }
        /** @var PaymentTokenInterface $paymentToken */
        $paymentToken = $this->paymentTokenFactory->create();
        $paymentToken->setGatewayToken($transactionId);
        $details = [];
        if ($payment->getData(OrderPaymentInterface::ECHECK_TYPE) == 'CC') {
            $paymentToken->setType(PaymentTokenFactoryInterface::TOKEN_TYPE_CREDIT_CARD)
                ->setExpiresAt($this->getExpirationDate($payment));
            $details = [
                'type' => $payment->getData(OrderPaymentInterface::CC_TYPE),
                'expirationDate' => $this->getExpirationDate($payment, 'm/Y')
            ];
        }
        else {
            $paymentToken->setType(PaymentTokenFactoryInterface::TOKEN_TYPE_ACCOUNT);
        }
        $details['maskedCC'] = $this->getMaskedAccount(
            $payment->getAdditionalInformation(PaymentDetailsHandler::PAYMENT_ACCOUNT)
        );
        $paymentToken->setTokenDetails($this->convertDetailsToJSON($details));

        return $paymentToken;
    }

    /**
     * Generate vault payment public hash
     *
     * @param PaymentTokenInterface $paymentToken
     * @return string
     */
    public function generatePublicHash(PaymentTokenInterface $paymentToken)
    {
        $hashKey = $paymentToken->getGatewayToken();
        if ($paymentToken->getCustomerId()) {
            $hashKey = $paymentToken->getCustomerId();
        }

        $hashKey .= $paymentToken->getPaymentMethodCode()
            . $paymentToken->getType()
            . $paymentToken->getTokenDetails();

        return $this->encryptor->getHash($hashKey);
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

        return $expDate->format($format);
    }

    /**
     * Convert payment token details to JSON
     * @param array $details
     * @return string
     */
    public function convertDetailsToJSON($details)
    {
        $json = $this->serializer->serialize($details);
        return $json ? $json : '{}';
    }

    /**
     * @param $details
     * @return array|bool|float|int|mixed|null|string
     */
    public function decodeDetailsFromJSON($details)
    {
        $data = $this->serializer->unserialize($details);
        return $data ? $data : '';
    }

    /**
     * @param InfoInterface $payment
     * @param PaymentTokenInterface $token
     * @return $this
     */
    public function addAdditionalDataToPayment(InfoInterface $payment, PaymentTokenInterface $token)
    {
        $details = $this->decodeDetailsFromJSON($token->getTokenDetails());
        if($details) {
            $payment->setData('cc_type', isset($details['type']) ? $details['type'] : '');
            $payment->setData('cc_last_4', isset($details['maskedCC']) ? $details['maskedCC'] : '');
            if(isset($details['expirationDate'])) {
                $date = explode('/', $details['expirationDate']);
                if (count($date) == 2) {
                    $payment->setData('cc_exp_month', $date[0]);
                    $payment->setData('cc_exp_year', $date[1]);
                }
            }
        }

        return $this;
    }

    /**
     * Get payment extension attributes
     * @param InfoInterface $payment
     * @return OrderPaymentExtensionInterface
     */
    public function getExtensionAttributes(InfoInterface $payment)
    {
        $extensionAttributes = $payment->getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->paymentExtensionFactory->create();
            $payment->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }

    /**
     * @param string $accountName
     * @return bool|string
     */
    private function getMaskedAccount($accountName)
    {
        return empty($accountName) ? 'XXXX' : substr($accountName, -4);
    }
}