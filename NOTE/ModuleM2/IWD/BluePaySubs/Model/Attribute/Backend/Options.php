<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Model\Attribute\Backend;

use IWD\BluePaySubs\Ui\DataProvider\Product\Form\Modifier\SubscriptionOptions;

/**
 * Options Class
 */
class Options extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Validate subscription interval(s)
     *
     * @param \Magento\Framework\DataObject $object
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validate($object)
    {
        /** @var \Magento\Catalog\Model\Product $object */
        parent::validate($object);

        $attrCode = $this->getAttribute()->getAttributeCode();
        $gridCode = SubscriptionOptions::SUBS_OPTIONS_GRID;
        $values = $object->getData($gridCode);

        return true;
    }
}
