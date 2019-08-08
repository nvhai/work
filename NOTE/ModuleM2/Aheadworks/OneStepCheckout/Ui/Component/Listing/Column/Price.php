<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Ui\Component\Listing\Column;

use Aheadworks\OneStepCheckout\Ui\ScopeCurrency;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Price
 * @package Aheadworks\OneStepCheckout\Ui\Component\Listing\Column
 */
class Price extends Column
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var ScopeCurrency
     */
    private $scopeCurrency;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param ScopeCurrency $scopeCurrency
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        PriceCurrencyInterface $priceCurrency,
        ScopeCurrency $scopeCurrency,
        array $components = [],
        array $data = []
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->scopeCurrency = $scopeCurrency;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $name = $this->getName();
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$name] = $this->priceCurrency->convert(
                    $item[$name],
                    null,
                    $this->scopeCurrency->getCurrencyCode()
                );
            }
        }
        return $dataSource;
    }
}
