var gulp = require('gulp');
var less = require('gulp-less');
var browserSync = require('browser-sync').create();

gulp.task('serve', ['less'], function() {

    browserSync.init({
        server: '/carlos',
        // proxy: 'localhost:8081'
    });

    gulp.watch("web/assets/styles/main.less", ['less']);
    gulp.watch("web/views/index.twig").on('change', browserSync.reload);
});

gulp.task('less', function() {
    return gulp.src('web/assets/styles/main.less')
      .pipe(less())
      .pipe(gulp.dest('web/assets/styles'))
      .pipe(browserSync.stream());
});

gulp.task('default', ['serve']);