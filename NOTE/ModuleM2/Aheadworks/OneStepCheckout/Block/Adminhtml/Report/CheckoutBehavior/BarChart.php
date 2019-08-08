<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Block\Adminhtml\Report\CheckoutBehavior;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

/**
 * Class BarChart
 *
 * @method string getTitle()
 * @method string getDescription()
 * @method array getFetchCriteria()
 *
 * @package Aheadworks\OneStepCheckout\Block\Adminhtml\Report\CheckoutBehavior
 */
class BarChart extends Template
{
    /**
     * inheritdoc
     */
    protected $_template = 'Aheadworks_OneStepCheckout::report/checkout_behavior/bar_chart.phtml';

    /**
     * @var Provider
     */
    private $provider;

    /**
     * @var Formatter
     */
    private $formatter;

    /**
     * @param Context $context
     * @param Provider $provider
     * @param Formatter $formatter
     * @param array $data
     */
    public function __construct(
        Context $context,
        Provider $provider,
        Formatter $formatter,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->provider = $provider;
        $this->formatter = $formatter;
    }

    /**
     * Get value
     *
     * @return int
     */
    public function getValue()
    {
        $items = $this->provider->findItems($this->getFetchCriteria());
        if (count($items) > 0) {
            return (int)$items[0]['completed'];
        }
        return 0;
    }

    /**
     * Get percentage
     *
     * @return float
     */
    public function getPercentage()
    {
        $items = $this->provider->findItems($this->getFetchCriteria());
        if (count($items) > 0) {
            return (float)$items[0]['completed_percent'];
        }
        return 0;
    }

    /**
     * Format percents
     *
     * @param float $value
     * @return string
     */
    public function formatPercents($value)
    {
        return $this->formatter->formatPercents($value);
    }
}
