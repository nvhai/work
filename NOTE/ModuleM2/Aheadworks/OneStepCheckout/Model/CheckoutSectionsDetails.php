<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model;

use Aheadworks\OneStepCheckout\Api\Data\CheckoutSectionsDetailsInterface;
use Aheadworks\OneStepCheckout\Api\Data\CheckoutSectionsDetailsExtensionInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

/**
 * Class CheckoutSectionsDetails
 * @package Aheadworks\OneStepCheckout\Model
 */
class CheckoutSectionsDetails extends AbstractExtensibleObject implements CheckoutSectionsDetailsInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPaymentMethods()
    {
        return $this->_get(self::PAYMENT_METHODS);
    }

    /**
     * {@inheritdoc}
     */
    public function setPaymentMethods($paymentMethods)
    {
        return $this->setData(self::PAYMENT_METHODS, $paymentMethods);
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingMethods()
    {
        return $this->_get(self::SHIPPING_METHODS);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingMethods($shippingMethods)
    {
        return $this->setData(self::SHIPPING_METHODS, $shippingMethods);
    }

    /**
     * {@inheritdoc}
     */
    public function getTotals()
    {
        return $this->_get(self::TOTALS);
    }

    /**
     * {@inheritdoc}
     */
    public function setTotals($totals)
    {
        return $this->setData(self::TOTALS, $totals);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(
        CheckoutSectionsDetailsExtensionInterface $extensionAttributes
    ) {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
