<?php
/**
 * Created by PhpStorm.
 * User: Philoupe
 * Date: 05/08/2017
 * Time: 13:03
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="song__popularity")
 */
class SongPopularity extends Popularity
{

    /**
     * @var Song
     * @ORM\ManyToOne(targetEntity="Song", fetch="EAGER")
     * @ORM\JoinColumn(name="song_id")
     */
    private $song;

    /**
     * SongPopularity constructor.
     * @param Song $song
     */
    public function __construct($popularity, $createdAt, Song $song)
    {
        parent::__construct($popularity, $createdAt);
        $this->song = $song;
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
     */
    public function setSong(Song $song)
    {
        $this->song = $song;
    }



}