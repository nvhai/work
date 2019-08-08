<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Serialize;

use Aheadworks\OneStepCheckout\Model\Serialize\CoreSerialize\Factory as CoreSerializeFactory;

/**
 * Class CoreSerialize
 * @package Aheadworks\OneStepCheckout\Model\Serialize
 */
class CoreSerialize implements SerializeInterface
{
    /**
     * @var CoreSerializeFactory
     */
    private $serializerFactory;

    /**
     * @param CoreSerializeFactory $serializerFactory
     */
    public function __construct(CoreSerializeFactory $serializerFactory)
    {
        $this->serializerFactory = $serializerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize($data)
    {
        $serializer = $this->serializerFactory->create();
        return $serializer->serialize($data);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($string)
    {
        $serializer = $this->serializerFactory->create();
        return $serializer->unserialize($string);
    }
}
