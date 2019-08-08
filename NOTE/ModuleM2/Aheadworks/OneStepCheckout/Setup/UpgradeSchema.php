<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Setup;

use Aheadworks\OneStepCheckout\Model\Report\Aggregation;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class UpgradeSchema
 * @package Aheadworks\OneStepCheckout\Setup
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var Aggregation
     */
    private $aggregation;

    /**
     * @param Aggregation $aggregation
     */
    public function __construct(Aggregation $aggregation)
    {
        $this->aggregation = $aggregation;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->generalizationOfAggregationTables($setup);
        }

        $setup->endSetup();
    }

    /**
     * Generalization of index aggregation tables
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function generalizationOfAggregationTables(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();

        $existingTables = [
            'aw_osc_report_abandoned_checkouts_index_aggregated_by_day',
            'aw_osc_report_abandoned_checkouts_index_aggregated_by_week',
            'aw_osc_report_abandoned_checkouts_index_aggregated_by_month',
            'aw_osc_report_abandoned_checkouts_index_aggregated_by_quarter',
            'aw_osc_report_abandoned_checkouts_index_aggregated_by_year'
        ];
        foreach ($existingTables as $table) {
            $connection->dropTable($setup->getTable($table));
        }
        $connection
            ->truncateTable($setup->getTable('aw_osc_report_abandoned_checkouts_index'))
            ->truncateTable($setup->getTable('aw_osc_report_abandoned_checkouts_index_idx'));

        foreach ($this->aggregation->getAggregations() as $aggregation) {
            $this->createAggregationTable($setup, $aggregation);
        }
    }

    /**
     * Create aggregation table
     *
     * @param SchemaSetupInterface $setup
     * @param string $aggregation
     * @throws \Zend_Db_Exception
     * @return void
     */
    private function createAggregationTable(SchemaSetupInterface $setup, $aggregation)
    {
        $tableName = 'aw_osc_report_aggregation_by_' . $aggregation;
        $table = $setup->getConnection()
            ->newTable($setup->getTable($tableName))
            ->addColumn(
                'period_from',
                Table::TYPE_DATE,
                null,
                ['nullable' => false, 'primary' => true],
                'Period From Date'
            )->addColumn(
                'period_to',
                Table::TYPE_DATE,
                null,
                ['nullable' => false, 'primary' => true],
                'Period To Date'
            )->addIndex(
                $setup->getIdxName($tableName, ['period_from']),
                ['period_from']
            )->addIndex(
                $setup->getIdxName($tableName, ['period_to']),
                ['period_to']
            )->setComment('Report Aggregation By ' . ucfirst($aggregation) . ' Table');
        $setup->getConnection()->createTable($table);
    }
}
