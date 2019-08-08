<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
// @codingStandardsIgnoreFile
namespace IWD\BluePay\Model\Adminhtml\System\Config\Backend;

class Encrypted extends \Magento\Config\Model\Config\Backend\Encrypted
{
    /**
     * Encrypt value before saving
     *
     * @return void
     */
    public function beforeSave()
    {
        $this->_dataSaveAllowed = false;
        $value = $this->getValue();
        // don't save value, if an obscured value was received. This indicates that data was not changed.
        if (!preg_match('/^\*+$/', $value) && !empty($value)) {
            if ($this->getPath() == "payment/iwd_bluepay/account_id" && strlen($value) != 12) {
                $this->_dataSaveAllowed = false;
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Error. Account ID must be 12 digits and begin with 100. Your settings have not been saved.")
                );
            } else if ($this->getPath() == "payment/iwd_bluepay/secret_key" && strlen($value) != 32) {
                $this->_dataSaveAllowed = false;
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Error. Secret Key must be 32 digits. Your settings have not been saved.")
                );
            }
            $this->_dataSaveAllowed = true;
            $encrypted = $this->_encryptor->encrypt($value);
            $this->setValue($encrypted);
        }
    }

}