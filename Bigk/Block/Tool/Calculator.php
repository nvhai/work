<?php

namespace Magenest\Bigk\Block\Tool;

use Magento\Framework\App\RequestInterface;

class Calculator extends \Magento\Framework\View\Element\Template {

    protected $_coreRegistry = null;
    protected $_bigkHelper;
    protected $httpContext;
    protected $request;

    public function __construct(
    \Magento\Framework\View\Element\Template\Context $context, \Magento\Framework\Registry $registry, \Magenest\Bigk\Helper\Data $bigkHelper, \Magento\Framework\App\Http\Context $httpContext, array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->request = $context->getRequest();
        $this->_bigkHelper = $bigkHelper;
        $this->httpContext = $httpContext;
        parent::__construct($context, $data);
    }

    public function _construct() {
        if (!$this->getConfig('general_settings/enabled'))
            return;
        parent::_construct();
    }

    protected function _addBreadcrumbs() {
        $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $breadcrumbsBlock->addCrumb(
                'home', [
            'label' => __('Home'),
            'title' => __('Go to Home Page'),
            'link' => $baseUrl
                ]
        );
        $breadcrumbsBlock->addCrumb(
                'bigk_calculation_tool', [
            'label' => __('Công cụ tính toán hệ BigK'),
            'title' => __('Công cụ tính toán hệ BigK'),
            'link' => ''
                ]
        );
    }

    public function getConfig($path, $default = '') {
        $result = $this->_bigkHelper->getConfig($path);
        if (!$result) {
            return $default;
        }
        return $result;
    }

    protected function _prepareLayout() {
        $this->_addBreadcrumbs();
        $this->pageConfig->addBodyClass('bigk-calculator-tool');
        return parent::_prepareLayout();
    }

}
