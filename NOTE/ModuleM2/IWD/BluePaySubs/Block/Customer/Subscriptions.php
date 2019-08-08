<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Block\Customer;

use Magento\Framework\App\Filesystem\DirectoryList;
use IWD\BluePaySubs\Api\Data\SubscriptionInterface;
use IWD\BluePaySubs\Helper\Data as Helper;

/**
 * Class Subscriptions
 * @package IWD\BluePaySubs\Block\Customer
 */
class Subscriptions extends \Magento\Framework\View\Element\Template
{
    /**
     * Default toolbar block name
     *
     * @var string
     */
    protected $defaultToolbarBlock = \Magento\Catalog\Block\Product\ProductList\Toolbar::class;

    /**
     * @var \IWD\BluePaySubs\Model\ResourceModel\Subscription\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \IWD\BluePaySubs\Model\ResourceModel\Subscription\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @var \Magento\Directory\Model\Currency[]
     */
    protected $currencies = [];

    /**
     * @var \IWD\BluePaySubs\Model\Source\Status
     */
    protected $statusSource;

    /**
     * @var \IWD\BluePaySubs\Model\Source\Period
     */
    protected $periodSource;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelperFactory;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * Subscriptions constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \IWD\BluePaySubs\Model\ResourceModel\Subscription\CollectionFactory $collectionFactory
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \IWD\BluePaySubs\Model\Source\Status $statusSource
     * @param \IWD\BluePaySubs\Model\Source\Period $periodSource
     * @param \Magento\Catalog\Helper\ImageFactory $imageHelperFactory
     * @param Helper $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \IWD\BluePaySubs\Model\ResourceModel\Subscription\CollectionFactory $collectionFactory,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \IWD\BluePaySubs\Model\Source\Status $statusSource,
        \IWD\BluePaySubs\Model\Source\Period $periodSource,
        \Magento\Catalog\Helper\ImageFactory $imageHelperFactory,
        Helper $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->collectionFactory = $collectionFactory;
        $this->currentCustomer = $currentCustomer;
        $this->currencyFactory = $currencyFactory;
        $this->statusSource = $statusSource;
        $this->periodSource = $periodSource;
        $this->imageHelperFactory = $imageHelperFactory;
        $this->helper = $helper;
    }

    /**
     * @param SubscriptionInterface $subscription
     * @param int $imageSize
     * @return string
     */
    public function getProductImage(SubscriptionInterface $subscription)
    {
        if($product = $this->helper->getProduct($subscription)) {
            $imageHelper = $this->imageHelperFactory->create();
            return $imageHelper->init($product, 'product_thumbnail_image')->getUrl();
        }

        return '';
    }

    /**
     * Get subscription view URL.
     *
     * @param SubscriptionInterface $subscription
     * @return string
     */
    public function getViewUrl(SubscriptionInterface $subscription)
    {
        return $this->_urlBuilder->getUrl('*/*/view', ['id' => $subscription->getId()]);
    }

    /**
     * Get subscription edit URL.
     *
     * @param SubscriptionInterface $subscription
     * @return string
     */
    public function getEditUrl(SubscriptionInterface $subscription)
    {
        return $this->_urlBuilder->getUrl('*/*/edit', ['id' => $subscription->getId()]);
    }

    /**
     * Get the formatted subscription subtotal.
     *
     * @param SubscriptionInterface $subscription
     * @return string
     */
    public function getAmount(SubscriptionInterface $subscription)
    {
        /** @var \IWD\BluePaySubs\Model\Subscription $subscription */
        $currency = $subscription->getData('quote_currency_code');

        if (!isset($this->currencies[$currency])) {
            $this->currencies[$currency] = $this->currencyFactory->create();
            $this->currencies[$currency]->load($currency);
        }

        return $this->currencies[$currency]->formatTxt($subscription->getAmount());
    }

    /**
     * Get the subscription status text.
     *
     * @param SubscriptionInterface $subscription
     * @return string
     */
    public function getStatus(SubscriptionInterface $subscription)
    {
        return $this->statusSource->getOptionText($subscription->getStatus());
    }

    /**
     * Get frequency label (Every ___) for grid.
     *
     * @param SubscriptionInterface $subscription
     * @return \Magento\Framework\Phrase | string
     */
    public function getPeriodTitle(SubscriptionInterface $subscription)
    {
        return $this->helper->generateOptionTitle($subscription->getData(), false);
    }

    /**
     * Get status source model.
     *
     * @return \IWD\BluePaySubs\Model\Source\Status
     */
    public function getStatusSource()
    {
        return $this->statusSource;
    }

    /**
     * Get the subscription collection
     *
     * @return \IWD\BluePaySubs\Model\ResourceModel\Subscription\Collection
     */
    public function getCollection()
    {
        if ($this->collection === null) {
            $this->collection = $this->collectionFactory->create();
            $this->collection->addFieldToFilter('main_table.customer_id', $this->currentCustomer->getCustomerId());
            $this->collection->joinQuoteCurrency();
        }

        return $this->collection;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        /** @var \Magento\Theme\Block\Html\Pager $pager */
        $pager = $this->getChildBlock('toolbar_pager');
        if($pager) {
            if ($this->getLimitPerPage()) {
                $pager->setLimit($this->getLimitPerPage());
            }
            $pager->setShowAmounts(false)
                ->setShowPerPage(false)
                ->setCollection($this->getCollection());
        }

        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        /** @var \Magento\Theme\Block\Html\Pager $pagerBlock */
        $pagerBlock = $this->getChildBlock('toolbar_pager');
        return $pagerBlock ? $pagerBlock->toHtml() : '';
    }

    /**
     * Return the base media directory for images
     *
     * @return string
     */
    public function getBaseMediaDir($path = '')
    {
        return $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($path);
    }
}
