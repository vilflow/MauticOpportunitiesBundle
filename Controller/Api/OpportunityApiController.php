<?php

namespace MauticPlugin\MauticOpportunitiesBundle\Controller\Api;

use Doctrine\Persistence\ManagerRegistry;
use Mautic\ApiBundle\Controller\CommonApiController;
use Mautic\ApiBundle\Helper\EntityResultHelper;
use Mautic\CoreBundle\Factory\ModelFactory;
use Mautic\CoreBundle\Helper\AppVersion;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\CoreBundle\Security\Permissions\CorePermissions;
use Mautic\CoreBundle\Translation\Translator;
use MauticPlugin\MauticOpportunitiesBundle\Entity\Opportunity;
use MauticPlugin\MauticOpportunitiesBundle\Model\OpportunityModel;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @extends CommonApiController<Opportunity>
 */
class OpportunityApiController extends CommonApiController
{
    /**
     * @var OpportunityModel|null
     */
    protected $model;

    public function __construct(
        CorePermissions $security,
        Translator $translator,
        EntityResultHelper $entityResultHelper,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        AppVersion $appVersion,
        RequestStack $requestStack,
        ManagerRegistry $doctrine,
        ModelFactory $modelFactory,
        EventDispatcherInterface $dispatcher,
        CoreParametersHelper $coreParametersHelper,
    ) {
        $opportunityModel = $modelFactory->getModel('opportunity');
        \assert($opportunityModel instanceof OpportunityModel);

        $this->model            = $opportunityModel;
        $this->entityClass      = Opportunity::class;
        $this->entityNameOne    = 'opportunity';
        $this->entityNameMulti  = 'opportunities';
        $this->permissionBase   = 'opportunities:opportunities';
        $this->serializerGroups = ['opportunityDetails'];

        parent::__construct(
            $security,
            $translator,
            $entityResultHelper,
            $router,
            $formFactory,
            $appVersion,
            $requestStack,
            $doctrine,
            $modelFactory,
            $dispatcher,
            $coreParametersHelper
        );
    }

