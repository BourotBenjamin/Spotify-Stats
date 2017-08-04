<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Album;
use AppBundle\Entity\Artist;
use AppBundle\Entity\Genre;
use AppBundle\Entity\PlayedSong;
use AppBundle\Entity\Song;
use AppBundle\Entity\SongStats;
use AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManager;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\SpotifyResourceOwner;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DefaultController extends Controller
{


    /**
     * @Route("/refresh", name="refesh")
     */
    public function refreshAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $this->get("app.services.update_user_history_service")->refreshToken($user, false);
        $this->get("app.services.update_user_history_service")->updateUserHistory($user, true);
        return $this->indexAction($request);
    }

    /**
     * @Route("/users", name="user_list")
     */
    public function usersAction(Request $request)
    {
        return $this->render('AppBundle:Default:user_list.html.twig', array(
            'users' => $this->getDoctrine()->getManager()->getRepository("AppBundle:User")->findAll()
        ));
    }
    /**
     * @Route("/", name="homepage")
     * @Route("/user/{id}", name="profile")
     */
    public function indexAction(Request $request, $id = -1)
    {
        if(!$this->isGranted("IS_AUTHENTICATED_FULLY"))
            return $this->redirectToRoute('hwi_oauth_connect');
        $em = $this->getDoctrine()->getManager();
        if($id == -1)
            $user = $this->get('security.token_storage')->getToken()->getUser();
        else
            $user = $em->getRepository("AppBundle:User")->find($id);
        if(!is_object($user))
            throw new HttpException(404, 'User not found');
        return $this->render('AppBundle:Default:index.html.twig', array(
            "user" => $user,
            "count" =>$em->getRepository("AppBundle:User")->countPlayedSongs($user->getId()),
            'achievements' => $em->getRepository('AppBundle:UserAchievement')->findBy(array('user' => $user), array('unlockedAt' => 'DESC'))
        ));
    }

    /**
     * @Route("/stats", name="stats")
     * @Route("/stats/{id}", name="user_stats")
     */
    public function statsAction(Request $request, $id = -1) {
        $em = $this->getDoctrine()->getManager();
        if($id == -1)
            $user = $this->get('security.token_storage')->getToken()->getUser();
        else
            $user = $em->getRepository("AppBundle:User")->find($id);
        if(!is_object($user))
            throw new HttpException(404, 'User not found');
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
        return $this->render('AppBundle:Default:stats.html.twig', array(
            "topArtists" => $topArtists,
            "topSongs" => $topSongs,
        ));
    }

    /**
     * @Route("/listened", name="listened")
     * @Route("/listened/{id}", name="user_listened")
     */
    public function listenedAction(Request $request, $id = -1) {
        $em = $this->getDoctrine()->getManager();
        if($id == -1)
            $user = $this->get('security.token_storage')->getToken()->getUser();
        else
            $user = $em->getRepository("AppBundle:User")->find($id);
        if(!is_object($user))
            throw new HttpException(404, 'User not found');
        $playedSongs = $em->getRepository("AppBundle:PlayedSong")->findBy(array("user"=> $user));
        return $this->render('AppBundle:Default:stats_songs.html.twig', array(
            "playedSongs" => $playedSongs,
        ));
    }

    /**
     * @Route("/genres", name="genres")
     * @Route("/genres/{id}", name="user_genres")
     */
    public function genresAction(Request $request, $id = -1) {
        $em = $this->getDoctrine()->getManager();
        if($id == -1)
            $user = $this->get('security.token_storage')->getToken()->getUser();
        else
            $user = $em->getRepository("AppBundle:User")->find($id);
        $genres = $em->getRepository("AppBundle:User")->getGenresListenedByUser($user->getId());
        return $this->render('@App/Default/stats_genres.html.twig', array(
            "genres" => $genres,
            "count" =>$em->getRepository("AppBundle:User")->countPlayedSongs($user->getId()),
        ));
    }

    /**
     * @Route("/song/{id}", name="song")
     */
    public function songAction($id) {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $this->get("app.services.update_user_history_service")->refreshToken($user, true);
        $song = $em->getRepository("AppBundle:Song")->find($id);
        $ch = curl_init("https://api.spotify.com/v1/audio-analysis/".$song->getSongId());
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $user->getToken()
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close($ch);
        $stats = json_decode($server_output, true);
        return $this->render('AppBundle:Default:song.html.twig', array(
            "stats" => $stats,
            "song" => $song,
        ));
    }

}
