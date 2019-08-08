<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Report\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\Group;
use Magento\Store\Model\Store as StoreModel;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;

/**
 * Class Store
 * @package Aheadworks\OneStepCheckout\Model\Report\Source
 */
class Store implements OptionSourceInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $options;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [['value' => 0, 'label' => __('All Store Views')]];
            /** @var WebsiteInterface|Website $website */
            foreach ($this->storeManager->getWebsites() as $website) {
                $this->options[] = [
                    'value' => $website->getId(),
                    'label' => $website->getName(),
                    'filter_by' => 'website'
                ];
                /** @var Group $group */
                foreach ($website->getGroups() as $group) {
                    $this->options[] = [
                        'value' => $group->getId(),
                        'label' => $group->getName(),
                        'filter_by' => 'store_group'
                    ];
                    /** @var StoreModel $store */
                    foreach ($group->getStores() as $store) {
                        $this->options[] = [
                            'value' => $store->getId(),
                            'label' => $store->getName(),
                            'filter_by' => 'store'
                        ];
                    }
                }
            }
        }
        return $this->options;
    }
}
