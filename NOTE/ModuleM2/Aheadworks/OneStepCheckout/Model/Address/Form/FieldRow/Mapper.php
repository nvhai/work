<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Address\Form\FieldRow;

/**
 * Class Mapper
 * @package Aheadworks\OneStepCheckout\Model\Address\Form\FieldRow
 */
class Mapper
{
    /**
     * @var array
     */
    private $map = [
        'prefix' => 'name-field-row',
        'firstname' => 'name-field-row',
        'middlename' => 'name-field-row',
        'lastname' => 'name-field-row',
        'suffix' => 'name-field-row',
        'company' => 'phone-company-field-row',
        'street' => 'address-field-row',
        'city' => 'city-field-row',
        'country_id' => 'country-region-zip-field-row',
        'region' => 'country-region-zip-field-row',
        'region_id' => 'country-region-zip-field-row',
        'postcode' => 'country-region-zip-field-row',
        'telephone' => 'phone-company-field-row',
        'fax' => 'phone-company-field-row'
    ];

    /**
     * Map to attributes
     *
     * @param string $fieldRow
     * @return array
     */
    public function toAttributes($fieldRow)
    {
        $attributes = [];
        foreach ($this->map as $attributeCode => $row) {
            if ($row == $fieldRow) {
                $attributes[] = $attributeCode;
            }
        }
        return $attributes;
    }

    /**
     * Map to field row
     *
     * @param string $attributeCode
     * @return string|null
     */
    public function toFieldRow($attributeCode)
    {
        return isset($this->map[$attributeCode])
            ? $this->map[$attributeCode]
            : null;
    }
}
