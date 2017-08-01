<?php
/**
 * Created by PhpStorm.
 * User: Philoupe
 * Date: 01/08/2017
 * Time: 20:49
 */

namespace AppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserAchievementRepository")
 * @ORM\Table(name="user__achievement")
 */
class UserAchievement
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */private $id;
    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id")
     */
    private $user;
    /**
     * @var Achievement
     * @ORM\ManyToOne(targetEntity="Achievement")
     * @ORM\JoinColumn(name="achievement_id")
     */
    private $achievement;
    /**
     * @ORM\Column(type="datetime")
     */
    private $unlockedAt;

    /**
     * UserAchievement constructor.
     * @param $user
     * @param $achievement
     * @param $unlockedAt
     */
    public function __construct($user, $achievement, $unlockedAt)
    {
        $this->user = $user;
        $this->achievement = $achievement;
        $this->unlockedAt = $unlockedAt;
    }

    /**
     * @return Achievement
     */
    public function getAchievement()
    {
        return $this->achievement;
    }

    /**
     * @return mixed
     */
    public function getUnlockedAt()
    {
        return $this->unlockedAt;
    }




}