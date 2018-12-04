const gulp = require('gulp');
const sass = require('gulp-sass');
const sourcemaps = require('gulp-sourcemaps');
const autoprefixer = require('gulp-autoprefixer');

sass.compiler = require('node-sass');

// ... variables
const autoprefixerOptions = {
  browsers: ['last 2 versions', '> 5%']
};

gulp.task('sass', function() {
  return gulp
    .src('./static/scss/*.scss')
    .pipe(sourcemaps.init())
    .pipe(sass().on('error', sass.logError))
    .pipe(autoprefixer(autoprefixerOptions))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./static/build'));
});

gulp.task('sass:watch', function() {
  gulp.watch('./static/scss/*.scss', ['sass']);
});

gulp.task('default', ['sass:watch']);
