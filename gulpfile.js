var    gulp = require('gulp'),
        path = require('path'),
        less = require('gulp-less'),
      uglify = require('gulp-uglify'),
      concat = require('gulp-concat'),
autoprefixer = require('gulp-autoprefixer'),
       shell = require('gulp-shell');

gulp.task('less', function () {
  return gulp.src('./less/**/style.less')
    .pipe(less({
      paths: [ path.join(__dirname, 'less', 'includes') ]
    }))
    .pipe(autoprefixer({
      browsers: ['last 3 versions'],
      cascade: false
    }))
    .pipe(gulp.dest('./assets/css'));
});

gulp.task('script', function() {
  return gulp.src([
      './src/js/lib/modernizr-3.3.1.min.js',
      './src/js/lib/jquery-1.12.0.js',
      './src/js/lib/lazysizes.min.js',
      './src/js/lib/jquery.waypoints.js',
      './src/js/lib/sticky.js',
      './src/js/script.js'
    ])
    .pipe(concat('script-min.js'))
    .pipe(uglify())
    .pipe(gulp.dest('assets/js'));
});

gulp.task('jekyll', shell.task(['jekyll build --config _config.yml']));

// Until gulp 4...
gulp.task('jekyll-sync', ['script', 'less'], shell.task(['jekyll build --config _config.yml']));
gulp.task('jekyll-sync-script', ['script'], shell.task(['jekyll build --config _config.yml']));
gulp.task('jekyll-sync-less', ['less'], shell.task(['jekyll build --config _config.yml']));

gulp.task('watch', function () {
  gulp.watch('./src/less/**/*.less', ['less', 'jekyll-sync-less']);
  gulp.watch('./src/js/**/*.js', ['script', 'jekyll-sync-script']);
  gulp.watch(['./**/*.php', './**/*.html', './**/*.md'], ['jekyll']);
});

gulp.task('default', ['less', 'script', 'jekyll-sync']);
