<?php

namespace BddBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

/**
 * RankingRepository
 */
class RankingRepository extends EntityRepository
{
    /**
     * @return mixed|null
     */
    public function findCurrent()
    {
        try {
            return $this->getEntityManager()
                ->createQueryBuilder()
                ->select('r')
                ->from('BddBundle:Ranking', 'r')
                ->orderBy('r.computedAt', 'desc')
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    /**
     * @param array $filters
     * @return \Doctrine\ORM\Query
     */
    public function findCoastersRanked(array $filters = [])
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('c', 'p', 'm')
            ->from('BddBundle:Coaster', 'c')
            ->innerJoin('c.park', 'p')
            ->leftJoin('c.manufacturer', 'm')
            ->where('c.rank is not null')
            ->orderBy('c.rank', 'asc');

        $this->applyFilters($qb, $filters);

        return $qb->getQuery();
    }

    /**
     * @param QueryBuilder $qb
     * @param array $filters
     */
    private function applyFilters(QueryBuilder $qb, array $filters = [])
    {
        $this->filterLocation($qb, $filters);
        $this->filterMaterialType($qb, $filters);
        $this->filterSeatingType($qb, $filters);
        $this->filterManufacturer($qb, $filters);
    }

    /**
     * @param QueryBuilder $qb
     * @param array $filters
     */
    private function filterLocation(QueryBuilder $qb, array $filters = [])
    {
        if (array_key_exists('country', $filters) && $filters['country'] !== '') {
            $qb
                ->join('p.country', 'co')
                ->andWhere('co.id = :country')
                ->setParameter('country', $filters['country']);
        } elseif (array_key_exists('continent', $filters) && $filters['continent'] !== '') {
            $qb
                ->join('p.country', 'co')
                ->join('co.continent', 'ct')
                ->andWhere('ct.id = :continent')
                ->setParameter('continent', $filters['continent']);
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param array $filters
     */
    private function filterMaterialType(QueryBuilder $qb, array $filters = [])
    {
        if (array_key_exists('materialType', $filters) && $filters['materialType'] !== '') {
            $qb
                ->join('c.materialType', 'mt')
                ->andWhere('mt.id = :materialType')
                ->setParameter('materialType', $filters['materialType']);
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param array $filters
     */
    private function filterSeatingType(QueryBuilder $qb, array $filters = [])
    {
        if (array_key_exists('seatingType', $filters) && $filters['seatingType'] !== '') {
            $qb
                ->join('c.seatingType', 'st')
                ->andWhere('st.id = :seatingType')
                ->setParameter('seatingType', $filters['seatingType']);
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param array $filters
     */
    private function filterManufacturer(QueryBuilder $qb, array $filters = [])
    {
        if (array_key_exists('manufacturer', $filters) && $filters['manufacturer'] !== '') {
            $qb
                ->andWhere('m.id = :manufacturer')
                ->setParameter('manufacturer', $filters['manufacturer']);
        }
    }
}
