/**
 * Modernizr automated setup.
 *
 * Scan our JS and SCSS files for references to Modernizr tests. If a reference
 * is found then include it in a custom build of Modernizr.
 *
 * We explicitly ask for the tests that are required by Drupal core as our
 * theme's info.yml file overrides the core Modernizr library in favour of using
 * the version built by this theme.
 */
module.exports = {
  dist: {
    "options": [
      "prefixes",
      "addTest",
      "testStyles",
      "setClasses"
    ],
    "tests": [
      "details",
      "inputtypes",
      "touchevents"
    ],
    "dest": "js/vendor/modernizr.min.js",
    "files": {
      "src": [
        "js/*.js",
        "js/**/*.js",
        "sass/*.scss",
        "sass/**/*.scss"
      ]
    }
  }
};
