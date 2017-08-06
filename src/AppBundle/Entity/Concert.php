<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Artist
 *
 * @ORM\Table(name="concert")
 * @ORM\Entity()
 */
class Concert
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(name="concert_id", type="integer")
     */
    private $concertId;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Artist")
     */
    private $artists;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="string")
     */
    private $externalUrl;

    /**
     * @var Venue
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Venue")
     * @ORM\JoinColumn(name="venue_id")
     */
    private $venue;

    /**
     * Concert constructor.
     * @param int $concertId
     * @param $date
     * @param $externalUrl
     * @param Venue $venue
     */
    public function __construct($concertId, $date, $externalUrl, Venue $venue)
    {
        $this->concertId = $concertId;
        $this->date = $date;
        $this->externalUrl = $externalUrl;
        $this->venue = $venue;
        $this->artists = new ArrayCollection();
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
     * @return int
     */
    public function getConcertId()
    {
        return $this->concertId;
    }

    /**
     * @param int $concertId
     * @return Concert
     */
    public function setConcertId(int $concertId)
    {
        $this->concertId = $concertId;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getArtists()
    {
        return $this->artists;
    }

    /**
     * @param ArrayCollection $artists
     * @return Concert
     */
    public function setArtists(ArrayCollection $artists)
    {
        $this->artists = $artists;
        return $this;
    }
    
    public function addArtist(Artist $artist) {
        if(!$this->artists->contains($artist))
            $this->artists[] = $artist;
        return $this;
    }


    public function removeArtist(Artist $artist) {
        $this->artists->removeElement($artist);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     * @return Concert
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

}
