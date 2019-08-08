<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePay\Block\Adminhtml\System\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;

class RegisterInfo extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * Render fieldset html
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        if($element->getId() == 'payment_us_iwd_bluepay_register_info') {
            $content = __(
                "Please select the reqister button to view exclusive BluePay rates from IWD 
                and complete the quick sign-up process."
            );
            $html = $this->getInfoHtml($content);
        } else {
            $text = __(
                "A BluePay account is required to use this subscription module.
            Please click the reqister button to view your exclusive rates from IWD and 
            complete the quick signup process"
            );
            $note = __('Please Note: ');
            $content = '<span class="notes">' . $note . '</span>' . $text;
            $html = $this->getInfoHtml($content);
        }

        return $html;
    }

    /**
     * @param $content
     * @return mixed
     */
    public function getInfoHtml($content)
    {
        $html = "<div class='bluepay-register content'>$content</div>";
        $html .= '<div><a href="https://cardconnect.com/partner/iwd" 
            target="_blank" class="action-primary bluepay-register button" type="button" data-role="action">
            <span>' . __('Register') . '</span></a></div>';

        return $html;
    }
}
