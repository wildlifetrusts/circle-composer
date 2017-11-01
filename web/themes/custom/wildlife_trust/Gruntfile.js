///**
// * All grunt tasks are seperated into individual files using `load-grunt-config`.
// * All grunt Tasks are now located inside the `grunt` folder.
// * Please refer to (confluence link) for more info.
// *
// * time-grunt                   - Grunt tasks performance monitoring (https://www.npmjs.com/package/grunt-timer)
// * load-grunt-config            - Allows you to keep our main Gruntfile short and succinct (https://github.com/firstandthird/load-grunt-config)
// * jit-grunt                    - An optimised plugin loader for Grunt. Load time of Grunt does not slow down even if there are many plugins. (https://github.com/shootaroo/jit-grunt)
// */
module.exports = function(grunt) {
  // Default configuration for use within Grunt tasks.
  // This will need updating on a per-project basis.
  var defaultConfig = {
    localUrl: 'http://national.wt.v.ctidigital.com'
  };

  // Measures the time each task takes.
  require('time-grunt')(grunt);

  // Load grunt config.
  require('load-grunt-config')(grunt, {
    jitGrunt: {
      staticMappings: {
        browsersync: 'grunt-browser-sync',
        icons: 'grunt/custom_icons.js'
      }
    },
    data: {
      // Update this URL to the current site URL.
      defaultConfig: defaultConfig
    }
  });
};
