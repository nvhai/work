<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model;

use Aheadworks\OneStepCheckout\Api\CheckoutSectionsManagementInterface;
use Aheadworks\OneStepCheckout\Api\GuestCheckoutSectionsManagementInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Class GuestCheckoutSectionsManagement
 * @package Aheadworks\OneStepCheckout\Model
 */
class GuestCheckoutSectionsManagement implements GuestCheckoutSectionsManagementInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var CheckoutSectionsManagementInterface
     */
    private $sectionManagement;

    /**
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param CheckoutSectionsManagementInterface $sectionManagement
     */
    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        CheckoutSectionsManagementInterface $sectionManagement
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->sectionManagement = $sectionManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionsDetails(
        $cartId,
        $sections,
        AddressInterface $shippingAddress,
        AddressInterface $billingAddress = null
    ) {
        $quoteIdMask = $this->quoteIdMaskFactory->create()
            ->load($cartId, 'masked_id');
        return $this->sectionManagement->getSectionsDetails(
            $quoteIdMask->getQuoteId(),
            $sections,
            $shippingAddress,
            $billingAddress
        );
    }
}
