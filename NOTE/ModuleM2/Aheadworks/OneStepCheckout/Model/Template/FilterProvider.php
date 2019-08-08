<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Template;

use Magento\Cms\Model\Template\Filter;
use Magento\Framework\Filter\Template as TemplateFilter;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class FilterProvider
 * @package Aheadworks\OneStepCheckout\Model\Template
 */
class FilterProvider
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $filterClassName;

    /**
     * @var TemplateFilter|null
     */
    private $filterInstance = null;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string $filterClassName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $filterClassName = Filter::class
    ) {
        $this->objectManager = $objectManager;
        $this->filterClassName = $filterClassName;
    }

    /**
     * Get filter instance
     *
     * @return TemplateFilter|mixed|null
     * @throws \Exception
     */
    public function getFilter()
    {
        if ($this->filterInstance === null) {
            $filterInstance = $this->objectManager->get($this->filterClassName);
            if (!$filterInstance instanceof TemplateFilter) {
                throw new \Exception(
                    'Template filter ' . $this->filterClassName . ' does not implement required interface.'
                );
            }
            $this->filterInstance = $filterInstance;
        }
        return $this->filterInstance;
    }
}
