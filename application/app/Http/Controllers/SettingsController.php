<?php namespace App\Http\Controllers;

use App;
use Input;
use Image;
use Storage;
use Artisan;
use Illuminate\Http\Request;

class SettingsController extends Controller {

    /**
     * Settings service instance.
     *
     * @var App\Services\Settings;
     */
    private $settings;

    /**
     * Laravel filesystem service instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    private $fs;

    public function __construct()
    {
        $this->middleware('loggedIn');
        $this->middleware('admin');

        $this->settings = App::make('Settings');
        $this->fs = App::make('Illuminate\Filesystem\Filesystem');
    }

    /**
     * Get all available settings as well as other
     * needed info to populate admin settings page.
     *
     * @return array
     */
    public function getAllSettings()
    {
        $transLocales = [];

        foreach($this->fs->directories(base_path('resources/lang')) as $path) {
            $name = basename($path);
            if ($name !== 'original') $transLocales[] = $name;
        }

        $paths = Storage::allFiles('application/app/Services/Providers');

        $providers = [];

        foreach($paths as $path) {
            if (str_contains($path, ['Resolver', '_',])) continue;
            $base = basename($path, '.php');
            list($providerName, $dataType) = preg_split('/(?=[A-Z])/', $base, 2, PREG_SPLIT_NO_EMPTY);
            $snakeCase = snake_case($dataType).'_provider';

            if ( ! isset($providers[$snakeCase])) {
                $providers[$snakeCase] = [
                    'name' => trim(preg_replace('/(?<!\ )[A-Z]/', ' $0', $dataType)).' Provider',
                    'values' => [],
                ];
            }

            $providers[$snakeCase]['values'][] = $providerName;
        }

        $providers['artist_provider']['values'][] = 'Local';
        $providers['album_provider']['values'][] = 'Local';

        return [
            'settings' => $this->settings->getAll(),
            'info'     => [
                'transLocales' => $transLocales,
                'providers'    => $providers
            ]
        ];
    }

    /**
     * Update Settings in the database with given ones.
     *
     * @return int
     */
    public function updateSettings()
    {
        $this->settings->setAll(Input::all());

        return response(trans('app.settingsUpdated'));
    }

    public function clearCache()
    {
        Artisan::call('cache:clear');

        return trans('app.clearedCache');
    }

    /**
     * Upload custom logo.
     *
     * @param Request $request
     * @return string
     */
    public function uploadLogo(Request $request)
    {
        $this->validate($request, [
            'file' => 'mimes:jpeg,png,jpg'
        ]);

        $path = '/assets/images/custom_logo_light.png';

        //if we get no file passed, reset logo to default instead
        if ( ! Input::hasFile('file')) {
            $this->settings->remove('logo_url');
            unlink(base_path().'/..'.$path);

            return url('assets/images/logo_light.png');
        }

        Image::make(Input::file('file'))->encode('png')->save(base_path().'/..'.$path);

        $this->settings->set('logo_url', 'custom_logo_light.png');

        return url().$path;
    }
}
