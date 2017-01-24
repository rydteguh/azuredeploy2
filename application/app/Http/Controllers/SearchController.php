<?php namespace App\Http\Controllers;

use App;
use Cache;
use Input;
use Carbon\Carbon;
use App\Services\Search\UserSearch;
use App\Services\Search\SearchSaver;
use App\Services\Search\YoutubeSearch;
use App\Services\Search\PlaylistSearch;
use App\Services\Providers\ProviderResolver;

class SearchController extends Controller {

    /**
     * Data provider resolver instance.
     *
     * @var ProviderResolver
     */
    private $resolver;

    /**
     * SearchSaver instance.
     *
     * @var SearchSaver
     */
    private $saver;

    /**
     * UserSearch instance.
     *
     * @var UserSearch
     */
    private $userSearch;

    /**
     * PlaylistSearch instance.
     *
     * @var PlaylistSearch
     */
    private $playlistSearch;

    /**
     * Create new SearchController instance.
     *
     * @param YoutubeSearch $audio
     */
	public function __construct(SearchSaver $saver, PlaylistSearch $playlistSearch, UserSearch $userSearch, ProviderResolver $resolver)
    {
        $this->resolver       = $resolver;
        $this->playlistSearch = $playlistSearch;
        $this->userSearch     = $userSearch;
        $this->saver          = $saver;
	}

    /**
     * Use active search provider to search for
     * songs, albums and artists matching given query.
     *
     * @param string $q
     * @return array
     */
    public function search($q)
    {
        $limit = Input::get('limit', 3);

        $results = Cache::remember('search.'.$q.$limit, Carbon::now()->addDays(3), function() use($q, $limit) {
            $results = $this->resolver->get('search')->search($q, $limit);

            if ($this->resolver->getProviderNameFor('search') !== 'Local') {
                $results = $this->saver->save($results);
            }

            $results['playlists'] = $this->playlistSearch->search($q, $limit);
            $results['users']     = $this->userSearch->search($q, $limit);

            return $results;
        });

        return $this->filterOutBlockedArtists($results);
    }

    /**
     * Search for audio matching given query.
     *
     * @param string $artist
     * @param string $artist
     *
     * @return array
     */
    public function searchAudio($artist, $track)
    {
        return $this->resolver->get('audio_search')->search($artist, $track, 1);
    }

    /**
     * Remove artists that were blocked by admin from search results.
     *
     * @param array $results
     * @return array
     */
    private function filterOutBlockedArtists($results)
    {
        if (($artists = App::make('Settings')->get('blockedArtists'))) {
            $artists = explode("\n", $artists);

            foreach($results['artists'] as $k => $artist) {
                if ($this->shouldBeBlocked($artist->name, $artists)) {
                    unset($results['artists'][$k]);
                }
            }

            foreach($results['albums'] as $k => $album) {
                if (isset($album['artist'])) {
                    if ($this->shouldBeBlocked($album['artist']['name'], $artists)) {
                        unset($results['albums'][$k]);
                    }
                }
            }

            foreach($results['tracks'] as $k => $track) {
                if (isset($track['album']['artist'])) {
                    if ($this->shouldBeBlocked($track['album']['artist']['name'], $artists)) {
                        unset($results['tracks'][$k]);
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Check if given artist should be blocked.
     *
     * @param string $name
     * @param array $toBlock
     * @return boolean
     */
    private function shouldBeBlocked($name, $toBlock)
    {
        foreach ($toBlock as $blockedName) {
            $pattern = '/' . str_replace('*', '.*?', strtolower($blockedName)) . '/i';
            if (preg_match($pattern, $name)) return true;
        }
    }

}
