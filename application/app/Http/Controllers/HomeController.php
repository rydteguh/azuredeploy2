<?php namespace App\Http\Controllers;

use App;
use Auth;
use Lang;
use Illuminate\View\View;
use App\Services\DeviceDetector;

class HomeController extends Controller {

	/**
	 * Settings service instance.
	 * 
	 * @var App\Services\Settings;
	 */
    private $settings;

    /**
	 * DeviceDetector service instance.
	 *
	 * @var App\Services\DeviceDetector;
	 */
    private $deviceDetector;

    public function __construct(DeviceDetector $deviceDetector)
    {
    	$this->settings = App::make('Settings');
    	$this->deviceDetector = $deviceDetector;
    }

    /**
	 * Show the application home screen to the user.
	 *
	 * @return View
	 */
	public function index()
	{
        $pushStateRootUrl = '/';

        if ($this->settings->get('enablePushState') && substr_count(url(), '/') > 2) {
            $pushStateRootUrl .= substr(url(), strrpos(url(), '/') + 1) . '/';
        }
        	
        return view('main')
			->with('user', Auth::user())
			->with('baseUrl', $this->settings->get('enable_https', false) ? secure_url('') : url())
            ->with('pushStateRootUrl', $pushStateRootUrl)
			->with('translations', json_encode(Lang::get('app')))
			->with('settings', $this->settings)
			->with('isMobile', $this->deviceDetector->isMobile())
			->with('isDemo', IS_DEMO)
			->with('version', VERSION);
	}
}
