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

    /*curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,
        "postvar1=value1&postvar2=value2&postvar3=value3");*/
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        if(!$this->isGranted("IS_AUTHENTICATED_FULLY"))
            return $this->redirectToRoute('hwi_oauth_connect');
        $em = $this->getDoctrine()->getManager();
        /** @var OAuthToken $token */
        $token = $this->get('security.context')->getToken();
        $user = $em->getRepository("AppBundle:User")->findOneBy(array("spotifyId"=> $token->getUser()->getUsername()));
        if(!is_object($user))
        {
            $user = new User();
            $user->setSpotifyId($token->getUser()->getUsername());
            $em->persist($user);
        } else {
            $owner = $this->get("hwi_oauth.resource_owner.spotify");
            $result = $owner->refreshAccessToken($token->getRefreshToken());
            if(isset($result["access_token"]))
                $token->setAccessToken($result["access_token"]);
        }
        $user->setToken($token->getAccessToken());
        $lastFetch = $user->getLastFetch();
        $songs = $artists = $topArtists = $topSongs = $artists2 = array();
        if($lastFetch)
            $ch = curl_init("https://api.spotify.com/v1/me/player/recently-played?limit=50&after=".$lastFetch);
        else
            $ch = curl_init("https://api.spotify.com/v1/me/player/recently-played?limit=50");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $token->getAccessToken()
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close($ch);
        $recently_played = json_decode($server_output, true);
        $newlastFetch = $newlastFetch ?? $recently_played["cursors"]["after"] ?? $lastFetch;
        foreach ($recently_played["items"] as $recently_played_item) {
            $artist = $em->getRepository("AppBundle:Artist")->findOneBy(array("artistId"=> $recently_played_item["track"]["artists"][0]["id"]));
            if(!is_object($artist)) {
                $artist = new Artist($recently_played_item["track"]["artists"][0]["id"], $recently_played_item["track"]["artists"][0]["name"]);
                $em->persist($artist);
            }
            $song = $em->getRepository("AppBundle:Song")->findOneBy(array("songId"=> $recently_played_item["track"]["id"]));
            if(!is_object($song)) {
                $song = new Song($recently_played_item["track"]["id"], $recently_played_item["track"]["name"], $artist);
                $em->persist($song);
            }
            $songPlayed = $em->getRepository("AppBundle:PlayedSong")->findOneBy(array("song" => $song, "user"=> $user));
            if(!is_object($songPlayed)) {
                $songPlayed = new PlayedSong($song, $user);
            } else {
                $songPlayed->addCount();
            }
            $em->persist($songPlayed);
        }
        $user->setLastFetch($newlastFetch ?? $lastFetch);
        $em->persist($user);

        $next = "https://api.spotify.com/v1/me/top/artists";
        $i = 0;
        while ($next !== false && $i < 100) {
            ++$i;
            /** @var OAuthToken $token */
            $ch = curl_init($next);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $token->getAccessToken()
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $server_output = curl_exec($ch);
            curl_close($ch);

            $recently_played = json_decode($server_output, true);
            $next = $recently_played["next"] ?? false;
            foreach ($recently_played["items"] as $artist) {
                $topArtists[] = $artist["name"];
            }
        }
        $next = "https://api.spotify.com/v1/me/top/tracks";
        $i = 0;
        while ($next !== false && $i < 100) {
            ++$i;
            /** @var OAuthToken $token */
            $ch = curl_init($next);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $token->getAccessToken()
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $server_output = curl_exec($ch);
            curl_close($ch);

            $recently_played = json_decode($server_output, true);
            $next = $recently_played["next"] ?? false;
            foreach ($recently_played["items"] as $song) {
                $topSongs[] = $song["name"];
                if (isset($artists2[$song["artists"][0]["name"]]))
                    $artists2[$song["artists"][0]["name"]] += 1;
                else
                    $artists2[$song["artists"][0]["name"]] = 1;
            }
        }
        $em->flush();

        return $this->render('AppBundle:Default:index.html.twig', array(
            "artist" => array_rand($artists2),
            "count" =>$em->getRepository("AppBundle:User")->countPlayedSongs($user->getId()),
        ));
    }

}
