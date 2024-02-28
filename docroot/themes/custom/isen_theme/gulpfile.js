var gulp = require('gulp');
var concat = require('gulp-concat');
var minifycss = require('gulp-minify-css')
var removeSourcemaps = require('gulp-remove-sourcemaps');
var rename = require("gulp-rename");
var sourcemaps = require('gulp-sourcemaps');
var sass = require('gulp-sass');
var uglify = require('gulp-uglify');
sass.compiler = require('node-sass');

gulp.task('sass', function () {
  return gulp.src('./scss/style.scss')
    .pipe(sourcemaps.init())
    .pipe(sass.sync().on('error', sass.logError))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('./css'));
});

gulp.task('cssmin', function () {
  return gulp.src('./css/style.css')
    .pipe(minifycss({ keepSpecialComments: 0 }))
    .pipe(rename('style.min.css'))
    .pipe(gulp.dest('./css'));
});

gulp.task('cssrename', function () {
  return gulp.src('./css/style.css')
    .pipe(rename('style.min.css'))
    .pipe(gulp.dest('./css'));
});

var js_scripts_footer = [
  './node_modules/bootstrap/dist/js/bootstrap.bundle.min.js',
  './js_custom/javascript.js'
];

gulp.task('jsfooter', function () {
  return gulp.src(js_scripts_footer)
    .pipe(concat('scripts-footer.js'))
    .pipe(removeSourcemaps())
    .pipe(gulp.dest('./js/'));
});

gulp.task('jsfooterrename', function () {
  return gulp.src('./js/scripts-footer.js')
    .pipe(rename('scripts-footer.min.js'))
    .pipe(gulp.dest('./js/'));
});

gulp.task('default', function () {
  gulp.src('app/Resources/assets/sass/**/*.scss')
    .pipe(sourcemaps.init())
    .pipe(sass())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('web/css'));
});

gulp.task('build', gulp.series([
  'sass',
  'cssmin'
]));
