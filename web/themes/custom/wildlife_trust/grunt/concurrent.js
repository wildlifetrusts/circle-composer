/**
 * Concurrent task groups.
 * Running tasks concurrently will speed up the build time.
 *
 * The tasks in each step will run concurrently. Therefore they should be tasks
 * which do not rely on each other.
 */
module.exports = {
  // Cleaning task.
  cleanStep: [
    'clean'
  ],

  // Development tasks.
  devStepOne: [
    'jshint',
    'svgmin:icons'
  ],

  devStepTwo: [
    'grunticon',
    'imagemin',
    'svgmin:images',
    'uglify:dev'
  ],

  devStepThree: [
    'sass_globbing:dist'
  ],

  devStepFour: [
    'sass:dev'
  ],

  devStepFive: [
    'postcss:dev',
    'modernizr:dist'
  ],

  // Production tasks.
  productionStepOne: [
    'jshint',
    'svgmin:icons',
    'sass_globbing:dist'
  ],

  productionStepTwo: [
    'grunticon',
    'imagemin',
    'svgmin:images',
    'uglify:dist'
  ],

  productionStepThree: [
    'sass:prod'
  ],

  productionStepFour: [
    'postcss:prod',
    'modernizr:dist'
  ]
};
