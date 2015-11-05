<?php
/**
 * Created by PhpStorm.
 * User: kendoctor
 * Date: 15/11/5
 * Time: 下午10:50
 */

namespace Knd\Bundle\RadBundle\Repository;

use Doctrine\ORM\EntityRepository as BaseEntityRepository;

class EntityRepository extends BaseEntityRepository
{
    public function createIndexQueryBuilder($criteria = array())
    {
        $qb = $this->createQueryBuilder('o');

        return $qb;
    }
}