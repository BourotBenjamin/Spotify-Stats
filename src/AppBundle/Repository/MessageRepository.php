<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * MessageRepository
 *
 * This class was generated by the PhpStorm "Php Annotations" Plugin. Add your own custom
 * repository methods below.
 */
class MessageRepository extends EntityRepository
{

    public function getLastMessagesByUsers($userId) {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult("message", "message");
        $rsm->addScalarResult("created_at", "created_at");
        $rsm->addScalarResult("username", "username");
        $rsm->addScalarResult("user_id", "user_id");
        $rsm->addScalarResult("is_read", "is_read");
        $sql = <<< SQL
            SELECT m.message, m.created_at, u.username, u.id user_id,  IF(m.is_read = 1 OR m.user_form_id = ?, 1, 0) is_read
            FROM message m
            JOIN (
                SELECT MAX(m.created_at) created_at, IF(m.user_form_id = ?, m.user_to_id, m.user_form_id) user
                FROM message m
                GROUP BY user
            ) m2 ON m.created_at = m2.created_at AND (m.user_form_id = m2.user OR m.user_to_id = m2.user)
            JOIN user u ON (IF(m.user_form_id = ?, m.user_to_id, m.user_form_id) = u.id)
            WHERE m.user_form_id = ? OR m.user_to_id = ?
            ORDER BY created_at DESC
SQL;
        $messages = $this->getEntityManager()
            ->createNativeQuery($sql, $rsm)
            ->setParameters(array($userId, $userId, $userId, $userId, $userId))
            ->getScalarResult();
        foreach ($messages as &$message)
            $message['message'] = str_replace("\n", "<br>", preg_replace("#(spotify\:[a-zA-Z0-9\:]+)#", '
                <iframe src="https://open.spotify.com/embed?uri=$1" width="250" height="80" frameborder="0" allowtransparency="true"></iframe>
                ', strip_tags($message['message'])));
        return $messages;
    }

    public function getCountUnreadMessages($userId) {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult("count", "count");
        $sql = <<< SQL
            SELECT COUNT(m.id) count
            FROM message m
            WHERE m.user_to_id = ? AND m.is_read = 1
SQL;
        return $this->getEntityManager()
            ->createNativeQuery($sql, $rsm)
            ->setParameters(array($userId))
            ->getSingleScalarResult();
    }


}
