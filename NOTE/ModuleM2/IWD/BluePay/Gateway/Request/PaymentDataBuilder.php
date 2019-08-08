<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePay\Gateway\Request;

use IWD\BluePay\Gateway\Config\Config;
use IWD\BluePay\Gateway\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Payment Data Builder
 */
class PaymentDataBuilder implements BuilderInterface
{
    use Formatter;

    /**
     * Payment info block
     */
    const PAYMENT = 'payment';

    /**
     * The billing amount of the request. This value must be greater than 0,
     * and must match the currency format of the merchant account.
     */
    const AMOUNT = 'amount';

    const TRANS_ID = 'transaction_id';

    /**
     * Card number
     */
    const CARD_NUMBER = 'cardNumber';

    /**
     * Expiration date
     */
    const CARD_EXPIRE = 'cardExpire';

    /**
     * CVV2 code
     */
    const CVV2 = 'cvv2';

    const PAYMENT_TYPE = 'paymentType';

    const ECHECK_ACCOUNT_TYPE = 'accountType';

    const ECHECK_ACCOUNT_NUMBER = 'accountNumber';

    const ECHECK_ROUTING_NUMBER = 'routingNumber';

    /**
     * The merchant account ID used to create a transaction.
     * Currency is also determined by merchant account ID.
     * If no merchant account ID is specified, will use your default merchant account.
     */
    const MERCHANT_ACCOUNT_ID = 'merchantAccountId';

    /**
     * Order ID
     */
    const ORDER_ID = 'orderId';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var array
     */
    private $paymentKeyMap = [
        self::CARD_EXPIRE => [
            OrderPaymentInterface::CC_EXP_MONTH => 2,
            OrderPaymentInterface::CC_EXP_YEAR => 2
        ],
        self::CARD_NUMBER => "cc_number",
        self::CVV2 => "cc_cid",
        self::PAYMENT_TYPE => OrderPaymentInterface::ECHECK_TYPE,
        self::ECHECK_ACCOUNT_TYPE => OrderPaymentInterface::ECHECK_ACCOUNT_TYPE,
        self::ECHECK_ACCOUNT_NUMBER => OrderPaymentInterface::ECHECK_ACCOUNT_NAME,
        self::ECHECK_ROUTING_NUMBER => OrderPaymentInterface::ECHECK_ROUTING_NUMBER
    ];

    /**
     * Constructor
     *
     * @param Config $config
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        Config $config,
        SubjectReader $subjectReader
    ) {
        $this->config = $config;
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $payment = $paymentDO->getPayment();
        $order = $paymentDO->getOrder();

        $result = [
            self::PAYMENT => $this->_collectPaymentInfo($payment),
            self::AMOUNT => $this->formatPrice($this->subjectReader->readAmount($buildSubject)),
            self::TRANS_ID => $this->_getTransactionId($payment),
            self::ORDER_ID => $order->getOrderIncrementId()
        ];

        return $result;
    }

    /**
     * @return array
     */
    protected function _collectPaymentInfo(OrderPaymentInterface $payment)
    {
        $result = [];
        $data = $payment->getData();
        foreach ($this->paymentKeyMap as $requestKey => $map) {
            $value = '';
            if(is_array($map)) {
                foreach ($map as $mapField => $length) {
                    if(isset($data[$mapField])) {
                        $num = substr($data[$mapField], -$length);
                        $value .= sprintf("%0{$length}d", $num);
                    }
                }
            }
            elseif(isset($data[$map])) {
                $value = $data[$map];
            }
            $result[$requestKey] = $value;
        }

        return $result;
    }

    /**
     * @param OrderPaymentInterface $payment
     * @return string|string[]
     */
    protected function _getTransactionId(OrderPaymentInterface $payment)
    {
        $transId = $payment->getAdditionalInformation(OrderPaymentInterface::CC_TRANS_ID);
        if(empty($transId)) {
            $extensionAttributes = $payment->getExtensionAttributes();
            if($vaultPayment = $extensionAttributes->getVaultPaymentToken()) {
                $transId = $vaultPayment->getGatewayToken();
            }
        }

        return $transId;
    }
}
