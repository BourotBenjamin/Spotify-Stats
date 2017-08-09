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
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\DiscogsResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\SpotifyResourceOwner;
use HWI\Bundle\OAuthBundle\Security\OAuthUtils;
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
        $result = $this->spotifyOwner->refreshAccessToken($user->getSpotifyRefreshToken());
        if(isset($result["access_token"]))
            $user->setSpotifyAccessToken($result["access_token"]);
        $this->em->persist($user);
        $this->em->flush();
    }

    public function getSpotifyContent(string $url, User $user, $key = null) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $user->getSpotifyAccessToken(),
            'User-Agent: philoupe/1.0'
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

    public function getDiscogsContent(string $url, User $user, $key = null) {
        $ch = curl_init($url);
        $params = [
            "oauth_consumer_key" => "JnZLOvYVqLPkFNEcUCQJ",
            "oauth_timestamp" => time(),
            "oauth_nonce" => md5(microtime(true).uniqid('', true)),
            "oauth_version" => "1.0",
            "oauth_signature_method" => "HMAC-SHA1",
            "oauth_token" => $user->getDiscogsAccessToken(),
        ];
        $params["oauth_signature"] = OAuthUtils::signRequest("GET",
                $url,
                $params,
                "DvClqgdjOHYnjykDeEzqjEscEJpqEBDe",
                $user->getDiscogSecretToken(),
                "HMAC-SHA1");
        $params["realm"] = "";
        foreach ($params as $paramKey => &$value) {
            $params[$paramKey] = $paramKey.'="'.rawurlencode($value).'"';
        }
        $headers = ["User-Agent: HWIOAuthBundle (https://github.com/hwi/HWIOAuthBundle)", "Content-Length: 0"];
        $headers[] = 'Authorization: OAuth '.implode(', ', $params);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        if($server_output == false) {
            var_dump(curl_error($ch));
            var_dump(curl_errno($ch));
        }
        curl_close($ch);
        $data = json_decode($server_output, true);
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
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'User-Agent: philoupe/1.0'
        ));
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