<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Layout\Processor;

use Aheadworks\OneStepCheckout\Model\Layout\DefinitionFetcher;
use Aheadworks\OneStepCheckout\Model\Layout\SelectiveMerger;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Class CartItemDetails
 * @package Aheadworks\OneStepCheckout\Model\Layout\Processor
 */
class CartItemDetails implements LayoutProcessorInterface
{
    /**
     * @var DefinitionFetcher
     */
    private $definitionFetcher;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var SelectiveMerger
     */
    private $merger;

    /**
     * @param DefinitionFetcher $definitionFetcher
     * @param ArrayManager $arrayManager
     * @param SelectiveMerger $merger
     */
    public function __construct(
        DefinitionFetcher $definitionFetcher,
        ArrayManager $arrayManager,
        SelectiveMerger $merger
    ) {
        $this->definitionFetcher = $definitionFetcher;
        $this->arrayManager = $arrayManager;
        $this->merger = $merger;
    }

    /**
     * {@inheritdoc}
     */
    public function process($jsLayout)
    {
        $detailsPath = 'components/checkout/children/cart-items/children/details/children';
        $definitionsPath = '//referenceBlock[@name="checkout.root"]/arguments/argument[@name="jsLayout"]'
            . '/item[@name="components"]/item[@name="checkout"]/item[@name="children"]'
            . '/item[@name="sidebar"]/item[@name="children"]/item[@name="summary"]'
            . '/item[@name="children"]/item[@name="cart_items"]/item[@name="children"]'
            . '/item[@name="details"]/item[@name="children"]';

        $detailsLayout = $this->merger->merge(
            $this->arrayManager->get($detailsPath, $jsLayout),
            $this->definitionFetcher->fetchArgs('checkout_index_index', $definitionsPath),
            ['subtotal']
        );
        return $this->arrayManager->set($detailsPath, $jsLayout, $detailsLayout);
    }
}
