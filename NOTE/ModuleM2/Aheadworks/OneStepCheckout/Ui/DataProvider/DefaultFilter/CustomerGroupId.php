<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Ui\DataProvider\DefaultFilter;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Class CustomerGroupId
 * @package Aheadworks\OneStepCheckout\Ui\DataProvider\DefaultFilter
 */
class CustomerGroupId
{
    /**
     * Request field name
     */
    const REQUEST_FIELD_NAME = 'customer_group_id';

    /**
     * Session param key
     */
    const SESSION_KEY = 'aw_osc_customer_group_id';

    /**
     * @var GroupManagementInterface
     */
    private $groupManagement;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * @param GroupManagementInterface $groupManagement
     * @param RequestInterface $request
     * @param SessionManagerInterface $session
     */
    public function __construct(
        GroupManagementInterface $groupManagement,
        RequestInterface $request,
        SessionManagerInterface $session
    ) {
        $this->groupManagement = $groupManagement;
        $this->request = $request;
        $this->session = $session;
    }

    /**
     * todo: consider getValue() method name
     * Get current customer group Id
     *
     * @return int
     */
    public function getCustomerGroupId()
    {
        $customerGroupId = null;
        $requestParamValue = $this->request->getParam(self::REQUEST_FIELD_NAME);
        if ($requestParamValue !== null) {
            $customerGroupId = $requestParamValue;
        } else {
            $sessionDataValue = $this->session->getData(self::SESSION_KEY);
            if ($sessionDataValue !== null) {
                $customerGroupId = $sessionDataValue;
            }
        }
        if ($customerGroupId === null) {
            $allGroup = $this->groupManagement->getAllCustomersGroup();
            $customerGroupId = $allGroup->getId();
        }
        $this->session->setData(self::SESSION_KEY, $customerGroupId);

        return $customerGroupId;
    }
}
