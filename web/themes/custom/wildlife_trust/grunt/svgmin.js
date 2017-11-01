/**
 * svgmin minifies SVGs found within the assets/images folder (i.e. not icons).
 */
module.exports = {
  images: {
    options: {
      plugins: [{
        removeDimensions: false,
        removeUselessStrokeAndFill: false
      }]
    },
    files: [{
      expand: true,
      cwd: 'assets/images',
      src: ['**/*.svg', '*.svg'],
      dest: 'images'
    }]
  },
  icons: {
    options: {
      plugins: [{
        removeDimensions: false,
        removeUselessStrokeAndFill: false
      }]
    },
    files: [{
      expand: true,
      cwd: 'assets/icons',
      src: ['**/*.svg', '*.svg'],
      dest: 'assets/icons/minified'
    }]
  }
};
