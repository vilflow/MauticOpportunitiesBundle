<?php

namespace MauticPlugin\MauticOpportunitiesBundle\Form\Type;

use Mautic\LeadBundle\Model\LeadModel;
use MauticPlugin\MauticOpportunitiesBundle\Helper\OpportunityFieldMetadataHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Mautic\CoreBundle\Translation\Translator;
use Symfony\Component\Validator\Constraints\NotBlank;
use MauticPlugin\MauticOpportunitiesBundle\Form\Type\OpportunityFieldsType;

/**
 * @extends AbstractType<mixed>
 */
class OpportunityFieldValueConditionType extends AbstractType
{
    public function __construct(
        protected Translator $translator,
        protected LeadModel $leadModel,
        protected OpportunityFieldMetadataHelper $opportunityFieldMetadataHelper,

    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'field',
            OpportunityFieldsType::class,
            [
                'label'                 => 'mautic.lead.campaign.event.field',
                'label_attr'            => ['class' => 'control-label'],
                'multiple'              => false,
                'placeholder'           => 'mautic.core.select',
                'attr'                  => [
                    'class'    => 'form-control',
                    'tooltip'  => 'mautic.lead.campaign.event.field_descr',
                    'onchange' => 'Mautic.updateOpportunityFieldValues(this)',
                ],
                'required'    => true,
                'constraints' => [
                    new NotBlank(
                        ['message' => 'mautic.core.value.required']
                    ),
                ],
            ]
        );

