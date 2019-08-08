<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model;

use Aheadworks\OneStepCheckout\Api\CartItemOptionsManagementInterface;
use Aheadworks\OneStepCheckout\Api\GuestCartItemOptionsManagementInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Class GuestCartItemOptionsManagement
 * @package Aheadworks\OneStepCheckout\Model
 */
class GuestCartItemOptionsManagement implements GuestCartItemOptionsManagementInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var CartItemOptionsManagementInterface
     */
    private $cartItemOptionsManagement;

    /**
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param CartItemOptionsManagementInterface $cartItemOptionsManagement
     */
    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        CartItemOptionsManagementInterface $cartItemOptionsManagement
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->cartItemOptionsManagement = $cartItemOptionsManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function update($itemId, $cartId, $options)
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()
            ->load($cartId, 'masked_id');
        return $this->cartItemOptionsManagement->update(
            $itemId,
            $quoteIdMask->getQuoteId(),
            $options
        );
    }
}
