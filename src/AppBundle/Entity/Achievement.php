<?php
/**
 * Created by PhpStorm.
 * User: Philoupe
 * Date: 01/08/2017
 * Time: 20:43
 */

namespace AppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="achievement")
 */
class Achievement
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
     * @ORM\Column(type="string")
     */
    private $imageUrl;
    /**
     * @ORM\Column(type="string")
     */
    private $rules;

    /**
     * Achievement constructor.
     * @param $name
     * @param $imageUrl
     * @param $rules
     */
    public function __construct($name, $imageUrl, $rules)
    {
        $this->name = $name;
        $this->imageUrl = $imageUrl;
        $this->rules = $rules;
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
     * @return mixed
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * @return mixed
     */
    public function getRules()
    {
        return $this->rules;
    }




}