<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\ResourceModel\Report\AbandonedCheckout;

use Aheadworks\OneStepCheckout\Model\ResourceModel\Report\AbstractCollection;
use Aheadworks\OneStepCheckout\Model\ResourceModel\Report\AbandonedCheckout as AbandonedCheckoutResource;
use Magento\Framework\DataObject;

/**
 * Class Collection
 * @package Aheadworks\OneStepCheckout\Model\ResourceModel\Report\AbandonedCheckout
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init(DataObject::class, AbandonedCheckoutResource::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function initColumns()
    {
        $abandonedCheckoutsCountExpr = new \Zend_Db_Expr('SUM(COALESCE(main_table.abandoned_checkouts_count, 0))');
        $completedCheckoutsCountExpr = new \Zend_Db_Expr('SUM(COALESCE(main_table.completed_checkouts_count, 0))');
        $this->columns = [
            'period_from' => 'aggregation_table.period_from',
            'period_to' => 'aggregation_table.period_to',
            'abandoned_checkouts_count' => $abandonedCheckoutsCountExpr,
            'abandoned_checkouts_revenue' => new \Zend_Db_Expr(
                'SUM(COALESCE(main_table.abandoned_checkouts_revenue, 0))'
            ),
            'completed_checkouts_count' => $completedCheckoutsCountExpr,
            'completed_checkouts_revenue' => new \Zend_Db_Expr(
                'SUM(COALESCE(main_table.completed_checkouts_revenue, 0))'
            ),
            'conversion' => new \Zend_Db_Expr(
                'COALESCE((100 / (' . $abandonedCheckoutsCountExpr . ' + ' . $completedCheckoutsCountExpr
                . ')) * ' . $completedCheckoutsCountExpr . ', 0)'
            )
        ];
    }
}
