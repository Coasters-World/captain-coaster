<?php

namespace BddBundle\Repository;

use BddBundle\Entity\User;

/**
 * ListeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ListeRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countAll()
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('count(1)')
            ->from('BddBundle:Liste', 'l')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    public function findAllTops()
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('l')
            ->addSelect('COUNT(l.id) as HIDDEN nb')
            ->from('BddBundle:Liste', 'l')
            ->join('l.listeCoasters', 'lc')
            ->where('l.main = 1')
            ->groupBy('l.id')
            ->having('nb > 2')
            ->orderBy('l.updatedAt', 'desc')
            ->getQuery();
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    public function findAllCustomLists()
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('l')
            ->addSelect('COUNT(l.id) as HIDDEN nb')
            ->from('BddBundle:Liste', 'l')
            ->join('l.listeCoasters', 'lc')
            ->where('l.main = 0')
            ->groupBy('l.id')
            ->having('nb > 2')
            ->orderBy('l.updatedAt', 'desc')
            ->getQuery();
    }

    /**
     * Return all lists for a user
     *
     * @param User $user
     * @return mixed
     */
    public function findAllByUser(User $user)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('l')
            ->from('BddBundle:Liste', 'l')
            ->where('l.user = :user')
            ->setParameter('user', $user)
            ->orderBy('l.updatedAt', 'desc')
            ->getQuery()
            ->getResult();
    }
}
