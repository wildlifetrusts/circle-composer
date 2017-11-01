/* global Vimeo, enquire */
(function ($) {

  /**
   * Behaviour to create auto-playing videos from Vimeo.
   *
   * @type {{attach: Drupal.behaviors.wildlifeTrustHeadlineHeroVideosVimeo.attach}}
   */
  Drupal.behaviors.wildlifeTrustHeadlineHeroVideosVimeo = {
    attach: function (context, settings) {
      // Only add the video above the desk CSS breakpoint.
      enquire.register(settings.breakpoints.lap, {
        match: function () {
          var doc = document;
          // Only continue if the code hasn't been run already.
          var body = doc.querySelector('body');
          if (body.classList.contains('vimeo-processed')) { return; }

          // Get the videos from drupalSettings.
          var videos = settings.vimeoVideos[0];

          // Check if the video is autoplaying.
          function videoWillAutoplay(video, vimeo) {
            if (videoState === 1) {
              $('#' + video).closest('.paragraph--type--headline-hero').addClass('has--video');
            }
            else {
              vimeo.unload();
            }
          }

          function setupVideo(video, options) {
            // Add video object and set the volume to zero.
            var vimeo = new Vimeo.Player(doc.getElementById(video), options);

            vimeo.setVolume(0);
            vimeo.on('play', function () {
              videoState = 1;
            });
            vimeo.play();
            vimeo.ready().then(function () {
              setTimeout(function () {
                videoWillAutoplay(video, vimeo);
              }, 2000);
            });
          }

          for (var video in videos) {
            if (videos.hasOwnProperty(video)) {
              var videoState = 0;
              var data = videos[video];

              // Set out video options.
              var options = {
                id: data.videoId,
                width: data.width,
                height: data.height,
                loop: true
              };

              setupVideo(video, options);
            }
          }

          // Add processed class.
          body.classList.add('vimeo-processed');
        }
      });

    }
  };

})(jQuery);
