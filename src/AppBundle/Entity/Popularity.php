<?php
/**
 * Created by PhpStorm.
 * User: Philoupe
 * Date: 05/08/2017
 * Time: 13:01
 */

namespace AppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

abstract class Popularity
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $popularity;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * Popularity constructor.
     * @param $id
     * @param $popularity
     * @param $createdAt
     */
    public function __construct($popularity, $createdAt)
    {
        $this->popularity = $popularity;
        $this->createdAt = $createdAt;
    }


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getPopularity()
    {
        return $this->popularity;
    }

    /**
     * @param mixed $popularity
     */
    public function setPopularity($popularity)
    {
        $this->popularity = $popularity;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }


}