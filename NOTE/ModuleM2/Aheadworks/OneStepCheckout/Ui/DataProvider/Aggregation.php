<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Ui\DataProvider;

use Aheadworks\OneStepCheckout\Model\Report\Source\Aggregation as AggregationSource;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Class Aggregation
 * @package Aheadworks\OneStepCheckout\Ui\DataProvider
 */
class Aggregation
{
    /**
     * Request field name
     */
    const REQUEST_FIELD_NAME = 'aggregated_by';

    /**
     * Session param key
     */
    const SESSION_KEY = 'aw_osc_aggregated_by';

    /**
     * Default aggregation
     */
    const DEFAULT_AGGREGATION = AggregationSource::WEEK;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * @param RequestInterface $request
     * @param SessionManagerInterface $session
     */
    public function __construct(
        RequestInterface $request,
        SessionManagerInterface $session
    ) {
        $this->request = $request;
        $this->session = $session;
    }

    /**
     * Get current aggregation
     *
     * @return string
     */
    public function getAggregation()
    {
        $aggregationType = self::DEFAULT_AGGREGATION;

        $requestParamValue = $this->request->getParam(self::REQUEST_FIELD_NAME);
        if ($requestParamValue) {
            $aggregationType = $requestParamValue;
        } else {
            $sessionDataValue = $this->session->getData(self::SESSION_KEY);
            if ($sessionDataValue) {
                $aggregationType = $sessionDataValue;
            }
        }
        $this->session->setData(self::SESSION_KEY, $aggregationType);

        return $aggregationType;
    }
}
