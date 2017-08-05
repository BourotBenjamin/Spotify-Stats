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
 * @ORM\Table(name="artist__popularity")
 */
class ArtistPopularity extends Popularity
{
    
    /**
     * @var Song
     * @ORM\ManyToOne(targetEntity="Artist", fetch="EAGER")
     * @ORM\JoinColumn(name="artist_id")
     */
    private $artist;

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

}