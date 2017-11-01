/* global enquire */
(function () {

  /**
   * Behaviour to decide which dimension to overflow in order for the
   * video to cover the whole paragraph.
   *
   * @type {{attach: Drupal.behaviors.wildlifeTrustHeadlineHeroVideos.attach}}
   */
  Drupal.behaviors.wildlifeTrustHeadlineHeroVideos = {
    attach: function (context, settings) {
      var doc = document;
      // Only continue if the code hasn't been run before.
      var body = doc.querySelector('body');
      if (body.classList.contains('videos-processed')) { return; }

      // Only run if the viewport width matches the CSS lap breakpoint.
      enquire.register(settings.breakpoints.lap, {
        match: function () {
          // Get the YouTube and Vimeo videos from drupalSettings.
          var vimeoVideos = settings.vimeoVideos[0];
          var youTubeVideos = settings.youTubeVideos[0];

          // Define function to add padding-bottom to the video wbackground
          // element so that the it matches the aspect ratio of the video.
          var responsiveVideo = function (videos) {
            for (var video in videos) {
              if (videos.hasOwnProperty(video)) {
                var data = videos[video];
                var ratio = data.height / data.width;

                doc.getElementById(video).style.paddingBottom = ratio * 100 + '%';
              }
            }
          };

          // Run the function on both sets of videos.
          responsiveVideo(vimeoVideos);
          responsiveVideo(youTubeVideos);
        }
      });

      // Add processed class.
      body.classList.add('videos-processed');
    }
  };

})();
