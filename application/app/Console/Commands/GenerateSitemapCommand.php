<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SitemapGenerator;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class GenerateSitemapCommand extends Command {

	private $generator;

    /**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'sitemap:generate';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate a sitemap for the site.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(SitemapGenerator $generator)
	{
		$this->generator = $generator;

        parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$this->generator->generate();
	}
}
