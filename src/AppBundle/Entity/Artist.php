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
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="artist_id", type="string", length=255, nullable=true)
     */
    private $artistId;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(name="picture_url", type="string", length=255, nullable=true)
     */
    private $pictureUrl;

    /**
     * @var string
     * @ORM\Column(name="discogs_id", type="integer", nullable=true)
     */
    private $discogsId;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Genre")
     */
    private $genres;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Artist", inversedBy="members")
     */
    private $groups;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Artist", mappedBy="groups")
     */
    private $members;

    public function __construct($artistId, $name, $discogsId = null)
    {
        $this->artistId = $artistId;
        $this->discogsId = $discogsId;
        $this->name = $name;
        $this->genres = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->members = new ArrayCollection();
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

    /**
     * @return string
     */
    public function getPictureUrl()
    {
        return $this->pictureUrl;
    }

    /**
     * @param string $pictureUrl
     */
    public function setPictureUrl($pictureUrl)
    {
        $this->pictureUrl = $pictureUrl;
    }

    /**
     * @return ArrayCollection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    public function addGroup(Artist $group) {
        if(!$this->groups->contains($group))
            $this->groups[] = $group;
        return $this;
    }


    public function removeGroup(Artist $group) {
        $this->groups->removeElement($group);
        return $this;
    }

    /**
     * @param ArrayCollection $groups
     * @return Artist
     */
    public function setGroups(ArrayCollection $groups)
    {
        $this->groups = $groups;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getMembers()
    {
        return $this->members;
    }

    public function addMember(Artist $member) {
        if(!$this->members->contains($member))
            $this->members[] = $member;
        return $this;
    }


    public function removeMember(Artist $member) {
        $this->members->removeElement($member);
        return $this;
    }
    
    /**
     * @param ArrayCollection $members
     * @return Artist
     */
    public function setMembers(ArrayCollection $members)
    {
        $this->members = $members;
        return $this;
    }

    /**
     * @return string
     */
    public function getDiscogsId()
    {
        return $this->discogsId;
    }

    /**
     * @param string $discogsId
     * @return Artist
     */
    public function setDiscogsId(string $discogsId)
    {
        $this->discogsId = $discogsId;
        return $this;
    }
    
    
    
}
