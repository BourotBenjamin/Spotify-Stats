<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Artist;
use AppBundle\Entity\PlayedSong;
use AppBundle\Entity\Song;
use AppBundle\Entity\User;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\SpotifyResourceOwner;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{

    private function refreshToken(OAuthToken $token) {
        $owner = $this->get("hwi_oauth.resource_owner.spotify");
        $result = $owner->refreshAccessToken($token->getRefreshToken());
        if(isset($result["access_token"]))
            $token->setAccessToken($result["access_token"]);
        return $token;
    }

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        if(!$this->isGranted("IS_AUTHENTICATED_FULLY"))
            return $this->redirectToRoute('hwi_oauth_connect');
        $em = $this->getDoctrine()->getManager();
        $token = $this->refreshToken($this->get('security.token_storage')->getToken());
        $user = $em->getRepository("AppBundle:User")->findOneBy(array("spotifyId"=> $token->getUser()->getUsername()));
        if(!is_object($user))
        {
            $user = new User();
            $user->setSpotifyId($token->getUser()->getUsername());
            $em->persist($user);
        } else {
        }
        $user->setToken($token->getAccessToken());
        $lastFetch = $user->getLastFetch();
        if($lastFetch)
            $ch = curl_init("https://api.spotify.com/v1/me/player/recently-played?limit=50&after=".$lastFetch);
        else
            $ch = curl_init("https://api.spotify.com/v1/me/player/recently-played?limit=49");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $token->getAccessToken()
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close($ch);
        $api_response = json_decode($server_output, true);
        $newlastFetch = $newlastFetch ?? $api_response["cursors"]["after"] ?? $lastFetch;
        $artists = $songs = $songsPlayed = [];
        foreach ($api_response["items"] as $recently_played_item) {
            $artist = $artists[$recently_played_item["track"]["artists"][0]["id"]] ?? null;
            if(!is_object($artist))
                $artist = $em->getRepository("AppBundle:Artist")->findOneBy(array("artistId"=> $recently_played_item["track"]["artists"][0]["id"]));
            if(!is_object($artist)) {
                $artist = new Artist($recently_played_item["track"]["artists"][0]["id"], $recently_played_item["track"]["artists"][0]["name"]);
                $em->persist($artist);
            }
            $artists[$recently_played_item["track"]["artists"][0]["id"]] = $artist;
            $song = $songs[$recently_played_item["track"]["id"]] ?? null;
            if(!is_object($song))
                $song = $em->getRepository("AppBundle:Song")->findOneBy(array("songId"=> $recently_played_item["track"]["id"]));
            if(!is_object($song)) {
                $song = new Song($recently_played_item["track"]["id"], $recently_played_item["track"]["name"], $artist);
                $em->persist($song);
            }
            $songs[$recently_played_item["track"]["id"]] = $song;
            $songPlayed = $songsPlayed[$recently_played_item["track"]["id"]] ?? null;
            if(!is_object($songPlayed))
                $songPlayed = $em->getRepository("AppBundle:PlayedSong")->findOneBy(array("song" => $song, "user"=> $user));
            if(!is_object($songPlayed)) {
                $songPlayed = new PlayedSong($song, $user);
            } else {
                $songPlayed->addCount();
            }
            $songsPlayed[$recently_played_item["track"]["id"]] = $songPlayed;
            $em->persist($songPlayed);
        }
        $user->setLastFetch($newlastFetch ?? $lastFetch);
        $em->persist($user);
        $em->flush();

        return $this->render('AppBundle:Default:index.html.twig', array(
            "count" =>$em->getRepository("AppBundle:User")->countPlayedSongs($user->getId()),
        ));
    }

    /**
     * @Route("/stats", name="stats")
     */
    public function statsAction() {
        $token = $this->refreshToken($this->get('security.token_storage')->getToken());
        $topArtists = $topSongs = $topSongsArtists = array();

        $ch = curl_init("https://api.spotify.com/v1/me/top/artists?limit=50");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $token->getAccessToken()
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close($ch);
        $topArtists = json_decode($server_output, true)["items"];

        $ch = curl_init("https://api.spotify.com/v1/me/top/tracks?limit=50");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $token->getAccessToken()
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close($ch);
        $topSongs = json_decode($server_output, true)["items"];

        return $this->render('AppBundle:Default:stats.html.twig', array(
            "topArtists" => $topArtists,
            "topSongs" => $topSongs,
        ));
    }

}
