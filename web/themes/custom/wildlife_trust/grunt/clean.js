/**
 * Clean is a general housekeeping tasks to remove all the generated files.
 */

module.exports = {
  src: [
    "assets/icons/minified",
    "css",
    "images",
    "icons",
    "js",
    "docs/sassdocs",
    "docs/kss",
    "sass/**/__*.scss",
    "sass/02_functions/_icon-referenced-icons.scss"
  ]
};
