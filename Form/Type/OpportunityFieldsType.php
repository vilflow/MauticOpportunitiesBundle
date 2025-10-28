<?php

namespace MauticPlugin\MauticOpportunitiesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
class OpportunityFieldsType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => $this->getOpportunityFieldChoices(),
            'expanded' => false,
            'multiple' => false,
            'label_attr' => ['class' => 'control-label'],
            'attr' => ['class' => 'form-control'],
        ]);
    }

    /**
     * Get all available opportunity fields for selection.
     */
    private function getOpportunityFieldChoices(): array
    {
        return [
            // Basic Information
            'mautic.opportunities.field.name' => 'name',
            'mautic.opportunities.field.description' => 'description',
            'mautic.opportunities.field.opportunity_external_id' => 'opportunityExternalId',

            // Financial
            'mautic.opportunities.field.amount' => 'amount',
            'mautic.opportunities.field.amount_usdollar' => 'amountUsdollar',
            'mautic.opportunities.field.probability' => 'probability',

            // Status & Type
            'mautic.opportunities.field.sales_stage' => 'salesStage',
            'mautic.opportunities.field.opportunity_type' => 'opportunityType',
            'mautic.opportunities.field.lead_source' => 'leadSource',
            'mautic.opportunities.deleted' => 'deleted',

            // Dates
            'mautic.opportunities.date_entered' => 'dateEntered',
            'mautic.opportunities.date_modified' => 'dateModified',
            'mautic.opportunities.date_closed' => 'dateClosed',
            'mautic.opportunities.updated_at' => 'updatedAt',

            // Next Steps
            'mautic.opportunities.field.next_step' => 'nextStep',

            // Academic/Conference Fields
            'mautic.opportunities.field.institution_c' => 'institutionC',
            'mautic.opportunities.field.review_result_c' => 'reviewResultC',
            'mautic.opportunities.field.paper_title_c' => 'paperTitleC',
            'mautic.opportunities.field.abstract_c' => 'abstractC',
            'mautic.opportunities.field.keywords_c' => 'keywordsC',
            'mautic.opportunities.field.co_authors_names_c' => 'coAuthorsNamesC',

            // Presentation & Registration
            'mautic.opportunities.field.presentation_type_c' => 'presentationTypeC',
            'mautic.opportunities.field.registration_type_c' => 'registrationTypeC',

            // Dates (Academic)
            'mautic.opportunities.field.abstract_book_send_date_c' => 'abstractBookSendDateC',
            'mautic.opportunities.field.abstract_result_send_date_c' => 'abstractResultSendDateC',
            'mautic.opportunities.field.abstract_result_ready_date_c' => 'abstractResultReadyDateC',

            // Payment Information
            'mautic.opportunities.field.payment_status_c' => 'paymentStatusC',
            'mautic.opportunities.field.payment_channel_c' => 'paymentChannelC',
            'mautic.opportunities.field.coupon_code_c' => 'couponCodeC',
            'mautic.opportunities.field.transaction_id_c' => 'transactionIdC',

            // URLs & Documents
            'mautic.opportunities.field.abstract_review_result_url_c' => 'abstractReviewResultUrlC',
            'mautic.opportunities.field.sales_receipt_url_c' => 'salesReceiptUrlC',
            'mautic.opportunities.field.invoice_url_c' => 'invoiceUrlC',
            'mautic.opportunities.field.invitation_letter_url_c' => 'invitationLetterUrlC',
            'mautic.opportunities.field.acceptance_letter_url_c' => 'acceptanceLetterUrlC',
            'mautic.opportunities.field.invitation_url' => 'invitationUrl',

            // Attachments
            'mautic.opportunities.field.abstract_attachment_c' => 'abstractAttachmentC',
            'mautic.opportunities.field.wire_transfer_attachment_c' => 'wireTransferAttachmentC',

            // Additional Information
            'mautic.opportunities.field.abstract_book_information_c' => 'abstractBookInformationC',
            'mautic.opportunities.field.extra_paper_c' => 'extraPaperC',
            'mautic.opportunities.field.form_type_c' => 'formTypeC',

            // Boolean Fields
            'mautic.opportunities.field.abstract_book_dpublication_c' => 'abstractBookDpublicationC',
            'mautic.opportunities.field.sms_permission_c' => 'smsPermissionC',
            'mautic.opportunities.field.withdraw_c' => 'withdrawC',

            // Location & Maps
            'mautic.opportunities.field.jjwg_maps_address_c' => 'jjwgMapsAddressC',
            'mautic.opportunities.field.jjwg_maps_lat_c' => 'jjwgMapsLatC',
            'mautic.opportunities.field.jjwg_maps_lng_c' => 'jjwgMapsLngC',
            'mautic.opportunities.field.jjwg_maps_geocode_status_c' => 'jjwgMapsGeocodeStatusC',

            // Integration IDs
            'mautic.opportunities.field.suitecrm_id' => 'suitecrmId',
        ];
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'opportunity_fields';
    }
}
