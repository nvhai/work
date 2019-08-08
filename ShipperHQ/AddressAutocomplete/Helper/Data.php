<?php
namespace ShipperHQ\AddressAutocomplete\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Helper\View
     */
    protected $_customerViewHelper;


    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Get config value
     */
    public function getConfigValue($value = '') {
        return $this->scopeConfig
            ->getValue(
                $value,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }
}