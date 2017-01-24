angular.module('app')

.factory('youtubePlayer', function($rootScope, $timeout, utils) {

    var youtube = {

        /**
         * User facing player interface.
         */
        frontPlayer: false,

        /**
         * Youtube player implementation.
         */
        youtubePlayer: false,

        play: function() {
            this.youtubePlayer.playVideo();
        },

        pause: function() {
            this.youtubePlayer.pauseVideo();
        },

        seekTo: function(time) {
            this.youtubePlayer.seekTo(time, true);
        },

        loadVideo: function(track, autoPlay, quality) {
            this.youtubePlayer.loadVideoById(track.youtube_id, autoPlay, quality);
        },

        cueVideo: function(track, autoPlay, quality) {
            this.youtubePlayer.cueVideoById(track.youtube_id, autoPlay, quality);
        },

        getDuration: function() {
            return this.youtubePlayer.getDuration();
        },

        getCurrentTime: function() {
            return this.youtubePlayer.getCurrentTime();
        },

        getVolume: function() {
            return this.youtubePlayer.getVolume(number);
        },

        setVolume: function(number) {
            this.youtubePlayer.setVolume(number);
        },

        mute: function() {
            this.youtubePlayer.mute();
        },

        unMute: function() {
            this.youtubePlayer.unMute();
        },

        isPlaying: function() {
            return this.youtubePlayer.getPlayerState() === 1;
        },

        init: function(frontPlayer) {
            this.frontPlayer = frontPlayer;

            utils.loadScript('https://www.youtube.com/iframe_api');

            window.onYouTubeIframeAPIReady = function() {
                youtube.youtubePlayer = new YT.Player('player', {
                    playerVars : {
                        autoplay: 0,
                        rel: 0,
                        showinfo: 0,
                        egm: 0,
                        showsearch: 0,
                        controls: 0,
                        modestbranding: 1,
                        iv_load_policy: 3,
                        disablekb: 1,
                        version: 3,
                        playsinline: 1,
                    },
                    events: {
                        onReady: function() {
                            $rootScope.$apply(function() {
                                youtube.frontPlayer.loadLastPlayerTrack();
                            });
                            $rootScope.$emit('player.loaded');
                        },
                        onError:function(e) {
                            if (e.data == 150 || e.data == 101) {
                                alertify.delay(2500).error(utils.trans('couldntFindTrack'));
                                youtube.frontPlayer.playNext();

                                $rootScope.$apply(function() {
                                    youtube.frontPlayer.stop();
                                })
                            }
                        },
                        onStateChange: function(ev) {
                            if (ev.data === YT.PlayerState.ENDED) {
                                $rootScope.$apply(function() {
                                    youtube.frontPlayer.playNext();
                                })
                            } else if (ev.data === YT.PlayerState.CUED || ev.data === YT.PlayerState.PLAYING) {
                                $rootScope.$apply(function() {
                                    youtube.frontPlayer.loadingTrack = false;
                                });

                                if (ev.data === YT.PlayerState.PLAYING) {

                                    $timeout(function() {
                                        $rootScope.$emit('player.trackChanged');

                                        if ( ! youtube.frontPlayer.isPlaying) {
                                            youtube.frontPlayer.isPlaying = true;
                                        }
                                    })
                                }
                            } else if (ev.data === YT.PlayerState.PAUSED) {
                                if (youtube.frontPlayer.isPlaying) {
                                    $rootScope.$apply(function() {
                                        youtube.frontPlayer.isPlaying = false;
                                    })
                                }
                            }
                        }
                    }
                });
            };
        }
    };

    return youtube;
});
