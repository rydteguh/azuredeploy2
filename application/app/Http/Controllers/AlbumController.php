<?php namespace App\Http\Controllers;

use App;
use Illuminate\Database\Eloquent\Collection;
use Input;
use Cache;
use Exception;
use App\Album;
use App\Artist;
use Carbon\Carbon;
use App\Services\Paginator;
use App\Services\CustomUploads;
use App\Services\ArtistSaver;
use App\Services\Providers\ProviderResolver;

class AlbumController extends Controller {

	/**
	 * External artist provider service.
	 *
	 * @var ProviderResolver
	 */
	private $resolver;

	/**
	 * Artist db saver instance.
	 *
	 * @var ArtistSaver
	 */
	private $saver;

	/**
	 * Paginator Instance.
	 *
	 * @var Paginator
	 */
	private $paginator;

    /**
     * CustomUplods Instance.
     *
     * @var CustomUploads
     */
    private $customUploads;

    /**
     * Settings service instance.
     *
     * @var App\Services\Settings
     */
    private $settings;

	/**
	 * Create new AlbumController instance.
	 */
	public function __construct(ProviderResolver $resolver, ArtistSaver $saver, Paginator $paginator, Album $model, CustomUploads $customUploads)
	{
        $this->middleware('admin', ['only' => ['destroy', 'index', 'update', 'store']]);

        if (IS_DEMO) {
            $this->middleware('disableOnDemoSite', ['only' => ['destroy', 'update', 'store', 'uploadImage']]);
        }

		$this->resolver      = $resolver;
		$this->saver         = $saver;
		$this->paginator     = $paginator;
		$this->model         = $model;
        $this->customUploads = $customUploads;
		$this->settings      = App::make('Settings');
	}

	/**
	 * Paginate all albums.
	 *
	 * @return Collection
	 */
	public function index()
	{
		return $this->paginator->paginate($this->model->with('tracks', 'artist'), Input::all(), 'albums');
	}

	/**
	 * Update album.
	 *
	 * @param  int  $id
	 * @return Album
*/
	public function update($id)
	{
		$album = $this->model->findOrFail($id);

		$album->fill(Input::except(['artist', 'tracks']))->save();

		return $album;
	}

    /**
     * Create new album.
     *
     * @param  int  $id
     * @return Album
     */
    public function store(\Illuminate\Http\Request $request)
    {
        $this->validate($request, [
            'name'               => 'required|min:1|max:255',
            'artist.name'        => 'required|min:1|max:255',
        ]);

        $artist = Artist::where('name', Input::get('artist.name'))->first();

        if ( ! $artist) {
            return response(trans('app.newAlbumNoArtist'), 403);
        }

        $album = Album::where('artist_id', $artist->id)->where('name', Input::get('name'))->first();

        if ($album) {
            return response(trans('app.newAlbumNameExists'), 403);
        }

        $input = Input::except(['artist', 'tracks']);
        $input['artist_id'] = $artist->id;

        return Album::create($input);
    }

	/**
	 * Get most popular albums.
	 *
	 * @return mixed
	 */
	public function getTopAlbums()
	{
		return $this->sampleImages(Cache::remember('albums.top', Carbon::now()->addDays($this->settings->get('homepage_update_interval')), function() {
			return $this->resolver->get('top_albums')->getTopAlbums();
		}), 'albums');
	}

	/**
	 * Return latest album releases.
	 *
	 * @return mixed
	 */
	public function getLatestAlbums()
	{
        return $this->sampleImages(Cache::remember('albums.latest', Carbon::now()->addDays($this->settings->get('homepage_update_interval')), function() {
            return $this->resolver->get('new_releases')->getNewReleases();
		}), 'albums');
	}

	/**
	 * Return artist to who given album belongs
	 * along with all other albums and tracks.
	 *
	 * @return Artist
	 */
	public function getAlbum()
	{
        $artistName = $name = preg_replace('!\s+!', ' ', Input::get('artistName'));
		$albumName  = $name = preg_replace('!\s+!', ' ', Input::get('albumName'));

        //fetch album by id
        if (Input::has('id')) {
            $album = Album::with('artist')->findOrFail(Input::get('id'));
            $this->updateAlbum($album, $album->artist->name, $album->name);
            $album = Album::with('tracks', 'artist')->findOrFail(Input::get('id'));
        }

		//fetch album that isn't attached to any one particular artist
		else if ( ! $artistName || $artistName === 'Various Artists') {
			$album = Album::where('name', $albumName)->where('artist_id', 0)->first();

            if ( ! $album) abort(404);

			$this->updateAlbum($album, $artistName, $albumName);

			$album = Album::where('name', $albumName)->where('artist_id', 0)->firstOrFail();

		//fetch specific artists album
		} else {
			$album = Album::where('name', $albumName)->whereHas('artist', function($q) use($artistName) {
				$q->where('name', $artistName);
			})->first();

			$this->updateAlbum($album, $artistName, $albumName);

			$album = Album::where('name', $albumName)->whereHas('artist', function($q) use($artistName) {
				$q->where('name', $artistName);
			})->firstOrFail();
		}

		$album->load('artist', 'tracks');

		return $album;
	}

    /**
     * Update or fetch album from third party site if needed.
     *
     * @param null|Album   $album
     * @param null|string  $artistName
     * @param string       $albumName
     */
	private function updateAlbum($album, $artistName, $albumName)
	{
		if ($this->settings->get('album_provider', 'local') === 'local') return;

		if ( ! $album || ! $album->fully_scraped || ! $album->tracks || $album->tracks->isEmpty()) {

			$data = $this->resolver->get('album')->getAlbum($artistName, $albumName);

            if ( ! $data && $album && ! $album->tracks->isEmpty()) return;

            if ( ! isset($data['album']['tracks']) || empty($data['album']['tracks'])) abort(404);

            //if we're fetching specific artists album and that artist is not
            //in our database yet, we will need to fetch the artist first
            if ($artistName && $data['artist'] && (! $album || ! $album->artist)) {
                $artist = $this->resolver->get('album')->getArtistOrFail($artistName);
                $this->saver->save($artist);

                //since fetching artist will get all his
                //albums automatically we can just return
                return;
            }

            try {
                $this->saver->saveAlbums(['albums' => [$data['album']]], $album ? $album->artist : null, $album ? $album->id : null);
            } catch (Exception $e) {
                //
            }

			if ( ! $album) {
				$album = Album::where('name', $data['album']['name'])->where('release_date', $data['album']['release_date'])->firstOrFail();
			}

			$this->saver->saveTracks([$data['album']], null, $album);
		}
	}

    /**
     * Upload new image for specified album and add link to it on album model.
     *
     * @param $id
     */
    public function uploadImage($id, \Illuminate\Http\Request $request)
    {
        $this->validate($request, [
            'file' => 'required|mimes:jpeg,png,jpg',
        ]);

        return $this->customUploads->upload(Input::file('file'), Album::with('Artist')->findOrFail($id));
    }

	/**
	 * Remove albums from database.
	 *
	 * @return mixed
	 */
	public function destroy()
	{
		if ( ! Input::has('items')) return;

		foreach (Input::get('items') as $album) {
            $album = Album::find($album['id']);

            if ($album) {
                $this->customUploads->deleteCustomAlbumImage($album);
                $album->tracks()->delete();
                $album->delete();
            }
		}

		return response(trans('app.deleted', ['number' => count(Input::get('items'))]));
	}
}
