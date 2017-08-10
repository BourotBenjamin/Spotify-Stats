<?php
/**
 * Created by PhpStorm.
 * User: Philoupe
 * Date: 01/08/2017
 * Time: 20:52
 */

namespace AppBundle\Services;


use AppBundle\Entity\Album;
use AppBundle\Entity\Artist;
use AppBundle\Entity\City;
use AppBundle\Entity\Concert;
use AppBundle\Entity\Country;
use AppBundle\Entity\Genre;
use AppBundle\Entity\SongPopularity;
use AppBundle\Entity\SongStats;
use AppBundle\Entity\User;
use AppBundle\Entity\Venue;
use Doctrine\ORM\EntityManager;

class UpdateGroupsService
{

    private $em;
    private $spotifyApi;
    private $artistsByIds;
    private $artistsUpdated;
    private $artistsData;

    public function __construct(EntityManager $em, SpotifyApiService $spotifyApi)
    {
        $this->em = $em;
        $this->spotifyApi = $spotifyApi;
        $this->artistsUpdated = [];
    }


    public function updateArtists(User &$user)
    {
        $artists = $this->em->getRepository('AppBundle:Artist')->findBy(["discogsId" => null]);
        foreach ($artists as $artist) {
            $results = $this->spotifyApi->getDiscogsContent("https://api.discogs.com/database/search?type=artist&q=".urlencode($artist->getName()), $user, "results");
            if(isset($results[0]["id"])) {
                $artist->setDiscogsId($results[0]["id"]);
                $this->em->persist($artist);
            }
        }
        $this->em->flush();
        echo("Part 1 OK \n");
        $this->artistsByIds = $this->artistsByNames = [];
        $artists = $this->em->getRepository('AppBundle:Artist')->findAll();
        foreach ($artists as &$artist) {
            if($artist->getDiscogsId() !== null && $artist->getArtistId() !== null)
                $this->artistsByIds[$artist->getDiscogsId()] = $artist;
        }
        $artistsIds = array_keys($this->artistsByIds);
        foreach ($artistsIds as $artistId) {
            $this->updateArtist($user, $artistId, 0);
        }
        $this->em->flush();
    }

    public function updateArtist(User &$user, $artistId, $deep, $parentArtistId = null)
    {
        if($deep >= 4)
            return null;
        if(!isset($this->artistsUpdated[$artistId][$deep]) && !isset($this->artistsUpdated[$artistId][$deep -1]) && !isset($this->artistsUpdated[$artistId][$deep - 2]) && !isset($this->artistsUpdated[$artistId][$deep - 2])) {
            $this->artistsUpdated[$artistId][$deep] = 1;
            if(!isset($this->artistsData[$artistId]))
                $this->artistsData[$artistId] = $this->spotifyApi->getDiscogsContent("https://api.discogs.com/artists/" . $artistId, $user);
            $artistData = $this->artistsData[$artistId];
            if (!isset($artistData["id"])) {
                var_dump($artistData);
                return null;
            }
            $artist = $this->artistsByIds[$artistData["id"]] ?? null;
            echo(str_repeat("    ", $deep) . $artistData["name"] . "\n");
            if (!is_object($artist)) {
                $artist = new Artist(null, $artistData["name"], $artistData["id"]);
                if (isset($artistData["images"][0]['resource_url']))
                    $artist->setPictureUrl($artistData["images"][0]['resource_url']);
                $this->artistsByIds[$artistData["id"]] = $artist;
            }
            if (isset($artistData["members"]))
                foreach ($artistData["members"] as $memberData) {
                    if ($parentArtistId != $memberData['id']) {
                        $member = $this->updateArtist($user, $memberData['id'], $deep + 1, $artist->getDiscogsId());
                        if (is_object($member)) {
                            $member->addGroup($artist);
                            $artist->addMember($member);
                            $this->em->persist($member);
                        }
                    }
                }
            if (isset($artistData["groups"]))
                foreach ($artistData["groups"] as $groupData) {
                    if ($parentArtistId != $groupData['id']) {
                        $group = $this->updateArtist($user, $groupData['id'], $deep + 1, $artist->getDiscogsId());
                        if (is_object($group)) {
                            $group->addMember($artist);
                            $artist->addGroup($group);
                            $this->em->persist($group);
                        }
                    }
                }
            $this->em->persist($artist);
            return $artist;
        } else {
            echo(str_repeat("    ", $deep) . $artistId . " Already done ! \n");
            return $this->artistsByIds[$artistId];
        }
    }

}