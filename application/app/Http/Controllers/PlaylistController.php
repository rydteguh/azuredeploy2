<?php namespace App\Http\Controllers;

use App;
use Auth;
use Input;
use Validator;
use App\User;
use App\Playlist;
use App\Http\Requests;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class PlaylistController extends Controller {

	public function __construct()
	{
		$this->user = Auth::user() ?: new User;
	}

	/**
	 * Follow playlist with currently logged in user.
	 *
	 * @param int $id
	 */
	public function follow($id)
	{
		if ( ! $this->user->playlists()->find($id)) {
			$this->user->playlists()->attach($id, ['owner' => 0]);
			return Playlist::find($id);
		}
	}

	/**
	 * Un-Follow playlist with currently logged in user.
	 *
	 * @param int $id
	 */
	public function unfollow($id)
	{
		if ($this->user->playlists()->find($id)) {
			$this->user->playlists()->detach($id);
		}
	}

	/**
	 * Fetch all playlists user has created or followed.
	 *
	 * @return Collection
	 */
	public function index()
	{
		return $this->user->playlists()->get();
	}

	/**
	 * Return playlist matching given id.
	 *
	 * @param {int|string} $id
	 * @return mixed
	 */
	public function show($id)
	{
		$playlist = Playlist::with('tracks.album.artist')->findOrFail($id);
		$owner    = $playlist->users()->wherePivot('owner', 1)->first();

		//only return playlist if it's public or current user is the owner of it
		if ($owner->id == $this->user->id || $playlist->public) {

			//set owner attribute if needed, otherwise it will error
			// out if playlist is not loaded via user relationship
			if ($owner->id == $this->user->id) {
				$playlist->owner = 1;
			} else {
				$playlist->owner = 0;
			}

			$playlist->createdBy = $owner->getNameOrEmail();
			$playlist->creatorId = $owner->id;

			return $playlist;
		}

		abort(403);
	}

	/**
	 * Create a new playlist.
	 *
	 * @return Playlist
	 */
	public function store(Request $request)
	{
		Validator::extend('uniqueName', function($attribute, $value) {
			return ! $this->user->playlists()->where('name', $value)->first();
		});

		$this->validate($request, [
			'name' => 'required|uniqueName|max:255',
		], ['unique_name' => trans('app.playlistNameExists')]);

        $input = Input::all();
        $input['public'] = App::make('Settings')->get('playlists_public_by_default', 0);


		$playlist = $this->user->playlists()->create($input, ['owner' => 1]);
		$playlist->owner = 1;

		return $playlist;
	}

	/**
	 * Update playlist.
	 *
	 * @param  int  $id
	 * @return Playlist
	 */
	public function update($id, \Illuminate\Http\Request $request)
	{
        $this->validate($request, [
            'description' => 'min:50|max:170',
            'name'        => 'min:3|max:50',
        ]);

        $playlist = Playlist::findOrFail($id);
		$owner    = $playlist->users()->wherePivot('owner', 1)->first();

		if ($owner->id == $this->user->id || $this->user->is_admin) {
			$playlist->fill(Input::all())->save();
			$playlist->owner = 1;
		} else {
			abort(403);
		}

		return $playlist;
	}

    /**
     * Upload new image for specified playlist and add link to it on artist model.
     *
     * @param $id
     */
    public function uploadImage($id, \Illuminate\Http\Request $request)
    {
        $playlist = Playlist::findOrFail($id);
        $owner    = $playlist->users()->wherePivot('owner', 1)->first();

        if ($owner->id !== $this->user->id && ! $this->user->is_admin) {
            abort(403);
        }

        $this->validate($request, [
            'file' => 'required|mimes:jpeg,png,jpg',
        ]);

        $file     = Input::file('file');
        $playlist = Playlist::findOrFail($id);
        $end      = 'assets/images/playlists/'.md5(Str::slug($playlist->name).'_'.$playlist->id).'.'.$file->getClientOriginalExtension();

        if (file_put_contents(base_path('../'.$end), file_get_contents($file))) {
            $playlist->image = url($end);
            $playlist->save();
            $playlist->owner = 1;
        }

        return $playlist;
    }

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		$playlist = Playlist::findOrFail($id);
		$owner    = $playlist->users()->wherePivot('owner', 1)->first();

		if ($owner->id == $this->user->id || $this->user->is_admin) {

            if ($playlist->image) {
                unlink(str_replace(url(), base_path(), $playlist->image));
            }

			$playlist->tracks()->detach();
			$playlist->delete();
		}
	}

}
