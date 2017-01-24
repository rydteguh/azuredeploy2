<?php namespace App\Http\Controllers;

use Storage;
use App\Services\SitemapGenerator;

class SitemapController extends Controller {

    private $generator;

    /**
     * Create new SitemapController instance.
     */
    public function __construct(SitemapGenerator $generator)
    {
        $this->middleware('admin', ['except' => ['showIndex']]);

        if (IS_DEMO) {
            $this->middleware('disableOnDemoSite', ['except' => ['showIndex']]);
        }

        $this->generator = $generator;
    }

    /**
     * Generate a sitemap of all urls of the site.
     *
     * @return string
     */
    public function generate()
    {
        $this->generator->generate();

        return trans('app.sitemapGenerated');
    }

    /**
     * Show sitemap index file if it exists.
     *
     * @return mixed
     */
    public function showIndex()
    {
        if (Storage::exists('sitemaps/sitemap-index.xml')) {
            return response(Storage::get('sitemaps/sitemap-index.xml'), 200)->header('Content-Type', 'text/xml');
        }
    }
}
