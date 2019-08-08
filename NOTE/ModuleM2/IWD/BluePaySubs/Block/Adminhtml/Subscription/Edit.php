<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Block\Adminhtml\Subscription;

use IWD\BluePaySubs\Model\Source\Status;

/**
 * Edit Class
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var Status
     */
    protected $statusSource;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * Edit constructor.
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param Status $statusSource
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        Status $statusSource,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        array $data
    ) {
        parent::__construct($context, $data);

        $this->registry = $registry;
        $this->statusSource = $statusSource;
        $this->messageManager = $messageManager;
    }

    /**
     * Internal constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Subscription'));

        $this->_objectId = 'entity_id';
        $this->_blockGroup = 'IWD_BluePaySubs';
        $this->_controller = 'adminhtml_subscription';
        $this->_mode = 'edit';
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Edit Subscription');
    }

    /**
     * Prepare layout.
     *
     * @return $this
     */
    protected function _preparelayout()
    {
        /** @var \IWD\BluePaySubs\Model\Subscription $subscription */
        $subscription = $this->registry->registry('current_bsubs');

        $this->addButton(
            'synchronize',
            [
                'label' => __('Synchronize'),
                'class' => 'sync',
                'onclick' => 'setLocation(\'' . $this->escapeUrl(
                        $this->getUrl(
                            '*/*/synchronize',
                            [
                                'entity_id' => $subscription->getId(),
                            ]
                        )
                    ) . '\')',
            ],
            0,
            100
        );

        if (time() >= strtotime($subscription->getNextDate()) && $this->statusSource->isActive($subscription)) {
            $this->removeButton('save');
            $this->removeButton('delete');
            $this->messageManager->addWarningMessage(__('Saving not allowed. Please, synchronize subscription'));
        } else {

            $this->addButton(
                'save_and_edit',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
                    ]
                ],
                0,
                1000
            );

            if ($this->statusSource->canSetStatus($subscription, Status::STATUS_ACTIVE)) {
                $this->addButton(
                    'activate',
                    [
                        'label' => __('Activate'),
                        'class' => 'activate',
                        'onclick' => 'setLocation(\'' . $this->escapeUrl(
                                $this->getUrl(
                                    '*/*/changeStatus',
                                    [
                                        'status' => Status::STATUS_ACTIVE,
                                        'entity_id' => $subscription->getId(),
                                    ]
                                )
                            ) . '\')',
                    ],
                    0,
                    200
                );
            }

            if ($this->statusSource->canSetStatus($subscription, Status::STATUS_STOPPED)) {
                $this->addButton(
                    'stop',
                    [
                        'label' => __('Stop'),
                        'class' => 'stop',
                        'onclick' => 'setLocation(\'' . $this->escapeUrl(
                                $this->getUrl(
                                    '*/*/changeStatus',
                                    [
                                        'status' => Status::STATUS_STOPPED,
                                        'entity_id' => $subscription->getId(),
                                    ]
                                )
                            ) . '\')',
                    ],
                    0,
                    300
                );
            }
        }

        parent::_prepareLayout();

        return $this;
    }
}
