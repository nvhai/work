<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Plugin\App\FrontController;

use Aheadworks\OneStepCheckout\Model\Serialize\CoreSerialize;
use Aheadworks\OneStepCheckout\Model\Serialize\PhpSerialize;
use Aheadworks\OneStepCheckout\Model\Serialize\SerializeInterface;
use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class CoreBridgeInitializer
 * @package Aheadworks\OneStepCheckout\Plugin\App\FrontController
 */
class CoreBridgeInitializer
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ProductMetadataInterface $productMetadata
    ) {
        $this->objectManager = $objectManager;
        $this->productMetadata = $productMetadata;
    }

    /**
     * @param FrontControllerInterface $subject
     * @param RequestInterface $request
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDispatch(FrontControllerInterface $subject, RequestInterface $request)
    {
        $this->configureSerializer();
    }

    /**
     * Configure serializer bridge
     *
     * @return void
     */
    private function configureSerializer()
    {
        $magentoVersion = $this->productMetadata->getVersion();
        $serializerClass = version_compare($magentoVersion, '2.2.0', '>=')
            ? CoreSerialize::class
            : PhpSerialize::class;

        // This is approach with a significant limitation:
        // impossible to inject dependency using class name in compiled mode.
        // Should be revised, factories are used in the current implementation.
        $this->objectManager->configure(
            [
                'preferences' => [
                    SerializeInterface::class => $serializerClass
                ]
            ]
        );
    }
}
