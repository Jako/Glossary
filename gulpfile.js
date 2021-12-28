const gulp = require('gulp'),
    autoprefixer = require('autoprefixer'),
    changed = require('gulp-changed'),
    composer = require('gulp-uglify/composer'),
    concat = require('gulp-concat'),
    cssnano = require('cssnano'),
    footer = require('gulp-footer'),
    format = require('date-format'),
    fs = require('fs'),
    header = require('gulp-header'),
    imagemin = require('gulp-imagemin'),
    postcss = require('gulp-postcss'),
    rename = require('gulp-rename'),
    replace = require('gulp-replace'),
    sass = require('gulp-sass')(require('sass')),
    uglifyjs = require('uglify-js'),
    uglify = composer(uglifyjs, console),
    pkg = require('./_build/config.json');

const banner = '/*!\n' +
    ' * <%= pkg.name %> - <%= pkg.description %>\n' +
    ' * Version: <%= pkg.version %>\n' +
    ' * Build date: ' + format("yyyy-MM-dd", new Date()) + '\n' +
    ' */';

gulp.task('scripts-mgr', function () {
    return gulp.src([
        'source/js/mgr/glossary.js',
        'source/js/mgr/widgets/home.panel.js',
        'source/js/mgr/widgets/terms.grid.js',
        'source/js/mgr/widgets/settings.panel.js',
        'source/js/mgr/sections/home.js'
    ])
        .pipe(concat('glossary.min.js'))
        .pipe(uglify())
        .pipe(header(banner + '\n', {pkg: pkg}))
        .pipe(gulp.dest('assets/components/glossary/js/mgr/'))
});

gulp.task('sass-mgr', function () {
    return gulp.src([
        'source/sass/mgr/glossary.scss'
    ])
        .pipe(sass().on('error', sass.logError))
        .pipe(postcss([
            autoprefixer()
        ]))
        .pipe(gulp.dest('source/css/mgr/'))
        .pipe(postcss([
            cssnano({
                preset: ['default', {
                    discardComments: {
                        removeAll: true
                    }
                }]
            })
        ]))
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(footer('\n' + banner, {pkg: pkg}))
        .pipe(gulp.dest('assets/components/glossary/css/mgr/'))
});

gulp.task('images-mgr', function () {
    return gulp.src('./source/img/**/*.+(png|jpg|gif|svg)')
        .pipe(changed('assets/components/glossary/img/mgr/'))
        .pipe(imagemin([
            imagemin.gifsicle({interlaced: true}),
            imagemin.mozjpeg({progressive: true}),
            imagemin.optipng({optimizationLevel: 7}),
            imagemin.svgo({
                plugins: [
                    {removeViewBox: true},
                    {cleanupIDs: true}
                ]
            })
        ]))
        .pipe(gulp.dest('assets/components/glossary/img/'));
});

gulp.task('bump-copyright', function () {
    return gulp.src([
        'core/components/glossary/model/glossary/glossarybase.class.php',
        'source/js/mgr/widgets/home.panel.js',
    ], {base: './'})
        .pipe(replace(/Copyright 2016(-\d{4})? by/g, 'Copyright ' + (new Date().getFullYear() > 2016 ? '2016-' : '') + new Date().getFullYear() + ' by'))
        .pipe(replace(/© 2018(-\d{4})? by/g, '© ' + (new Date().getFullYear() > 2016 ? '2016-' : '') + new Date().getFullYear()))
        .pipe(gulp.dest('.'));
});
gulp.task('bump-version', function () {
    return gulp.src([
        'core/components/glossary/model/glossary/glossarybase.class.php',
    ], {base: './'})
        .pipe(replace(/version = '\d+.\d+.\d+[-a-z0-9]*'/ig, 'version = \'' + pkg.version + '\''))
        .pipe(gulp.dest('.'));
});
gulp.task('bump-docs', function () {
    return gulp.src([
        'mkdocs.yml',
    ], {base: './'})
        .pipe(replace(/&copy; 2016(-\d{4})?/g, '&copy; ' + (new Date().getFullYear() > 2016 ? '2016-' : '') + new Date().getFullYear()))
        .pipe(gulp.dest('.'));
});
gulp.task('bump', gulp.series('bump-copyright', 'bump-version', 'bump-docs'));


gulp.task('watch', function () {
    // Watch .js files
    gulp.watch(['./source/js/**/*.js'], gulp.series('scripts-mgr'));
    // Watch .scss files
    gulp.watch(['./source/scss/**/*.scss'], gulp.series('sass-mgr'));
    // Watch .scss files
    gulp.watch(['./source/img/**/*.(png|jpg|gif|svg)'], gulp.series('images-mgr'));
});

// Default Task
gulp.task('default', gulp.series('bump', 'scripts-mgr', 'sass-mgr', 'images-mgr'));