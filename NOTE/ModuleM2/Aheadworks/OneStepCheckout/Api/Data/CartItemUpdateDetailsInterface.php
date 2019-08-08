<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface CartItemUpdateDetailsInterface
 * @package Aheadworks\OneStepCheckout\Api\Data
 */
interface CartItemUpdateDetailsInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const CART_DETAILS = 'cart_details';
    const PAYMENT_DETAILS = 'payment_details';
    /**#@-*/

    /**
     * Get cart details
     *
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function getCartDetails();

    /**
     * Set cart details
     *
     * @param \Magento\Quote\Api\Data\CartInterface $cartDetails
     * @return $this
     */
    public function setCartDetails($cartDetails);

    /**
     * Get payment details
     *
     * @return \Magento\Checkout\Api\Data\PaymentDetailsInterface
     */
    public function getPaymentDetails();

    /**
     * Set payment details
     *
     * @param \Magento\Checkout\Api\Data\PaymentDetailsInterface $paymentDetails
     * @return $this
     */
    public function setPaymentDetails($paymentDetails);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\OneStepCheckout\Api\Data\CartItemUpdateDetailsExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\OneStepCheckout\Api\Data\CartItemUpdateDetailsExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\OneStepCheckout\Api\Data\CartItemUpdateDetailsExtensionInterface $extensionAttributes
    );
}
