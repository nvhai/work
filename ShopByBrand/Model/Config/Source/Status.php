<?php
namespace Vnecoms\ShopByBrand\Model\Config\Source;
class Status implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(){
        return [['value' => 1, 'label' => __('Enable')], ['value' => 0, 'label' => __('Disable')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(){
        return [0 => __('Disabled'), 1 => __('Enabled')];
    }
    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $_options = [];
        foreach ($this->toOptionArray() as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }

}