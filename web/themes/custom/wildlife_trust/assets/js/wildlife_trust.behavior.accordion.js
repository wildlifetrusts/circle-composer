/**
 * @file
 * Accordion behaviours.
 *
 */
(function () {

  'use strict';

  /**
   * Behaviour to open/close accordion items.
   *
   * @type {{attach: Drupal.behaviors.wildlifeTrustAccordion.attach}}
   */
  Drupal.behaviors.wildlifeTrustAccordion = {
    attach: function () {
      // Set up namespace and define functions.
      var WTACCORDION = {};

      // Function to check if an element is visible inside viewport.
      WTACCORDION.isVisible = function (el) {
        // Get the element bounding rectangle.
        var rect = el.getBoundingClientRect();

        // Return if the element falls within the viewport.
        return rect.bottom >= 0 && rect.top <= window.innerHeight;
      };

      // Function to close unselected accordion items.
      WTACCORDION.closer = function (i, item, allItems) {
        // Loop through all the items.
        allItems.forEach(function (el, j) {
          // Continue if the current index doesn't equal the index of the clicked-on item.
          if (i !== j) {
            var detailsElement = el.parentNode;

            // Close any open details elements.
            if (detailsElement.hasAttribute('open')) {
              detailsElement.removeAttribute('open');
              el.setAttribute('aria-expanded', 'false');
            }
          }
        });

        if (item.parentNode.hasAttribute('open')) {
          item.setAttribute('aria-expanded', 'false');
        }
        else {
          item.setAttribute('aria-expanded', 'true');
        }

        // Scroll to the top of the open item if it isn't visible in the viewport.
        if (!WTACCORDION.isVisible(item)) {
          window.scrollTo(0, item.getBoundingClientRect().top + window.pageYOffset);
        }
      };

      // Loop through each instance of an accordion paragraph.
      [].forEach.call(document.querySelectorAll('.paragraph--type--accordion'), function (accordion) {
        // Only continue if the accordion hasn't already been processed.
        if (accordion.classList.contains('accordion-processed')) { return; }

        // Get the default open item from the data attribute.
        var defaultOpenItem = parseInt(accordion.getAttribute('data-default-open-item'));
        var accordionItems = accordion.querySelectorAll('.paragraph--type--accordion-item');

        // Stop here if there are no accordion items.
        if (!accordionItems) { return; }

        // Create an array in which to store accordion items.
        var summaryElements = [];

        // Loop through each instance of an accordion item paragraph.
        [].forEach.call(accordionItems, function (accordionItemWrapper, i) {
          // Get the accordion item.
          var accordionItem = accordionItemWrapper.querySelector('details');

          // Get the summary element.
          var summaryElement = accordionItem.querySelector('summary');

          // Make sure the summary element can receive keyboard focus. I'm looking at you, IE...
          summaryElement.setAttribute('tabindex', '0');

          // Add accessibility roles and other data.
          summaryElement.setAttribute('role', 'button');

          if (!accordionItem.hasAttribute('open')) {
            summaryElement.setAttribute('aria-expanded', 'false');
          }

          // Add the current accordion item's summary element to the summary elements array.
          summaryElements.push(summaryElement);

          // Open the accordion item that's set to open by default on page load.
          if (i + 1 === defaultOpenItem) {
            accordionItem.setAttribute('open', '');
            summaryElement.setAttribute('aria-expanded', 'true');
          }
        });

        // Once all the summary elements have been processed, loop through them to add event listeners.
        summaryElements.forEach(function (summaryElement, i) {
          // Set up 'click' event listener.
          summaryElement.addEventListener('click', function () {
            // Toggle the accordion items.
            WTACCORDION.closer(i, summaryElement, summaryElements);
          });

          // Set up the 'keypress' event listener.
          summaryElement.addEventListener('keypress', function (e) {
            // Only continue if the 'enter' key is pressed.
            if (e.which === 13) {
              // Toggle the accordion items
              WTACCORDION.closer(i, summaryElement, summaryElements);
            }
          });
        });

        // Add processed class to the accordion.
        accordion.classList.add('accordion-processed');
      });
    }
  };

})();
