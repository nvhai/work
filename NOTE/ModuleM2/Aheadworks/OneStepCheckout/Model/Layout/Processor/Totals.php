<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Layout\Processor;

use Aheadworks\OneStepCheckout\Model\Layout\CrossMerger;
use Aheadworks\OneStepCheckout\Model\Layout\DefinitionFetcher;
use Aheadworks\OneStepCheckout\Model\Layout\Processor\Totals\Sorter;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Class Totals
 * @package Aheadworks\OneStepCheckout\Model\Layout\Processor
 */
class Totals implements LayoutProcessorInterface
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
     * @var CrossMerger
     */
    private $merger;

    /**
     * @var Sorter
     */
    private $sorter;

    /**
     * @param DefinitionFetcher $definitionFetcher
     * @param ArrayManager $arrayManager
     * @param CrossMerger $merger
     * @param Sorter $sorter
     */
    public function __construct(
        DefinitionFetcher $definitionFetcher,
        ArrayManager $arrayManager,
        CrossMerger $merger,
        Sorter $sorter
    ) {
        $this->definitionFetcher = $definitionFetcher;
        $this->arrayManager = $arrayManager;
        $this->merger = $merger;
        $this->sorter = $sorter;
    }

    /**
     * {@inheritdoc}
     */
    public function process($jsLayout)
    {
        $totalsPath = 'components/checkout/children/totals/children';

        $totalsLayout = $this->arrayManager->get($totalsPath, $jsLayout);
        if ($totalsLayout) {
            $totalsLayout = $this->addTotals($totalsLayout);
            $totalsLayout = $this->sorter->sort($totalsLayout);
            $jsLayout = $this->arrayManager->set($totalsPath, $jsLayout, $totalsLayout);
        }
        return $jsLayout;
    }

    /**
     * Add totals definitions
     *
     * @param array $layout
     * @return array
     */
    private function addTotals(array $layout)
    {
        $path = '//referenceBlock[@name="checkout.root"]/arguments/argument[@name="jsLayout"]'
            . '/item[@name="components"]/item[@name="checkout"]/item[@name="children"]'
            . '/item[@name="sidebar"]/item[@name="children"]/item[@name="summary"]'
            . '/item[@name="children"]/item[@name="totals"]/item[@name="children"]';
        $layout = $this->merger->merge(
            $layout,
            $this->definitionFetcher->fetchArgs('checkout_index_index', $path)
        );
        return $layout;
    }
}
