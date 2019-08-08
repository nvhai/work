<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Layout\Processor\AddressAttributes;

use Aheadworks\OneStepCheckout\Model\Address\Form\FieldRow\Mapper as FieldRowMapper;

/**
 * Class Merger
 * @package Aheadworks\OneStepCheckout\Model\Layout\Processor\AddressAttributes
 */
class Merger
{
    /**
     * @var array
     */
    private $formElementMap = [
        'checkbox' => 'Magento_Ui/js/form/element/select',
        'select' => 'Magento_Ui/js/form/element/select',
        'textarea'  => 'Magento_Ui/js/form/element/textarea',
        'multiline' => 'Magento_Ui/js/form/components/group',
        'multiselect' => 'Magento_Ui/js/form/element/multiselect',
    ];

    /**
     * @var array
     */
    private $templateMap = [
        'image' => 'ui/form/element/media'
    ];

    /**
     * @var array
     */
    private $inputValidationMap = [
        'alpha' => 'validate-alpha',
        'numeric' => 'validate-number',
        'alphanumeric' => 'validate-alphanum',
        'url' => 'validate-url',
        'email' => 'email2',
    ];

    /**
     * @var array
     */
    private $initiallyHiddenFields = ['region'];

    /**
     * @var FieldRowMapper
     */
    private $fieldRowMapper;

    /**
     * @param FieldRowMapper $fieldRowMapper
     */
    public function __construct(FieldRowMapper $fieldRowMapper)
    {
        $this->fieldRowMapper = $fieldRowMapper;
    }

    /**
     * Merge additional address fields for given provider
     *
     * @param array $elements
     * @param string $providerName
     * @param string $dataScopePrefix
     * @param array $fieldRows
     * @return array
     */
    public function merge($elements, $providerName, $dataScopePrefix, array $fieldRows = [])
    {
        foreach ($elements as $attributeCode => $attributeConfig) {
            $row = $this->fieldRowMapper->toFieldRow($attributeCode);
            if (!$row) {
                $row = $attributeCode . '-field-row';
                $fieldRows[$row] = $this->getNewRowConfig();
            }

            $additionalConfig = isset($fieldRows[$row]['children'][$attributeCode])
                ? $fieldRows[$row]['children'][$attributeCode]
                : [];
            if ($this->isFieldVisible($attributeConfig, $additionalConfig)
                || $this->isFieldInitiallyHidden($attributeCode)
            ) {
                $fieldRows[$row]['children'][$attributeCode] = $this->getFieldConfig(
                    $attributeCode,
                    $attributeConfig,
                    $additionalConfig,
                    $providerName,
                    $dataScopePrefix
                );
            }
        }
        return $fieldRows;
    }

    /**
     * Get new field row config
     *
     * @return array
     */
    private function getNewRowConfig()
    {
        return [
            'component' => 'uiComponent',
            'config' => [
                'template' => 'Aheadworks_OneStepCheckout/form/field-row'
            ],
            'children' => []
        ];
    }

    /**
     * Retrieve UI field configuration for given attribute
     *
     * @param string $attributeCode
     * @param array $attributeConfig
     * @param array $additionalConfig
     * @param string $providerName
     * @param string $dataScopePrefix
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function getFieldConfig(
        $attributeCode,
        array $attributeConfig,
        array $additionalConfig,
        $providerName,
        $dataScopePrefix
    ) {
        if (isset($attributeConfig['validation']['input_validation'])) {
            $validationRule = $attributeConfig['validation']['input_validation'];
            $attributeConfig['validation'][$this->inputValidationMap[$validationRule]] = true;
            unset($attributeConfig['validation']['input_validation']);
        }

        if ($attributeConfig['formElement'] == 'multiline') {
            return $this->getMultilineFieldConfig(
                $attributeCode,
                $attributeConfig,
                $additionalConfig,
                $providerName,
                $dataScopePrefix
            );
        }

        $uiComponent = isset($this->formElementMap[$attributeConfig['formElement']])
            ? $this->formElementMap[$attributeConfig['formElement']]
            : 'Magento_Ui/js/form/element/abstract';
        $elementTemplate = isset($this->templateMap[$attributeConfig['formElement']])
            ? $this->templateMap[$attributeConfig['formElement']]
            : 'ui/form/element/' . $attributeConfig['formElement'];

        $element = [
            'component' => isset($additionalConfig['component']) ? $additionalConfig['component'] : $uiComponent,
            'config' => [
                'customScope' => $dataScopePrefix,
                'customEntry' => isset($additionalConfig['config']['customEntry'])
                    ? $additionalConfig['config']['customEntry']
                    : null,
                'template' => 'Aheadworks_OneStepCheckout/form/field',
                'elementTmpl' => isset($additionalConfig['config']['elementTmpl'])
                    ? $additionalConfig['config']['elementTmpl']
                    : $elementTemplate,
                'tooltip' => isset($additionalConfig['config']['tooltip'])
                    ? $additionalConfig['config']['tooltip']
                    : null
            ],
            'dataScope' => $dataScopePrefix . '.' . $attributeCode,
            'label' => $attributeConfig['label'],
            'provider' => $providerName,
            'sortOrder' => isset($additionalConfig['sortOrder'])
                ? $additionalConfig['sortOrder']
                : $attributeConfig['sortOrder'],
            'validation' => $this->mergeConfigurationNode('validation', $additionalConfig, $attributeConfig),
            'options' => isset($attributeConfig['options']) ? $attributeConfig['options'] : [],
            'filterBy' => isset($additionalConfig['filterBy']) ? $additionalConfig['filterBy'] : null,
            'customEntry' => isset($additionalConfig['customEntry']) ? $additionalConfig['customEntry'] : null
        ];

        if (isset($additionalConfig['visible'])) {
            $element['visible'] = $additionalConfig['visible'];
        } elseif ($this->isFieldInitiallyHidden($attributeCode)) {
            $element['visible'] = false;
        } else {
            $element['visible'] = true;
        }
        if (isset($attributeConfig['value']) && $attributeConfig['value'] != null) {
            $element['value'] = $attributeConfig['value'];
        } elseif (isset($attributeConfig['default']) && $attributeConfig['default'] != null) {
            $element['value'] = $attributeConfig['default'];
        }
        if (isset($additionalConfig['config']['additionalClasses'])) {
            $element['config']['additionalClasses'] = $additionalConfig['config']['additionalClasses'];
        }

        return $element;
    }

    /**
     * Merge two configuration nodes recursively
     *
     * @param string $nodeName
     * @param array $mainSource
     * @param array $additionalSource
     * @return array
     */
    private function mergeConfigurationNode($nodeName, array $mainSource, array $additionalSource)
    {
        $mainData = isset($mainSource[$nodeName]) ? $mainSource[$nodeName] : [];
        $additionalData = isset($additionalSource[$nodeName]) ? $additionalSource[$nodeName] : [];
        return array_replace_recursive($additionalData, $mainData);
    }

