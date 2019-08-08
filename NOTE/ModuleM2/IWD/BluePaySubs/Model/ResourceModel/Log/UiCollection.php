<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Model\ResourceModel\Log;

use IWD\BluePaySubs\Setup\InstallSchema;

/**
 * UiCollection Class
 */
class UiCollection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * Init collection select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        // Join the subscription table
        $this->join(
            [
                InstallSchema::TABLE_IWD_BLUEPAY_SUBS => $this->getTable(InstallSchema::TABLE_IWD_BLUEPAY_SUBS)
            ],
            InstallSchema::TABLE_IWD_BLUEPAY_SUBS . '.entity_id=main_table.subs_id',
            [
                'name' => 'description',
                'store_id',
            ]
        );

        return $this;
    }
}
