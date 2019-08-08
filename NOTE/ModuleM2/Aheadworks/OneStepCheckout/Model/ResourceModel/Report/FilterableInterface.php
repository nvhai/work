<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\ResourceModel\Report;

/**
 * Interface FilterableInterface
 * @package Aheadworks\OneStepCheckout\Model\ResourceModel\Report
 */
interface FilterableInterface
{
    /**
     * Add customer group filter
     *
     * @param int $groupId
     * @return $this
     */
    public function addCustomerGroupIdFilter($groupId);

    /**
     * Add store filter
     *
     * @param int $storeId
     * @return $this
     */
    public function addStoreIdFilter($storeId);

    /**
     * Add store group filter
     *
     * @param int $storeGroupId
     * @return $this
     */
    public function addStoreGroupIdFilter($storeGroupId);

    /**
     * Add website filter
     *
     * @param int $websiteId
     * @return $this
     */
    public function addWebsiteIdFilter($websiteId);

    /**
     * Add period filter
     *
     * @param string $periodFrom
     * @param string $periodTo
     * @return $this
     */
    public function addPeriodFilter($periodFrom, $periodTo);
}
