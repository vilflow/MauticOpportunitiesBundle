<?php

namespace MauticPlugin\MauticOpportunitiesBundle\EventListener;

use Mautic\CampaignBundle\CampaignEvents;
use Mautic\CampaignBundle\Event\CampaignBuilderEvent;
use Mautic\CampaignBundle\Event\CampaignExecutionEvent;
use MauticPlugin\MauticOpportunitiesBundle\Entity\OpportunityRepository;
use MauticPlugin\MauticOpportunitiesBundle\MauticOpportunitiesEvents;
use MauticPlugin\MauticOpportunitiesBundle\Form\Type\OpportunityFieldValueConditionType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CampaignSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private OpportunityRepository $opportunityRepository
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD => ['onCampaignBuild', 0],
            MauticOpportunitiesEvents::ON_CAMPAIGN_TRIGGER_CONDITION => ['onCampaignTriggerCondition', 0],
        ];
    }

    public function onCampaignBuild(CampaignBuilderEvent $event): void
    {
        // Opportunity Field Value Condition
        $condition = [
            'label'       => 'mautic.opportunities.campaign.condition.opportunity_field_value',
            'description' => 'mautic.opportunities.campaign.condition.opportunity_field_value_descr',
            'formType'    => OpportunityFieldValueConditionType::class,
            'formTheme'   => '@MauticOpportunities/FormTheme/OpportunityFieldValue/_opportunity_field_value_widget.html.twig',
            'eventName'   => MauticOpportunitiesEvents::ON_CAMPAIGN_TRIGGER_CONDITION,
        ];
        $event->addCondition('opportunities.field_value', $condition);
    }

    public function onCampaignTriggerCondition(CampaignExecutionEvent $event): void
    {
        $lead = $event->getLead();
        if (!$lead || !$lead->getId()) {
            $event->setResult(false);
            return;
        }

        $config = $event->getConfig();

        // Opportunity Field Value Condition
        if ($event->checkContext('opportunities.field_value')) {
            $field = $config['field'] ?? '';
            $operator = $config['operator'] ?? 'eq';
            $value = $config['value'] ?? '';

            if (empty($field)) {
                $event->setResult(false);
                return;
            }

            // Handle empty/not empty operators
            if (in_array($operator, ['empty', '!empty']) && empty($value)) {
                $value = null;
            }

            $hasOpportunity = $this->opportunityRepository->contactHasOpportunityByFieldValue(
                $lead->getId(),
                $field,
                $operator,
                $value
            );
            $event->setResult($hasOpportunity);
            return;
        }

        // Default: condition not recognized
        $event->setResult(false);
    }
}