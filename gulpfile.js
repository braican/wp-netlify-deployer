const gulp = require('gulp');
const sass = require('gulp-sass');
const sourcemaps = require('gulp-sourcemaps');
const autoprefixer = require('gulp-autoprefixer');
const babel = require('gulp-babel');

sass.compiler = require('node-sass');

// ... variables
const autoprefixerOptions = {
  browsers: ['last 2 versions', '> 5%']
};

gulp.task('sass', () => {
  return gulp
    .src('./static/scss/*.scss')
    .pipe(sourcemaps.init())
    .pipe(sass().on('error', sass.logError))
    .pipe(autoprefixer(autoprefixerOptions))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./static/build'));
});

gulp.task('js', () => {
  return gulp
    .src('./static/js/*.js')
    .pipe(
      babel({
        presets: ['@babel/env']
      })
    )
    .pipe(gulp.dest('./static/build'));
});

gulp.task('watch', () => {
  gulp.watch('./static/scss/*.scss', ['sass']);
  gulp.watch('./static/js/*.js', ['js']);
});

gulp.task('default', ['watch']);
