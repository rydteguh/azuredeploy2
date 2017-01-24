<?php namespace App\Http\Middleware;

use App;
use App\Genre;
use App\Artist;
use App\Album;
use App\Track;
use App\Playlist;
use Closure;
use Illuminate\Http\Request;

class PrerenderIfCrawler  {

    private $settings;

    private $userAgents = [
        'baiduspider',
        'facebookexternalhit',
        'twitterbot',
        'rogerbot',
        'linkedinbot',
        'embedly',
        'quora link preview',
        'showyoubot',
        'outbrain',
        'pinterest',
        'developers.google.com/+/web/snippet',
        'googlebot',
        'bingbot',
        'Slurp'
    ];

    public function __construct()
    {
        $this->settings = App::make('Settings');
    }

    /**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
	    if ($this->shouldPrerender($request)) {
            $model = $this->getShareableModel($request);

            if ( ! $model || ! $model['model']) abort(404);

            if (in_array($model['type'], ['newReleases', 'top50', 'popularAlbums', 'popularGenres'])) {
                return view('view-for-crawlers-collection')->with('collection', $model['model'])->with('type', $model['type'])->with('settings', App::make('Settings'));
            } else {
                return view('view-for-crawlers')->with('model', $model['model'])->with('type', $model['type'])->with('settings', App::make('Settings'));
            }
        }

        return $next($request);
	}

    /**
     * Fetch shareable model from db based on route params.
     *
     * @param Request $request
     * @return array
     */
    private function getShareableModel(Request $request)
    {
        $parts = $request->segments();

        if ($parts[0] === 'artist') {

            //find by artist id
            if (isset($parts[2]) && $parts[2]) {
                $model = Artist::findOrFail($parts[1]);

            //find by artist name
            } else {
                $name  = urldecode(str_replace('+', ' ', $parts[1]));
                $model = Artist::where('name', $name)->firstOrFail();
            }

            return ['type' => 'artist', 'model' => $model];
        } else if ($parts[0] === 'album') {

            //find by album id
            if (isset($parts[3]) && $parts[3]) {
                $album = Album::findOrFail($parts[1]);

            //find by album name and artist name
            } else if (isset($parts[2])) {
                $albumName  = urldecode(urldecode(str_replace('+', ' ', $parts[2])));
                $artistName = urldecode(urldecode(str_replace('+', ' ', $parts[1])));

                $album = Album::where('name', $albumName)->whereHas('artist', function($q) use($artistName) {
                    $q->where('name', $artistName);
                })->first();

            //find by album name
            } else {
                $albumName  = urldecode(urldecode(str_replace('+', ' ', $parts[1])));
                $album = Album::where('name', $albumName)->where('artist_id', 0)->first();
            }

            return ['type' => 'album', 'model' => $album];
        } else if ($parts[0] === 'playlist') {
            return ['type' => 'playlist', 'model' => Playlist::findOrFail($parts[1])];
        } else if ($parts[0] === 'track') {
            return ['type' => 'track', 'model' => Track::findOrFail($parts[1])];
        } else if ($parts[0] === 'genre') {
            return ['type' => 'genre', 'model' => Genre::with(['artists' => function($q) {
                return $q->limit(20);
            }])->where('name', urldecode($parts[1]))->first()];
        } else if ($parts[0] === 'new-releases') {
            return ['type' => 'newReleases', 'model' => app('App\Http\Controllers\AlbumController')->getLatestAlbums()];
        } else if ($parts[0] === 'popular-genres') {
            return ['type' => 'popularGenres', 'model' => app('App\Http\Controllers\GenreController')->getGenres($this->settings->get('homepageGenres', 'default'))];
        } else if ($parts[0] === 'top-songs') {
            return ['type' => 'top50', 'model' => app('App\Http\Controllers\TrackController')->getTopSongs()];
        } else if ($parts[0] === 'popular-albums' || $parts[0] === 'top-albums') {
            return ['type' => 'popularAlbums', 'model' => app('App\Http\Controllers\AlbumController')->getTopAlbums()];
        }
    }

    /**
     * Returns whether the request must be prerendered server side for crawler.
     *
     * @param Request $request
     * @return bool
     */
    private function shouldPrerender(Request $request)
    {
        $userAgent   = strtolower($request->server->get('HTTP_USER_AGENT'));
        $bufferAgent = $request->server->get('X-BUFFERBOT');

        $shouldPrerender = false;

        if (!$userAgent) return false;

        if (!$request->isMethod('GET')) return false;

        //google bot
        if ($request->query->has('_escaped_fragment_')) $shouldPrerender = true;

        //other crawlers
        foreach ($this->userAgents as $crawlerUserAgent) {
            if (str_contains($userAgent, strtolower($crawlerUserAgent))) {
                $shouldPrerender = true;
            }
        }

        if ($bufferAgent) $shouldPrerender = true;

        if (!$shouldPrerender) return false;

        return true;
    }

}
