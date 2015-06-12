var gulp = require('gulp');
var uglify = require('gulp-uglify');
var rename = require('gulp-rename');
var watch = require('gulp-watch');
var minifyCss = require('gulp-minify-css');
var watch = require('gulp-watch');
var notify = require('gulp-notify');

gulp.task( 'admin', function() {
	gulp.src('admin/assets/js/*.js')
		.pipe(uglify())
		.pipe(rename({
			extname: '.min.js'
		}))
		.pipe(gulp.dest('admin/assets/js/min'))
		.pipe(notify('admin js'));
	
	gulp.src('admin/assets/css/*.css')
		.pipe(minifyCss({compatiability:'ie8'}))
		.pipe(rename({
			extname: '.min.css'
		}))
		.pipe(gulp.dest('admin/assets/css/min'))
		.pipe(notify('admin css'));
		
});

gulp.task( 'public', function() {
	gulp.src('public/assets/js/*.js')
		.pipe(uglify())
		.pipe(rename({
			extname: '.min.js'
		}))
		.pipe(gulp.dest('public/assets/js/min'))
		.pipe(notify('public js'));
	
	gulp.src('public/assets/css/*.css')
		.pipe(minifyCss({compatiability:'ie8'}))
		.pipe(rename({
			extname: '.min.css'
		}))
		.pipe(gulp.dest('public/assets/css/min'))
		.pipe(notify('public css'));
		
});

gulp.task('watch', function(){
	gulp.watch('public/assets/js/*.js', ['public'] );
	gulp.watch('public/assets/css/*.css', ['public'] );
	
	gulp.watch('admin/assets/js/*.js', ['admin'] );
	gulp.watch('admin/assets/css/*.css', ['admin'] );
})

gulp.task('default', ['admin', 'public']);