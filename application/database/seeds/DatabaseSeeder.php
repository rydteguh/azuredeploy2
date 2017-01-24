<?php

use App\Genre;
use App\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

        $genres = 'rock, rap, pop, punk, country, trance,death metal,dance,alternative, hard rock, punk rock,post-rock,electronica,
            indie rock,thrash metal,hardcore,funk,jazz,focus,experimental,british,party,folk,indie,Progressive metal,industrial,
            Classical,acoustic,psychedelic,classic rock,new wave,Hip-Hop,alternative rock,Progressive rock,electronic,soul,
            ambient,latino,chillout,heavy metal,instrumental,metalcore,blues,black metal,piano,metal, rnb, 90s, sleep, 80s';

        Setting::insert([
            ['name' => 'homepage', 'value' => 'default'],
            ['name' => 'enableRegistration', 'value' => 1],
            ['name' => 'siteName', 'value' => 'BeMusic'],
            ['name' => 'enablePushState', 'value' => 0],
            ['name' => 'dateLocale', 'value' => 'en'],
            ['name' => 'pushStateRootUrl', 'value' => '/'],
            ['name' => 'primaryHomeSection', 'value' => 'popular-genres'],
            ['name' => 'artist_update_interval', 'value' => 14],
            ['name' => 'latest_albums_update_interval', 'value' => 3],
            ['name' => 'homepage_update_interval', 'value' => 3],
            ['name' => 'force_login', 'value' => 0],
            ['name' => 'enable_https', 'value' => 0],
            ['name' => 'latest_albums_strict', 'value' => 1],
            ['name' => 'youtube_region_code', 'value' => 'US'],
            ['name' => 'show_youtube_player', 'value' => 1],
            ['name' => 'hide_lyrics_button', 'value' => 0],
            ['name' => 'hide_video_button', 'value' => 0],
            ['name' => 'hide_queue', 'value' => 0],
            ['name' => 'youtube_api_key', 'value' => 'AIzaSyB9pD8M_ejk9dJQHUqrZEAn9xonb0If1ks'],
            ['name' => 'default_player_volume', 'value' => 30],
            ['name' => 'show_fullscreen_button', 'value' => 0],
            ['name' => 'require_email_confirmation', 'value' => 0],
            ['name' => 'save_artist_bio', 'value' => 1],
            ['name' => 'playlists_public_by_default', 'value' => 0],
            ['name' => 'wikipedia_language', 'value' => 'en'],
            ['name' => 'homepageGenres', 'value' => $genres],

            ['name' => 'artist_provider', 'value' => 'Local'],
            ['name' => 'album_provider', 'value' => 'Local'],
            ['name' => 'radio_provider', 'value' => 'Local'],
            ['name' => 'genres_provider', 'value' => 'Local'],
            ['name' => 'album_images_provider', 'value' => 'real'],
            ['name' => 'artist_images_provider', 'value' => 'real'],
            ['name' => 'new_releases_provider', 'value' => 'Local'],
            ['name' => 'top_tracks_provider', 'value' => 'Local'],
            ['name' => 'top_albums_provider', 'value' => 'Local'],
            ['name' => 'search_provider', 'value' => 'Local'],
            ['name' => 'audio_search_provider', 'value' => 'Youtube'],
            ['name' => 'artist_bio_provider', 'value' => 'wikipedia'],
        ]);
	}
}
