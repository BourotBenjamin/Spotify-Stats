<?php
/**
 * Created by PhpStorm.
 * User: Philoupe
 * Date: 01/08/2017
 * Time: 20:52
 */

namespace AppBundle\Services;


use AppBundle\Entity\Album;
use AppBundle\Entity\City;
use AppBundle\Entity\Concert;
use AppBundle\Entity\Country;
use AppBundle\Entity\Genre;
use AppBundle\Entity\SongPopularity;
use AppBundle\Entity\SongStats;
use AppBundle\Entity\User;
use AppBundle\Entity\Venue;
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
            $songFeatures = $this->spotifyApi->getSpotifyContent("https://api.spotify.com/v1/audio-features?ids=" . implode(",", $songsIdsSubArray), $user, "audio_features");
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
            $artistsInfos = $this->spotifyApi->getSpotifyContent("https://api.spotify.com/v1/artists?ids=" . implode(",", $artistsIdsSubArray), $user, "artists");
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
                if(isset($artistInfos['images'][0]))
                    $artist->setPictureUrl($artistInfos['images'][0]['url']);
                $this->em->persist($artist);
            }
        }
    }

    public function updateArtistsConcerts()
    {
        $concertsByIds = $artistsByName = $venuesByName = $countriesByName = $citiesByName = array();
        $artists = $this->em->getRepository('AppBundle:Artist')->findAll();
        foreach ($artists as &$artist)
            $artistsByName[$artist->getName()] = $artist;
        $countries = $this->em->getRepository('AppBundle:Country')->findAll();
        foreach ($countries as &$country)
            $countriesByName[$country->getName()] = $country;
        $cities = $this->em->getRepository('AppBundle:City')->findAll();
        foreach ($cities as &$city)
            $citiesByName[$city->getName().";".$city->getCountry()->getName()] = $city;
        $venues = $this->em->getRepository('AppBundle:Venue')->findAll();
        foreach ($venues as &$venue)
            $venuesByName[$venue->getName().";".$venue->getCity()->getName().";".$venue->getCity()->getCountry()->getName()] = $venue;
        $concerts = $this->em->getRepository('AppBundle:Concert')->findAll();
        foreach ($concerts as &$concert)
            $concertsByIds[$concert->getId()] = 1;
        foreach ($artists as &$artist) {
            $concerts = $this->spotifyApi->getExternalContent( "https://rest.bandsintown.com/artists/"
                .str_replace('+', '%20', urlencode($artist->getName()))
                ."/events?app_id=philoupe%2F1.0%20%28%2Bhttp%3A%2F%2Fphiloupe.ddns.net%2F%29");
            if(is_array($concerts))
                foreach ($concerts as &$concert) {
                    if (isset($concert['id'])) {
                        if (!isset($concertsByIds[$concert['id']])) {
                            $country = $concert['venue']['country'];
                            $city = $country . ";" . $concert['venue']['city'];
                            $venue = $city . ";" . $concert['venue']['name'];
                            if (!isset($venuesByName[$venue])) {
                                if (!isset($citiesByName[$city])) {
                                    if (!isset($countriesByName[$country])) {
                                        $countriesByName[$country] = new Country($concert['venue']['country']);
                                        $this->em->persist($countriesByName[$country]);
                                    }
                                    $citiesByName[$city] = new City($concert['venue']['city'], $countriesByName[$country]);
                                    $this->em->persist($citiesByName[$city]);
                                }
                                $venuesByName[$venue] = new Venue($concert['venue']['name'], $citiesByName[$city]);
                                $this->em->persist($venuesByName[$venue]);
                            }
                            $concertEntity = new Concert(
                                $concert['id'],
                                new \DateTime($concert['datetime']),
                                $concert['url'],
                                $venuesByName[$venue]
                            );
                            foreach ($concert["lineup"] as $artistName)
                                if (isset($artistsByName[$artistName]))
                                    $concertEntity->addArtist($artistsByName[$artistName]);
                            $this->em->persist($concertEntity);
                        }
                    } elseif(isset($concert[0]) && is_string($concert[0]))
                        echo($concert[0]." : ".$artist->getName()."\n");
                }
        }
        $this->em->flush();
    }

    public function updateArtistsAlbums(User &$user)
    {
        $artists = $this->em->getRepository('AppBundle:Artist')->findAll();
        $albums = $this->em->getRepository('AppBundle:Album')->getAllIds();
        $albumsByIds = [];
        foreach ($albums as &$album) {
            if(!empty($album['id']))
                $albumsByIds[$album['id']] = 1;
        }
        foreach ($artists as &$artist) {
            if(!empty($artist->getArtistId())){
                $offset = 0;
                do {
                    $albums = $this->spotifyApi->getSpotifyContent("https://api.spotify.com/v1/artists/" . $artist->getArtistId() . "/albums?limit=50&offset=".$offset, $user);
                    foreach ($albums['items'] as $album) {
                        if(isset($albumsByIds[$album['id']])) {
                            $albumsByIds[$album['id']] = new Album(
                                $album['id'],
                                $album['name'],
                                $album['images'][0]['url'],
                                $album['type']);
                            $this->em->persist($albumsByIds[$album['id']]);
                        }
                    }
                    $offset += 50;
                } while($albums["total"] > $offset);
            }
        }
        $this->em->flush();
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
            $songInfos = $this->spotifyApi->getSpotifyContent("https://api.spotify.com/v1/tracks?ids=" . implode(",", $songsIdsSubArray), $user, "tracks");
            foreach ($songInfos as $key => $song)
                if(!empty($song))
                {
                    $songEntity = $songsByIds[$song["id"]];
                    if(!is_object($songEntity->getAlbum())) {
                        if (!isset($albumsByIds[$song["album"]['id']])) {
                            $albumsByIds[$song["album"]['id']] = new Album(
                                $song["album"]['id'],
                                $song["album"]['name'],
                                $song["album"]['images'][0]['url'],
                                $song["album"]['type']);
                            $this->em->persist($albumsByIds[$song["album"]['id']]);
                        } else
                            $albumsByIds[$song["album"]['id']]->setType($song["album"]['type']);
                        $songEntity->setAlbum($albumsByIds[$song["album"]['id']]);
                        $this->em->persist($albumsByIds[$song["album"]['id']]);
                    }
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