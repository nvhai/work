<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Ui\DataProvider;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class CollectionFactory
 * @package Aheadworks\OneStepCheckout\Ui\DataProvider
 */
class CollectionFactory
{
    /**
     * @var array
     */
    private $collections;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param ObjectManagerInterface $objectManagerInterface
     * @param RequestInterface $request
     * @param array $collections
     */
    public function __construct(
        ObjectManagerInterface $objectManagerInterface,
        RequestInterface $request,
        array $collections = []
    ) {
        $this->collections = $collections;
        $this->objectManager = $objectManagerInterface;
        $this->request = $request;
    }

    /**
     * Get report collection
     *
     * @param string $requestName
     * @param string $aggregationType
     * @return Collection
     * @throws \Exception
     */
    public function getReport($requestName, $aggregationType)
    {
        if (!isset($this->collections[$requestName])) {
            throw new \Exception(sprintf('Not registered handle %s', $requestName));
        }
        $collection = $this->objectManager->create(
            $this->collections[$requestName],
            ['aggregationType' => $aggregationType]
        );
        if (!$collection instanceof Collection) {
            throw new \Exception(sprintf('%s is not of Collection type.', $requestName));
        }
        return $collection;
    }
}
