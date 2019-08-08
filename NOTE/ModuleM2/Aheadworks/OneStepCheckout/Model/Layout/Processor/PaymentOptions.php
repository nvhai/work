<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Layout\Processor;

use Aheadworks\OneStepCheckout\Model\Layout\CrossMerger;
use Aheadworks\OneStepCheckout\Model\Layout\DefinitionFetcher;
use Aheadworks\OneStepCheckout\Model\Layout\Processor\PaymentOptions\Filter;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Class PaymentOptions
 * @package Aheadworks\OneStepCheckout\Model\Layout\Processor
 */
class PaymentOptions implements LayoutProcessorInterface
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
     * @var Filter
     */
    private $filter;

    /**
     * @param DefinitionFetcher $definitionFetcher
     * @param ArrayManager $arrayManager
     * @param CrossMerger $merger
     * @param Filter $filter
     */
    public function __construct(
        DefinitionFetcher $definitionFetcher,
        ArrayManager $arrayManager,
        CrossMerger $merger,
        Filter $filter
    ) {
        $this->definitionFetcher = $definitionFetcher;
        $this->arrayManager = $arrayManager;
        $this->merger = $merger;
        $this->filter = $filter;
    }

    /**
     * {@inheritdoc}
     */
    public function process($jsLayout)
    {
        $paymentOptionsPath = 'components/checkout/children/payment-options/children';
        $definitionsPath = '//referenceBlock[@name="checkout.root"]/arguments/argument[@name="jsLayout"]'
            . '/item[@name="components"]/item[@name="checkout"]/item[@name="children"]'
            . '/item[@name="steps"]/item[@name="children"]/item[@name="billing-step"]'
            . '/item[@name="children"]/item[@name="payment"]/item[@name="children"]'
            . '/item[@name="afterMethods"]/item[@name="children"]';

        $paymentOptionsLayout = $this->merger->merge(
            $this->arrayManager->get($paymentOptionsPath, $jsLayout),
            $this->definitionFetcher->fetchArgs('checkout_index_index', $definitionsPath)
        );
        $paymentOptionsLayout = $this->filter->filter($paymentOptionsLayout);
        return $this->arrayManager->set($paymentOptionsPath, $jsLayout, $paymentOptionsLayout);
    }
}
