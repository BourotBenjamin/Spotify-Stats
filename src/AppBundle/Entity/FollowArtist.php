<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Song
 *
 * @ORM\Table(name="follow__artist")
 * @ORM\Entity()
 */
class FollowArtist
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
     * @var Song
     * @ORM\ManyToOne(targetEntity="Artist", fetch="EAGER")
     * @ORM\JoinColumn(name="artist_id")
     */
    private $artist;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id")
     */
    private $user;

    public function __construct(Song $artists, User $user)
    {
        $this->artist = $artists;
        $this->user = $user;
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
     * @return Song
     */
    public function getArtist()
    {
        return $this->artist;
    }

    /**
     * @param Song $artist
     */
    public function setArtist(Song $artist)
    {
        $this->artist = $artist;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return PlayedSong
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

}
