<?php

namespace MauticPlugin\MauticOpportunitiesBundle\Segment\Query\Filter;

use Mautic\LeadBundle\Segment\ContactSegmentFilter;
use Mautic\LeadBundle\Segment\Query\Filter\BaseFilterQueryBuilder;
use Mautic\LeadBundle\Segment\Query\QueryBuilder;

class OpportunityFieldFilterQueryBuilder extends BaseFilterQueryBuilder
{
    public static function getServiceId(): string
    {
        return 'mautic.opportunities.segment.query.builder.opportunity_field';
    }

    public function applyQuery(QueryBuilder $queryBuilder, ContactSegmentFilter $filter): QueryBuilder
    {
        $leadsTableAlias = $queryBuilder->getTableAlias(MAUTIC_TABLE_PREFIX.'leads');
        $filterOperator = $filter->getOperator();
        $filterParameters = $filter->getParameterValue();

        // Map filter field names to actual column names
        $fieldColumn = $this->mapFilterFieldToColumn($filter->getField());

        // Check if this is a date field - if so, use DATE() function to compare only date part without time
        $isDateField = $this->isDateField($fieldColumn);

        // For date fields with comparison operators, convert parameters to date-only format first
        // This must be done BEFORE generating parameter names and holders
        $useDateOnlyComparison = $isDateField && in_array($filterOperator, ['eq', 'neq', 'gt', 'gte', 'lt', 'lte']);
        if ($useDateOnlyComparison) {
            $filterParameters = $this->convertToDateOnly($filterParameters);
        }

        if (is_array($filterParameters)) {
            $parameters = [];
            foreach ($filterParameters as $filterParameter) {
                $parameters[] = $this->generateRandomParameterName();
            }
        } else {
            $parameters = $this->generateRandomParameterName();
        }

        $filterParametersHolder = $filter->getParameterHolder($parameters);
        $tableAlias = $this->generateRandomParameterName();

        // Create subquery to find contacts with matching opportunity criteria (using direct contact_id relationship)
        $subQueryBuilder = $queryBuilder->createQueryBuilder();
        $subQueryBuilder->select($tableAlias.'_o.contact_id')
                       ->from(MAUTIC_TABLE_PREFIX.'opportunities', $tableAlias.'_o')
                       ->where($tableAlias.'_o.contact_id IS NOT NULL');

        $fieldExpression = $isDateField ? 'DATE('.$tableAlias.'_o.'.$fieldColumn.')' : $tableAlias.'_o.'.$fieldColumn;

        switch ($filterOperator) {
            case 'empty':
                $subQueryBuilder->andWhere($subQueryBuilder->expr()->or(
                    $subQueryBuilder->expr()->isNull($tableAlias.'_o.'.$fieldColumn),
                    $subQueryBuilder->expr()->eq($tableAlias.'_o.'.$fieldColumn, $subQueryBuilder->expr()->literal(''))
                ));
                $queryBuilder->addLogic($queryBuilder->expr()->in($leadsTableAlias.'.id', $subQueryBuilder->getSQL()), $filter->getGlue());
                break;
            case 'notEmpty':
                $subQueryBuilder->andWhere($subQueryBuilder->expr()->and(
                    $subQueryBuilder->expr()->isNotNull($tableAlias.'_o.'.$fieldColumn),
                    $subQueryBuilder->expr()->neq($tableAlias.'_o.'.$fieldColumn, $subQueryBuilder->expr()->literal(''))
                ));
                $queryBuilder->addLogic($queryBuilder->expr()->in($leadsTableAlias.'.id', $subQueryBuilder->getSQL()), $filter->getGlue());
                break;
            case 'neq':
                if ($useDateOnlyComparison) {
                    // Use literal value for date comparison to avoid type conversion issues
                    $dateValue = is_array($filterParameters) ? reset($filterParameters) : $filterParameters;
                    $subQueryBuilder->andWhere('DATE('.$tableAlias.'_o.'.$fieldColumn.') != '.$subQueryBuilder->expr()->literal($dateValue));
                    // Don't bind parameters for date comparisons since we're using literals
                    $parameters = null;
                } else {
                    $subQueryBuilder->andWhere($subQueryBuilder->expr()->neq($tableAlias.'_o.'.$fieldColumn, $filterParametersHolder));
                }
                $queryBuilder->addLogic($queryBuilder->expr()->notIn($leadsTableAlias.'.id', $subQueryBuilder->getSQL()), $filter->getGlue());
                break;
            case 'notIn':
                $subQueryBuilder->andWhere($subQueryBuilder->expr()->in($tableAlias.'_o.'.$fieldColumn, $filterParametersHolder));
                $queryBuilder->addLogic($queryBuilder->expr()->notIn($leadsTableAlias.'.id', $subQueryBuilder->getSQL()), $filter->getGlue());
                break;
            case 'notLike':
                $subQueryBuilder->andWhere($subQueryBuilder->expr()->like($tableAlias.'_o.'.$fieldColumn, $filterParametersHolder));
                $queryBuilder->addLogic($queryBuilder->expr()->notIn($leadsTableAlias.'.id', $subQueryBuilder->getSQL()), $filter->getGlue());
                break;
            case 'eq':
            case 'like':
            case 'startsWith':
            case 'endsWith':
            case 'contains':
            case 'in':
            case 'gt':
            case 'gte':
            case 'lt':
            case 'lte':
            case 'regexp':
                if ($useDateOnlyComparison) {
                    // For date fields, use DATE() function to compare only the date part
                    // Use literal value to avoid type conversion issues
                    $dateValue = is_array($filterParameters) ? reset($filterParameters) : $filterParameters;
                    $subQueryBuilder->andWhere('DATE('.$tableAlias.'_o.'.$fieldColumn.') '.$this->getOperatorSymbol($filterOperator).' '.$subQueryBuilder->expr()->literal($dateValue));
                    // Don't bind parameters for date comparisons since we're using literals
                    $parameters = null;
                } else {
                    $subQueryBuilder->andWhere($subQueryBuilder->expr()->$filterOperator($tableAlias.'_o.'.$fieldColumn, $filterParametersHolder));
                }
                $queryBuilder->addLogic($queryBuilder->expr()->in($leadsTableAlias.'.id', $subQueryBuilder->getSQL()), $filter->getGlue());
                break;
            default:
                throw new \Exception('Unknown operator "'.$filterOperator.'" for opportunity field filter');
        }

        // Only bind parameters if we're not using literals (date comparisons use literals)
        if ($parameters !== null) {
            $queryBuilder->setParametersPairs($parameters, $filterParameters);
        }

        return $queryBuilder;
    }

