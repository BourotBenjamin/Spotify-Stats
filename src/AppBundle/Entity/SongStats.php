<?php
/**
 * Created by PhpStorm.
 * User: Philoupe
 * Date: 29/07/2017
 * Time: 16:34
 */

namespace AppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="song_stats")
 */
class SongStats
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $popularity;

    /**
     * @ORM\Column(type="float")
     */
    private $danceability;

    /**
     * @ORM\Column(type="float")
     */
    private $energy;

    /**
     * @ORM\Column(type="float")
     */
    private $valence;

    /**
     * @ORM\Column(type="float")
     */
    private $speechiness;

    /**
     * @ORM\Column(type="float")
     */
    private $acousticness;

    /**
     * @ORM\Column(type="float")
     */
    private $liveness;

    /**
     * @ORM\Column(type="float")
     */
    private $instrumentalness;

    /**
     * @ORM\Column(type="float")
     */
    private $loudness;

    /**
     * @ORM\Column(type="integer")
     */
    private $songKey;

    /**
     * @ORM\Column(type="float")
     */
    private $tempo;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getPopularity()
    {
        return $this->popularity;
    }

    /**
     * @param mixed $popularity
     * @return SongStats
     */
    public function setPopularity($popularity)
    {
        $this->popularity = $popularity;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDanceability()
    {
        return $this->danceability;
    }

    /**
     * @param mixed $danceability
     * @return SongStats
     */
    public function setDanceability($danceability)
    {
        $this->danceability = $danceability;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEnergy()
    {
        return $this->energy;
    }

    /**
     * @param mixed $energy
     * @return SongStats
     */
    public function setEnergy($energy)
    {
        $this->energy = $energy;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValence()
    {
        return $this->valence;
    }

    /**
     * @param mixed $valence
     * @return SongStats
     */
    public function setValence($valence)
    {
        $this->valence = $valence;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSpeechiness()
    {
        return $this->speechiness;
    }

    /**
     * @param mixed $speechiness
     * @return SongStats
     */
    public function setSpeechiness($speechiness)
    {
        $this->speechiness = $speechiness;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAcousticness()
    {
        return $this->acousticness;
    }

    /**
     * @param mixed $acousticness
     * @return SongStats
     */
    public function setAcousticness($acousticness)
    {
        $this->acousticness = $acousticness;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLiveness()
    {
        return $this->liveness;
    }

    /**
     * @param mixed $liveness
     * @return SongStats
     */
    public function setLiveness($liveness)
    {
        $this->liveness = $liveness;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInstrumentalness()
    {
        return $this->instrumentalness;
    }

    /**
     * @param mixed $instrumentalness
     * @return SongStats
     */
    public function setInstrumentalness($instrumentalness)
    {
        $this->instrumentalness = $instrumentalness;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLoudness()
    {
        return $this->loudness;
    }

    /**
     * @param mixed $loudness
     * @return SongStats
     */
    public function setLoudness($loudness)
    {
        $this->loudness = $loudness;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getTempo()
    {
        return $this->tempo;
    }

    /**
     * @param mixed $tempo
     * @return SongStats
     */
    public function setTempo($tempo)
    {
        $this->tempo = $tempo;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSongKey()
    {
        return $this->songKey;
    }

    /**
     * @param mixed $songKey
     * @return SongStats
     */
    public function setSongKey($songKey)
    {
        $this->songKey = $songKey;
        return $this;
    }






}