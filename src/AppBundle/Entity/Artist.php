<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Artist
 *
 * @ORM\Table(name="artist")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ArtistRepository")
 */
class Artist
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="artist_id", type="string", length=255)
     */
    private $artistId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Genre")
     */
    private $genres;

    public function __construct($artistId, $name)
    {
        $this->artistId = $artistId;
        $this->name = $name;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getArtistId()
    {
        return $this->artistId;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Artist
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param ArrayCollection $genres
     * @return  Artist
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

}
