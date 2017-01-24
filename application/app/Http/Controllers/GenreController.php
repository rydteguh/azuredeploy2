<?php namespace App\Http\Controllers;

use DB;
use App;
use Cache;
use Input;
use App\Genre;
use Carbon\Carbon;
use App\Services\Paginator;
use Illuminate\Database\Eloquent\Collection;
use App\Services\Providers\ProviderResolver;

class GenreController extends Controller {

	/**
	 * Paginator Instance.
	 *
	 * @var Paginator
	 */
	private $paginator;

    /**
     * Settings service instance.
     *
     * @var App\Services\Settings
     */
    private $settings;

    /**
     * Data provider resolver instance.
     *
     * @var ProviderResolver
     */
    private $resolver;

	public function __construct(Paginator $paginator, ProviderResolver $resolver)
	{
		$this->paginator = $paginator;
        $this->resolver = $resolver;
        $this->settings = App::make('Settings');
	}

	/**
	 * Get genres and artists related to it.
	 *
	 * @return Collection
	 */
	public function getGenres()
	{
        return $this->resolver->get('genres')->getGenres(Input::get('names'));
	}

	/**
	 * Paginate given genres artists.
	 *
	 * @param string $name
	 * @return array
	 */
	public function paginateArtists($name)
	{
        $genres = $this->settings->get('homepageGenres');

        if ($genres) {
            $genres = array_map(function($genre) { return trim($genre); }, explode(',', $genres));
        }

        if ($genres && in_array($name, $genres)) {
            $genre = Genre::firstOrCreate(['name' => $name]);
        } else {
            $genre = Genre::where('name', $name)->firstOrFail();
        }

        Cache::remember($name.'artists', Carbon::now()->addDays(3), function() use ($genre) {
            return $this->resolver->get('genres')->getGenreArtists($genre);
        });

        $input = Input::all(); $input['itemsPerPage'] = 20;
        $artists = $this->paginator->paginate($genre->artists(), $input, 'artists')->toArray();

        return ['genre' => $genre, 'artists' => $this->sampleImages($artists, 'artists')];
	}
}
