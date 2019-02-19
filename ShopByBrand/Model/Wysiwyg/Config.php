<?php
/**
 * Copyright � 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\ShopByBrand\Model\Wysiwyg;

/**
 * Wysiwyg Config for Editor HTML Element
 */
class Config extends \Magento\Cms\Model\Wysiwyg\Config
{

    /**
     * Return Wysiwyg config as \Magento\Framework\DataObject
     *
     * Config options description:
     *
     * enabled:                 Enabled Visual Editor or not
     * hidden:                  Show Visual Editor on page load or not
     * use_container:           Wrap Editor contents into div or not
     * no_display:              Hide Editor container or not (related to use_container)
     * translator:              Helper to translate phrases in lib
     * files_browser_*:         Files Browser (media, images) settings
     * encode_directives:       Encode template directives with JS or not
     *
     * @param array|\Magento\Framework\DataObject $data Object constructor params to override default config values
     * @return \Magento\Framework\DataObject
     */
    public function getConfig($data = [])
    {
        $config = new \Magento\Framework\DataObject();

        $config->setData(
            [
                'enabled' => $this->isEnabled(),
                'hidden' => $this->isHidden(),
                'use_container' => false,
                'add_variables' => false,
                'add_widgets' => false,
                'no_display' => false,
                'encode_directives' => false,
                'add_images' => false,

            ]
        );

        $config->setData('directives_url_quoted', preg_quote($config->getData('directives_url')));


        if (is_array($data)) {
            $config->addData($data);
        }


        return $config;
    }

}
