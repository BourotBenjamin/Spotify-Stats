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
 * @ORM\Entity
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
     * @ORM\Column(type="string")
     */
    private $pictureUrl;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Genre")
     * @ORM\JoinColumn(name="artist_id")
     */
    private $genres;

    /**
     * Album constructor.
     * @param $id
     * @param $albumId
     * @param $name
     * @param $pictureUrl
     */
    public function __construct($albumId, $name, $pictureUrl)
    {
        $this->albumId = $albumId;
        $this->name = $name;
        $this->pictureUrl = $pictureUrl;
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
     * @param ArrayCollection $genres
     * @return  Album
     */
    public function setGenres(ArrayCollection $genres)
    {
        $this->genres = $genres;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getGenres()
    {
        return $this->genres;
    }

    public function addGenre(Genre $genre) {
        if(!$this->genres->contains($genre))
            $this->genres[] = $genre;
        return $this;
    }


    public function removeGenre(Genre $genre) {
        $this->genres->removeElement($genre);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPictureUrl()
    {
        return $this->pictureUrl;
    }

}