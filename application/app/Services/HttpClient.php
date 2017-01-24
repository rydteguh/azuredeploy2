<?php namespace App\Services;

use App;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ParseException;

class HttpClient {

	private $client;
	private $showFeedback;
	private $options;

	public function __construct($params = [], $showFeedback = false)
	{
		$params['timeout'] = 8.0;
		$params['exceptions'] = false;

		$this->client = new Client($params);

		$verifyCert = App::make('App\Services\Settings')->get('enable_cert_verification', true);
		$this->client->setDefaultOption('verify', $verifyCert);

		$this->showFeedback = $showFeedback;
	}

	public function get($url, $options = [])
	{
        $r = $this->client->get($url, array_merge($options, ['exceptions' => false]));

		if ($r->getStatusCode() === 429 && $r->hasHeader('Retry-After')) {
			$seconds = $r->getHeader('Retry-After') ? $r->getHeader('Retry-After') : 5;
			$this->feedback('Hit rate limit, sleeping for '.$seconds.' sec.');
			sleep($seconds);
			$this->feedback('Retrying call, to: '.$url);
			$r = $this->get($url);
		}

        try {
            $json = is_array($r) ? $r : $r->json();
        } catch (ParseException $e) {
            $json = '';
        }

		return $json;
	}

	public function post($url, $options)
	{
		$r = $this->client->post($url, array_merge($options, ['exceptions' => false]));

		if ($r->getStatusCode() === 429 && $r->hasHeader('Retry-After')) {
			$seconds = $r->getHeader('Retry-After') ? $r->getHeader('Retry-After') : 5;
			$this->feedback('Hit rate limit, sleeping for '.$seconds.' sec.');
			sleep($seconds);
			$this->feedback('Retrying call, to: '.$url);
			$r = $this->get($url);
		}

		return is_array($r) ? $r : $r->json();
	}

	public function feedback($msg)
	{
		if ( ! $this->showFeedback) return;

		$msg = $msg."<br>";
		echo $msg;
		flush();

		$levels = ob_get_level();
		for ($i=0; $i<$levels; $i++)
			ob_end_flush();
	}
}