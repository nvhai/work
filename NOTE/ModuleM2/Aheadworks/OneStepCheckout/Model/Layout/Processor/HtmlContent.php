<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Layout\Processor;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;

/**
 * Class HtmlContent
 * @package Aheadworks\OneStepCheckout\Model\Layout\Processor
 */
class HtmlContent implements LayoutProcessorInterface
{
    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @param ArrayManager $arrayManager
     * @param LayoutInterface $layout
     */
    public function __construct(
        ArrayManager $arrayManager,
        LayoutInterface $layout
    ) {
        $this->arrayManager = $arrayManager;
        $this->layout = $layout;
    }

    /**
     * {@inheritdoc}
     */
    public function process($jsLayout)
    {
        $htmlContentPaths = ['components/checkout/children/column-top/children'];
        foreach ($htmlContentPaths as $path) {
            $blocksLayout = $this->arrayManager->get($path, $jsLayout);
            foreach ($blocksLayout as &$layout) {
                if (isset($layout['block']['class'])) {
                    /** @var BlockInterface|Template $blockInstance */
                    $blockInstance = $this->layout->createBlock($layout['block']['class']);
                    if (isset($layout['block']['template'])) {
                        $blockInstance->setTemplate($layout['block']['template']);
                    }
                    if (!isset($layout['config'])) {
                        $layout['config'] = [];
                    }
                    $layout['config']['content'] = $blockInstance->toHtml();
                }
            }
            $jsLayout = $this->arrayManager->set($path, $jsLayout, $blocksLayout);
        }

        return $jsLayout;
    }
}
