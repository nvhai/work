<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Block\Adminhtml\Subscription\Edit\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * History grid
 */
class History extends \Magento\Backend\Block\Widget\Grid\Extended implements TabInterface
{
    /**
     * @var  \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \IWD\BluePaySubs\Model\Source\Status
     */
    protected $statusSource;

    /**
     * @var \IWD\BluePaySubs\Model\Source\Agent
     */
    protected $agentSource;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param \IWD\BluePaySubs\Model\Source\Status $statusSource
     * @param \IWD\BluePaySubs\Model\Source\Agent $agentSource
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $registry,
        \IWD\BluePaySubs\Model\Source\Status $statusSource,
        \IWD\BluePaySubs\Model\Source\Agent $agentSource,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);

        $this->collectionFactory = $collectionFactory;
        $this->registry = $registry;
        $this->statusSource = $statusSource;
        $this->agentSource = $agentSource;
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('History');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('History');
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('subs_history_grid');
        $this->setDefaultSort('history_log_id');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);
    }

    /**
     * Apply various selection filters to prepare the sales order grid collection.
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        /** @var \IWD\BluePaySubs\Model\Subscription $subscription */
        $subscription = $this->registry->registry('current_bsubs');

        /** @var \IWD\BluePaySubs\Model\ResourceModel\Log\UiCollection $collection */
        $collection = $this->collectionFactory->getReport('bsubs_log_data_source')->addFieldToSelect(
            '*'
        )->addFieldToFilter(
            'main_table.subs_id',
            $subscription->getId()
        );
        $collection->getSelect()->joinLeft(
            ['orders' => 'sales_order'],
            'main_table.order_increment_id = orders.increment_id',
            ['order_id' => 'orders.entity_id']
        );

        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'history_log_id',
            [
                'header' => __('ID'),
                'index' => 'log_id',
                'filter_index' => 'main_table.log_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'history_created_at',
            [
                'header' => __('Date'),
                'index' => 'created_at',
                'filter_index' => 'main_table.created_at',
                'type' => 'datetime',
            ]
        );

        $this->addColumn(
            'history_status',
            [
                'header' => __('Subscription Status'),
                'index' => 'status',
                'filter_index' => 'main_table.status',
                'type' => 'options',
                'options' => $this->statusSource->getOptionArray(),
            ]
        );

        $this->addColumn(
            'history_order_increment_id',
            [
                'header' => __('Order #'),
                'index' => 'order_increment_id',
                'renderer' => \IWD\BluePaySubs\Block\Adminhtml\Subscription\Edit\Tab\History\Renderer::class,
//                'bodyTmpl' => 'ui/grid/cells/html'
            ]
        );

        $this->addColumn(
            'history_transaction_id',
            [
                'header' => __('Transaction ID #'),
                'index' => 'transaction_id',
            ]
        );

        $this->addColumn(
            'history_agent_id',
            [
                'header' => __('Agent'),
                'index' => 'agent_id',
                'type' => 'options',
                'options' => $this->agentSource->getOptionArray(),
            ]
        );

        $this->addColumn(
            'history_description',
            [
                'header' => __('Description'),
                'index' => 'description',
                'filter_index' => 'main_table.description',
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * {@inheritdoc}
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/logGrid', ['_current' => true]);
    }

    /**
     * Retrieve the Url for a specified sales order row.
     *
     * @param \IWD\BluePaySubs\Model\Log $row
     * @return string
     */
    public function getRowUrl($row)
    {
        if (!empty($row->getData('order_id'))) {
            return $this->getUrl('sales/order/view', ['order_id' => $row->getData('order_id')]);
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    protected function _afterLoadCollection()
    {
        foreach ($this->getCollection()->getItems() as $item) {
            $incId = $item->getOrderIncrementId();
            if($item->getOrderId() && $incId) {
                $url = $this->getOrderUrl($item->getOrderId());
                $item->setOrderIncrementId("<a href='$url' target='_blank'>$incId</a>");
            }
        }

        return parent::_afterLoadCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function getOrderUrl($orderId)
    {
        return $this->getUrl('sales/order/view', ['order_id' => $orderId]);
    }
}
