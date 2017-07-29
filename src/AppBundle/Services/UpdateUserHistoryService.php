<?php
/**
 * Created by PhpStorm.
 * User: Philoupe
 * Date: 29/07/2017
 * Time: 21:16
 */

namespace AppBundle\Services;


use AppBundle\Entity\Artist;
use AppBundle\Entity\PlayedSong;
use AppBundle\Entity\Song;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\SpotifyResourceOwner;

class UpdateUserHistoryService
{

    private $em;
    private $spotify;

    /**
     * UpdateUserHistoryService constructor.
     * @param $em
     */
    public function __construct(EntityManager $em, SpotifyResourceOwner $spotify)
    {
        $this->em = $em;
        $this->spotify = $spotify;
    }

    function refreshToken(User $user, bool $flush = false) {
        $result = $this->spotify->refreshAccessToken($user->getRefreshToken());
        if(isset($result["access_token"]))
            $user->setToken($result["access_token"]);
        $this->em->persist($user);
        $this->em->flush();
    }

    function updateUsersHistory()
    {
        $users = $this->em->getRepository('AppBundle:User')->findAll();
        foreach ($users as $user) {
            $this->refreshToken($user);
            $this->updateUserHistory($user);
        }
        $this->em->flush();
    }

    function updateUserHistory(User $user, bool $flush = false) {
            $lastFetch = $user->getLastFetch();
            if($lastFetch)
                $ch = curl_init("https://api.spotify.com/v1/me/player/recently-played?limit=50&after=".$lastFetch);
            else
                $ch = curl_init("https://api.spotify.com/v1/me/player/recently-played?limit=49");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $user->getToken()
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $server_output = curl_exec($ch);
            curl_close($ch);
            $api_response = json_decode($server_output, true);
            $newlastFetch = $newlastFetch ?? $api_response["cursors"]["after"] ?? $lastFetch;
            $artists = $songs = $songsPlayed = [];
            if(isset($api_response["items"])) {
                foreach ($api_response["items"] as $recently_played_item) {
                    $artist = $artists[$recently_played_item["track"]["artists"][0]["id"]] ?? null;
                    if (!is_object($artist))
                        $artist = $this->em->getRepository("AppBundle:Artist")->findOneBy(array("artistId" => $recently_played_item["track"]["artists"][0]["id"]));
                    if (!is_object($artist)) {
                        $artist = new Artist($recently_played_item["track"]["artists"][0]["id"], $recently_played_item["track"]["artists"][0]["name"]);
                        $this->em->persist($artist);
                    }
                    $artists[$recently_played_item["track"]["artists"][0]["id"]] = $artist;
                    $song = $songs[$recently_played_item["track"]["id"]] ?? null;
                    if (!is_object($song))
                        $song = $this->em->getRepository("AppBundle:Song")->findOneBy(array("songId" => $recently_played_item["track"]["id"]));
                    if (!is_object($song)) {
                        $song = new Song($recently_played_item["track"]["id"], $recently_played_item["track"]["name"], $artist);
                        $this->em->persist($song);
                    }
                    $songs[$recently_played_item["track"]["id"]] = $song;
                    $songPlayed = $songsPlayed[$recently_played_item["track"]["id"]] ?? null;
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
                $user->setLastFetch($newlastFetch ?? $lastFetch);
                $this->em->persist($user);
                if ($flush)
                    $this->em->flush();
            }
    }

}