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

        switch ($operator) {
            case 'eq':
                $qb->andWhere($fieldAlias . ' = :value');
                $qb->setParameter('value', $value);
                break;
            case 'neq':
                $qb->andWhere($fieldAlias . ' != :value');
                $qb->setParameter('value', $value);
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
                $qb->andWhere($fieldAlias . ' > :value');
                $qb->setParameter('value', $value);
                break;
            case 'gte':
                $qb->andWhere($fieldAlias . ' >= :value');
                $qb->setParameter('value', $value);
                break;
            case 'lt':
                $qb->andWhere($fieldAlias . ' < :value');
                $qb->setParameter('value', $value);
                break;
            case 'lte':
                $qb->andWhere($fieldAlias . ' <= :value');
                $qb->setParameter('value', $value);
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
}
