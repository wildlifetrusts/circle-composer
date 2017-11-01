/**
 * Uglification and minimization of JS files.
 */
module.exports = {
  dev: {
    options: {
      mangle: false,
      compress: false,
      beautify: true
    },
    files: [{
      expand: true,
      cwd: 'assets/js',
      src: ['**/*.js', '!**/*.min.js'],
      dest: 'js',
      ext: '.min.js',
      extDot: 'last'
    }]
  },
  dist: {
    options: {
      mangle: true,
      compress: true
    },
    files: [{
      expand: true,
      cwd: 'assets/js',
      src: ['**/*.js', '!**/*.min.js'],
      dest: 'js',
      ext: '.min.js',
      extDot: 'last'
    }]
  }
};
