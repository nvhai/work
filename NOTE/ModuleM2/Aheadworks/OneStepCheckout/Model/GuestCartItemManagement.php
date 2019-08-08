<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model;

use Aheadworks\OneStepCheckout\Api\CartItemManagementInterface;
use Aheadworks\OneStepCheckout\Api\GuestCartItemManagementInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Api\Data\TotalsItemInterface;

/**
 * Class GuestCartItemManagement
 * @package Aheadworks\OneStepCheckout\Model
 */
class GuestCartItemManagement implements GuestCartItemManagementInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var CartItemManagementInterface
     */
    private $cartItemManagement;

    /**
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param CartItemManagementInterface $cartItemManagement
     */
    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        CartItemManagementInterface $cartItemManagement
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->cartItemManagement = $cartItemManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($itemId, $cartId)
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()
            ->load($cartId, 'masked_id');
        return $this->cartItemManagement->remove($itemId, $quoteIdMask->getQuoteId());
    }

    /**
     * {@inheritdoc}
     */
    public function update(TotalsItemInterface $item, $cartId)
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()
            ->load($cartId, 'masked_id');
        return $this->cartItemManagement->update($item, $quoteIdMask->getQuoteId());
    }
}