    /**
     * Normalize incoming parameters to match form fields and accept snake_case.
     * Also accept related Contact IDs and Event IDs as input parameters.
     *
     * Supported keys (in addition to default form keys):
     * - opportunity_external_id => opportunityExternalId
     * - opportunity_type => opportunityType
     * - lead_source => leadSource
     * - amount_usdollar => amountUsdollar
     * - date_closed => dateClosed
     * - next_step => nextStep
     * - sales_stage => salesStage
     * - institution_c => institutionC
     * - review_result_c => reviewResultC
     * - abstract_book_send_date_c => abstractBookSendDateC
     * - abstract_review_result_url_c => abstractReviewResultUrlC
     * - abstract_book_dpublication_c => abstractBookDpublicationC
     * - extra_paper_c => extraPaperC
     * - sales_receipt_url_c => salesReceiptUrlC
     * - abstract_result_send_date_c => abstractResultSendDateC
     * - registration_type_c => registrationTypeC
     * - abstract_c => abstractC
     * - abstract_book_information_c => abstractBookInformationC
     * - payment_status_c => paymentStatusC
     * - coupon_code_c => couponCodeC
     * - abstract_result_ready_date_c => abstractResultReadyDateC
     * - paper_title_c => paperTitleC
     * - sms_permission_c => smsPermissionC
     * - jjwg_maps_geocode_status_c => jjwgMapsGeocodeStatusC
     * - invoice_url_c => invoiceUrlC
     * - presentation_type_c => presentationTypeC
     * - invitation_letter_url_c => invitationLetterUrlC
     * - withdraw_c => withdrawC
     * - keywords_c => keywordsC
     * - jjwg_maps_lng_c => jjwgMapsLngC
     * - jjwg_maps_lat_c => jjwgMapsLatC
     * - transaction_id_c => transactionIdC
     * - co_authors_names_c => coAuthorsNamesC
     * - abstract_attachment_c => abstractAttachmentC
     * - acceptance_letter_url_c => acceptanceLetterUrlC
     * - payment_channel_c => paymentChannelC
     * - wire_transfer_attachment_c => wireTransferAttachmentC
     * - jjwg_maps_address_c => jjwgMapsAddressC
     * - invitation_url => invitationUrl
     * - suitecrm_id => suitecrmId
     * - contact_id => contact (int)
     * - contactIds => contact (first int from array)
     * - event_id => event (int)
     * - eventIds => event (first int from array)
     *
     * @param array<mixed> $parameters
     * @param object       $entity
     * @param string       $action
     *
     * @return mixed
     */
    protected function prepareParametersForBinding(Request $request, $parameters, $entity, $action)
    {
        // snake_case to camelCase mappings
        $map = [
            'opportunity_external_id'          => 'opportunityExternalId',
            'opportunity_type'                 => 'opportunityType',
            'lead_source'                      => 'leadSource',
            'amount_usdollar'                  => 'amountUsdollar',
            'date_closed'                      => 'dateClosed',
            'next_step'                        => 'nextStep',
            'sales_stage'                      => 'salesStage',
            'institution_c'                    => 'institutionC',
            'review_result_c'                  => 'reviewResultC',
            'abstract_book_send_date_c'        => 'abstractBookSendDateC',
            'abstract_review_result_url_c'     => 'abstractReviewResultUrlC',
            'abstract_book_dpublication_c'     => 'abstractBookDpublicationC',
            'extra_paper_c'                    => 'extraPaperC',
            'sales_receipt_url_c'              => 'salesReceiptUrlC',
            'abstract_result_send_date_c'      => 'abstractResultSendDateC',
            'registration_type_c'              => 'registrationTypeC',
            'abstract_c'                       => 'abstractC',
            'abstract_book_information_c'      => 'abstractBookInformationC',
            'payment_status_c'                 => 'paymentStatusC',
            'coupon_code_c'                    => 'couponCodeC',
            'abstract_result_ready_date_c'     => 'abstractResultReadyDateC',
            'paper_title_c'                    => 'paperTitleC',
            'sms_permission_c'                 => 'smsPermissionC',
            'jjwg_maps_geocode_status_c'       => 'jjwgMapsGeocodeStatusC',
            'invoice_url_c'                    => 'invoiceUrlC',
            'presentation_type_c'              => 'presentationTypeC',
            'invitation_letter_url_c'          => 'invitationLetterUrlC',
            'withdraw_c'                       => 'withdrawC',
            'keywords_c'                       => 'keywordsC',
            'jjwg_maps_lng_c'                  => 'jjwgMapsLngC',
            'jjwg_maps_lat_c'                  => 'jjwgMapsLatC',
            'transaction_id_c'                 => 'transactionIdC',
            'co_authors_names_c'               => 'coAuthorsNamesC',
            'abstract_attachment_c'            => 'abstractAttachmentC',
            'acceptance_letter_url_c'          => 'acceptanceLetterUrlC',
            'payment_channel_c'                => 'paymentChannelC',
            'wire_transfer_attachment_c'       => 'wireTransferAttachmentC',
            'jjwg_maps_address_c'              => 'jjwgMapsAddressC',
            'invitation_url'                   => 'invitationUrl',
            'suitecrm_id'                      => 'suitecrmId',
        ];

        foreach ($map as $from => $to) {
            if (array_key_exists($from, $parameters)) {
                $parameters[$to] = $parameters[$from];
                unset($parameters[$from]);
            }
        }

        // Accept contactId/contact_id/contactIds and map to 'contact' (EntityType accepts ID)
        if (isset($parameters['contactId']) && !isset($parameters['contact'])) {
            $parameters['contact'] = (int) $parameters['contactId'];
            unset($parameters['contactId']);
        }
        if (isset($parameters['contact_id']) && !isset($parameters['contact'])) {
            $parameters['contact'] = (int) $parameters['contact_id'];
            unset($parameters['contact_id']);
        }
        if (isset($parameters['contactIds']) && !isset($parameters['contact'])) {
            if (is_array($parameters['contactIds']) && !empty($parameters['contactIds'])) {
                $parameters['contact'] = (int) reset($parameters['contactIds']);
            }
            unset($parameters['contactIds']);
        }

        // Accept eventId/event_id/eventIds and map to 'event'
        if (isset($parameters['eventId']) && !isset($parameters['event'])) {
            $parameters['event'] = (int) $parameters['eventId'];
            unset($parameters['eventId']);
        }
        if (isset($parameters['event_id']) && !isset($parameters['event'])) {
            $parameters['event'] = (int) $parameters['event_id'];
            unset($parameters['event_id']);
        }
        if (isset($parameters['eventIds']) && !isset($parameters['event'])) {
            if (is_array($parameters['eventIds']) && !empty($parameters['eventIds'])) {
                $parameters['event'] = (int) reset($parameters['eventIds']);
            }
            unset($parameters['eventIds']);
        }
        
        return parent::prepareParametersForBinding($request, $parameters, $entity, $action);
    }
}
