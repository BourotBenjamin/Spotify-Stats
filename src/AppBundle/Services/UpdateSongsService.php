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
use AppBundle\Entity\SongStats;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;

class UpdateSongsService
{

    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }


    public function updateSongStats(User &$user)
    {
        $songs = $this->em->getRepository('AppBundle:Song')->findBy(array('stats' => null));
        $songsByIds = [];
        foreach ($songs as $songEntity) {
                $songsByIds[$songEntity->getSongId()] = $songEntity;
        }
        $songsIds = array_chunk(array_keys($songsByIds), 50);
        foreach ($songsIds as $songsIdsSubArray) {
            $ch = curl_init("https://api.spotify.com/v1/audio-features?ids=" . implode(",", $songsIdsSubArray));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $user->getToken()
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $server_output = curl_exec($ch);
            curl_close($ch);
            $songFeatures = json_decode($server_output, true)["audio_features"];
            foreach ($songFeatures as $key => $song)
                if(!empty($song))
                {
                    $songEntity = $songsByIds[$song["id"]];
                    if(!is_object($songStats = $songEntity->getStats())) {
                        $songStats = new SongStats();
                        $songStats->setAcousticness($song['acousticness'])
                            ->setDanceability($song['danceability'])
                            ->setEnergy($song['energy'])
                            ->setInstrumentalness($song['instrumentalness'])
                            ->setSongKey($song['key'])
                            ->setLiveness($song['liveness'])
                            ->setLoudness($song['loudness'])
                            ->setSpeechiness($song['speechiness'])
                            ->setTempo($song['tempo'])
                            ->setValence($song['valence']);
                        $this->em->persist($songStats);
                        $songEntity->setStats($songStats);
                        $this->em->persist($songEntity);
                    }
                }
        }
    }


    public function updateArtistsGenres(User &$user)
    {
        $artists = $this->em->getRepository('AppBundle:Artist')->findAll();
        $artistsByIds = [];
        foreach ($artists as &$artist) {
            $artistsByIds[$artist->getArtistId()] = &$artist;
        }
        $genres = $this->em->getRepository('AppBundle:Genre')->findAll();
        $genresByNames = [];
        foreach ($genres as &$genre) {
            $genresByNames[$genre->getName()] = &$genre;
        }

        $artistsIds = array_chunk(array_keys($artistsByIds), 20);
        foreach ($artistsIds as $artistsIdsSubArray) {
            $ch = curl_init("https://api.spotify.com/v1/artists?ids=" . implode(",", $artistsIdsSubArray));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $user->getToken()
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $server_output = curl_exec($ch);
            curl_close($ch);
            $artistsInfos = json_decode($server_output, true)["artists"];
            foreach ($artistsInfos as $artistInfos) {
                $artist = $artistsByIds[$artistInfos['id']];
                foreach ($artistInfos['genres'] as $genreName) {
                    if (isset($genres[$genreName]))
                        $genre = $genres[$genreName];
                    else {
                        $genre = new Genre();
                        $genre->setName($genreName);
                        $this->em->persist($genre);
                        $genres[$genreName] = $genre;
                    }
                    $artist->addGenre($genre);
                }
                $this->em->persist($artist);
            }
        }
    }

    public function updateSongAlbumAndPopularity(User &$user)
    {
        $songs = $this->em->getRepository('AppBundle:Song')->findAll();
        $songsByIds = [];
        foreach ($songs as $songEntity) {
            if(!is_object($songEntity->getStats()))
                $songsByIds[$songEntity->getSongId()] = $songEntity;
        }
        $albums = $this->em->getRepository('AppBundle:Album')->findAll();
        $albumsByIds = [];
        foreach ($albums as &$album) {
            $albumsByIds[$album->getAlbumId()] = &$album;
        }
        $songsIds = array_chunk(array_keys($songsByIds), 50);
        foreach ($songsIds as $songsIdsSubArray) {
            $ch = curl_init("https://api.spotify.com/v1/tracks?ids=" . implode(",", $songsIdsSubArray));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $user->getToken()
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $server_output = curl_exec($ch);
            curl_close($ch);
            $songInfos = json_decode($server_output, true)["audio_features"];
            foreach ($songInfos as $key => $song)
                if(!empty($song))
                {
                    $songEntity = $songsByIds[$song["id"]];
                    if(!isset($albumsByIds[$song["album"]['id']])) {
                        $albumsByIds[$song["album"]['id']] = new Album(
                            $song["album"]['id'],
                            $song["album"]['name'],
                            $song["album"]['images'][0]['url']);
                        $this->em->persist($albumsByIds[$song["album"]['id']]);
                    }
                    $songEntity->setAlbum($albumsByIds[$song["album"]['id']]);
                    if(is_object($songStats = $songEntity->getStats()))
                        $songStats->setPopularity($song['popularity']);
                    $this->em->persist($songStats);
                    $this->em->persist($songEntity);
                }
        }
    }


}