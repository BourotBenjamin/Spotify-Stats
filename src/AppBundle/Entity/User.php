<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 */
class User
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
     * @var int
     *
     * @ORM\Column(name="spotify_id", type="integer", unique=true)
     */
    private $spotifyId;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=255, unique=true)
     */
    private $token;


    /**
     * @ORM\Column(name="last_fetch", type="bigint", nullable=true)
     */
    private $last_fetch;


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
     * Set spotifyId
     *
     * @param integer $spotifyId
     * @return User
     */
    public function setSpotifyId($spotifyId)
    {
        $this->spotifyId = $spotifyId;

        return $this;
    }

    /**
     * Get spotifyId
     *
     * @return integer 
     */
    public function getSpotifyId()
    {
        return $this->spotifyId;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return User
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * Get token
     *
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return integer
     */
    public function getLastFetch()
    {
        return $this->last_fetch;
    }

    /**
     * @param integer $last_fetch
     * @return User
     */
    public function setLastFetch($last_fetch)
    {
        $this->last_fetch = $last_fetch;
        return $this;
    }
}
