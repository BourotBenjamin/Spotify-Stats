<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Song
 *
 * @ORM\Table(name="song")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SongRepository")
 */
class Song
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
     * @ORM\Column(name="song_id", type="string")
     */
    private $songId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var Artist
     * @ORM\ManyToOne(targetEntity="Artist")
     * @ORM\JoinColumn(name="artist_id")
     */
    private $artist;

    /**
     * @var SongStats
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\SongStats", fetch="EAGER")
     * @ORM\JoinColumn(name="stats_id", nullable=true)
     */
    private $stats;

    /**
     * @var Album
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Album", fetch="EAGER")
     * @ORM\JoinColumn(name="album_id", nullable=true)
     */
    private $album;


    public function __construct($id, $name, Artist $artist)
    {
        $this->songId = $id;
        $this->name = $name;
        $this->artist = $artist;
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
     * Set name
     *
     * @param string $name
     * @return Song
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
     * Set artist
     *
     * @param Artist $artist
     * @return Song
     */
    public function setArtist($artist)
    {
        $this->artist = $artist;
        return $this;
    }

    /**
     * Get artist
     *
     * @return Artist
     */
    public function getArtist()
    {
        return $this->artist;
    }

    /**
     * @return mixed
     */
    public function getSongId()
    {
        return $this->songId;
    }

    /**
     * @param mixed $songId
     * @return Song
     */
    public function setSongId($songId)
    {
        $this->songId = $songId;
        return $this;
    }

    /**
     * @param SongStats $stats
     * @return Song
     */
    public function setStats(SongStats $stats)
    {
        $this->stats = $stats;
        return $this;
    }

    /**
     * @return SongStats
     */
    public function getStats()
    {
        return $this->stats;
    }

    /**
     * @return Album
     */
    public function getAlbum()
    {
        return $this->album;
    }

    /**
     * @param Album $album
     * @return Song
     */
    public function setAlbum(Album $album)
    {
        $this->album = $album;
        return $this;
    }

}
