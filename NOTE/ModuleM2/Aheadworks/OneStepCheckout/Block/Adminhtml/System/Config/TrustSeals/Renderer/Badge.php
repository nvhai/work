<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Block\Adminhtml\System\Config\TrustSeals\Renderer;

use Magento\Backend\Block\Template;

/**
 * Class Badge
 * @package Aheadworks\OneStepCheckout\Block\Adminhtml\System\Config\TrustSeals\Renderer
 */
class Badge extends Template
{
    /**
     * {@inheritdoc}
     */
    protected $_template = 'system/config/trust_seals/badge.phtml';

    /**
     * Sets name for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Set html id for input element
     *
     * @param string $id
     * @return $this
     */
    public function setInputId($id)
    {
        return $this->setId($id);
    }
}
