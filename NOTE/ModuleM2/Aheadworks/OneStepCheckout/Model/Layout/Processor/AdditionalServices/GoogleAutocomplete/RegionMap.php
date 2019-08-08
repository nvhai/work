<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Layout\Processor\AdditionalServices\GoogleAutocomplete;

use Magento\Directory\Model\ResourceModel\Region\Collection;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;

/**
 * Class RegionMap
 * @package Aheadworks\OneStepCheckout\Model\Layout\Processor\AdditionalServices\GoogleAutocomplete
 */
class RegionMap
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get region map
     *
     * @return array
     */
    public function getMap()
    {
        $map = [];
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        foreach ($collection as $item) {
            $map[] = [
                'code' => $item->getCode(),
                'countryId' => $item->getCountryId(),
                'id' => $item->getRegionId()
            ];
        }
        return $map;
    }
}
