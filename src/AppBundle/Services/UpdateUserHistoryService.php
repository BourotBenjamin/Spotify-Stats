<?php
/**
 * Created by PhpStorm.
 * User: Philoupe
 * Date: 29/07/2017
 * Time: 21:16
 */

namespace AppBundle\Services;


use AppBundle\Entity\Album;
use AppBundle\Entity\Artist;
use AppBundle\Entity\Genre;
use AppBundle\Entity\PlayedSong;
use AppBundle\Entity\Song;
use AppBundle\Entity\SongStats;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\SpotifyResourceOwner;

class UpdateUserHistoryService
{

    private $em;
    private $spotifyApi;

    /**
     * UpdateUserHistoryService constructor.
     * @param $em
     */
    public function __construct(EntityManager $em, SpotifyApiService $spotifyApi)
    {
        $this->em = $em;
        $this->spotifyApi = $spotifyApi;
    }

    function updateUsersHistory()
    {
        $users = $this->em->getRepository('AppBundle:User')->findAll();
        foreach ($users as $user) {
            $this->updateUserHistory($user);
        }
        $this->em->flush();
    }

    function updateUserHistory(User $user, $flush = false) {
        $api_response = $this->spotifyApi->getSpotifyContent("https://api.spotify.com/v1/me/player/recently-played?limit=49".(($lastFetch = $user->getLastFetch()) ? "&after=".$lastFetch : ""), $user, "items");
        foreach ($api_response["items"] as $recently_played_item) {
            $artist = null;
            if(isset($artists[$recently_played_item["track"]["artists"][0]["id"]]))
                $artist = $artists[$recently_played_item["track"]["artists"][0]["id"]];
            if (!is_object($artist))
                $artist = $this->em->getRepository("AppBundle:Artist")->findOneBy(array("artistId" => $recently_played_item["track"]["artists"][0]["id"]));
            if (!is_object($artist)) {
                $artist = new Artist($recently_played_item["track"]["artists"][0]["id"], $recently_played_item["track"]["artists"][0]["name"]);
                $this->em->persist($artist);
            }
            $artists[$recently_played_item["track"]["artists"][0]["id"]] = $artist;
            $song = null;
            if(isset($songs[$recently_played_item["track"]["id"]]))
                $song = $songs[$recently_played_item["track"]["id"]];
            if (!is_object($song))
                $song = $this->em->getRepository("AppBundle:Song")->findOneBy(array("songId" => $recently_played_item["track"]["id"]));
            if (!is_object($song)) {
                $song = new Song($recently_played_item["track"]["id"], $recently_played_item["track"]["name"], $artist);
                $this->em->persist($song);
            }
            $songs[$recently_played_item["track"]["id"]] = $song;
            $songPlayed = null;
            if(isset($songsPlayed[$recently_played_item["track"]["id"]]))
                $songPlayed = $songsPlayed[$recently_played_item["track"]["id"]];
            if (!is_object($songPlayed))
                $songPlayed = $this->em->getRepository("AppBundle:PlayedSong")->findOneBy(array("song" => $song, "user" => $user));
            if (!is_object($songPlayed)) {
                $songPlayed = new PlayedSong($song, $user);
            } else {
                $songPlayed->addCount();
            }
            $songsPlayed[$recently_played_item["track"]["id"]] = $songPlayed;
            $this->em->persist($songPlayed);
        }
        if(isset($api_response["cursors"]["after"]))
            $user->setLastFetch($api_response["cursors"]["after"]);
        $this->em->persist($user);
        if ($flush)
            $this->em->flush();
    }


}