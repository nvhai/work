<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Block\Page;

use Aheadworks\OneStepCheckout\Model\Config;
use Aheadworks\OneStepCheckout\Model\Template\FilterProvider;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Description
 * @package Aheadworks\OneStepCheckout\Block\Page
 */
class Description extends Template
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var FilterProvider
     */
    private $filterProvider;

    /**
     * @param Context $context
     * @param Config $config
     * @param FilterProvider $filterProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $config,
        FilterProvider $filterProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->filterProvider = $filterProvider;
    }

    /**
     * Get checkout description html
     *
     * @return string
     */
    public function getDescriptionHtml()
    {
        return $this->filterProvider->getFilter()
            ->setStoreId($this->_storeManager->getStore()->getId())
            ->filter($this->config->getCheckoutDescription());
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if ($this->config->getCheckoutDescription()) {
            return parent::_toHtml();
        }
        return '';
    }
}
