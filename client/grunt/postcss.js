var autoprefixer = require('autoprefixer-core');

module.exports = {
  options: {
    processors: [
      autoprefixer({
        browsers: ['last 2 version']
      }).postcss
    ]
  },
  dist: {
    src: 'static/css/*.css'
  }
};
