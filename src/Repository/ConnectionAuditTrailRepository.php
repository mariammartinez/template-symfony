<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\ConnectionAuditTrail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ConnectionAuditTrail|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConnectionAuditTrail|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConnectionAuditTrail[]    findAll()
 * @method ConnectionAuditTrail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConnectionAuditTrailRepository extends ServiceEntityRepository
{
    /**
     * UserRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConnectionAuditTrail::class);
    }


    /**
     * @param string $apiToken
     * @param string $deviceSessionToken
     *
     * @return int|mixed|string|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findApiSession(string $apiToken, string $deviceSessionToken)
    {
        $q = $this->createQueryBuilder('c')
            ->addSelect('a')
            ->addSelect('u')
            ->innerJoin('c.application', 'a')
            ->innerJoin('c.user', 'u')
            ->where('a.api_token = :api_token')
            ->andWhere('c.device_session_token = :device_session_token')
            ->andWhere('c.ended_at IS NULL')
            ->setParameter('api_token', $apiToken)
            ->setParameter('device_session_token', $deviceSessionToken)
            ->getQuery();
        return $q->getOneOrNullResult();
    }


    /**
     * @param int $reason 1=logout, 2=timeout, 3=force
     * @param string $deviceSessionToken
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function logoutDevice(string $deviceSessionToken, int $reason): void
    {
        $sSql = 'UPDATE connection_audit_trail SET reason = :reason, ended_at = NOW() WHERE ended_at IS NULL AND device_session_token = :device_session_token';
        $params = ['reason' => $reason, 'device_session_token' => $deviceSessionToken];
        $this->getEntityManager()->getConnection()->executeUpdate($sSql, $params);
    }
}
