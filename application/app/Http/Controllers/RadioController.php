<?php namespace App\Http\Controllers;

use App;
use Cache;
use Input;
use Carbon\Carbon;
use App\Services\Providers\ProviderResolver;

class RadioController extends Controller {

    /**
     * External artist provider service.
     *
     * @var ProviderResolver
     */
    private $resolver;

    /**
     * Create new RadioController instance.
     */
    public function __construct(ProviderResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Start artist radio on Spotify based on supplied artist name.
     */
    public function artistRadio()
    {
        $name = str_replace('+', ' ', Input::get('name'));

        return Cache::remember("radio.$name", Carbon::now()->addDays(2), function() use($name) {
            return $this->resolver->get('radio')->getSuggestions($name);
        });
    }
}
