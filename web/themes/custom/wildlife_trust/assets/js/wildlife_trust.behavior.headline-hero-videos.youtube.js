/* global YT, enquire */
(function ($) {

  /**
   * Behaviour to create auto-playing videos without controls from YouTube.
   *
   * @type {{attach: Drupal.behaviors.wildlifeTrustHeadlineHeroVideosYouTube.attach}}
   */
  Drupal.behaviors.wildlifeTrustHeadlineHeroVideosYouTube = {
    attach: function (context, settings) {
      // Only add videos above the desk CSS breakpoint.
      enquire.register(settings.breakpoints.lap, {
        match: function () {
          var doc = document;
          var videoState = 0;

          // Only continue if the code hasn't been run already.
          var body = doc.querySelector('body');
          if (body.classList.contains('youtube-processed')) { return; }

          // Get the videos from drupalSettings.
          var videos = settings.youTubeVideos[0];

          // Functions to control the video.
          function videoReady(e) {
            e.target.setVolume(0);
            e.target.playVideo();
          }

          function videoStateChange(e) {
            videoState = e.data;

            if (e.data === YT.PlayerState.BUFFERING) {
              setTimeout(function () {
                videoWillAutoplay(e);
              }, 2000);
            }

            // If the video has ended, start it again.
            if (e.data === YT.PlayerState.ENDED) {
              e.target.playVideo();
            }
          }

          function videoWillAutoplay(e) {
            if (videoState !== 1) {
              e.target.destroy();
            }
            else {
              var $iframe = $(e.target.getIframe());
              $iframe.closest('.paragraph--type--headline-hero').addClass('has--video');
            }
          }

          // Function to create video objects.
          var createVideos = function () {
            for (var video in videos) {
              if (videos.hasOwnProperty(video)) {
                var data = videos[video];
                // Add an inner element so that the mark-up will be the same
                // for both YouTube and Vimeo videos (YouTube replaces the
                // target with an iframe and Vimeo adds the iframe as a child).
                var innerElement = doc.createElement('div');
                innerElement.setAttribute('id', video + '-iframe');
                doc.getElementById(video).appendChild(innerElement);

                // Set out the video options.
                var options = {
                  videoId: data.videoId,
                  width: data.width,
                  height: data.height,
                  playerVars: {
                    'controls': 0,
                    'disablekb': 0,
                    'modestbranding': 1,
                    'rel': 0,
                    'showinfo': 0
                  },
                  events: {
                    'onReady': videoReady,
                    'onStateChange': videoStateChange
                  }
                };

                // Create the video object.
                new YT.Player(video + '-iframe', options);
              }
            }
          };

          // Check whether or not the YouTube constructor exists.
          if (typeof(YT) === 'undefined' || typeof(YT.Player) === 'undefined') {
            // Only start creating the video objects when the YouTube API
            // has fully downloaded.
            window.onYouTubeIframeAPIReady = createVideos;
          }
          else {
            createVideos();
          }

          // Add processed class.
          body.classList.add('youtube-processed');
        }
      });
    }
  };
})(jQuery);
