<?php
namespace Vnecoms\ShopByBrand\Block\Adminhtml\Brand\Edit;

use Magento\Backend\Block\Widget\Form as WidgetForm;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * @return WidgetForm
     */
    protected function _prepareForm()
    {
        //echo $this->getData('action');exit;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post','enctype'=>'multipart/form-data']]
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}