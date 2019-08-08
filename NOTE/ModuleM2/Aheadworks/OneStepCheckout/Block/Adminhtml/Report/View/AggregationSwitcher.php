<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Block\Adminhtml\Report\View;

use Aheadworks\OneStepCheckout\Model\Report\Source\Aggregation as AggregationSource;
use Aheadworks\OneStepCheckout\Ui\DataProvider\Aggregation;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

/**
 * Class AggregationSwitcher
 * @package Aheadworks\OneStepCheckout\Block\Adminhtml\Report\View
 */
class AggregationSwitcher extends Template
{
    /**
     * @inheritdoc
     */
    protected $_template = 'Aheadworks_OneStepCheckout::report/view/aggregation_switcher.phtml';

    /**
     * @var AggregationSource
     */
    private $aggregationSource;

    /**
     * @var Aggregation
     */
    private $aggregation;

    /**
     * @param Context $context
     * @param AggregationSource $aggregationSource
     * @param Aggregation $aggregation
     * @param array $data
     */
    public function __construct(
        Context $context,
        AggregationSource $aggregationSource,
        Aggregation $aggregation,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->aggregationSource = $aggregationSource;
        $this->aggregation = $aggregation;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->aggregationSource->toOptionArray();
    }

    /**
     * Get current aggregation
     *
     * @return string
     */
    public function getAggregation()
    {
        return $this->aggregation->getAggregation();
    }

    /**
     * Get aggregated by url
     *
     * @param string $aggregation
     * @return string
     */
    public function getAggregatedByUrl($aggregation)
    {
        return $this->getUrl(
            '*/*/*',
            ['_query' => [Aggregation::REQUEST_FIELD_NAME => $aggregation], '_current' => true]
        );
    }
}
