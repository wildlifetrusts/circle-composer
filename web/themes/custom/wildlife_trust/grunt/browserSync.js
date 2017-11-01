/**
 * BrowserSync task to allow live reloading of assets without page refresh -
 * plus a whole lot more: https://www.browsersync.io/
 *
 * Cuts out repetitive manual tasks and live reloads to URL pushing, form
 * replication to click mirroring.
 */
module.exports =  {
  dev: {
    bsFiles: {
      src: [
        'css/*.css',
        'js/*.js',
        'icons/*.*',
        'images/*.*'
      ]
    },
    options: {
      watchTask: true,
      proxy: '<%= defaultConfig.localUrl %>'
    }
  }
};
