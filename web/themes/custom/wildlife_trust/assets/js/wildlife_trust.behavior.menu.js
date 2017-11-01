/* global enquire */
/* jshint laxbreak : true */
(function ($) {
  'use strict';

  // Set up namespace.
  var WTMENU = {};

  // Function to add a burger button.
  WTMENU.addBurger = function (parent, clickFunction) {
    // Create a button.
    var button = document.createElement('button');
    button.classList.add('menu-toggle');
    button.addEventListener('click', clickFunction);

    // Create an element to hold the button icon.
    var iconEl = document.createElement('span');
    iconEl.classList.add('menu-toggle__icon');
    iconEl.setAttribute('aria-hidden', 'true');
    iconEl.setAttribute('role', 'presentation');

    // Create an element to hold the button text.
    var textEl = document.createElement('span');
    textEl.classList.add('menu-toggle__text');
    textEl.textContent = 'Menu';

    // Create elements for each of the burger lines
    // and append them to the button.
    ['top', 'middle', 'bottom'].forEach(function (linePosition) {
      var line = document.createElement('span');
      line.classList.add('menu-toggle__icon__line');
      line.classList.add('menu-toggle__icon__line--' + linePosition);
      iconEl.appendChild(line);
    });

    // Append the icon and text elements to the button.
    button.appendChild(iconEl);
    button.appendChild(textEl);

    // Append the button to the parent element.
    parent.appendChild(button);
  };

  // Function to hide a drop-down.
  WTMENU.hide = function (el) {
    el.classList.add('visually-hidden');
    el.classList.remove('is--open');

    // Update section heights.
    $.fn.matchHeight._update();
  };

  // Function to show a drop-down.
  WTMENU.show = function (el) {
    el.classList.remove('visually-hidden');
    el.classList.add('is--open');

    // Update section heights.
    $.fn.matchHeight._update();
  };

  // Function to indicate that an element has an associated drop-down open.
  WTMENU.indicateOpen = function (els) {
    els.forEach(function (el) {
      el.classList.add('has--dropdown--open');
    });
  };

  // Function to indicate that an element has an associated drop-down closed.
  WTMENU.indicateClosed = function (els) {
    els.forEach(function (el) {
      el.classList.remove('has--dropdown--open');
    });
  };

  /**
   * Behaviour to handle the burger toggle.
   *
   * @type {{attach: Drupal.behaviors.wildlifeTrustMenuToggle.attach}}
   */
  Drupal.behaviors.wildlifeTrustMenuToggle = {
    attach: function () {
      var doc = document;
      var body = doc.querySelector('body');
      var regionBranding = doc.querySelector('.l-region--branding');

      // Only continue if the code hasn't already been run.
      if (body.classList.contains('menu-toggle-processed')) { return; }

      // Click function to open/close the main menu.
      var toggleMainMenu = function () {
        if (body.classList.contains('is--menu-open')) {
          // Add a closing indicator class for CSS animations
          // then remove it after a time that is longer than the
          // CSS animation time to allow for discrepancies in JS/CSS timings.
          body.classList.add('is--menu-closing');
          setTimeout(function () {
            body.classList.remove('is--menu-closing');
          }, 400);
        }
        body.classList.toggle('is--menu-open');
      };

      // Add a burger toggle button to the branding region.
      WTMENU.addBurger(regionBranding, toggleMainMenu);

      // Add processed class.
      body.classList.add('menu-toggle-processed');
    }
  };

  /**
   * Behaviour to handle the menu drop-downs.
   *
   * @type {{attach: Drupal.behaviors.wildlifeTrusTMenuDropDowns.attach}}
   */
  Drupal.behaviors.wildlifeTrusTMenuDropDowns = {
    attach: function (context, settings) {
      // Only continue if the menu exists and hasn't been processed already.
      var menu = document.querySelector('.menu--main');
      if (!menu || menu.classList.contains('menu-processed')) { return; }

      // Only continue if there is at least one link with a drop-down.
      var itemsWithDropDown = menu.querySelectorAll('.menu__item--expanded');
      if (!itemsWithDropDown) { return; }

      // Define arrays to hold all the elements.
      var allItems = [];
      var allLinks = [];
      var allDropDowns = [];

      // Define breakpoint.
      var aboveDesk = settings.breakpoints.desk;

      // Loop through all links with a drop-down in order to hide them by
      // default when the page loads and to attach event handlers to the
      // link to open/close the drop-down.
      [].forEach.call(itemsWithDropDown, function (item) {
        // Only continue if there is a drop-down and a link within the menu item.
        var dropDown = item.querySelector('.menu__section');
        var link = item.querySelector('.menu__link');
        if (!dropDown || !link) { return; }

        // Push elements to their respective arrays.
        allItems.push(item);
        allLinks.push(link);
        allDropDowns.push(dropDown);

        // Initialise boolean to indicate whether or not the user
        // is moving on a touch device.
        var isMoving = false;

        // Hide by default.
        WTMENU.hide(dropDown);

        // Duplicate the list item and link, duplicate the standard classes
        // and append the link to the list so as to render them both again
        // in the drop-down.
        var clonedItem = item.cloneNode(false);
        var clonedLink = link.cloneNode(true);
        clonedLink.className = 'menu__link menu__link--title';
        clonedItem.className = 'menu__item';
        clonedItem.appendChild(clonedLink);

        // Get the relevant elements and insert the cloned list item as
        // the first item in the drop-down.
        var firstDropDownItem = dropDown.querySelector('.menu__item');
        var firstDropDownItemParent = firstDropDownItem.parentNode;
        firstDropDownItemParent.insertBefore(clonedItem, firstDropDownItem);

        // Create variables for two timers: one to delay the opening
        // off a menu drop-down and the other for to delay the closing of a
        // menu drop-down.
        var timerIn;
        var timerOut;

        // Function to handle different event types.
        var handleEvent = function (event) {
          // If the drop-down is closed, open it. Otherwise, close it.
          if (!link.classList.contains('has--dropdown--open')) {
            switch (event.type) {
              case 'touchmove':
                // Indicate that a touch user is moving.
                isMoving = true;
                break;
              case 'touchend':
                // Only open a drop-down if the touch user isn't moving.
                if (!isMoving) {
                  event.preventDefault();
                  // Hide drop-downs and remove indicator classes from
                  // items not in the current 'active trail'.
                  enquire.register(aboveDesk, {
                    match: function () {
                      allDropDowns.forEach(function (dropDown) {
                        var itemMenuClasses = item.parentNode.classList;
                        var dropDownMenuClasses = dropDown.parentNode.parentNode.classList;

                        // Check if the clicked-on item and the drop-down share
                        // a common menu ancestor.
                        var hasSiblingsToClose = itemMenuClasses.contains('menu--level-0')
                          && dropDownMenuClasses.contains('menu--level-0')
                          || itemMenuClasses.contains('menu--level-1')
                          && dropDownMenuClasses.contains('menu--level-1')
                          || itemMenuClasses.contains('menu--level-2')
                          && dropDownMenuClasses.contains('menu--level-2');

                        if (hasSiblingsToClose) {
                          // Hide the drop-down.
                          WTMENU.hide(dropDown);

                          // Remove indicator classes from all except the
                          // current trail.
                          $(item)
                            .siblings('.has--dropdown--open')
                            .children('.has--dropdown')
                            .removeClass('has--dropdown--open')
                            .end()
                            .siblings('.has--dropdown--open')
                            .removeClass('has--dropdown--open');
                        }
                      });
                    }
                  });

                  // Show this specific drop-down.
                  WTMENU.show(dropDown);
                  WTMENU.indicateOpen([item, link]);
                }
                isMoving = false;
                break;
              case 'mouseenter':
                // Start timer.
                timerIn = setTimeout(function () {
                  WTMENU.show(dropDown);
                  WTMENU.indicateOpen([item, link]);
                }, 200);
                break;
              case 'mouseleave':
                // Stop the function from running if the mouse leaves the
                // list item.
                clearTimeout(timerIn);
                break;
              case 'focus':
                // Make sure the menu is visible then open the drop-down.
                document.querySelector('body').classList.add('is--menu-open');
                WTMENU.show(dropDown);
                WTMENU.indicateOpen([item, link]);
                break;
            }
          }
          else {
            switch (event.type) {
              case 'touchend':
                // Only close the drop-down if the touch user isn't moving.
                if (!isMoving) {
                  event.preventDefault();
                  WTMENU.hide(dropDown);
                  WTMENU.indicateClosed([item, link]);
                }
                isMoving = false;
                break;
              case 'mouseleave':
                // Start timer.
                timerOut = setTimeout(function () {
                  WTMENU.hide(dropDown);
                  WTMENU.indicateClosed([item, link]);
                }, 200);
                break;
              case 'mouseenter':
                clearTimeout(timerOut);
                break;
            }
          }
        };

        // Add event handlers to open/close the drop-downs.
        link.addEventListener('touchend', function (e) { handleEvent(e); });
        link.addEventListener('touchmove', function (e) { handleEvent(e); });
        link.addEventListener('focus', function (e) { handleEvent(e); });
        item.addEventListener('mouseenter', function (e) { handleEvent(e); });
        item.addEventListener('mouseleave', function (e) { handleEvent(e); });

        // Add processed class.
        menu.classList.add('menu-processed');
      });

      // Function to close the menu if an event target is outside
      // both the main and auxiliary menus.
      var closeMenuIfTargetOutside = function () {
        var $target = $(event.target);
        var body = document.querySelector('body');

        // Boolean to detect whether or not the click came
        // from outside both the menus and the menu toggle.
        var outsideMenus = !$target.closest(menu).length
          && !$target.closest('.menu--auxiliary').length;

        if (outsideMenus) {
          // Hide all the drop-downs and indicate that they're closed.
          allDropDowns.forEach(function (dropDown) {
            WTMENU.hide(dropDown);
          });
          WTMENU.indicateClosed(allItems.concat(allLinks));

          // Close the menu.
          body.classList.add('is--menu-closing');
          setTimeout(function () {
            body.classList.remove('is--menu-closing');
          }, 400);
          body.classList.remove('is--menu-open');
        }
      };

      enquire.register(aboveDesk, {
        match: function () {
          // Close the menu if anywhere outside is clicked when on
          // desktop-sized viewports.
          document.addEventListener('touchstart', closeMenuIfTargetOutside);
        },
        unmatch: function () {
          // Remove event listener on mobile-sized viewports.
          document.removeEventListener('touchstart', closeMenuIfTargetOutside);
        }
      });


    }
  };

  /**
   * Behaviour to align the drop-downs and make sure they're
   * equal height on desktop-sized viewports.
   *
   * @type {{attach: Drupal.behaviors.wildlifeTrustDropDownAligner.attach}}
   */
  Drupal.behaviors.wildlifeTrustDropDownAligner = {
    attach: function (context, settings) {
      var $els = $('.menu--main', context)
        .find('.menu')
        .not('.menu--level-0')
        .once();

      enquire.register(settings.breakpoints.desk, {
        match: function () {
          $els.matchHeight({ byRow: false });
        },
        unmatch: function () {
          $els.matchHeight({ remove: true });
        }
      });

    }
  };

  /**
   * Behaviour to check whether the menu width is larger than the space
   * in which it should fit.
   *
   * @type {{attach: Drupal.behaviors.wildlifeTrustMenuWidth.attach}}
   */
  Drupal.behaviors.wildlifeTrustMenuWidth = {
    attach: function (context, settings) {
      // Get the branding block and nav region and continue if both exist.
      var doc = document;
      var brandingBlock = doc.querySelector('.block--wildlife-theming-branding-block');
      var menu = doc.querySelector('.menu--main');
      var bottomBar = doc.querySelector('.l-bottom-bar');
      if (!brandingBlock || !menu || !bottomBar) { return; }

      // Functon to return the natural width of the branding block.
      var getNaturalWidth = function (el) {
        // Add 'position: absolute;' to account for the possibility
        // of flex-box giving the branding block and unnatural width.
        el.style.position = 'absolute';
        // Get the width.
        var width = el.clientWidth;
        // Remove absolute positioning
        el.style.position = '';
        // Return width.
        return width;
      };

      // Function to add a class to the bottom bar layout element when the
      // menu is too big to fit by the branding block.
      var navWidthHandler = Drupal.debounce(function () {
        if (doc.documentElement.clientWidth - getNaturalWidth(menu) < getNaturalWidth(brandingBlock)) {
          bottomBar.classList.add('has--wide-menu');
        }
        else {
          bottomBar.classList.remove('has--wide-menu');
        }
      }, 200);

      // Only listen for the window resize event and consequently run the
      // function on desktop-sized viewports.
      enquire.register(settings.breakpoints.desk, {
        match: function () {
          navWidthHandler();
          window.addEventListener('resize', navWidthHandler);
        },
        unmatch: function () {
          window.removeEventListener('resize', navWidthHandler);
          bottomBar.classList.remove('has--wide-menu');
        }
      });

    }
  };

  /**
   * Behaviour to add a menu close button to the first level of drop-downs.
   *
   * @type {{attach: Drupal.behaviors.wildlifeTrustMenuCloseButton.attach}}
   */
  Drupal.behaviors.wildlifeTrustMenuCloseButton = {
    attach: function () {
      var doc = document;
      var body = doc.querySelector('body');
      // Only continue if there is a drop-down or the code hasn't already been run.
      var mainSections = document.querySelectorAll('.menu__section--level-0');
      if (!mainSections || body.classList.contains('menu-section-toggle')) { return; }

      // Click function to close the menu's drop-downs.
      var closeDropDowns = function () {
        var menuSection = this.parentNode.parentNode.parentNode;
        // Only continue if there is at least one link with a drop-down.
        var openItems = menuSection.querySelectorAll('li.has--dropdown--open');
        if (!openItems) { return; }

        [].forEach.call(openItems, function (item) {
          // Only continue if there is a drop-down and a link within the menu item.
          var dropDown = item.querySelector('.menu__section');
          var link = item.querySelector('.menu__link');
          if (!dropDown || !link) { return; }

          // Hide the menu drop-downs and indicate that they're closed.
          WTMENU.hide(dropDown);
          WTMENU.indicateClosed([item, link]);
        });
      };

      // Add a burger toggle button to evey top-level menu section.
      [].forEach.call(mainSections, function (mainSection) {
        WTMENU.addBurger(mainSection, closeDropDowns);
      });

      // Add processed class.
      body.classList.add('menu-section-toggle');
    }
  };

})(jQuery);
