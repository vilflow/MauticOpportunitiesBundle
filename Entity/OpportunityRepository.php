<?php

namespace MauticPlugin\MauticOpportunitiesBundle\Entity;

use Mautic\CoreBundle\Entity\CommonRepository;

/**
 * @extends CommonRepository<Opportunity>
 */
class OpportunityRepository extends CommonRepository
{
    protected function getDefaultOrder(): array
    {
        return [
            ['o.name', 'ASC'],
        ];
    }

    public function getTableAlias(): string
    {
        return 'o';
    }

    public function findByExternalId(string $externalId): ?Opportunity
    {
        return $this->findOneBy(['opportunityExternalId' => $externalId]);
    }

    public function findByContactId(int $contactId): array
    {
        return $this->createQueryBuilder('o')
            ->where('o.contact = :contactId')
            ->setParameter('contactId', $contactId)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByEventId(int $eventId): array
    {
        return $this->createQueryBuilder('o')
            ->where('o.event = :eventId')
            ->setParameter('eventId', $eventId)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByStage(string $stage): array
    {
        return $this->createQueryBuilder('o')
            ->where('o.salesStage = :stage')
            ->setParameter('stage', $stage)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findBySuitecrmId(?string $suitecrmId): ?Opportunity
    {
        if (null === $suitecrmId) {
            return null;
        }
        
        return $this->findOneBy(['suitecrmId' => $suitecrmId]);
    }


    /**
     * Check if contact has opportunities matching field value criteria
     */
    public function contactHasOpportunityByFieldValue(int $contactId, string $field, string $operator, $value): bool
    {
        $qb = $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->where('o.contact = :contactId')
            ->setParameter('contactId', $contactId);

        $fieldAlias = 'o.' . $field;

        // Check if this is a date field
        $isDateField = $this->isDateField($field);

        // For date fields, convert value to date-only format and use DATE() or SUBSTRING()
        if ($isDateField && in_array($operator, ['eq', 'neq', 'gt', 'gte', 'lt', 'lte'])) {
            $dateValue = $this->convertToDateOnly($value);

            // Check field type from metadata
            $metadata = $this->_em->getClassMetadata(Opportunity::class);
            $useDateFunction = false;

            if ($metadata->hasField($field)) {
                $fieldMapping = $metadata->getFieldMapping($field);
                $fieldType = $fieldMapping['type'] ?? 'string';
                // For DATETIME_MUTABLE fields, use DATE() function
                $useDateFunction = in_array($fieldType, ['datetime', 'datetimetz', 'datetime_immutable']);
            }

            $columnExpression = $useDateFunction ? 'DATE(' . $fieldAlias . ')' : $fieldAlias;
        }

        switch ($operator) {
            case 'eq':
                if ($isDateField && isset($columnExpression)) {
                    $qb->andWhere($fieldAlias . ' IS NOT NULL');
                    $qb->andWhere($columnExpression . ' = :value');
                    $qb->setParameter('value', $dateValue);
                } else {
                    $qb->andWhere($fieldAlias . ' = :value');
                    $qb->setParameter('value', $value);
                }
                break;
            case 'neq':
                if ($isDateField && isset($columnExpression)) {
                    $qb->andWhere($fieldAlias . ' IS NOT NULL');
                    $qb->andWhere($columnExpression . ' != :value');
                    $qb->setParameter('value', $dateValue);
                } else {
                    $qb->andWhere($fieldAlias . ' != :value');
                    $qb->setParameter('value', $value);
                }
                break;
            case 'like':
                $qb->andWhere($fieldAlias . ' LIKE :value');
                $qb->setParameter('value', '%' . $value . '%');
                break;
            case '!like':
                $qb->andWhere($fieldAlias . ' NOT LIKE :value');
                $qb->setParameter('value', '%' . $value . '%');
                break;
            case 'startsWith':
                $qb->andWhere($fieldAlias . ' LIKE :value');
                $qb->setParameter('value', $value . '%');
                break;
            case 'endsWith':
                $qb->andWhere($fieldAlias . ' LIKE :value');
                $qb->setParameter('value', '%' . $value);
                break;
            case 'gt':
                if ($isDateField && isset($columnExpression)) {
                    $qb->andWhere($fieldAlias . ' IS NOT NULL');
                    $qb->andWhere($columnExpression . ' > :value');
                    $qb->setParameter('value', $dateValue);
                } else {
                    $qb->andWhere($fieldAlias . ' > :value');
                    $qb->setParameter('value', $value);
                }
                break;
            case 'gte':
                if ($isDateField && isset($columnExpression)) {
                    $qb->andWhere($fieldAlias . ' IS NOT NULL');
                    $qb->andWhere($columnExpression . ' >= :value');
                    $qb->setParameter('value', $dateValue);
                } else {
                    $qb->andWhere($fieldAlias . ' >= :value');
                    $qb->setParameter('value', $value);
                }
                break;
            case 'lt':
                if ($isDateField && isset($columnExpression)) {
                    $qb->andWhere($fieldAlias . ' IS NOT NULL');
                    $qb->andWhere($columnExpression . ' < :value');
                    $qb->setParameter('value', $dateValue);
                } else {
                    $qb->andWhere($fieldAlias . ' < :value');
                    $qb->setParameter('value', $value);
                }
                break;
            case 'lte':
                if ($isDateField && isset($columnExpression)) {
                    $qb->andWhere($fieldAlias . ' IS NOT NULL');
                    $qb->andWhere($columnExpression . ' <= :value');
                    $qb->setParameter('value', $dateValue);
                } else {
                    $qb->andWhere($fieldAlias . ' <= :value');
                    $qb->setParameter('value', $value);
                }
                break;
            case 'empty':
                $qb->andWhere('(' . $fieldAlias . ' IS NULL OR ' . $fieldAlias . ' = \'\')');
                break;
            case '!empty':
                $qb->andWhere($fieldAlias . ' IS NOT NULL')
                   ->andWhere($fieldAlias . ' != \'\'');
                break;
            case 'in':
                if (is_array($value)) {
                    $qb->andWhere($fieldAlias . ' IN (:value)');
                    $qb->setParameter('value', $value);
                } else {
                    // Handle comma-separated values
                    $values = explode(',', $value);
                    $values = array_map('trim', $values);
                    $qb->andWhere($fieldAlias . ' IN (:value)');
                    $qb->setParameter('value', $values);
                }
                break;
            case '!in':
                if (is_array($value)) {
                    $qb->andWhere($fieldAlias . ' NOT IN (:value)');
                    $qb->setParameter('value', $value);
                } else {
                    // Handle comma-separated values
                    $values = explode(',', $value);
                    $values = array_map('trim', $values);
                    $qb->andWhere($fieldAlias . ' NOT IN (:value)');
                    $qb->setParameter('value', $values);
                }
                break;
            case 'regexp':
                $qb->andWhere($fieldAlias . ' REGEXP :value');
                $qb->setParameter('value', $value);
                break;
            case '!regexp':
                $qb->andWhere($fieldAlias . ' NOT REGEXP :value');
                $qb->setParameter('value', $value);
                break;
            default:
                // Default to equals
                $qb->andWhere($fieldAlias . ' = :value');
                $qb->setParameter('value', $value);
                break;
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * Check if a field is a date/datetime field
     */
    private function isDateField(string $field): bool
    {
        $dateFields = [
            'dateEntered',
            'dateModified',
            'createdAt',
            'updatedAt',
            'dateClosed',
            'abstractBookSendDateC',
            'abstractResultSendDateC',
            'abstractResultReadyDateC',
        ];

        return in_array($field, $dateFields);
    }

    /**
     * Convert date parameters to date-only format (Y-m-d)
     */
    private function convertToDateOnly($value): string
    {
        // Handle DateTime objects
        if ($value instanceof \DateTime || $value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        // Handle string datetime formats
        if (is_string($value) && !empty($value)) {
            // Extract date part from datetime strings
            if (preg_match('/^(\d{4}-\d{2}-\d{2})[\sT]\d{2}:\d{2}/', $value, $matches)) {
                return $matches[1];
            }

            // Already in date-only format
            if (preg_match('/^(\d{4}-\d{2}-\d{2})$/', $value, $matches)) {
                return $matches[1];
            }

            // Try to parse as date
            $timestamp = strtotime($value);
            if ($timestamp !== false) {
                return date('Y-m-d', $timestamp);
            }
        }

        // Return original value if we can't parse it
        return $value;
    }
}
