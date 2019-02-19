<?php
namespace Vendor\Module\Plugin\Block\Adminhtml\User\Edit\Tab;

class Main
{
    /**
     * Get form HTML
     *
     * @return string
     */
    public function aroundGetFormHtml(
        \Magento\User\Block\User\Edit\Tab\Main $subject,
        \Closure $proceed
    )
    {
        $form = $subject->getForm();
        if (is_object($form)) {
             $baseFieldset = $form->getElement('base_fieldset');
            /** @var $model \Magento\User\Model\User */
            $model = $this->registry->registry('permissions_user');
            $data = $model->getData();
            $baseFieldset->addField(
                'manage_data',
                'select',
                [
                    'name' => 'manage_data',
                    'label' => __('Select Manager'),
                    'title' => __('Select Manager'),
                    'options' => ['0' => __('--Select--'), '1' => __('Option 1'), '2' => __('Option 2'), '3' => __('Option 3')],
                    'class' => 'select',
                    'value' => isset($data['manage_data']) ? $data['manage_data'] : 0
                ]
            );

            $subject->setForm($form);
        }

        return $proceed();

        
    }
}