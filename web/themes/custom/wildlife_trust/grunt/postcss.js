/**
 * PostCSS provides the following actions on the generated CSS:
 * - Autoprefixer: Adds browser prefixes for the supported browsers stated.
 * - CSSNano: Minifies and cleans up CSS.
 */
module.exports = {
  prod: {
    options: {
      map: false,
      processors: [
        require('autoprefixer')({
          browsers: [
            'last 2 versions',
            'ie 11',
            'ios 6',
            'android 4'
          ]
        }),
        require('pixrem')(),
        require('cssnano')({
          zindex: false,
          normalizeUrl: {
            stripWWW: false
          }
        })
      ]
    },
    src: 'css/*.css'
  },
  dev: {
    options: {
      map: true,
      processors: [
        require('autoprefixer')({
          browsers: [
            'last 2 versions',
            'ie 11',
            'ios 6',
            'android 4'
          ]
        }),
        require('pixrem')()
      ]
    },
    src: 'css/*.css'
  }
};
