<!DOCTYPE html>
<html>

    <head>
        <meta charset="UTF-8">

        <title>{{ trans('app.'.$type) }} - {{ $settings->get('siteName') }}</title>

        <meta name="google" content="notranslate">
        <link rel="canonical" href="{{ str_replace('%20', '+', Request::url()) }}" />

        <meta itemprop="name" content="{{ trans('app.'.$type) }}">

        <!-- Twitter Card data -->
        <meta name="twitter:card" content="{{ $type  }}">
        <meta name="twitter:title" content="{{ trans('app.'.$type) }} - {{ $settings->get('siteName') }}">
        <meta name="twitter:url" content="{{ Request::url() }}">

        <!-- Open Graph data -->
        <meta property="og:title" content="{{ trans('app.'.$type) }} - {{ $settings->get('siteName') }}" />
        <meta property="og:url" content="{{ Request::url() }}" />
        <meta property="og:site_name" content="{{ $settings->get('siteName') }}" />
        <meta property="og:description" content="{{ $settings->get(snake_case($type).'_meta_desc', $settings->get('metaDescription')) }}" />
        <meta name="twitter:description" content="{{ $settings->get(snake_case($type).'_meta_desc', $settings->get('metaDescription')) }}" />
        <meta itemprop="description" content="{{ $settings->get(snake_case($type).'_meta_desc', $settings->get('metaDescription')) }}" />
    </head>

    <body>

        <h1>{{ trans('app.'.$type) }}</h1>

        <p>{{ $settings->get(snake_case($type).'_meta_desc', $settings->get('metaDescription')) }}</p>

        @foreach($collection as $item)
            @if ($type === 'popularAlbums' || $type === 'newReleases')
                @if($item['artist'] && $item['artist']['name'] !== 'Various Artists')
                    <a href="{{ url('album/'.$item['id'].'/'.urlencode($item['artist']['name']).'/'.urlencode($item['name'])) }}">
                @else
                    <a href="{{ url('album/'.$item['id'].'/'.urlencode($item['name'])) }}">
                @endif
            @elseif($type === 'popularGenres')
                <a href="{{ url('genre/'.urlencode($item['name'])) }}">
            @elseif($type === 'top50')
                <a href="{{ url('track/'.$item['id']) }}">
            @endif
                <figure>

                    <?php

                    if ($type === 'popularGenres') {
                        $image = App::make('App\Services\Providers\Lastfm\LastfmGenres')->getLocalImagePath($item['name']);
                    } else if ($type === 'top50') {
                        $image = $item['album']['image'];
                    } else {
                        $image = $item['image'];
                    }

                    ?>

                    <img src="{{ $image }}" alt="{{ $item['name'] }}">
                    <figcaption>{{ isset($item['artists']) ? $item['artists'][0] .' - '. $item['name'] : $item['name'] }}</figcaption>
                </figure>
            </a>
        @endforeach

    </body>
</html>
