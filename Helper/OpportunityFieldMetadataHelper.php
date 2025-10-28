<?php

namespace MauticPlugin\MauticOpportunitiesBundle\Helper;

use Mautic\CoreBundle\Translation\Translator;
use Mautic\LeadBundle\Helper\FormFieldHelper;
use MauticPlugin\MauticOpportunitiesBundle\Entity\Opportunity;

class OpportunityFieldMetadataHelper
{
    private const SELECT_FIELDS = [
        'salesStage',
        'opportunityType',
        'leadSource',
        'presentationTypeC',
        'registrationTypeC',
        'paymentStatusC',
        'paymentChannelC',
        'reviewResultC',
        'formTypeC',
        'deleted',
    ];

    private const NUMBER_FIELDS = [
        'amount',
        'amountUsdollar',
        'probability',
        'jjwgMapsLngC',
        'jjwgMapsLatC',
    ];

    private const DATE_FIELDS = [
        'dateEntered',
        'dateModified',
        'createdAt',
        'updatedAt',
        'dateClosed',
        'abstractBookSendDateC',
        'abstractResultSendDateC',
        'abstractResultReadyDateC',
    ];

    private const BOOLEAN_FIELDS = [
        'abstractBookDpublicationC',
        'smsPermissionC',
        'withdrawC',
        'deleted',
    ];

    public function __construct(private Translator $translator)
    {
    }

    public function getFieldType(?string $field): string
    {
        if (null === $field) {
            return 'default';
        }

        if (in_array($field, self::BOOLEAN_FIELDS, true)) {
            return 'bool';
        }

        if (in_array($field, self::SELECT_FIELDS, true)) {
            return 'select';
        }

        if (in_array($field, self::NUMBER_FIELDS, true)) {
            return 'number';
        }

        if (in_array($field, self::DATE_FIELDS, true)) {
            return 'date';
        }

        return 'text';
    }

    public function sanitizeFieldAlias(string $field): string
    {
        return (string) preg_replace('/[^A-Za-z0-9_]/', '', $field);
    }

    /**
     * Check if a field should be rendered as text input even if it has predefined options
     * This allows for both predefined values and free-form input
     */
    public function allowsFreeformInput(string $field): bool
    {
        // No fields allow freeform input for opportunities (all selects are strict)
        return false;
    }

    /**
     * @return array{options:?array<string,string>, customChoiceValue:?string, optionsAttr:array<string,array<string,mixed>>}
     */
    public function getFieldOptions(string $field, string $operator, mixed $currentValue = null): array
    {
        $options = null;
        $customChoiceValue = null;
        $optionsAttr = [];

        // For DATE fields, only show predefined date options for 'date' operator
        if (in_array($field, self::DATE_FIELDS, true)) {
            // Show date options dropdown only for 'date' operator
            if ('date' === $operator) {
                $options = [
                    'custom'     => $this->translator->trans('mautic.campaign.event.timed.choice.custom'),
                    'anniversary' => $this->translator->trans('mautic.campaign.event.timed.choice.anniversary'),
                    '+P0D'       => $this->translator->trans('mautic.campaign.event.timed.choice.today'),
                    '-P1D'       => $this->translator->trans('mautic.campaign.event.timed.choice.yesterday'),
                    '+P1D'       => $this->translator->trans('mautic.campaign.event.timed.choice.tomorrow'),
                ];
                $optionsAttr['custom'] = ['data-custom' => 1, 'data-datepicker' => 1];
                $customChoiceValue = 'custom';

                return [
                    'options'           => $options,
                    'customChoiceValue' => $customChoiceValue,
                    'optionsAttr'       => $optionsAttr,
                ];
            }

            // For all other operators (=, !=, >, <, >=, <=, empty, !empty), return no options
            // This will make the field render as a date picker input
            return [
                'options'           => null,
                'customChoiceValue' => null,
                'optionsAttr'       => [],
            ];
        }

        switch ($field) {
            case 'salesStage':
                $options = Opportunity::getStageChoices();
                break;
            case 'opportunityType':
                $options = Opportunity::getOpportunityTypeChoices();
                break;
            case 'leadSource':
                $options = Opportunity::getLeadSourceChoices();
                break;
            case 'presentationTypeC':
                $options = Opportunity::getPresentationTypeChoices();
                break;
            case 'registrationTypeC':
                $options = Opportunity::getRegistrationTypeChoices();
                break;
            case 'paymentStatusC':
                $options = Opportunity::getPaymentStatusChoices();
                break;
            case 'paymentChannelC':
                $options = Opportunity::getPaymentChannelChoices();
                break;
            case 'reviewResultC':
                $options = Opportunity::getReviewResultChoices();
                break;
            case 'formTypeC':
                $options = Opportunity::getFormTypeChoices();
                break;
            case 'abstractBookDpublicationC':
            case 'smsPermissionC':
            case 'withdrawC':
            case 'deleted':
                $options = [
                    '0' => $this->translator->trans('mautic.core.form.no'),
                    '1' => $this->translator->trans('mautic.core.form.yes'),
                ];
                break;
            default:
                // Non-date, non-select fields
                break;
        }

        return [
            'options'           => $options,
            'customChoiceValue' => $customChoiceValue,
            'optionsAttr'       => $optionsAttr,
        ];
    }
}
