<?php

declare(strict_types=1);

namespace MauticPlugin\MauticOpportunitiesBundle\Entity;

use Mautic\CoreBundle\Entity\CommonRepository;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\QueryBuilder;

class OpportunityRepository extends CommonRepository
{
    /**
     * Find opportunity by SuiteCRM ID
     */
    public function findBySuitecrmId(?string $suitecrmId): ?Opportunity
    {
        if (null === $suitecrmId) {
            return null;
        }

        return $this->findOneBy(['suitecrmId' => $suitecrmId]);
    }

    /**
     * Find opportunity by external ID
     */
    public function findByExternalId(?string $externalId): ?Opportunity
    {
        if (null === $externalId) {
            return null;
        }

        return $this->findOneBy(['opportunityExternalId' => $externalId]);
    }

    /**
     * Find opportunity by name
     */
    public function findByName(?string $name): ?Opportunity
    {
        if (null === $name) {
            return null;
        }

        return $this->findOneBy(['name' => $name]);
    }

    public function findBySuitecrm(?string $suitecrmId): ?Opportunity
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

        $column = 'o.' . $field;
        $this->applyOperatorToQuery($qb, $column, $operator, $value);

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    private function applyOperatorToQuery(QueryBuilder $qb, string $column, string $operator, mixed $value): void
    {
        $parameter = 'value';
        $trimmedValue = is_string($value) ? trim($value) : $value;

        switch ($operator) {
            case '=':
            case 'eq':
                $qb->andWhere($column.' = :'.$parameter);
                $qb->setParameter($parameter, $trimmedValue);
                break;
            case '!=':
            case 'neq':
                $qb->andWhere('('.$column.' != :'.$parameter.' OR '.$column.' IS NULL)');
                $qb->setParameter($parameter, $trimmedValue);
                break;
            case 'like':
                $qb->andWhere($column.' LIKE :'.$parameter)
                    ->setParameter($parameter, '%'.$trimmedValue.'%');
                break;
            case '!like':
                $qb->andWhere($column.' NOT LIKE :'.$parameter)
                    ->setParameter($parameter, '%'.$trimmedValue.'%');
                break;
            case 'contains':
                $qb->andWhere($column.' LIKE :'.$parameter)
                    ->setParameter($parameter, '%'.$trimmedValue.'%');
                break;
            case 'startsWith':
                $qb->andWhere($column.' LIKE :'.$parameter)
                    ->setParameter($parameter, $trimmedValue.'%');
                break;
            case 'endsWith':
                $qb->andWhere($column.' LIKE :'.$parameter)
                    ->setParameter($parameter, '%'.$trimmedValue);
                break;
            case 'gt':
                $qb->andWhere($column.' > :'.$parameter)
                    ->setParameter($parameter, $trimmedValue);
                break;
            case 'gte':
                $qb->andWhere($column.' >= :'.$parameter)
                    ->setParameter($parameter, $trimmedValue);
                break;
            case 'lt':
                $qb->andWhere($column.' < :'.$parameter)
                    ->setParameter($parameter, $trimmedValue);
                break;
            case 'lte':
                $qb->andWhere($column.' <= :'.$parameter)
                    ->setParameter($parameter, $trimmedValue);
                break;
            case 'in':
                $values = is_array($trimmedValue) ? $trimmedValue : array_map('trim', explode(',', (string) $trimmedValue));
                $values = array_values(array_filter($values, static fn($item) => $item !== '' && $item !== null));
                if (empty($values)) {
                    $qb->andWhere('1 = 0');
                    break;
                }
                $qb->andWhere($column.' IN (:'.$parameter.')')
                    ->setParameter($parameter, $values);
                break;
            case '!in':
                $values = is_array($trimmedValue) ? $trimmedValue : array_map('trim', explode(',', (string) $trimmedValue));
                $values = array_values(array_filter($values, static fn($item) => $item !== '' && $item !== null));
                if (empty($values)) {
                    break;
                }
                $qb->andWhere($column.' NOT IN (:'.$parameter.')')
                    ->setParameter($parameter, $values);
                break;
            case 'empty':
                $qb->andWhere('('.$column.' IS NULL OR '.$column." = '' )");
                break;
            case '!empty':
                $qb->andWhere($column.' IS NOT NULL')
                    ->andWhere($column." != ''");
                break;
            case 'regexp':
                $qb->andWhere($column.' REGEXP :'.$parameter)
                    ->setParameter($parameter, $trimmedValue);
                break;
            case '!regexp':
                $qb->andWhere($column.' NOT REGEXP :'.$parameter)
                    ->setParameter($parameter, $trimmedValue);
                break;
            case 'date':
                // Handle anniversary special case - match month and day only
                if ('anniversary' === $trimmedValue) {
                    $dateValue = $this->convertRelativeDateToActual($trimmedValue);
                    $qb->andWhere('SUBSTRING('.$column.', 6, 5) = SUBSTRING(:'.$parameter.', 6, 5)')
                        ->setParameter($parameter, $dateValue);
                } else {
                    // Check if this is a relative interval (e.g., -30, +30, -P50D, +P1M)
                    $isRelativeInterval = $this->isRelativeInterval($trimmedValue);

                    if ($isRelativeInterval) {
                        // For relative intervals: create a RANGE filter
                        $rangeParams = $this->calculateDateRange($trimmedValue);
                        $startDate = $rangeParams['start'];
                        $endDate = $rangeParams['end'];

                        // Get field metadata to check if it's a datetime or date field
                        $metadata = $this->_em->getClassMetadata(Opportunity::class);
                        $fieldName = str_replace('o.', '', $column);

                        $useSubstring = false;
                        if ($metadata->hasField($fieldName)) {
                            $fieldMapping = $metadata->getFieldMapping($fieldName);
                            $fieldType = $fieldMapping['type'] ?? 'string';
                            $useSubstring = in_array($fieldType, ['datetime', 'datetimetz', 'datetime_immutable']);
                        }

                        // Build BETWEEN query
                        if ($useSubstring) {
                            // For datetime fields, extract date part first
                            $qb->andWhere('SUBSTRING('.$column.', 1, 10) BETWEEN :startDate AND :endDate')
                                ->setParameter('startDate', $startDate)
                                ->setParameter('endDate', $endDate);
                        } else {
                            // For date fields, direct comparison
                            $qb->andWhere($column.' BETWEEN :startDate AND :endDate')
                                ->setParameter('startDate', $startDate)
                                ->setParameter('endDate', $endDate);
                        }
                    } else {
                        // For absolute dates: exact match
                        $dateValue = $this->convertRelativeDateToActual($trimmedValue);

                        // Get field metadata to check if it's a datetime or date field
                        $metadata = $this->_em->getClassMetadata(Opportunity::class);
                        $fieldName = str_replace('o.', '', $column);

                        if ($metadata->hasField($fieldName)) {
                            $fieldMapping = $metadata->getFieldMapping($fieldName);
                            $fieldType = $fieldMapping['type'] ?? 'string';

                            // For datetime fields, extract just the date part
                            if (in_array($fieldType, ['datetime', 'datetimetz', 'datetime_immutable'])) {
                                $qb->andWhere('SUBSTRING('.$column.', 1, 10) = :'.$parameter)
                                    ->setParameter($parameter, $dateValue);
                            } else {
                                // For date fields, direct comparison works
                                $qb->andWhere($column.' = :'.$parameter)
                                    ->setParameter($parameter, $dateValue);
                            }
                        } else {
                            // Fallback: assume datetime and extract date part
                            $qb->andWhere('SUBSTRING('.$column.', 1, 10) = :'.$parameter)
                                ->setParameter($parameter, $dateValue);
                        }
                    }
                }
                break;
            default:
                $qb->andWhere($column.' = :'.$parameter)
                    ->setParameter($parameter, $trimmedValue);
        }
    }