        // function to add 'template' choice field dynamically
        $func = function (FormEvent $e): void {
            $data = $e->getData();
            $form = $e->getForm();

            $fieldValues = null;
            $fieldType   = null;
            $choiceAttr  = [];
            $operator    = '=';

            if (isset($data['field'])) {
                $selectedField = $data['field'];
                $operator = $data['operator'] ?? '=';

                $fieldType = $this->opportunityFieldMetadataHelper->getFieldType($selectedField);

                $currentValue = $data['value'] ?? null;
                $optionsMeta = $this->opportunityFieldMetadataHelper->getFieldOptions($selectedField, $operator, $currentValue);
                if (!empty($optionsMeta['options'])) {
                    $fieldValues = $optionsMeta['options'];
                    $customChoiceValue = $optionsMeta['customChoiceValue'];
                    $optionsAttr = $optionsMeta['optionsAttr'];
                    if (null !== $customChoiceValue) {
                        $choiceAttr = function ($value, $key, $index) use ($customChoiceValue, $optionsAttr): array {
                            if ($customChoiceValue === $value) {
                                return ['data-custom' => 1];
                            }
                            return $optionsAttr[$value] ?? [];
                        };
                    } elseif (!empty($optionsAttr)) {
                        $choiceAttr = function ($value, $key, $index) use ($optionsAttr): array {
                            return $optionsAttr[$value] ?? [];
                        };
                    }
                }
            }

            $supportsValue   = !in_array($operator, ['empty', '!empty']);
            $supportsChoices = !in_array($operator, ['empty', '!empty', 'regexp', '!regexp']);

            // Check if field allows freeform input and current value
            $rawSubmittedValue = $data['value'] ?? null;
            $hasCurrentValue = !(
                null === $rawSubmittedValue
                || (is_string($rawSubmittedValue) && '' === $rawSubmittedValue)
                || (is_array($rawSubmittedValue) && [] === $rawSubmittedValue)
            );
            $allowsFreeform = isset($data['field']) && $this->opportunityFieldMetadataHelper->allowsFreeformInput($data['field']);

            // Display selectbox for a field with choices, textbox for others
            // For fields that allow freeform input, only show dropdown if there's a current value that matches predefined options
            if ((!empty($fieldValues) || 'select' === $fieldType) && $supportsChoices) {
                $valueMatchesPredefinedOptions = static function ($value) use ($fieldValues): bool {
                    // If no fieldValues, can't match predefined options
                    if (empty($fieldValues)) {
                        return false;
                    }

                    if (is_array($value)) {
                        foreach ($value as $item) {
                            if (!isset($fieldValues[$item ?? ''])) {
                                return false;
                            }
                        }

                        return [] !== $value;
                    }

                    return isset($fieldValues[$value ?? '']);
                };

                if ($allowsFreeform && $hasCurrentValue && !$valueMatchesPredefinedOptions($rawSubmittedValue)) {
                    $shouldShowAsText = true;
                } else {
                    $shouldShowAsText = false;
                }
            } else {
                $shouldShowAsText = true;
            }

            // For date fields with 'date' operator, show select with predefined options
            // For other operators, show date picker input directly
            if ('date' === $fieldType && !empty($fieldValues) && $supportsChoices && 'date' === $operator) {
                $multiple = in_array($operator, ['in', '!in']);
                $rawValue = $data['value'] ?? null;
                $value    = $multiple && !is_array($rawValue) ? (null !== $rawValue ? [$rawValue] : []) : $rawValue;

                $form->add(
                    'value',
                    TextType::class,
                    [
                        'label'       => 'mautic.form.field.form.value',
                        'label_attr'  => ['class' => 'control-label'],
                        'attr'        => [
                            'class'                => 'form-control opportunity-date-value-placeholder',
                            'onchange'             => 'Mautic.updateOpportunityFieldValueOptions(this)',
                            'data-toggle'          => $fieldType,
                            'data-onload-callback' => 'updateOpportunityFieldValueOptions',
                        ],
                        'required'    => $supportsValue,
                        'constraints' => $supportsValue ? [
                            new NotBlank(
                                ['message' => 'mautic.core.value.required']
                            ),
                        ] : [],
                        'data'        => $value,
                    ]
                );
            } elseif (!$shouldShowAsText && ((!empty($fieldValues) || 'select' === $fieldType) && $supportsChoices)) {
                $multiple = in_array($operator, ['in', '!in']);
                $rawValue = $data['value'] ?? null;
                $value    = $multiple && !is_array($rawValue) ? (null !== $rawValue ? [$rawValue] : []) : $rawValue;

                $form->add(
                    'value',
                    ChoiceType::class,
                    [
                        // Symfony expects 'label' => 'value'
                        'choices'           => !empty($fieldValues) ? array_flip($fieldValues) : [],
                        'label'             => 'mautic.form.field.form.value',
                        'label_attr'        => ['class' => 'control-label'],
                        'attr'              => [
                            'class'                => 'form-control',
                            'onchange'             => 'Mautic.updateOpportunityFieldValueOptions(this)',
                            'data-toggle'          => $fieldType,
                            'data-onload-callback' => 'updateOpportunityFieldValueOptions',
                        ],
                        'choice_attr' => $choiceAttr,
                        'required'    => $supportsValue,
                        'constraints' => $supportsValue ? [
                            new NotBlank(
                                ['message' => 'mautic.core.value.required']
                            ),
                        ] : [],
                        'multiple' => $multiple,
                        'data'     => $value,
                    ]
                );
            } else {
                $attr = [
                    'class'                => 'form-control',
                    'data-toggle'          => $fieldType,
                    'data-onload-callback' => 'updateOpportunityFieldValueOptions',
                ];

                // Add datepicker for date/datetime fields
                if ('date' === $fieldType) {
                    $attr['class'] .= ' opportunitydatepicker';
                    $attr['placeholder'] = 'yyyy-mm-dd';
                }

                // Add datalist support for freeform fields with predefined options
                if ($allowsFreeform && !empty($fieldValues)) {
                    $attr['list'] = 'opportunity_field_options_' . ($data['field'] ?? '');
                    $attr['data-options'] = json_encode(array_values($fieldValues));
                }

                if (!$supportsValue) {
                    $attr['disabled'] = 'disabled';
                }

                $form->add(
                    'value',
                    TextType::class,
                    [
                        'label'       => 'mautic.form.field.form.value',
                        'label_attr'  => ['class' => 'control-label'],
                        'attr'        => $attr,
                        'constraints' => ($supportsValue) ? [
                            new NotBlank(
                                ['message' => 'mautic.core.value.required']
                            ),
                        ] : [],
                    ]
                );
            }

            $form->add(
                'operator',
                ChoiceType::class,
                [
                    'choices'           => $this->leadModel->getOperatorsForFieldType(null == $fieldType ? 'default' : $fieldType, ['date']),
                    'label'             => 'mautic.lead.lead.submitaction.operator',
                    'label_attr'        => ['class' => 'control-label'],
                    'attr'              => [
                        'onchange' => 'Mautic.updateOpportunityFieldValues(this)',
                    ],
                ]
            );
        };

        // Register the function above as EventListener on PreSet and PreBind
        $builder->addEventListener(FormEvents::PRE_SET_DATA, $func);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, $func);
    }

    public function getBlockPrefix(): string
    {
        return 'campaignevent_opportunity_field_value';
    }
}
