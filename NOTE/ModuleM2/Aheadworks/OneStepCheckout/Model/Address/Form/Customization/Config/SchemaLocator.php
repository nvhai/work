<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Address\Form\Customization\Config;

use Magento\Framework\Config\SchemaLocatorInterface;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader as ModuleReader;

/**
 * Class SchemaLocator
 * @package Aheadworks\OneStepCheckout\Model\Address\Form\Customization\Config
 */
class SchemaLocator implements SchemaLocatorInterface
{
    /**
     * @var string
     */
    private $schema;

    /**
     * @param ModuleReader $moduleReader
     */
    public function __construct(ModuleReader $moduleReader)
    {
        $this->schema = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Aheadworks_OneStepCheckout')
            . '/osc_attribute_customization.xsd';
    }

    /**
     * {@inheritdoc}
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * {@inheritdoc}
     */
    public function getPerFileSchema()
    {
        return null;
    }
}
