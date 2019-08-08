<?php

namespace IWD\BluePay\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Stdlib\DateTime\DateTime;
use IWD\BluePay\Gateway\Config\Config;
use IWD\BluePay\Api\Data\CardInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface as orderCollectionFactory;

/**
 * Class Card
 * @package IWD\BluePay\Model
 */
class Card extends AbstractModel implements CardInterface
{
    /**
     * Flag is save new CC
     */
    const IS_SAVE_CC = 'cc_save';

    /**
     * Saved CC id
     */
    const SAVED_CC_ID = 'cc_id';

    /**
     * Last 4 credit card numbers
     */
    const CARD_LAST_4 = 'cc_number';

    /**
     * CC Type Mapper
     */
    const CC_TYPES_MAPPER = 'cctypes_mapper';

    /**
     * Account Type
     */
    const ACCOUNT_TYPE = 'account_type';

    /**
     * Routing Number
     */
    const ROUTING_NUMBER = 'routing_number';

    /**
     * Account Number
     */
    const ACCOUNT_NUMBER = 'account_number';

    /**
     * Name On Account
     */
    const NAME_ON_ACCOUNT = 'name_on_account';

    /**
     * eCheck Type
     */
    const ECHECK_TYPE = 'echeck_type';

    /**
     * Bank Name
     */
    const BANK_NAME = 'bank_name';

    /**
     * Opaque Descriptor
     */
    const OPAQUE_DESCRIPTION = 'opaque_descriptor';

    /**
     * Opaque Value
     */
    const OPAQUE_VALUE = 'opaque_value';

    /**
     * Opaque Number
     */
    const OPAQUE_NUMBER = 'opaque_number';

    /**
     * @var orderCollectionFactory
     */
    private $orderCollectionFactory;

    private $dateTime;

    private $config;

    /**
     * Card constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param orderCollectionFactory $orderCollectionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        OrderInterfaceFactory $orderCollectionFactory,
        DateTime $dateTime,
        Config $config,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->dateTime = $dateTime;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init('IWD\BluePay\Model\ResourceModel\Card');
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerEmail()
    {
        return $this->getData(self::CUSTOMER_EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function getMaskedAccount()
    {
        return $this->getData(self::MASKED_ACCOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function getTransId()
    {
        return $this->getData(self::TRANS_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getExpirationDate($format = false)
    {
        if ($format) {
            $expirationDay = $this->getData(self::EXPIRATION_DATE);
            return empty($expirationDay) ? 'MM/YYYY' : date('m/Y', strtotime($expirationDay));
        }

        return $this->getData(self::EXPIRATION_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentId()
    {
        return $this->getData(self::PAYMENT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerIp()
    {
        return $this->getData(self::CUSTOMER_IP);
    }

    /**
     * {@inheritdoc}
     */
    public function getHash()
    {
        return $this->getData(self::HASH);
    }

    /**
     * {@inheritdoc}
     */
    public function getAdditionalData($key = null)
    {
        if (empty($key) && !is_string($key)) {
            return $this->getData(self::ADDITIONAL_DATA);
        }
        $data = json_decode($this->getData(self::ADDITIONAL_DATA), true);

        return isset($data[$key]) ? $data[$key]  : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerEmail($email)
    {
        return $this->setData(self::CUSTOMER_EMAIL, $email);
    }

    /**
     * {@inheritdoc}
     */
    public function setMaskedAccount($maskedAccount)
    {
        return $this->setData(self::MASKED_ACCOUNT, $maskedAccount);
    }

    /**
     * {@inheritdoc}
     */
    public function setTransId($transId)
    {
        return $this->setData(self::TRANS_ID, $transId);
    }

    /**
     * {@inheritdoc}
     */
    public function setExpirationDate($expirationDate)
    {
        if(strlen($expirationDate) == 4) {
            $month = intval(substr($expirationDate, 0, 2));
            $year = 2000 + intval(substr($expirationDate, -2));
            $expirationDate = $this->dateTime->date(null, "$year-$month");
        }
        return $this->setData(self::EXPIRATION_DATE, $expirationDate);
    }

    /**
     * {@inheritdoc}
     */
    public function setPaymentId($paymentId)
    {
        return $this->setData(self::PAYMENT_ID, $paymentId);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerIp($ip)
    {
        return $this->setData(self::CUSTOMER_IP, $ip);
    }

    /**
     * {@inheritdoc}
     */
    public function setHash($hash)
    {
        return $this->setData(self::HASH, $hash);
    }

    /**
     * {@inheritdoc}
     */
    public function setAdditionalData($additionalData)
    {
        return $this->setData(self::ADDITIONAL_DATA, json_encode($additionalData));
    }

    /**
     * {@inheritdoc}
     */
    public function addAdditionalData($key, $value = null)
    {
        $additionalData = $this->getData(self::ADDITIONAL_DATA);
        if (is_string($additionalData)) {
            $additionalData = json_decode($additionalData, true);
        }

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $additionalData[$k] = $v;
            }
        } else {
            $additionalData[$key] = $value;
        }

        return $this->setAdditionalData($additionalData);
    }

    /**
     * {@inheritdoc}
     */
    public function unsetAdditionalData($key)
    {
        $additionalData = $this->getData(self::ADDITIONAL_DATA);
        if (is_string($additionalData)) {
            $additionalData = json_decode($additionalData, true);
        }

        if (is_array($additionalData)) {
            unset($additionalData[$key]);
        }

        return $this->setAdditionalData($additionalData);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_DATE, $createdAt);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_DATE, $updatedAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getCardLast4()
    {
        $ccNumber = $this->getMaskedAccount();
        return empty($ccNumber) ? 'XXXX' : substr($ccNumber, -4);
    }

    /**
     * {@inheritdoc}
     */
    public function getCardType()
    {
        $type = $this->getAdditionalData('CARD_TYPE');
        $replaced = str_replace(' ', '-', strtolower($type));
        $mapper = $this->getCcTypesMapper();

        return isset($mapper[$replaced]) ? $mapper[$replaced] : $type;
    }

    /**
     * {@inheritdoc}
     */
    public function isExpired()
    {
        return ($this->getExpirationDate() != '' && strtotime($this->getExpirationDate()) < time());
    }

    /**
     * {@inheritdoc}
     */
    public function isInUse()
    {
        $paymentMethod = \IWD\BluePay\Model\Ui\ConfigProvider::CODE;

        $collection = $this->orderCollectionFactory->create();
        $collection->addFieldToSelect([])
            ->addFieldToFilter('customer_id', $this->getCustomerId())
            ->getSelect()
            ->join(
                ['payment' => $collection->getTable("sales_order_payment")],
                'main_table.payment_id=payment.entity_id',
                []
            )->where("method='$paymentMethod'");

        return $collection->getSize() > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpirationMonth()
    {
        $expirationDay = $this->getData(self::EXPIRATION_DATE);
        return empty($expirationDay) ? '0' : date('n', strtotime($expirationDay));
    }

    /**
     * {@inheritdoc}
     */
    public function getExpirationYear()
    {
        $expirationDay = $this->getData(self::EXPIRATION_DATE);
        return empty($expirationDay) ? '0' : date('Y', strtotime($expirationDay));
    }

    /**
     * @return array|mixed
     */
    protected function getCcTypesMapper()
    {
        $result = json_decode(
            $this->config->getValue(self::CC_TYPES_MAPPER),
            true
        );

        return is_array($result) ? $result : [];
    }
}
