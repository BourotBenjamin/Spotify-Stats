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

class UpdateSongsService
{

    private $em;
    private $spotifyApi;

    public function __construct(EntityManager $em, SpotifyApiService $spotifyApi)
    {
        $this->em = $em;
        $this->spotifyApi = $spotifyApi;
    }


    public function updateSongStats(User &$user)
    {
        $songs = $this->em->getRepository('AppBundle:Song')->findBy(array('stats' => null));
        $songsByIds = [];
        foreach ($songs as $songEntity) {
            if(!empty($songEntity->getSongId()))
                $songsByIds[$songEntity->getSongId()] = $songEntity;
        }
        $songsIds = array_chunk(array_keys($songsByIds), 50);
        foreach ($songsIds as $songsIdsSubArray) {
            $songFeatures = $this->spotifyApi->getSpotifyContent("https://api.spotify.com/v1/audio-features?ids=" . implode(",", $songsIdsSubArray), $user)["audio_features"];
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
            if(!empty($artist->getArtistId()))
                $artistsByIds[$artist->getArtistId()] = &$artist;
        }
        $genres = $this->em->getRepository('AppBundle:Genre')->findAll();
        $genresByNames = [];
        foreach ($genres as &$genre) {
            $genresByNames[$genre->getName()] = &$genre;
        }

        $artistsIds = array_chunk(array_keys($artistsByIds), 20);
        foreach ($artistsIds as $artistsIdsSubArray) {
            $artistsInfos = $this->spotifyApi->getSpotifyContent("https://api.spotify.com/v1/artists?ids=" . implode(",", $artistsIdsSubArray), $user)["artists"];
            foreach ($artistsInfos as $artistInfos) {
                $artist = $artistsByIds[$artistInfos['id']];
                foreach ($artistInfos['genres'] as $genreName) {
                    if (isset($genresByNames[$genreName]))
                        $genre = $genresByNames[$genreName];
                    else {
                        $genre = new Genre();
                        $genre->setName($genreName);
                        $this->em->persist($genre);
                        $genresByNames[$genreName] = $genre;
                    }
                    $artist->addGenre($genre);
                }
                $this->em->persist($artist);
            }
        }
    }

    public function updateSongAlbumAndPopularity(User &$user)
    {
        $songsByIds = $albumsByIds = $popularitiesById = [];
        $songs = $this->em->getRepository('AppBundle:Song')->findAll();
        foreach ($songs as $songEntity) {
            if(!empty($songEntity->getSongId()))
                $songsByIds[$songEntity->getSongId()] = $songEntity;
        }
        $popularities = $this->em->getRepository('AppBundle:Song')->getLastPouplarityValues();
        foreach ($popularities as $popularity) {
            $popularitiesById[$popularity['song_id']] = $popularity['popularity'];
        }
        $albums = $this->em->getRepository('AppBundle:Album')->findAll();
        foreach ($albums as &$album) {
            if(!empty($album->getAlbumId()))
                $albumsByIds[$album->getAlbumId()] = &$album;
        }
        $songsIds = array_chunk(array_keys($songsByIds), 50);
        foreach ($songsIds as $songsIdsSubArray) {
            $songInfos = $this->spotifyApi->getSpotifyContent("https://api.spotify.com/v1/tracks?ids=" . implode(",", $songsIdsSubArray), $user)["tracks"];
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
                    if(($popularitiesById[$songEntity->getId()] ?? 0) != $song['popularity']) {
                        if (is_object($songStats = $songEntity->getStats())) {
                            $songStats->setPopularity($song['popularity']);
                            $this->em->persist($songStats);
                        }
                        $popularity = new SongPopularity($song['popularity'], new \DateTime(), $songEntity);
                        $this->em->persist($popularity);
                    }
                    $this->em->persist($songEntity);
                }
        }
    }


}