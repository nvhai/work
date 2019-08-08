<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePaySubs\Block\Customer\Subscriptions;

use Magento\Framework\View\Element\Template;
use IWD\BluePaySubs\Api\Data\SubscriptionInterface;
use IWD\BluePaySubs\Model\Source\Status;
use IWD\BluePaySubs\Helper\Data as Helper;

/**
 * Class Edit
 * @package IWD\BluePaySubs\Block\Customer\Subscriptions\Edit
 */
class Edit extends Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * Tab constructor.
     * @param Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Registry $registry,
        Helper $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     */
    public function getCancelUrl()
    {
        return $this->getUrl('*/bsubs_edit/changeStatus', [
            'id' => $this->getCurrentSubscription()->getId(),
            'status' => Status::STATUS_STOPPED
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/bsubs/index');
    }

    /**
     * @return bool
     */
    public function isStopped()
    {
        return $this->getCurrentSubscription()->getStatus() == Status::STATUS_STOPPED;
    }

    /**
     * @return bool
     */
    public function canCustomerStop()
    {
        return $this->helper->canCustomerStop();
    }

    /**
     * @return null | SubscriptionInterface
     */
    public function getCurrentSubscription()
    {
        return $this->registry->registry('current_subs');
    }
}