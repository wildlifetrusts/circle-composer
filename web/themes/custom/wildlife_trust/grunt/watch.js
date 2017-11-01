/**
 * Watch files for changes and perform the tasks.
 */
module.exports = {
  options: {
    // Global options here.
  },
  grunt: {
    files: ['Gruntfile.js']
  },
  'assets-images': {
    files: ['assets/images/**'],
    tasks: [
      'newer:imagemin',
      'newer:svgmin'
    ]
  },
  sass: {
    files: ['sass/**'],
    tasks: [
      'sass_globbing:dist',
      'sass:dev',
      'modernizr:dist'
    ]
  },
  css: {
    options: {
      // If we don't set spawn to false the postcss:dev tasks will endlessly run
      // since the task itself changes the css file.
      spawn: false
    },
    files: ['css/{,**/}*.css'],
    tasks: ['postcss:dev']
  },
  js: {
    files: ['assets/js/{,**/}*.js'],
    tasks: ['uglify:dev']
  }
};