    private function mapFilterFieldToColumn(string $field): string
    {
        // Map segment filter field names to actual database column names
        $fieldMap = [
            // Basic fields
            'opportunity_name' => 'name',
            'opportunity_external_id' => 'opportunity_external_id',
            'opportunity_event' => 'event_id',
            'opportunity_description' => 'description',
            'opportunity_deleted' => 'deleted',
            'opportunity_type' => 'opportunity_type',
            'opportunity_lead_source' => 'lead_source',
            'opportunity_amount' => 'amount',
            'opportunity_amount_usdollar' => 'amount_usdollar',
            'opportunity_date_closed' => 'date_closed',
            'opportunity_next_step' => 'next_step',
            'opportunity_sales_stage' => 'sales_stage',
            'opportunity_probability' => 'probability',

            // Date fields
            'opportunity_date_entered' => 'date_entered',
            'opportunity_date_modified' => 'date_modified',
            'opportunity_created_at' => 'created_at',
            'opportunity_updated_at' => 'updated_at',

            // Academic/Conference fields
            'opportunity_institution_c' => 'institution_c',
            'opportunity_review_result_c' => 'review_result_c',
            'opportunity_abstract_book_send_date_c' => 'abstract_book_send_date_c',
            'opportunity_abstract_review_result_url_c' => 'abstract_review_result_url_c',
            'opportunity_abstract_book_dpublication_c' => 'abstract_book_dpublication_c',
            'opportunity_extra_paper_c' => 'extra_paper_c',
            'opportunity_sales_receipt_url_c' => 'sales_receipt_url_c',
            'opportunity_abstract_result_send_date_c' => 'abstract_result_send_date_c',
            'opportunity_registration_type_c' => 'registration_type_c',
            'opportunity_abstract_c' => 'abstract_c',
            'opportunity_abstract_book_information_c' => 'abstract_book_information_c',
            'opportunity_payment_status_c' => 'payment_status_c',
            'opportunity_coupon_code_c' => 'coupon_code_c',
            'opportunity_abstract_result_ready_date_c' => 'abstract_result_ready_date_c',
            'opportunity_paper_title_c' => 'paper_title_c',
            'opportunity_sms_permission_c' => 'sms_permission_c',
            'opportunity_jjwg_maps_geocode_status_c' => 'jjwg_maps_geocode_status_c',
            'opportunity_invoice_url_c' => 'invoice_url_c',
            'opportunity_presentation_type_c' => 'presentation_type_c',
            'opportunity_invitation_letter_url_c' => 'invitation_letter_url_c',
            'opportunity_withdraw_c' => 'withdraw_c',
            'opportunity_keywords_c' => 'keywords_c',
            'opportunity_jjwg_maps_lng_c' => 'jjwg_maps_lng_c',
            'opportunity_jjwg_maps_lat_c' => 'jjwg_maps_lat_c',
            'opportunity_transaction_id_c' => 'transaction_id_c',
            'opportunity_co_authors_names_c' => 'co_authors_names_c',
            'opportunity_abstract_attachment_c' => 'abstract_attachment_c',
            'opportunity_acceptance_letter_url_c' => 'acceptance_letter_url_c',
            'opportunity_payment_channel_c' => 'payment_channel_c',
            'opportunity_wire_transfer_attachment_c' => 'wire_transfer_attachment_c',
            'opportunity_jjwg_maps_address_c' => 'jjwg_maps_address_c',
            'opportunity_form_type_c' => 'form_type_c',
            'opportunity_suitecrm_id' => 'suitecrm_id',
            'opportunity_invitation_url' => 'invitation_url',
        ];

        return $fieldMap[$field] ?? $field;
    }

