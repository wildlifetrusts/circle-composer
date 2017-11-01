/**
 * KSS.
 */
module.exports = {
  options: {
    verbose: true,
    helpers: 'docs/kss-helpers',
    css: '../kss-helpers/css/wildlife-kss.css',
    homepage: '../kss-helpers/homepage.md',
    template: 'docs/kss-theme'
  },
  dist: {
    src: ['sass'],
    dest: 'docs/kss'
  }
};
