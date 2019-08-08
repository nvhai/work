<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\ResourceModel\Report;

use Aheadworks\OneStepCheckout\Model\ResourceModel\Report\AbandonedCheckout as AbandonedCheckoutResource;
use Aheadworks\OneStepCheckout\Ui\DataProvider\Aggregation;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection as AbstractDbCollection;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractCollection
 * @package Aheadworks\OneStepCheckout\Model\ResourceModel\Report
 */
class AbstractCollection extends AbstractDbCollection implements FilterableInterface
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = 'index_id';

    /**
     * @var string
     */
    private $aggregationType;

    /**
     * @var array
     */
    protected $columns;

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param EventManager $eventManager
     * @param string $aggregationType
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        EventManager $eventManager,
        $aggregationType = Aggregation::DEFAULT_AGGREGATION,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->aggregationType = $aggregationType;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        $aggregationTableName = 'aw_osc_report_aggregation_by_' . $this->aggregationType;
        $this->getSelect()
            ->from(['main_table' => $this->getMainTable()], $this->getColumns())
            ->joinRight(
                ['aggregation_table' => $this->getTable($aggregationTableName)],
                'main_table.period >= aggregation_table.period_from '
                . 'AND main_table.period <= aggregation_table.period_to',
                []
            )
            ->group('aggregation_table.period_from');
    }

    /**
     * Init report columns
     *
     * @return void
     */
    protected function initColumns()
    {
        // no implementation, should be overridden in children classes
    }

    /**
     * Get report columns
     *
     * @return array
     */
    protected function getColumns()
    {
        if (!$this->columns) {
            $this->initColumns();
        }
        return $this->columns;
    }

    /**
     * Get totals row items list
     *
     * @return array
     */
    public function getTotalsItems()
    {
        $select = clone $this->getSelect();
        $select
            ->reset(Select::GROUP)
            ->reset(Select::LIMIT_COUNT)
            ->reset(Select::LIMIT_OFFSET);

        $columns = $this->getColumns();
        $columns['period_from'] = 'main_table.period_from';
        $columns['period_to'] = 'main_table.period_to';

        $totalsSelect = $this->getConnection()->select()
            ->from(['main_table' => $select], $columns);
        return [$this->getConnection()->fetchRow($totalsSelect) ?: []];
    }

    /**
     * Retrieve chart rows items list
     *
     * @param array $columnNames
     * @return array
     */
    public function getChartRows($columnNames)
    {
        $connection = $this->getConnection();
        $select = clone $this->getSelect();
        $select
            ->reset(Select::ORDER)
            ->reset(Select::LIMIT_COUNT)
            ->reset(Select::LIMIT_OFFSET);

        $columns = ['period_from' => $connection->getCheckSql(
            'main_table.period_from = main_table.period_to',
            'main_table.period_from',
            'CONCAT(main_table.period_from, \' - \', main_table.period_to)'
        )];
        foreach ($columnNames as $columnName) {
            $columns[$columnName] = 'main_table.' . $columnName;
        }

        $chartSelect = $this->getConnection()->select()
            ->from(['main_table' => $select], $columns);
        return $connection->fetchAll($chartSelect) ?: [];
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomerGroupIdFilter($groupId)
    {
        $this->addFilter('main_table.customer_group_id', ['eq' => $groupId], 'public');
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addStoreIdFilter($storeId)
    {
        $this->addFilter('main_table.store_id', ['eq' => $storeId], 'public');
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addStoreGroupIdFilter($storeGroupId)
    {
        $this->addFilter('store_table.group_id', ['eq' => $storeGroupId], 'public');
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addWebsiteIdFilter($websiteId)
    {
        $this->addFilter('store_table.website_id', ['eq' => $websiteId], 'public');
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addPeriodFilter($periodFrom, $periodTo)
    {
        $connection = $this->getConnection();

        $select = $this->getSelect();
        $fromPart = $select->getPart(Select::FROM);
        $columns = $select->getPart(Select::COLUMNS);

        $columns = $this->changePeriodColumns($columns, $periodFrom, $periodTo);
        $select->setPart(Select::COLUMNS, $columns);

        $aggregationJoinCondition = $fromPart['aggregation_table']['joinCondition']
            . ' AND main_table.period >= ' . $connection->quote($periodFrom)
            . ' AND main_table.period <= ' . $connection->quote($periodTo);

        $fromPart['aggregation_table']['joinCondition'] = $aggregationJoinCondition;
        $select->setPart(Select::FROM, $fromPart);

        $this->addFilter('aggregation_table.period_from', ['lteq' => $periodTo], 'public');
        $this->addFilter('aggregation_table.period_to', ['gteq' => $periodFrom], 'public');

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _renderFiltersBefore()
    {
        $this->joinStoreTable();
        parent::_renderFiltersBefore();
    }

    /**
     * Join store table
     *
     * @return $this
     */
    private function joinStoreTable()
    {
        if (!$this->getFlag('store_table_joined')
            && ($this->getFilter('store_table.group_id') || $this->getFilter('store_table.website_id'))
        ) {
            $this->getSelect()->joinLeft(
                ['store_table' => $this->getTable('store')],
                'main_table.store_id = store_table.store_id',
                ['*']
            );
            $this->setFlag('store_table_joined', true);
        }
        return $this;
    }

    /**
     * @param array $columns
     * @param string $periodFrom
     * @param string $periodTo
     * @return array
     */
    private function changePeriodColumns($columns, $periodFrom, $periodTo)
    {
        foreach ($columns as &$column) {
            if (is_string($column[1]) && $column[1] == 'period_from') {
                $column[1] = new \Zend_Db_Expr(
                    "IF(aggregation_table.period_from < '{$periodFrom}', '{$periodFrom}', "
                    . "aggregation_table.period_from)"
                );
            }
            if (is_string($column[1]) && $column[1] == 'period_to') {
                $column[1] = new \Zend_Db_Expr(
                    "IF(aggregation_table.period_to > '{$periodTo}', '{$periodTo}', aggregation_table.period_to)"
                );
            }
        }

        return $columns;
    }
}
