<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Ui\DataProvider\DefaultFilter;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Class StoreId
 * @package Aheadworks\OneStepCheckout\Ui\DataProvider\DefaultFilter
 */
class StoreId
{
    /**
     * Request field name
     */
    const REQUEST_FIELD_NAME = 'store_id';

    /**
     * Session param key
     */
    const SESSION_KEY = 'aw_osc_store_id';

    /**
     * Default filter value
     */
    const DEFAULT_VALUE = 0;

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
     * Get filter value
     *
     * @return int
     */
    public function getValue()
    {
        $value = self::DEFAULT_VALUE;

        $requestParamValue = $this->request->getParam(self::REQUEST_FIELD_NAME);
        if ($requestParamValue !== null) {
            $value = $requestParamValue;
        } else {
            $sessionDataValue = $this->session->getData(self::SESSION_KEY);
            if ($sessionDataValue !== null) {
                $value = $sessionDataValue;
            }
        }
        $this->session->setData(self::SESSION_KEY, $value);

        return $value;
    }
}
