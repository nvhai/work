<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Address\Form\AttributeMeta;

use Aheadworks\OneStepCheckout\Model\Address\Form\AttributeMeta\Modifier\ModifierPool;

/**
 * Class Modifier
 * @package Aheadworks\OneStepCheckout\Model\Address\Form\AttributeMeta
 */
class Modifier
{
    /**
     * @var ModifierPool
     */
    private $modifierPool;

    /**
     * @param ModifierPool $modifierPool
     */
    public function __construct(ModifierPool $modifierPool)
    {
        $this->modifierPool = $modifierPool;
    }

    /**
     * Modify attribute metadata
     *
     * @param string $attributeCode
     * @param array $metadata
     * @param string $addressType
     * @return array
     */
    public function modify($attributeCode, $metadata, $addressType)
    {
        $modifier = $this->modifierPool->getModifier($attributeCode);
        return $modifier->modify($metadata, $addressType);
    }
}
