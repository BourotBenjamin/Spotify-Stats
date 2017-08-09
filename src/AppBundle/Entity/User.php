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
     * @ORM\Column(name="spotify_access_token", type="string", length=255, unique=true)
     */
    private $spotifyAccessToken;

    /**
     * @ORM\Column(name="spotify_refresh_token", type="string")
     */
    private $spotifyRefreshToken;

    /**
     * @ORM\Column(name="discogs_id", type="string", unique=true)
     */
    private $discogsId;

    /**
     * @ORM\Column(name="discogs_access_token", type="string", length=255, unique=true)
     */
    private $discogsAccessToken;
    /**
     * @ORM\Column(name="discogs_secret_token", type="string", length=255, unique=true)
     */
    private $discogsSecretToken;

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
     * Get spotifyId
     *
     * @return integer
     */
    public function getSpotifyId()
    {
        return $this->spotifyId;
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
     * @return mixed
     */
    public function getSpotifyAccessToken()
    {
        return $this->spotifyAccessToken;
    }

    /**
     * @param mixed $spotifyAccessToken
     * @return User
     */
    public function setSpotifyAccessToken($spotifyAccessToken)
    {
        $this->spotifyAccessToken = $spotifyAccessToken;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSpotifyRefreshToken()
    {
        return $this->spotifyRefreshToken;
    }

    /**
     * @param mixed $spotifyRefreshToken
     * @return User
     */
    public function setSpotifyRefreshToken($spotifyRefreshToken)
    {
        $this->spotifyRefreshToken = $spotifyRefreshToken;
        return $this;
    }

    /**
     * @param mixed $discogsId
     */
    public function setDiscogsId($discogsId)
    {
        $this->discogsId = $discogsId;
    }

    /**
     * @return mixed
     */
    public function getDiscogsId()
    {
        return $this->discogsId;
    }

    /**
     * @return mixed
     */
    public function getDiscogsAccessToken()
    {
        return $this->discogsAccessToken;
    }

    /**
     * @param mixed $discogsAccessToken
     * @return User
     */
    public function setDiscogsAccessToken($discogsAccessToken)
    {
        $this->discogsAccessToken = $discogsAccessToken;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDiscogSecretToken()
    {
        return $this->discogsSecretToken;
    }

    /**
     * @param mixed $discogsSecretToken
     * @return User
     */
    public function setDiscogsSecretToken($discogsSecretToken)
    {
        $this->discogsSecretToken = $discogsSecretToken;
        return $this;
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
