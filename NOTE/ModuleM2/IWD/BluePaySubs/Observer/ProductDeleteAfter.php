<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Observer;

use IWD\BluePaySubs\Model\ResourceModel\Subscription\CollectionFactory;
use IWD\BluePaySubs\Model\Source\Status;
use Magento\Framework\Exception\LocalizedException;

/**
 * ProductDeleteAfter Class
 */
class ProductDeleteAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * ProductDeleteAfter constructor.
     *
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $observer->getEvent()->getData('product');

        if(!empty($subsIds = $this->getProductSubscriptionIds($product))) {
            throw new LocalizedException(
                __('Sorry, some subscription still active (IDs %1). Please, stop them before delete product',
                    implode(', ', $subsIds)
                )
            );
        }
    }

    public function getProductSubscriptionIds(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $subsIds = [];

        try {
            /** @var \IWD\BluePaySubs\Model\ResourceModel\Subscription\Collection $collection */
            $collection = $this->collectionFactory->create();
            $items = $collection->getItems();
            /** @var \IWD\BluePaySubs\Model\Subscription $item */
            foreach ($items as $item) {
                if($item->getStatus() != Status::STATUS_ACTIVE) {
                    continue;
                }
                $subsId = $item->getId();
                /** @var \Magento\Quote\Model\Quote\Item[] $quoteItems */
                $quoteItems = $item->getQuote()->getAllItems();
                foreach ($quoteItems as $quoteItem) {
                    if($quoteItem->getProduct()->getId() == $product->getId() && !in_array($subsId, $subsIds)) {
                        $subsIds[] = $subsId;
                    }
                }
            }
        } catch (\Exception $e) {
            return $subsIds;
        }

        return $subsIds;
    }
}
