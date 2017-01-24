<!DOCTYPE html>
<html>

    <head>
        <meta charset="UTF-8">

        <title>{{ $model->name }} - {{ $settings->get('siteName') }}</title>

        <meta name="google" content="notranslate">
        <link rel="canonical" href="{{ strtolower(str_replace('%20', '+', Request::url())) }}" />

        <meta itemprop="name" content="{{ $model->name }}">

        <!-- Twitter Card data -->
        <meta name="twitter:card" content="{{ $type  }}">
        <meta name="twitter:title" content="{{ $model->name }} - {{ $settings->get('siteName') }}">
        <meta name="twitter:url" content="{{ Request::url() }}">

        <!-- Open Graph data -->
        <meta property="og:title" content="{{ $model->name }} - {{ $settings->get('siteName') }}" />
        <meta property="og:url" content="{{ Request::url() }}" />
        <meta property="og:site_name" content="{{ $settings->get('siteName') }}" />

        <?php
            if ($type === 'artist') {
                $description = trans('app.listenTo') .' '. $model->name .' '. trans('app.on') .' '. $settings->get('siteName');

                if ($model->bio) {
                    try {
                        $description = json_decode($model->bio)->bio;
                    } catch(Exception $e) {
                        //
                    }
                }
            } else if ($type === 'playlist') {
                if ($model->description) {
                    $description = $model->description;
                } else {
                    $description = $model->name .', playlist by ' . $model->users()->wherePivot('owner', 1)->first()->getNameOrEmail() .' '. trans('app.on') .' '. $settings->get('siteName');
                }

                if ($model->image) {
                	$image = $model->image;
                } else if ( ! $model->tracks->isEmpty()) {
                	$image = $model->tracks->first()->album->image;
                } else {
                	$image = url().'/assets/images/album-no-image.png';
                }
            } 
        ?>

        @if ($type === 'artist')
            <meta property="og:description" content="{{ str_limit($description, 160) }}" />
            <meta property="og:type" content="music.musician" />
            <meta property="og:image" content="{{ $model->image_large  }}">
            <meta itemprop="image" content="{{ $model->image_large }}">
            <meta itemprop="description" content="{{ str_limit($description, 160) }}">
            <meta property="description" content="{{ str_limit($description, 160) }}">
            <meta property="og:image:width" content="1000">
            <meta property="og:image:height" content="667">
            <meta name="twitter:description" content="{{ $description }}" />
            <meta name="twitter:image" content="{{ $model->image_large  }}">
        @elseif ($type === 'album')
            <meta property="og:description" content="{{ $model->name }}, album by {{ $model->artist_id ? $model->artist->name : trans('app.variousArtists') }} {{ trans('app.on') }} {{ $settings->get('siteName') }}" />
            <meta property="og:image" content="{{ $model->image  }}">
            <meta name="twitter:image" content="{{ $model->image  }}">
            <meta name="twitter:description" content="{{ $model->name }}, album by {{ $model->artist_id ? $model->artist->name : trans('app.variousArtists') }} {{ trans('app.on') }} {{ $settings->get('siteName') }}" />
            <meta itemprop="description" content="{{ $model->name }}, album by {{ $model->artist_id ? $model->artist->name : trans('app.variousArtists') }} {{ trans('app.on') }} {{ $settings->get('siteName') }}" />
            <meta itemprop="image" content="{{ $model->image }}">
            <meta property="og:image:width" content="300">
            <meta property="og:image:height" content="300">
        @elseif ($type === 'genre')
            <meta property="og:description" content="Listen to most popular {{ $model->name }} artists {{ trans('app.on') }} {{ $settings->get('siteName') }}" />
            <meta property="og:image" content="{{ App::make('Settings')->get('enable_https') ? secure_url('assets/images/genres/'.$model->name.'.jpg') : url('assets/images/genres/'.$model->name.'.jpg') }}">
            <meta name="twitter:image" content="{{ App::make('Settings')->get('enable_https') ? secure_url('assets/images/genres/'.$model->name.'.jpg') : url('assets/images/genres/'.$model->name.'.jpg') }}">
            <meta name="twitter:description" content="Listen to most popular {{ $model->name }} artists {{ trans('app.on') }} {{ $settings->get('siteName') }}" />
            <meta itemprop="description" content="Listen to most popular {{ $model->name }} artists {{ trans('app.on') }} {{ $settings->get('siteName') }}" />
            <meta itemprop="image" content="{{ App::make('Settings')->get('enable_https') ? secure_url('assets/images/genres/'.$model->name.'.jpg') : url('assets/images/genres/'.$model->name.'.jpg') }}">
            <meta property="og:image:width" content="300">
            <meta property="og:image:height" content="300">
        @elseif ($type === 'playlist')
            <meta property="og:description" content="{{ $description }}" />
            <meta name="twitter:description" content="{{ $description }}" />
            <meta itemprop="description" content="{{ $description }}" />
            <meta property="og:type" content="music.playlist" />
            <meta property="og:image" content="{{ $image }}">
            <meta name="twitter:image" content="{{ $image }}">
            <meta itemprop="image" content="{{ $image }}">
            <meta property="music:song_count" content="{{ count($model->tracks) }}">
            <meta property="og:image:width" content="300">
            <meta property="og:image:height" content="300">

            @foreach($model->tracks as $index => $track)
                <meta property="music:song" content="{{ url().'/track/'.$track->id }}">
                <meta property="music:song:track" content="{{ $index }}">
            @endforeach
        @elseif ($type === 'track')
            <meta property="og:description" content="{{ $model->name }}, a song by {{ implode(', ', $model->artists)  }} {{ trans('app.on') }} {{ $settings->get('siteName') }}" />
            <meta name="twitter:description" content="{{ $model->name }}, a song by {{ implode(', ', $model->artists)  }} {{ trans('app.on') }} {{ $settings->get('siteName') }}" />
            <meta itemprop="description" content="{{ $model->name }}, a song by {{ implode(', ', $model->artists)  }} {{ trans('app.on') }} {{ $settings->get('siteName') }}" />
            <meta property="og:type" content="music.song" />

            @if($model->album)
                <meta property="og:image:url" content="{{ $model->album->image }}" />
                <meta name="twitter:image" content="{{ $model->album->image }}" />
                <meta itemprop="image" content="{{ $model->album->image }}">
            @endif

            <meta property="music:duration" content="{{ $model->duration }}">

            <meta property="og:image:width" content="300">
            <meta property="og:image:height" content="300">
        @endif

        @if ($type !== 'album' && $type !== 'track')
            <meta property="og:updated_time" content="{{ $model->updated_at->timestamp }}" />
        @endif
    </head>

    <body>
        @if ($type === 'album')
            <h2>{{ $model->artist_id ? $model->artist->name : trans('app.variousArtists')  }}</h2>
        @endif

        @if ($type === 'artist')
            <p>{{ $description }}</p>
        @elseif ($type === 'album')
            <p>{{ $model->name }}, album by {{ $model->artist_id ? $model->artist->name : trans('app.variousArtists') }} {{ trans('app.on') }} {{ $settings->get('siteName') }}</p>
        @elseif ($type === 'genre')
            <p>Listen to most popular {{ $model->name }} artists {{ trans('app.on') }} {{ $settings->get('siteName') }}</p>
        @elseif ($type === 'playlist')
            <p>{{ $description }}</p>
        @elseif ($type === 'track')
            <p><strong>{{ $model->name }}</strong>, a song by <strong>{{ implode(', ', $model->artists) }}</strong> {{ trans('app.on') }} {{ $settings->get('siteName') }}</p>
        @endif

        <a href="{{ Request::url() }}">
            @if ($type === 'playlist')
                <img src="{{ $image }}" alt="{{ $model->name }}">
            @elseif ($type === 'track')
                @if($model->album)
                    <img src="{{ $model->album->image  }}" alt="{{ $model->name }}">
                @endif
            @elseif ($type === 'genre')
                <img src="{{ App::make('Settings')->get('enable_https') ? secure_url('assets/images/genres/'.$model->name.'.jpg') : url('assets/images/genres/'.$model->name.'.jpg')  }}">
            @else
                <img src="{{ $type === 'artist' ? $model->image_large : $model->image  }}" alt="{{ $model->name }}">
            @endif
        </a>

        @if ($type === 'genre')
            @foreach($model->artists as $artist)
                <ul>
                    <li>
                        <figure>
                            <img src="{{ $artist->image_small }}" alt="{{ $artist->name }}">
                            <figcaption><a href="{{ url('artist/'.urlencode($artist->name)) }}">{{ $artist->name }}</a></figcaption>
                        </figure>
                    </li>
                </ul>
            @endforeach
        @endif

        @if($type === 'artist')
            @foreach($model->albums as $album)
                <h3><a href="{{ url().'/album/'.$album->id.'/'.($album->artist ? urlencode($album->artist->name).'/'.urlencode($album->name) : urlencode($album->name)) }}">{{ $album->name }}</a> - {{ $album->release_date }}</h3>

                <ul>
                    @foreach($album->tracks as $track)
                        <li><a href="{{ url().'/track/'.$track->id  }}">{{ $track->name }} - {{ $album->name }} - {{ $model->name }}</a></li>
                    @endforeach
                </ul>
            @endforeach

        @elseif($type === 'album')
            <h3><a href="{{ url().'/album/'.$model->id.'/'.($model->artist ? urlencode($model->artist->name).'/'.urlencode($model->name) : urlencode($model->name)) }}">{{ $model->name }}</a> - {{ $model->release_date }}</h3>
            <ul>
                @foreach($model->tracks as $track)
                    <li>
                    
                    <a href="{{ url().'/track/'.$track->id  }}">{{ $track->name }}</a>
                    </li>
                @endforeach
            </ul>
        @endif
    </body>
</html>
