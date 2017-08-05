<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * ArtistRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ArtistRepository extends EntityRepository
{

    public function getTopArtistsByUser($userId) {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult("artist_id", "artist_id");
        $sql = <<< SQL
            SELECT SUM(count) total, MAX(p.id) max, s.artist_id
            FROM played__song p
            JOIN song s ON p.song_id = s.id
            WHERE p.user_id = ?
            GROUP BY s.artist_id
            ORDER BY total DESC, max DESC 
            LIMIT 50
SQL;
        $ids = $this->getEntityManager()
            ->createNativeQuery($sql, $rsm)
            ->setParameters([$userId])
            ->getScalarResult();
        return $this->findBy(["id" => array_column($ids, "artist_id")]);
    }

}
