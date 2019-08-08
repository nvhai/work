<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Layout\Processor\AdditionalServices;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class ServiceComponentPool
 * @package Aheadworks\OneStepCheckout\Model\Layout\Processor\AdditionalServices
 */
class ServiceComponentPool
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $pool = [
        'google-autocomplete' => GoogleAutocomplete::class
    ];

    /**
     * @var ServiceComponentInterface[]
     */
    private $instances = [];

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $pool
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $pool = []
    ) {
        $this->objectManager = $objectManager;
        $this->pool = array_merge($this->pool, $pool);
    }

    /**
     * Get service component instance
     *
     * @param string $code
     * @return ServiceComponentInterface|null
     * @throws \Exception
     */
    public function getServiceComponent($code)
    {
        if (!isset($this->instances[$code])) {
            if (!isset($this->pool[$code])) {
                return null;
            }
            $instance = $this->objectManager->create($this->pool[$code]);
            if (!$instance instanceof ServiceComponentInterface) {
                throw new \Exception(
                    sprintf(
                        'Service component %s does not implement required interface.',
                        $code
                    )
                );
            }
            $this->instances[$code] = $instance;
        }
        return $this->instances[$code];
    }
}
