<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Entity\User as BaseUser;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 */
class User extends BaseUser
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="spotify_id", type="string", unique=true)
     */
    private $spotifyId;

    /**
     * @ORM\Column(name="token", type="string", length=255, unique=true)
     */
    private $token;

    /**
     * @ORM\Column(name="refresh_token", type="string")
     */
    private $refreshToken;

    /**
     * @ORM\Column(name="last_fetch", type="bigint", nullable=true)
     */
    private $last_fetch;

    /**
     * @return mixed
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * @param mixed $refreshToken
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
        return $this;
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
