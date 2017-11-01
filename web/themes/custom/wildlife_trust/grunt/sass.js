/**
 * LibSASS setup and config.
 *
 * includePaths allows us to use paths outside /sass folder within scss files.
 */
module.exports = {
  options: {
    includePaths: [
      'node_modules',
      'libraries',
      'libraries/support-for/sass',   // See https://github.com/JohnAlbin/normalize-scss/issues/43 .
      'libraries/normalize-scss/sass' // As above.
    ]
  },
  // Production settings
  prod: {
    options: {
      outputStyle: 'compressed',
      sourceMap: false
    },
    files: [{
      expand: true,
      cwd: 'sass',
      src: ['*.scss'],
      dest: 'css',
      ext: '.css',
      extDot: 'last'
    }]
  },
  // Development settings
  dev: {
    options: {
      outputStyle: 'nested',
      sourceMap: true
    },
    files: [{
      expand: true,
      cwd: 'sass',
      src: ['*.scss'],
      dest: 'css',
      ext: '.css',
      extDot: 'last'
    }]
  },
  // KSS
  kss: {
    options: {
      outputStyle: 'nested',
      sourceMap: true
    },
    files: [{
      expand: true,
      cwd: 'docs/kss-helpers/sass',
      src: ['*.scss'],
      dest: 'docs/kss-helpers/css',
      ext: '.css',
      extDot: 'last'
    }]
  }
};
