<?php

namespace MauticPlugin\MauticOpportunitiesBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Mautic\LeadBundle\Event\LeadListFiltersChoicesEvent;
use Mautic\LeadBundle\Event\SegmentDictionaryGenerationEvent;
use Mautic\LeadBundle\LeadEvents;
use Mautic\LeadBundle\Provider\TypeOperatorProviderInterface;
use Mautic\LeadBundle\Segment\Query\Filter\ForeignValueFilterQueryBuilder;
use MauticPlugin\MauticOpportunitiesBundle\Segment\Query\Filter\OpportunityFieldFilterQueryBuilder;
use MauticPlugin\MauticOpportunitiesBundle\Entity\Opportunity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SegmentFilterSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TypeOperatorProviderInterface $typeOperatorProvider,
        private TranslatorInterface $translator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LeadEvents::LIST_FILTERS_CHOICES_ON_GENERATE => [
                ['onGenerateSegmentFiltersAddOpportunityFields', -10],
            ],
            LeadEvents::SEGMENT_DICTIONARY_ON_GENERATE => [
                ['onSegmentDictionaryGenerate', 0],
            ],
        ];
    }

    public function onGenerateSegmentFiltersAddOpportunityFields(LeadListFiltersChoicesEvent $event): void
    {
        if (!$event->isForSegmentation()) {
            return;
        }

        $choices = [
            // Basic fields
            'opportunity_id' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_id'),
                'properties' => ['type' => 'number'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('number'),
                'object'     => 'lead',
            ],
            'opportunity_name' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_name'),
                'properties' => ['type' => 'text'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('text'),
                'object'     => 'lead',
            ],
            'opportunity_external_id' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_external_id'),
                'properties' => ['type' => 'text'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('text'),
                'object'     => 'lead',
            ],
            'opportunity_description' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_description'),
                'properties' => ['type' => 'text'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('text'),
                'object'     => 'lead',
            ],
            'opportunity_deleted' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_deleted'),
                'properties' => ['type' => 'boolean'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('boolean'),
                'object'     => 'lead',
            ],
            'opportunity_type' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_type'),
                'properties' => [
                    'type' => 'select',
                    'list' => Opportunity::getOpportunityTypeChoices(),
                ],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('select'),
                'object'     => 'lead',
            ],
            'opportunity_lead_source' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_lead_source'),
                'properties' => [
                    'type' => 'select',
                    'list' => Opportunity::getLeadSourceChoices(),
                ],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('select'),
                'object'     => 'lead',
            ],
            'opportunity_amount' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_amount'),
                'properties' => ['type' => 'number'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('number'),
                'object'     => 'lead',
            ],
            'opportunity_amount_usdollar' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_amount_usdollar'),
                'properties' => ['type' => 'number'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('number'),
                'object'     => 'lead',
            ],
            'opportunity_date_closed' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_date_closed'),
                'properties' => ['type' => 'date'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('date'),
                'object'     => 'lead',
            ],
            'opportunity_next_step' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_next_step'),
                'properties' => ['type' => 'text'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('text'),
                'object'     => 'lead',
            ],
            'opportunity_sales_stage' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_sales_stage'),
                'properties' => [
                    'type' => 'select',
                    'list' => Opportunity::getStageChoices(),
                ],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('select'),
                'object'     => 'lead',
            ],
            'opportunity_probability' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_probability'),
                'properties' => ['type' => 'number'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('number'),
                'object'     => 'lead',
            ],

            // Date fields
            'opportunity_date_entered' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_date_entered'),
                'properties' => ['type' => 'datetime'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('datetime'),
                'object'     => 'lead',
            ],
            'opportunity_date_modified' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_date_modified'),
                'properties' => ['type' => 'datetime'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('datetime'),
                'object'     => 'lead',
            ],
            'opportunity_created_at' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_created_at'),
                'properties' => ['type' => 'datetime'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('datetime'),
                'object'     => 'lead',
            ],
            'opportunity_updated_at' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_updated_at'),
                'properties' => ['type' => 'datetime'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('datetime'),
                'object'     => 'lead',
            ],

            // Academic/Conference fields
            'opportunity_institution_c' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_institution_c'),
                'properties' => ['type' => 'text'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('text'),
                'object'     => 'lead',
            ],
            'opportunity_review_result_c' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_review_result_c'),
                'properties' => [
                    'type' => 'select',
                    'list' => Opportunity::getReviewResultChoices(),
                ],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('select'),
                'object'     => 'lead',
            ],
            'opportunity_abstract_book_send_date_c' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_abstract_book_send_date_c'),
                'properties' => ['type' => 'date'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('date'),
                'object'     => 'lead',
            ],
            'opportunity_abstract_review_result_url_c' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_abstract_review_result_url_c'),
                'properties' => ['type' => 'url'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('url'),
                'object'     => 'lead',
            ],
            'opportunity_abstract_book_dpublication_c' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_abstract_book_dpublication_c'),
                'properties' => ['type' => 'boolean'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('boolean'),
                'object'     => 'lead',
            ],
            'opportunity_extra_paper_c' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_extra_paper_c'),
                'properties' => ['type' => 'text'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('text'),
                'object'     => 'lead',
            ],
            'opportunity_sales_receipt_url_c' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_sales_receipt_url_c'),
                'properties' => ['type' => 'url'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('url'),
                'object'     => 'lead',
            ],
            'opportunity_abstract_result_send_date_c' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_abstract_result_send_date_c'),
                'properties' => ['type' => 'date'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('date'),
                'object'     => 'lead',
            ],
            'opportunity_registration_type_c' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_registration_type_c'),
                'properties' => [
                    'type' => 'select',
                    'list' => Opportunity::getRegistrationTypeChoices(),
                ],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('select'),
                'object'     => 'lead',
            ],
            'opportunity_abstract_c' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_abstract_c'),
                'properties' => ['type' => 'text'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('text'),
                'object'     => 'lead',
            ],
            'opportunity_abstract_book_information_c' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_abstract_book_information_c'),
                'properties' => ['type' => 'text'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('text'),
                'object'     => 'lead',
            ],
            'opportunity_payment_status_c' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_payment_status_c'),
                'properties' => [
                    'type' => 'select',
                    'list' => Opportunity::getPaymentStatusChoices(),
                ],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('select'),
                'object'     => 'lead',
            ],
            'opportunity_coupon_code_c' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_coupon_code_c'),
                'properties' => ['type' => 'text'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('text'),
                'object'     => 'lead',
            ],
            'opportunity_abstract_result_ready_date_c' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_abstract_result_ready_date_c'),
                'properties' => ['type' => 'date'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('date'),
                'object'     => 'lead',
            ],
            'opportunity_paper_title_c' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_paper_title_c'),
                'properties' => ['type' => 'text'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('text'),
                'object'     => 'lead',
            ],
            'opportunity_sms_permission_c' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_sms_permission_c'),
                'properties' => ['type' => 'boolean'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('boolean'),
                'object'     => 'lead',
            ],
            'opportunity_jjwg_maps_geocode_status_c' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_jjwg_maps_geocode_status_c'),
                'properties' => ['type' => 'text'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('text'),
                'object'     => 'lead',
            ],
            'opportunity_invoice_url_c' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_invoice_url_c'),
                'properties' => ['type' => 'url'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('url'),
                'object'     => 'lead',
            ],
            'opportunity_presentation_type_c' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_presentation_type_c'),
                'properties' => [
                    'type' => 'select',
                    'list' => Opportunity::getPresentationTypeChoices(),
                ],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('select'),
                'object'     => 'lead',
            ],
            'opportunity_invitation_letter_url_c' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_invitation_letter_url_c'),
                'properties' => ['type' => 'url'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('url'),
                'object'     => 'lead',
            ],
            'opportunity_withdraw_c' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_withdraw_c'),
                'properties' => ['type' => 'boolean'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('boolean'),
                'object'     => 'lead',
            ],
            'opportunity_keywords_c' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_keywords_c'),
                'properties' => ['type' => 'text'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('text'),
                'object'     => 'lead',
            ],
            'opportunity_jjwg_maps_lng_c' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_jjwg_maps_lng_c'),
                'properties' => ['type' => 'number'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('number'),
                'object'     => 'lead',
            ],
            'opportunity_jjwg_maps_lat_c' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_jjwg_maps_lat_c'),
                'properties' => ['type' => 'number'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('number'),
                'object'     => 'lead',
            ],
            'opportunity_transaction_id_c' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_transaction_id_c'),
                'properties' => ['type' => 'text'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('text'),
                'object'     => 'lead',
            ],
            'opportunity_co_authors_names_c' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_co_authors_names_c'),
                'properties' => ['type' => 'text'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('text'),
                'object'     => 'lead',
            ],
            'opportunity_abstract_attachment_c' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_abstract_attachment_c'),
                'properties' => ['type' => 'text'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('text'),
                'object'     => 'lead',
            ],
            'opportunity_acceptance_letter_url_c' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_acceptance_letter_url_c'),
                'properties' => ['type' => 'url'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('url'),
                'object'     => 'lead',
            ],
            'opportunity_payment_channel_c' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_payment_channel_c'),
                'properties' => [
                    'type' => 'select',
                    'list' => Opportunity::getPaymentChannelChoices(),
                ],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('select'),
                'object'     => 'lead',
            ],
            'opportunity_wire_transfer_attachment_c' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_wire_transfer_attachment_c'),
                'properties' => ['type' => 'text'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('text'),
                'object'     => 'lead',
            ],
            'opportunity_jjwg_maps_address_c' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_jjwg_maps_address_c'),
                'properties' => ['type' => 'text'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('text'),
                'object'     => 'lead',
            ],
            'opportunity_form_type_c' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_form_type_c'),
                'properties' => [
                    'type' => 'select',
                    'list' => Opportunity::getFormTypeChoices(),
                ],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('select'),
                'object'     => 'lead',
            ],
            'opportunity_suitecrm_id' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_suitecrm_id'),
                'properties' => ['type' => 'text'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('text'),
                'object'     => 'lead',
            ],
            'opportunity_invitation_url' => [
                'label'      => $this->translator->trans('mautic.opportunities.segment.opportunity_invitation_url'),
                'properties' => ['type' => 'url'],
                'operators'  => $this->typeOperatorProvider->getOperatorsForFieldType('url'),
                'object'     => 'lead',
            ],
        ];

        foreach ($choices as $alias => $fieldOptions) {
            $event->addChoice('Opportunity', $alias, $fieldOptions);
        }
    }

    public function onSegmentDictionaryGenerate(SegmentDictionaryGenerationEvent $event): void
    {
        // Use custom OpportunityFieldFilterQueryBuilder for opportunity segment filters
        $opportunityFields = [
            // Basic fields
            'opportunity_id', 'opportunity_name', 'opportunity_external_id', 'opportunity_description',
            'opportunity_deleted', 'opportunity_type', 'opportunity_lead_source', 'opportunity_amount',
            'opportunity_amount_usdollar', 'opportunity_date_closed', 'opportunity_next_step',
            'opportunity_sales_stage', 'opportunity_probability',

            // Date fields
            'opportunity_date_entered', 'opportunity_date_modified', 'opportunity_created_at', 'opportunity_updated_at',

            // Academic/Conference fields
            'opportunity_institution_c', 'opportunity_review_result_c', 'opportunity_abstract_book_send_date_c',
            'opportunity_abstract_review_result_url_c', 'opportunity_abstract_book_dpublication_c',
            'opportunity_extra_paper_c', 'opportunity_sales_receipt_url_c', 'opportunity_abstract_result_send_date_c',
            'opportunity_registration_type_c', 'opportunity_abstract_c', 'opportunity_abstract_book_information_c',
            'opportunity_payment_status_c', 'opportunity_coupon_code_c', 'opportunity_abstract_result_ready_date_c',
            'opportunity_paper_title_c', 'opportunity_sms_permission_c', 'opportunity_jjwg_maps_geocode_status_c',
            'opportunity_invoice_url_c', 'opportunity_presentation_type_c', 'opportunity_invitation_letter_url_c',
            'opportunity_withdraw_c', 'opportunity_keywords_c', 'opportunity_jjwg_maps_lng_c',
            'opportunity_jjwg_maps_lat_c', 'opportunity_transaction_id_c', 'opportunity_co_authors_names_c',
            'opportunity_abstract_attachment_c', 'opportunity_acceptance_letter_url_c', 'opportunity_payment_channel_c',
            'opportunity_wire_transfer_attachment_c', 'opportunity_jjwg_maps_address_c', 'opportunity_form_type_c',
            'opportunity_suitecrm_id', 'opportunity_invitation_url'
        ];

        foreach ($opportunityFields as $fieldName) {
            $config = [
                'type' => OpportunityFieldFilterQueryBuilder::getServiceId(),
                'field' => $fieldName,
            ];

            // Add null_value for numeric fields to handle empty values properly
            if (in_array($fieldName, ['opportunity_amount', 'opportunity_amount_usdollar', 'opportunity_probability', 'opportunity_jjwg_maps_lng_c', 'opportunity_jjwg_maps_lat_c'])) {
                $config['null_value'] = 0;
            }

            $event->addTranslation($fieldName, $config);
        }
    }
}