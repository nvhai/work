<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\ResourceModel\Report;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class AbandonedCheckout
 * @package Aheadworks\OneStepCheckout\Model\ResourceModel\Report
 */
class AbandonedCheckout extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('aw_osc_report_abandoned_checkouts_index', 'index_id');
    }

    /**
     * Fetch min available date
     *
     * @return string
     * @throws LocalizedException
     */
    public function fetchMinDate()
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(
                ['main_table' => $this->getMainTable()],
                ['min' => new \Zend_Db_Expr('MIN(period)')]
            );
        return $connection->fetchOne($select);
    }
}
