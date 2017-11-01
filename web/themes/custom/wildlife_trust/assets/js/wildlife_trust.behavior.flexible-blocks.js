(function () {

  /**
   * Behaviour to open/close the flexible blocks (if they have a title).
   *
   * @type {{attach: Drupal.behaviors.wildlifeTrustFlexibleBlocks.attach}}
   */
  Drupal.behaviors.wildlifeTrustFlexibleBlocks = {
    attach: function (context, settings) {
      var getSiblings = function (element) {
        var firstChild = element.parentNode.firstChild;
        var siblings = [];
        for (; firstChild; firstChild = firstChild.nextSibling) {
          if (firstChild.nodeType === 1 && firstChild !== element) {
            siblings.push(firstChild);
          }
        }
        return siblings;
      };

      var breakpoints = settings.breakpoints;
      var blockTitles = document.querySelectorAll('.flexible-blocks-title');

      // Only continue if there are flexible block titles.
      if (!blockTitles) { return; }

      // Loop through each flexible block title.
      [].forEach.call(blockTitles, function (blockTitle) {
        // Only continue if the block hasn't already been processed.
        if (blockTitle.classList.contains('flexible-block-processed')) { return; }

        // Get the block content items.
        var blockContents = getSiblings(blockTitle);

        if (blockContents !== null) {
          // Set up toggle function.
          var toggle = function () {
            blockTitle.classList.toggle('is--open');
            [].forEach.call(blockContents, function(blockContent) {
              blockContent.classList.toggle('is--open');
              blockContent.classList.toggle('visually-hidden');
            });
          };

          var addAccordions = function() {
            // Hide the block content by default.
            [].forEach.call(blockContents, function(blockContent) {
              blockContent.classList.add('visually-hidden');
            });
            blockTitle.addEventListener('click', toggle);
          };

          var removeAccordions = function() {
            // Remove added classes.
            blockTitle.classList.remove('is--open');
            [].forEach.call(blockContents, function(blockContent) {
              blockContent.classList.remove('is--open');
              blockContent.classList.remove('visually-hidden');
            });
            blockTitle.removeEventListener('click', toggle);
          };

          enquire.register(breakpoints.lap, {
            match : function() {
              removeAccordions();
            },
            unmatch : function() {
              addAccordions();
            },
            setup : function() {
              addAccordions();
            }
          });
        }
      });
    }
  };

})();
