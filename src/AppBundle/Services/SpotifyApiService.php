<?php
/**
 * Created by PhpStorm.
 * User: Philoupe
 * Date: 01/08/2017
 * Time: 20:52
 */

namespace AppBundle\Services;


use AppBundle\Entity\Album;
use AppBundle\Entity\Genre;
use AppBundle\Entity\SongPopularity;
use AppBundle\Entity\SongStats;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\SpotifyResourceOwner;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class SpotifyApiService
{

    private $em;
    private $spotifyOwner;

    public function __construct(EntityManager $em, SpotifyResourceOwner $spotifyOwner)
    {
        $this->em = $em;
        $this->spotifyOwner = $spotifyOwner;
    }

    private function refreshToken(User $user) {
        $result = $this->spotifyOwner->refreshAccessToken($user->getRefreshToken());
        if(isset($result["access_token"]))
            $user->setToken($result["access_token"]);
        $this->em->persist($user);
        $this->em->flush();
    }

    public function getSpotifyContent(string $url, User $user, $key = null) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $user->getToken()
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        if($server_output == false) {
            var_dump(curl_error($ch));
            var_dump(curl_errno($ch));
        }
        curl_close($ch);
        $data = json_decode($server_output, true);
        if(isset($data["error"]["status"])) {
            if($data["error"]["status"] == 401) {
                $this->refreshToken($user);
                return $this->getSpotifyContent($url, $user);
            } else {
                echo($url."\n".$data["error"]["status"]." ".$data["error"]["message"]."\n");
                return [];
            }
        }
        if($key === null)
            return $data;
        elseif(isset($data[$key]))
            return $data[$key];
        else
            return [];
    }

    public function getExternalContent(string $url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        if($server_output == false) {
            var_dump(curl_error($ch));
            var_dump(curl_errno($ch));
        }
        curl_close($ch);
        $data = json_decode($server_output, true);
        return $data;
    }

}