    /**
     * Check if address attribute is visible on frontend
     *
     * @param array $attributeConfig
     * @param array $additionalConfig
     * @return bool
     */
    private function isFieldVisible(array $attributeConfig, array $additionalConfig = [])
    {
        if ($attributeConfig['visible'] == false
            || (isset($additionalConfig['visible']) && $additionalConfig['visible'] == false)
        ) {
            return false;
        }
        return true;
    }

    /**
     * Check if field initially hidden
     *
     * @param string $attributeCode
     * @return bool
     */
    private function isFieldInitiallyHidden($attributeCode)
    {
        return in_array($attributeCode, $this->initiallyHiddenFields);
    }

    /**
     * Retrieve field configuration for street address attribute
     *
     * @param string $attributeCode
     * @param array $attributeConfig
     * @param array $additionalConfig
     * @param string $providerName name of the storage container used by UI component
     * @param string $dataScopePrefix
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function getMultilineFieldConfig(
        $attributeCode,
        array $attributeConfig,
        array $additionalConfig,
        $providerName,
        $dataScopePrefix
    ) {
        $lines = [];
        unset($attributeConfig['validation']['required-entry']);
        for ($lineIndex = 0; $lineIndex < (int)$attributeConfig['size']; $lineIndex++) {
            $isFirstLine = $lineIndex === 0;
            $label = isset($additionalConfig['label'])
                ? $additionalConfig['label']
                : $attributeConfig['label'];
            $line = [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'label' => $label . ' Line' . ($isFirstLine ? '' : ' ' . ($lineIndex + 1)),
                'config' => [
                    'customScope' => $dataScopePrefix,
                    'template' => 'Aheadworks_OneStepCheckout/form/field-multiline',
                    'elementTmpl' => $isFirstLine && $attributeCode == 'street'
                        ? 'Aheadworks_OneStepCheckout/form/element/input-autocomplete'
                        : 'ui/form/element/input'
                ],
                'dataScope' => $lineIndex,
                'provider' => $providerName,
                'validation' => $isFirstLine
                    ? array_merge(
                        ['required-entry' => (bool)$attributeConfig['required']],
                        $attributeConfig['validation']
                    )
                    : $attributeConfig['validation']

            ];
            if ($isFirstLine && isset($attributeConfig['default']) && $attributeConfig['default'] != null) {
                $line['value'] = $attributeConfig['default'];
            }
            $lines[] = $line;
        }
        return [
            'component' => 'Magento_Ui/js/form/components/group',
            'required' => (bool)$attributeConfig['required'],
            'dataScope' => $dataScopePrefix . '.' . $attributeCode,
            'provider' => $providerName,
            'sortOrder' => $attributeConfig['sortOrder'],
            'type' => 'group',
            'config' => [
                'template' => 'ui/group/group',
                'fieldTemplate' => 'Aheadworks_OneStepCheckout/form/field-multiline',
                'additionalClasses' => $attributeCode
            ],
            'children' => $lines,
        ];
    }
}
