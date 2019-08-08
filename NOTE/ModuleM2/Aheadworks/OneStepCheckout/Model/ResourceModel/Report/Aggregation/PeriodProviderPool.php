<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\ResourceModel\Report\Aggregation;

use Aheadworks\OneStepCheckout\Model\ResourceModel\Report\Aggregation\PeriodProvider\Day;
use Aheadworks\OneStepCheckout\Model\ResourceModel\Report\Aggregation\PeriodProvider\Week;
use Aheadworks\OneStepCheckout\Model\ResourceModel\Report\Aggregation\PeriodProvider\Month;
use Aheadworks\OneStepCheckout\Model\ResourceModel\Report\Aggregation\PeriodProvider\Quarter;
use Aheadworks\OneStepCheckout\Model\ResourceModel\Report\Aggregation\PeriodProvider\Year;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class PeriodProviderPool
 * @package Aheadworks\OneStepCheckout\Model\ResourceModel\Report\Aggregation
 */
class PeriodProviderPool
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $providers = [
        'day' => Day::class,
        'week' => Week::class,
        'month' => Month::class,
        'quarter' => Quarter::class,
        'year' => Year::class
    ];

    /**
     * @var array
     */
    private $providerInstances = [];

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $providers
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $providers = []
    ) {
        $this->objectManager = $objectManager;
        $this->providers = array_merge($this->providers, $providers);
    }

    /**
     * Get aggregation period provider
     *
     * @param string $aggregationType
     * @return PeriodProviderInterface
     * @throws \Exception
     */
    public function getProvider($aggregationType)
    {
        if (!isset($this->providerInstances[$aggregationType])) {
            $providerInstance = $this->objectManager->create($this->providers[$aggregationType]);
            if (!$providerInstance instanceof PeriodProviderInterface) {
                throw new \Exception(
                    sprintf(
                        'Aggregation period provider %s does not implement required interface.',
                        $aggregationType
                    )
                );
            }
            $this->providerInstances[$aggregationType] = $providerInstance;
        }
        return $this->providerInstances[$aggregationType];
    }
}
