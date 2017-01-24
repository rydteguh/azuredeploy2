<?php namespace App\Http\Controllers;

use App;
use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class Controller extends BaseController {

	use DispatchesCommands, ValidatesRequests;

    public function sampleImages($payload, $type)
    {
        $settings = App::make('App\Services\Settings');

        if ($type === 'artist') {

            if ($settings->get('artist_images_provider') === 'sample') {
                $r = rand(0, 30);
                $payload['image_large'] = url('assets/images/artist-big-no-image.png');
                $payload['image_small'] = url("assets/images/samples/$r.jpg");

                $payload['similar'] = $payload->similar->map(function($artist, $k) {
                    $artist['image_large'] = url("assets/images/samples/$k.jpg");
                    $artist['image_small'] = url("assets/images/samples/$k.jpg");
                    return $artist;
                });
            }

            if ($settings->get('album_images_provider') === 'sample') {
                $payload['albums'] = $payload->albums->map(function($album, $k) {
                    $album['image'] = url("assets/images/samples/$k.jpg");
                    return $album;
                });

                if ($payload->topTracks) {
                    $payload['topTracks'] = $payload->topTracks->map(function($track, $k) {
                        $track['album']['image'] = url("assets/images/samples/$k.jpg");
                        return $track;
                    });
                }
            }

        }

        else if ($type === 'artists' && $settings->get('artist_images_provider') === 'sample') {
            $func = function($artist, $k) {
                $artist['image_small'] = url("assets/images/samples/$k.jpg");
                $artist['image_large'] = url("assets/images/samples/$k.jpg");
                return $artist;
            };

            if (isset($payload['data'])) {
                $payload['data'] = array_map($func, $payload['data'], array_keys($payload['data']));
            } else {
                $payload = array_map($func, $payload, array_keys($payload));
            }
        }

        else if ($type === 'albums' && $settings->get('album_images_provider') === 'sample') {
            if ( ! is_array($payload)) {
                $payload = $payload->toArray();
            }

            $payload = array_map(function($album, $k) {
                $album['image'] = url("assets/images/samples/$k.jpg");
                return $album;
            }, $payload, array_keys($payload));
        }


        return $payload;
    }

}
