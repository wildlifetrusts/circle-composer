/**
 * Imagemin minifies images in the assets/images folder.
 */
module.exports = {
  options: {
    pngquant: true
  },
  images: {
    files: [{
      expand: true,
      cwd: 'assets/images',
      src: ['**/*.{png,jpg,gif}'],
      dest: 'images/'
    }]
  }
};
