<?php
/**
 * Created by PhpStorm.
 * User: Philoupe
 * Date: 01/08/2017
 * Time: 20:52
 */

namespace AppBundle\Services;


use Doctrine\ORM\EntityManager;

class UpdateUsersAchievementsService
{

    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function updateAchievements() {
        $achievements = $this->em->getRepository('AppBundle:Achievement')->findAll();
        foreach ($achievements as $achievement) {
            $this->em->getRepository('AppBundle:UserAchievement')->updateAchievementsToUnlock($achievement->getId(), json_decode($achievement->getRules(), true));
        }
    }

}