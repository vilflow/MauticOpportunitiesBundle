<?php

namespace MauticPlugin\MauticOpportunitiesBundle\Form\Type;

use Mautic\CoreBundle\Helper\ArrayHelper;
use Mautic\CoreBundle\Translation\Translator;
use Mautic\LeadBundle\Helper\FormFieldHelper;
use MauticPlugin\MauticOpportunitiesBundle\Entity\Opportunity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<mixed>
 */
class OpportunityFieldValueConditionType extends AbstractType
{
    public function __construct(
        protected Translator $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'field',
            ChoiceType::class,
            [
                'label' => 'mautic.opportunities.campaign.condition.field',
                'label_attr' => ['class' => 'control-label'],
                'choices' => $this->getOpportunityFieldChoices(),
                'placeholder' => 'mautic.core.select',
                'attr' => [
                    'class' => 'form-control',
                    'tooltip' => 'mautic.opportunities.campaign.condition.field_descr',
                    'onchange' => 'Mautic.updateOpportunityFieldValues(this)',
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(
                        ['message' => 'mautic.core.value.required']
                    ),
                ],
            ]
        );

        // Function to add 'template' choice field dynamically
        $func = function (FormEvent $e): void {
            $data = $e->getData();
            $form = $e->getForm();

            $fieldValues = null;
            $fieldType = null;
            $choiceAttr = [];
            $operator = '=';

            if (isset($data['field'])) {
                $field = $data['field'];
                $operator = $data['operator'] ?? 'eq';

                // Get field values based on the opportunity field
                $fieldValues = $this->getFieldValues($field);
                $fieldType = $this->getFieldType($field);
            }

            $supportsValue = !in_array($operator, ['empty', '!empty']);
            $supportsChoices = !in_array($operator, ['empty', '!empty', 'regexp', '!regexp']);

            // Display selectbox for a field with choices, textbox for others
            if (!empty($fieldValues) && $supportsChoices) {
                $multiple = in_array($operator, ['in', '!in']);
                $value = $multiple && !is_array($data['value']) ? [$data['value']] : $data['value'];

                $form->add(
                    'value',
                    ChoiceType::class,
                    [
                        'choices' => array_flip($fieldValues),
                        'label' => 'mautic.form.field.form.value',
                        'label_attr' => ['class' => 'control-label'],
                        'attr' => [
                            'class' => 'form-control',
                            'onchange' => 'Mautic.updateOpportunityFieldValueOptions(this)',
                            'data-toggle' => $fieldType,
                            'data-onload-callback' => 'updateOpportunityFieldValueOptions',
                        ],
                        'choice_attr' => $choiceAttr,
                        'required' => true,
                        'constraints' => [
                            new NotBlank(
                                ['message' => 'mautic.core.value.required']
                            ),
                        ],
                        'multiple' => $multiple,
                        'data' => $value,
                    ]
                );
            } else {
                $attr = [
                    'class' => 'form-control',
                    'data-toggle' => $fieldType,
                    'data-onload-callback' => 'updateOpportunityFieldValueOptions',
                ];

                if (!$supportsValue) {
                    $attr['disabled'] = 'disabled';
                }

                $form->add(
                    'value',
                    TextType::class,
                    [
                        'label' => 'mautic.form.field.form.value',
                        'label_attr' => ['class' => 'control-label'],
                        'attr' => $attr,
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
                    'choices' => $this->getOperatorsForFieldType($fieldType),
                    'label' => 'mautic.lead.lead.submitaction.operator',
                    'label_attr' => ['class' => 'control-label'],
                    'attr' => [
                        'onchange' => 'Mautic.updateOpportunityFieldValues(this)',
                    ],
                ]
            );
        };

        // Register the function above as EventListener on PreSet and PreBind
        $builder->addEventListener(FormEvents::PRE_SET_DATA, $func);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, $func);
    }

    private function getOpportunityFieldChoices(): array
    {
        return [
            'mautic.opportunities.field.name' => 'name',
            'mautic.opportunities.field.description' => 'description',
            'mautic.opportunities.field.opportunity_type' => 'opportunityType',
            'mautic.opportunities.field.lead_source' => 'leadSource',
            'mautic.opportunities.field.amount' => 'amount',
            'mautic.opportunities.field.amount_usdollar' => 'amountUsdollar',
            'mautic.opportunities.field.date_closed' => 'dateClosed',
            'mautic.opportunities.field.next_step' => 'nextStep',
            'mautic.opportunities.field.sales_stage' => 'salesStage',
            'mautic.opportunities.field.probability' => 'probability',
            'mautic.opportunities.field.institution_c' => 'institutionC',
            'mautic.opportunities.field.review_result_c' => 'reviewResultC',
            'mautic.opportunities.field.abstract_book_send_date_c' => 'abstractBookSendDateC',
            'mautic.opportunities.field.abstract_review_result_url_c' => 'abstractReviewResultUrlC',
            'mautic.opportunities.field.abstract_book_dpublication_c' => 'abstractBookDpublicationC',
            'mautic.opportunities.field.extra_paper_c' => 'extraPaperC',
            'mautic.opportunities.field.sales_receipt_url_c' => 'salesReceiptUrlC',
            'mautic.opportunities.field.abstract_result_send_date_c' => 'abstractResultSendDateC',
            'mautic.opportunities.field.registration_type_c' => 'registrationTypeC',
            'mautic.opportunities.field.abstract_c' => 'abstractC',
            'mautic.opportunities.field.abstract_book_information_c' => 'abstractBookInformationC',
            'mautic.opportunities.field.payment_status_c' => 'paymentStatusC',
            'mautic.opportunities.field.coupon_code_c' => 'couponCodeC',
            'mautic.opportunities.field.abstract_result_ready_date_c' => 'abstractResultReadyDateC',
            'mautic.opportunities.field.paper_title_c' => 'paperTitleC',
            'mautic.opportunities.field.sms_permission_c' => 'smsPermissionC',
            'mautic.opportunities.field.jjwg_maps_geocode_status_c' => 'jjwgMapsGeocodeStatusC',
            'mautic.opportunities.field.invoice_url_c' => 'invoiceUrlC',
            'mautic.opportunities.field.presentation_type_c' => 'presentationTypeC',
            'mautic.opportunities.field.invitation_letter_url_c' => 'invitationLetterUrlC',
            'mautic.opportunities.field.withdraw_c' => 'withdrawC',
            'mautic.opportunities.field.keywords_c' => 'keywordsC',
            'mautic.opportunities.field.jjwg_maps_lng_c' => 'jjwgMapsLngC',
            'mautic.opportunities.field.jjwg_maps_lat_c' => 'jjwgMapsLatC',
            'mautic.opportunities.field.transaction_id_c' => 'transactionIdC',
            'mautic.opportunities.field.co_authors_names_c' => 'coAuthorsNamesC',
            'mautic.opportunities.field.abstract_attachment_c' => 'abstractAttachmentC',
            'mautic.opportunities.field.acceptance_letter_url_c' => 'acceptanceLetterUrlC',
            'mautic.opportunities.field.payment_channel_c' => 'paymentChannelC',
            'mautic.opportunities.field.wire_transfer_attachment_c' => 'wireTransferAttachmentC',
            'mautic.opportunities.field.jjwg_maps_address_c' => 'jjwgMapsAddressC',
            'mautic.opportunities.field.opportunity_external_id' => 'opportunityExternalId',
            'mautic.opportunities.field.invitation_url' => 'invitationUrl',
            'mautic.opportunities.field.suitecrm_id' => 'suitecrmId',
        ];
    }

    private function getFieldValues(?string $field): ?array
    {
        if (null === $field) {
            return null;
        }

        switch ($field) {
            case 'salesStage':
                return Opportunity::getStageChoices();
            case 'opportunityType':
                return Opportunity::getOpportunityTypeChoices();
            case 'leadSource':
                return Opportunity::getLeadSourceChoices();
            case 'presentationTypeC':
                return Opportunity::getPresentationTypeChoices();
            case 'registrationTypeC':
                return Opportunity::getRegistrationTypeChoices();
            case 'paymentStatusC':
                return Opportunity::getPaymentStatusChoices();
            case 'paymentChannelC':
                return Opportunity::getPaymentChannelChoices();
            case 'reviewResultC':
                return Opportunity::getReviewResultChoices();
            case 'abstractBookDpublicationC':
            case 'smsPermissionC':
            case 'withdrawC':
                return [
                    0 => $this->translator->trans('mautic.core.form.no'),
                    1 => $this->translator->trans('mautic.core.form.yes'),
                ];
            default:
                return null;
        }
    }

    private function getFieldType(?string $field): string
    {
        if (null === $field) {
            return 'text';
        }

        switch ($field) {
            case 'amount':
            case 'amountUsdollar':
            case 'probability':
            case 'jjwgMapsLngC':
            case 'jjwgMapsLatC':
                return 'number';
            case 'dateClosed':
            case 'abstractBookSendDateC':
            case 'abstractResultSendDateC':
            case 'abstractResultReadyDateC':
                return 'date';
            case 'abstractBookDpublicationC':
            case 'smsPermissionC':
            case 'withdrawC':
                return 'boolean';
            case 'salesStage':
            case 'opportunityType':
            case 'leadSource':
            case 'presentationTypeC':
            case 'registrationTypeC':
            case 'paymentStatusC':
            case 'paymentChannelC':
            case 'reviewResultC':
                return 'select';
            default:
                return 'text';
        }
    }

    private function getOperatorsForFieldType(?string $fieldType): array
    {
        $operators = [
            'text' => [
                'mautic.core.operator.equals' => 'eq',
                'mautic.core.operator.not.equals' => 'neq',
                'mautic.core.operator.contains' => 'like',
                'mautic.core.operator.not.contains' => '!like',
                'mautic.core.operator.starts.with' => 'startsWith',
                'mautic.core.operator.ends.with' => 'endsWith',
                'mautic.core.operator.empty' => 'empty',
                'mautic.core.operator.not.empty' => '!empty',
                'mautic.core.operator.regexp' => 'regexp',
                'mautic.core.operator.not.regexp' => '!regexp',
            ],
            'number' => [
                'mautic.core.operator.equals' => 'eq',
                'mautic.core.operator.not.equals' => 'neq',
                'mautic.core.operator.greater.than' => 'gt',
                'mautic.core.operator.greater.than.equal' => 'gte',
                'mautic.core.operator.less.than' => 'lt',
                'mautic.core.operator.less.than.equal' => 'lte',
                'mautic.core.operator.empty' => 'empty',
                'mautic.core.operator.not.empty' => '!empty',
            ],
            'date' => [
                'mautic.core.operator.equals' => 'eq',
                'mautic.core.operator.not.equals' => 'neq',
                'mautic.core.operator.greater.than' => 'gt',
                'mautic.core.operator.greater.than.equal' => 'gte',
                'mautic.core.operator.less.than' => 'lt',
                'mautic.core.operator.less.than.equal' => 'lte',
                'mautic.core.operator.empty' => 'empty',
                'mautic.core.operator.not.empty' => '!empty',
            ],
            'boolean' => [
                'mautic.core.operator.equals' => 'eq',
                'mautic.core.operator.not.equals' => 'neq',
            ],
            'select' => [
                'mautic.core.operator.equals' => 'eq',
                'mautic.core.operator.not.equals' => 'neq',
                'mautic.core.operator.in' => 'in',
                'mautic.core.operator.not.in' => '!in',
                'mautic.core.operator.empty' => 'empty',
                'mautic.core.operator.not.empty' => '!empty',
            ],
        ];

        return $operators[$fieldType ?? 'text'] ?? $operators['text'];
    }

    public function getBlockPrefix(): string
    {
        return 'opportunity_field_value_condition';
    }
}