<?php
/**
 * Created by PhpStorm.
 * User: Philoupe
 * Date: 06/08/2017
 * Time: 19:31
 */

namespace AppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="venue")
 */
class Venue
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @var City
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\City")
     * @ORM\JoinColumn(name="city_id")
     */
    private $city;

    /**
     * Venue constructor.
     * @param $name
     * @param City $city
     */
    public function __construct($name, City $city)
    {
        $this->name = $name;
        $this->city = $city;
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return City
     */
    public function getCity()
    {
        return $this->city;
    }




}