    /**
     * Check if a field is a date/datetime field that should be compared without time
     * This includes both DATE_MUTABLE and DATETIME_MUTABLE fields
     */
    private function isDateField(string $fieldColumn): bool
    {
        // All these fields should use DATE() comparison to ignore time component
        $dateFields = [
            'date_entered',        // DATETIME_MUTABLE
            'date_modified',       // DATETIME_MUTABLE
            'created_at',          // DATETIME_MUTABLE
            'updated_at',          // DATETIME_MUTABLE
            'date_closed',         // DATE_MUTABLE
            'abstract_book_send_date_c',    // DATE_MUTABLE
            'abstract_result_send_date_c',  // DATE_MUTABLE
            'abstract_result_ready_date_c', // DATE_MUTABLE
        ];

        return in_array($fieldColumn, $dateFields);
    }

    /**
     * Convert filter operator to SQL operator symbol
     */
    private function getOperatorSymbol(string $operator): string
    {
        $operatorMap = [
            'eq' => '=',
            'neq' => '!=',
            'gt' => '>',
            'gte' => '>=',
            'lt' => '<',
            'lte' => '<=',
        ];

        return $operatorMap[$operator] ?? '=';
    }

    /**
     * Convert date parameters to date-only format (Y-m-d) if they contain time information
     * This ensures dates like '2025-10-23 10:00:01' become '2025-10-23'
     * Handles both single values and arrays
     *
     * @param mixed $filterParameters Single value or array of values
     * @return mixed Converted value(s) in the same format (single value or array)
     */
    private function convertToDateOnly($filterParameters)
    {
        if (is_array($filterParameters)) {
            return array_map(function($param) {
                return $this->extractDateFromParameter($param);
            }, $filterParameters);
        } else {
            return $this->extractDateFromParameter($filterParameters);
        }
    }

    /**
     * Extract date-only part from a parameter value
     * Handles various datetime formats including DateTime objects
     *
     * @param mixed $param Parameter value (string, DateTime, or other)
     * @return string Extracted date in Y-m-d format or original value
     */
    private function extractDateFromParameter($param)
    {
        // Handle DateTime objects
        if ($param instanceof \DateTime || $param instanceof \DateTimeInterface) {
            return $param->format('Y-m-d');
        }

        // Handle string datetime formats
        if (is_string($param) && !empty($param)) {
            // Try to match various datetime formats:
            // YYYY-MM-DD HH:MM:SS (standard MySQL datetime)
            // YYYY-MM-DD HH:MM:SS.microseconds
            // YYYY-MM-DDTHH:MM:SS (ISO 8601)
            // YYYY-MM-DDTHH:MM:SS.microseconds
            // YYYY-MM-DDTHH:MM:SS+00:00 (ISO 8601 with timezone)
            if (preg_match('/^(\d{4}-\d{2}-\d{2})[\sT]\d{2}:\d{2}/', $param, $matches)) {
                // Extract only the date part (YYYY-MM-DD)
                return $matches[1];
            }

            // Already in date-only format YYYY-MM-DD
            if (preg_match('/^(\d{4}-\d{2}-\d{2})$/', $param, $matches)) {
                return $matches[1];
            }

            // Try to parse as a date/datetime string using strtotime
            $timestamp = strtotime($param);
            if ($timestamp !== false) {
                return date('Y-m-d', $timestamp);
            }
        }

        // Return original value if we can't parse it
        return $param;
    }
}