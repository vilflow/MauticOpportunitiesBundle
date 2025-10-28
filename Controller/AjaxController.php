<?php

declare(strict_types=1);

namespace MauticPlugin\MauticOpportunitiesBundle\Controller;

use Mautic\CoreBundle\Controller\AjaxController as CoreAjaxController;
use Mautic\CoreBundle\Helper\InputHelper;
use Mautic\LeadBundle\Model\LeadModel;
use MauticPlugin\MauticOpportunitiesBundle\Helper\OpportunityFieldMetadataHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AjaxController extends CoreAjaxController
{
    public function updateOpportunityFieldValuesAction(Request $request, OpportunityFieldMetadataHelper $metadataHelper, LeadModel $leadModel): JsonResponse
    {
        $alias    = InputHelper::clean($request->request->get('alias'));
        $operator = InputHelper::clean($request->request->get('operator'));
        $changed  = InputHelper::clean($request->request->get('changed'));

        $data = [
            'success'     => 0,
            'options'     => null,
            'optionsAttr' => [],
            'operators'   => null,
            'disabled'    => false,
            'fieldType'   => 'default',
        ];

        if (!$alias) {
            return $this->sendJsonResponse($data);
        }

        $fieldType = $metadataHelper->getFieldType($alias);
        $data['fieldType'] = $fieldType;

        // Operators for this field type
        $data['operators'] = $leadModel->getOperatorsForFieldType($fieldType ?: 'default', ['date']);

        if (!$operator) {
            $operator = '=';
        }

        $optionsMeta = $metadataHelper->getFieldOptions($alias, $operator);

        if (!empty($optionsMeta['options'])) {
            $data['options'] = $optionsMeta['options'];
            $data['optionsAttr'] = $optionsMeta['optionsAttr'] ?? [];
        }

        // Also pass customChoiceValue if available (for date fields)
        if (isset($optionsMeta['customChoiceValue'])) {
            $data['customChoiceValue'] = $optionsMeta['customChoiceValue'];
        }

        if (in_array($operator, ['empty', '!empty'], true)) {
            $data['disabled'] = true;
        }

        $data['success'] = 1;

        return $this->sendJsonResponse($data);
    }
}
