<?php
/**
 * Created by PhpStorm.
 * User: Philoupe
 * Date: 29/07/2017
 * Time: 16:30
 */

namespace AppBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AlbumRepository")
 * @ORM\Table(name="album")
 */
class Album
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
    private $albumId;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $pictureUrl;

    /**
     * @var string
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;


    /**
     * Album constructor.
     * @param $id
     * @param $albumId
     * @param $name
     * @param $pictureUrl
     */
    public function __construct($albumId, $name, $pictureUrl, $type)
    {
        $this->albumId = $albumId;
        $this->name = $name;
        $this->pictureUrl = $pictureUrl;
        $this->type = $type;
        $this->genres = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getAlbumId()
    {
        return $this->albumId;
    }

    /**
     * @param mixed $albumId
     * @return  Album
     */
    public function setAlbumId($albumId)
    {
        $this->albumId = $albumId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPictureUrl()
    {
        return $this->pictureUrl;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

}