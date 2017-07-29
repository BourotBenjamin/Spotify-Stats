<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Artist;
use AppBundle\Entity\PlayedSong;
use AppBundle\Entity\Song;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\SpotifyResourceOwner;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{


    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        if(!$this->isGranted("IS_AUTHENTICATED_FULLY"))
            return $this->redirectToRoute('hwi_oauth_connect');
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $this->get("app.services.update_user_history_service")->refreshToken($user, true);
        $this->get("app.services.update_user_history_service")->updateUserHistory($user, true);

        return $this->render('AppBundle:Default:index.html.twig', array(
            "count" =>$em->getRepository("AppBundle:User")->countPlayedSongs($user->getId()),
        ));
    }

    /**
     * @Route("/stats", name="stats")
     */
    public function statsAction() {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $this->get("app.services.update_user_history_service")->refreshToken($user, true);

        $ch = curl_init("https://api.spotify.com/v1/me/top/artists?limit=50");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $user->getToken()
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close($ch);
        $topArtists = json_decode($server_output, true)["items"];

        $ch = curl_init("https://api.spotify.com/v1/me/top/tracks?limit=50");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $user->getToken()
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close($ch);
        $topSongs = json_decode($server_output, true)["items"];

        $ch = curl_init("https://api.spotify.com/v1/audio-features?ids=".implode(",", array_column($topSongs, "id")));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $user->getToken()
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close($ch);
        $topSongsStats = json_decode($server_output, true)["audio_features"];
        return $this->render('AppBundle:Default:stats.html.twig', array(
            "topArtists" => $topArtists,
            "songs" => $topSongs,
            "songsStats" => $topSongsStats,
        ));
    }

    /**
     * @Route("/listened", name="listened")
     */
    public function listenedAction() {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $this->get("app.services.update_user_history_service")->refreshToken($user, true);
        $songsPlayed = $em->getRepository("AppBundle:PlayedSong")->findBy(array("user"=> $user));
        $songsPlayedIds = [];
        foreach ($songsPlayed as $songPlayed) {
            if(!empty($songPlayed->getSong()->getSongId()))
                $songsPlayedIds[] = $songPlayed->getSong()->getSongId();
        }

        $songsStats = $songs = [];
        $songsPlayedIds = array_chunk($songsPlayedIds, 50);
        foreach ($songsPlayedIds as $songsPlayedIdsSubArray) {
            $ch = curl_init("https://api.spotify.com/v1/tracks?ids=" . implode(",", $songsPlayedIdsSubArray));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $user->getToken()
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $server_output = curl_exec($ch);
            curl_close($ch);
            $songs = array_merge($songs, json_decode($server_output, true)["tracks"]);
            $ch = curl_init("https://api.spotify.com/v1/audio-features?ids=" . implode(",", $songsPlayedIdsSubArray));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $user->getToken()
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $server_output = curl_exec($ch);
            curl_close($ch);
            $songsStats = array_merge($songsStats, json_decode($server_output, true)["audio_features"]);
        }
        return $this->render('AppBundle:Default:stats_songs.html.twig', array(
            "songs" => $songs,
            "songsStats" => $songsStats,
        ));
    }

}
