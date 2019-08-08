<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Block\Adminhtml\Report\View;

use Aheadworks\OneStepCheckout\Model\Report\Source\Store as StoreSource;
use Aheadworks\OneStepCheckout\Ui\DataProvider\DefaultFilter\StoreId as StoreFilter;
use Aheadworks\OneStepCheckout\Ui\DataProvider\DefaultFilter\StoreGroupId as StoreGroupFilter;
use Aheadworks\OneStepCheckout\Ui\DataProvider\DefaultFilter\WebsiteId as WebsiteFilter;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

/**
 * Class StoreSwitcher
 * @package Aheadworks\OneStepCheckout\Block\Adminhtml\Report\View
 */
class StoreSwitcher extends Template
{
    /**
     * @inheritdoc
     */
    protected $_template = 'Aheadworks_OneStepCheckout::report/view/store_switcher.phtml';

    /**
     * @var StoreFilter
     */
    private $storeFilter;

    /**
     * @var StoreGroupFilter
     */
    private $storeGroupFilter;

    /**
     * @var WebsiteFilter
     */
    private $websiteFilter;

    /**
     * @var StoreSource
     */
    private $storeSource;

    /**
     * @var string
     */
    private $filterBy;

    /**
     * @var int
     */
    private $value;

    /**
     * @param Context $context
     * @param StoreFilter $storeFilter
     * @param StoreGroupFilter $storeGroupFilter
     * @param WebsiteFilter $websiteFilter
     * @param StoreSource $storeSource
     * @param array $data
     */
    public function __construct(
        Context $context,
        StoreFilter $storeFilter,
        StoreGroupFilter $storeGroupFilter,
        WebsiteFilter $websiteFilter,
        StoreSource $storeSource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->storeFilter = $storeFilter;
        $this->storeGroupFilter = $storeGroupFilter;
        $this->websiteFilter = $websiteFilter;
        $this->storeSource = $storeSource;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->storeSource->toOptionArray();
    }

    /**
     * Get selected store Id
     *
     * @return int
     */
    private function getSelectedStoreId()
    {
        return $this->storeFilter->getValue();
    }

    /**
     * Get selected store group Id
     *
     * @return int
     */
    private function getSelectedStoreGroupId()
    {
        return $this->storeGroupFilter->getValue();
    }

    /**
     * Get selected website Id
     *
     * @return int
     */
    private function getSelectedWebsiteId()
    {
        return $this->websiteFilter->getValue();
    }

    /**
     * Get current type of filter param: 'website', 'store_group', 'store'
     *
     * @return string
     */
    public function getFilterBy()
    {
        if (!$this->filterBy) {
            if ($this->getSelectedStoreId()) {
                $this->filterBy = 'store';
            } elseif ($this->getSelectedStoreGroupId()) {
                $this->filterBy = 'store_group';
            } elseif ($this->getSelectedWebsiteId()) {
                $this->filterBy = 'website';
            } else {
                $this->filterBy = '';
            }
        }
        return $this->filterBy;
    }

    /**
     * Get current value
     *
     * @return int|null
     */
    public function getValue()
    {
        if (!$this->value) {
            switch ($this->getFilterBy()) {
                case 'store':
                    $this->value = $this->getSelectedStoreId();
                    break;
                case 'store_group':
                    $this->value = $this->getSelectedStoreGroupId();
                    break;
                case 'website':
                    $this->value = $this->getSelectedWebsiteId();
                    break;
                default:
                    $this->value = 0;
                    break;
            }
        }
        return $this->value;
    }

    /**
     * Check if option is current
     *
     * @param array $option
     * @return bool
     */
    public function isCurrent($option)
    {
        if (isset($option['filter_by'])) {
            return $option['filter_by'] == $this->getFilterBy()
                && $option['value'] == $this->getValue();
        }
        return $option['value'] == $this->getValue();
    }

    /**
     * Get current option title
     *
     * @return string
     */
    public function getCurrentOptionTitle()
    {
        foreach ($this->getOptions() as $option) {
            if ($this->isCurrent($option)) {
                return $option['label'];
            }
        }
        return '';
    }

    /**
     * Get filter url
     *
     * @param array $option
     * @return string
     */
    public function getFilterUrl($option)
    {
        $query = [
            StoreFilter::REQUEST_FIELD_NAME => 0,
            StoreGroupFilter::REQUEST_FIELD_NAME => 0,
            WebsiteFilter::REQUEST_FIELD_NAME => 0
        ];

        if (isset($option['filter_by'])) {
            $filterBy = $option['filter_by'];
            $requestFieldName = StoreFilter::REQUEST_FIELD_NAME;
            if ($filterBy == 'store_group') {
                $requestFieldName = StoreGroupFilter::REQUEST_FIELD_NAME;
            } elseif ($filterBy == 'website') {
                $requestFieldName = WebsiteFilter::REQUEST_FIELD_NAME;
            }
            $query[$requestFieldName] = $option['value'];
        }

        return $this->getUrl('*/*/*', ['_query' => $query, '_current' => true]);
    }
}
