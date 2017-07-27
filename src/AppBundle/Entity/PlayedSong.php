<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Song
 *
 * @ORM\Table(name="played__song")
 * @ORM\Entity()
 */
class PlayedSong
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
     * @ORM\ManyToOne(targetEntity="Song")
     * @ORM\JoinColumn(name="song_id")
     */
    private $song;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id")
     */
    private $user;

    /**
     * @var int
     * @ORM\Column(name="count", type="integer")
     */
    private $count;

    public function __construct(Song $song, User $user)
    {
        $this->song = $song;
        $this->user = $user;
        $this->count = 1;
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
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @return PlayedSong
     */
    public function addCount()
    {
        $this->count += 1;
        return $this;
    }

    /**
     * @return Song
     */
    public function getSong()
    {
        return $this->song;
    }

    /**
     * @param Song $song
     * @return PlayedSong
     */
    public function setSong(Song $song)
    {
        $this->song = $song;
        return $this;
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
