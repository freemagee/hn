var gulp = require('gulp'),
rename = require('gulp-rename'),
sass = require('gulp-sass'),
autoprefixer = require('gulp-autoprefixer'),
notify = require('gulp-notify'),
sourcemaps = require('gulp-sourcemaps'),
watch = require('gulp-watch'),
livereload = require('livereload');

gulp.task('styles', function() {
    gulp.src('./src/scss/main.scss')
        .pipe(sourcemaps.init())
        .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
        // .pipe(autoprefixer({
        //     browsers: ['last 2 Chrome versions', 'last 2 Firefox versions']
        // }))
        .pipe(sourcemaps.write('./', {includeContent: false, sourceRoot: '../scss'}))
        .pipe(gulp.dest('./src/css'))
        .pipe(notify({ message: 'Styles task complete' })
    );
});

gulp.task('watch', function() {
    // Watch .scss files
    gulp.watch('./src/scss/*.scss', ['styles']);

    // Create LiveReload server
    server = livereload.createServer();
    server.watch(__dirname + "/src/css");
});