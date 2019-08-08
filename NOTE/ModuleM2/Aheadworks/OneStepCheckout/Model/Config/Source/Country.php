<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Config\Source;

use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Country
 * @package Aheadworks\OneStepCheckout\Model\Config\Source
 */
class Country implements OptionSourceInterface
{
    /**
     * @var CountryCollectionFactory
     */
    private $collectionFactory;

    /**
     * @var array
     */
    private $options;

    /**
     * @param CountryCollectionFactory $collectionFactory
     */
    public function __construct(CountryCollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $collection = $this->collectionFactory->create();
            $this->options = $collection->loadData()->toOptionArray(' ');
        }
        return $this->options;
    }
}
