<?php

namespace Magenest\Bigk\Controller\Tool;

use Magento\Framework\Controller\ResultFactory;

class Calculator extends \Magento\Framework\App\Action\Action {

    protected $_bigkHelper;
    protected $resultForwardFactory;

    public function __construct(
    \Magento\Framework\App\Action\Context $context, \Magento\Store\Model\StoreManager $storeManager, \Magenest\Bigk\Helper\Data $bigkHelper, \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
    ) {
        $this->_bigkHelper = $bigkHelper;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    public function execute() {
        if (!$this->_bigkHelper->getConfig('bigk/general_settings/enabled')) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->set(__('Công cụ tính toán hệ BigK'));
        return $resultPage;
    }

}