    private function isRelativeInterval(string $value): bool
    {
        if (preg_match('/^([+-])(PT?)(\d+)([DIMHWY])$/i', $value, $matches)) {
            $amount = (int)$matches[3];
            if ($amount === 0) {
                return false; // +P0D = today
            }
            if ($amount === 1 && strtoupper($matches[4]) === 'D') {
                return false; // -P1D = yesterday, +P1D = tomorrow
            }
            return true;
        }
        return false;
    }

    private function calculateDateRange(string $value): array
    {
        $today = new \DateTime('now', new \DateTimeZone('UTC'));
        $today->setTime(0, 0, 0);

        if (preg_match('/^([+-])(PT?)(\d+)([DIMHWY])$/i', $value, $matches)) {
            $sign = $matches[1];
            $timePrefix = strtoupper($matches[2]);
            $amount = $matches[3];
            $unit = strtoupper($matches[4]);

            $isTimeInterval = ($timePrefix === 'PT' && in_array($unit, ['H', 'M']));

            $unitMap = [
                'D' => 'day',
                'W' => 'week',
                'M' => $isTimeInterval ? 'minute' : 'month',
                'Y' => 'year',
                'H' => 'hour',
                'I' => 'minute',
            ];

            $modifier = $amount . ' ' . ($unitMap[$unit] ?? 'day');
            if ((int)$amount !== 1) {
                $modifier .= 's';
            }

            if ($sign === '-') {
                $startDate = clone $today;
                $startDate->modify('-' . $modifier);
                $endDate = clone $today;

                return [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d'),
                ];
            } else {
                $startDate = clone $today;
                $endDate = clone $today;
                $endDate->modify('+' . $modifier);

                return [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d'),
                ];
            }
        }

        return [
            'start' => $today->format('Y-m-d'),
            'end' => $today->format('Y-m-d'),
        ];
    }

    private function convertRelativeDateToActual(string $value): string
    {
        if (preg_match('/^([+-])(PT?)(\d+)([DIMHWY])$/i', $value, $matches)) {
            $sign = $matches[1];
            $timePrefix = strtoupper($matches[2]);
            $amount = $matches[3];
            $unit = strtoupper($matches[4]);

            $isTimeInterval = ($timePrefix === 'PT' && in_array($unit, ['H', 'M']));

            $unitMap = [
                'D' => 'day',
                'W' => 'week',
                'M' => $isTimeInterval ? 'minute' : 'month',
                'Y' => 'year',
                'H' => 'hour',
                'I' => 'minute',
            ];

            $modifier = $sign . $amount . ' ' . ($unitMap[$unit] ?? 'day');
            if ((int)$amount !== 1) {
                $modifier .= 's';
            }

            $date = new \DateTime('now', new \DateTimeZone('UTC'));
            $date->modify($modifier);

            return $date->format('Y-m-d');
        }

        switch (strtolower($value)) {
            case 'today':
                return (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d');
            case 'yesterday':
                return (new \DateTime('yesterday', new \DateTimeZone('UTC')))->format('Y-m-d');
            case 'tomorrow':
                return (new \DateTime('tomorrow', new \DateTimeZone('UTC')))->format('Y-m-d');
            case 'anniversary':
                return (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d');
            default:
                // Return as-is if already in date format or let DB handle
                return $value;
        }
    }
}
