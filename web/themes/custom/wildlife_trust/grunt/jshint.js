/**
 * JSHint automation to catch any javascript issues or errors.
 * http://jshint.com/
 * See .jshintrc for config.
 */
module.exports = {
  options: {
    jshintrc: '.jshintrc'
  },
  all: ['assets/js/{,**/}*.js']
};
