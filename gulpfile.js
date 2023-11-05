var gulp = require('gulp');
var sourcemaps = require('gulp-sourcemaps');
var source = require('vinyl-source-stream');
var buffer = require('vinyl-buffer');
var browserify = require('browserify');
var watchify = require('watchify');
var babel = require('babelify');
var uglify = require('gulp-uglify');
var sass = require('gulp-sass')(require('sass'));
var rename = require("gulp-rename");
var iife = require('gulp-iife');
var merge = require('merge-stream');

let APP_SCRIPTS = [
    {
        name: 'main.js',
        src: 'assets/admin/src/scripts/main.js',
        dst: 'assets/admin/dist/scripts',
        type: 'admin',
    },
    {
        name: 'video.js',
        src: 'assets/frontend/src/scripts/video.js',
        dst: 'assets/frontend/dist/scripts',
        type: 'frontend',
    },
    {
        name: 'http.js',
        src: 'assets/shared/src/scripts/http.js',
        dst: 'assets/shared/dist/scripts',
        type: 'shared',
    },
    {
        name: 'uploader.js',
        src: 'assets/shared/src/scripts/uploader.js',
        dst: 'assets/shared/dist/scripts',
        type: 'shared',
    },
    {
        name: 'chunked-upload.js',
        src: 'assets/shared/src/scripts/chunked-upload.js',
        dst: 'assets/shared/dist/scripts',
        type: 'shared',
    },
    {
        name: 'upload-modal.js',
        src: 'assets/shared/src/scripts/upload-modal.js',
        dst: 'assets/shared/dist/scripts',
        type: 'shared',
    },
    {
        name: 'tinymce-upload.js',
        src: 'assets/shared/src/scripts/tinymce-upload.js',
        dst: 'assets/shared/dist/scripts',
        type: 'shared',
    },
];

let APP_STYLES = [
    {
        name: 'main.scss',
        src: 'assets/admin/src/styles/main.scss',
        dst: 'assets/admin/dist/styles',
        type: 'admin',
    },
    {
        name: 'video.scss',
        src: 'assets/frontend/src/styles/video.scss',
        dst: 'assets/frontend/dist/styles',
        type: 'frontend',
    },
    {
        name: 'videos-table.scss',
        src: 'assets/frontend/src/styles/videos-table.scss',
        dst: 'assets/frontend/dist/styles',
        type: 'frontend',
    },
    {
        name: 'upload-modal.scss',
        src: 'assets/shared/src/styles/upload-modal.scss',
        dst: 'assets/shared/dist/styles',
        type: 'shared',
    },
];

// Compile sass
const compile_sass = (type, minify = false) => {
    let config = {outputStyle: minify ? 'compressed' : 'expanded'};
    let ext = minify ? '.min.css' : '.css'
    var files = APP_STYLES.filter(obj => {
        return obj.type === type
    });
    var tasks = files.map(function (element) {
        return gulp.src('./'+element.src)
            .pipe(sass(config).on('error', sass.logError))
            .pipe(rename(element.name.replaceAll('.scss', '').replaceAll('.sass') + ext))
            .pipe(gulp.dest('./'+element.dst));
    });
    return merge(tasks);
}

// Compile sass admin
const compile_sass_admin = (minify = false) => {
    return compile_sass('admin', minify);
}
// Compile sass frontend
const compile_sass_frontend = (minify = false) => {
    return compile_sass('frontend', minify);
}
// Compile sass shared
const compile_sass_shared = (minify = false) => {
    return compile_sass('shared', minify);
}

const build_sass_admin_unmin = () => {
    return compile_sass_admin(false);
}
const build_sass_frontend_unmin = () => {
    return compile_sass_frontend(false);
}
const build_sass_shared_unmin = () => {
    return compile_sass_shared(false);
}
gulp.task('sass_all_unmin', gulp.parallel(build_sass_admin_unmin, build_sass_frontend_unmin, build_sass_shared_unmin));

const sassWatchUnmin = () => {
    // Init compilation before gulp starts watching...
    compile_sass_admin();
    compile_sass_frontend();
    compile_sass_shared();

    // Start watching for changes...
    gulp.watch('./assets/**/src/styles/*.scss', gulp.series('sass_all_unmin'));
}

// Compile without minification.
const compileWithoutMinify = () => {
    var bundle = (w, name, dest) => {
        var isSuccess = true;
        w.bundle()
            .on('error', function (err) {
                console.error(err);
                isSuccess = false;
                this.emit('end');
            })
            .pipe(source(name))
            .pipe(buffer())
            .pipe(sourcemaps.init({loadMaps: false}))
            .pipe(sourcemaps.write('./'))
            .pipe(gulp.dest(dest))
            .on('end', function () {
                if (isSuccess) console.log('Bundled without minification!');
            })
    }
    var bundlers = [];
    for (let i in APP_SCRIPTS) {
        bundlers[i] = watchify(browserify('./' + APP_SCRIPTS[i].src, {debug: true}).transform(babel));
    }
    var rebundle = () => {
        for (let i in APP_SCRIPTS) {
            bundle(bundlers[i], APP_SCRIPTS[i].name, APP_SCRIPTS[i].dst);
        }
    }
    bundler.on('update', function () {
        console.log('-> bundling...');
        rebundle();
    });
    rebundle();
}

// Run `bundle:unminify` to bundle unminified assets.
gulp.task('bundle:unminify', gulp.parallel(compileWithoutMinify, sassWatchUnmin));


/* Task to compile sass admin */
const sass_admin = () => {
    return compile_sass_admin(true);
}
const sass_frontend = () => {
    return compile_sass_frontend(true);
}
const sass_shared = () => {
    return compile_sass_frontend(true);
}
gulp.task('sass_all', gulp.parallel(sass_admin, sass_frontend, sass_shared));

function sassWatch() {
    // Init compilation before gulp starts watching...
    compile_sass_admin(true);
    compile_sass_frontend(true);
    compile_sass_shared(true);
    gulp.watch('./assets/**/src/styles/*.scss', gulp.series('sass_all'));
}

/* Task to watch sass changes */
gulp.task('sass:watch', sassWatch);


/* Task to compile JS */
function compile(watch) {

    var bundle = function (w, name, dest) {
        var isSuccess = true;
        w.bundle()
            .on('error', function (err) {
                console.error(err);
                isSuccess = false;
                this.emit('end');
            })
            .pipe(source(name))
            .pipe(buffer())
            .pipe(uglify())
            .pipe(sourcemaps.init({loadMaps: false}))
            .pipe(sourcemaps.write('./'))
            .pipe(rename({suffix: '.min'}))
            .pipe(gulp.dest(dest))
            .on('end', function () {
                if (isSuccess) {
                    console.log('Yay success!')
                }
            })
    };

    var bundlers = [];
    for (let i in APP_SCRIPTS) {
        bundlers[i] = watchify(browserify('./' + APP_SCRIPTS[i].src, {debug: true}).transform(babel))
    }

    function rebundle() {
        for (let i in APP_SCRIPTS) {
            bundle(bundlers[i], APP_SCRIPTS[i].name, APP_SCRIPTS[i].dst);
        }
    }

    if (watch) {
        for (let i in APP_SCRIPTS) {
            bundlers[i].on('update', function () {
                console.log('-> bundling...');
                rebundle();
            });
        }
    }
    rebundle();
}

function watch() {
    return compile(true);
}

gulp.task('build', function () {
    return compile();
});
gulp.task('watch', function () {
    return watch();
});

gulp.task('default', gulp.parallel('watch', 'sass:watch', 'bundle:unminify'));

/** Tasks for deployment */
const build_sass_admin = () => {
    return compile_sass_admin(true);
}
const build_sass_frontend = () => {
    return compile_sass_frontend(true);
}
const build_sass_shared = () => {
    return compile_sass_shared(true);
}
gulp.task('run:build:sass', gulp.parallel(build_sass_admin, build_sass_frontend, build_sass_shared));

// Bundle script without watching.
const bundleJsWithoutWatch = () => {
    return merge(APP_SCRIPTS.map(function (file) {
        return browserify({
            entries: './' + file.src,
            debug: true
        }).transform(babel)
            .bundle()
            .pipe(source(file.name))
            .pipe(buffer())
            .pipe(uglify())
            .pipe(sourcemaps.init({loadMaps: false}))
            .pipe(sourcemaps.write('./'))
            .pipe(rename({suffix: '.min'}))
            .pipe(gulp.dest(file.dst))
    }));
}

// Run build without watching: watching keeps git actions stuck on 'build'
gulp.task('run:build', gulp.parallel(bundleJsWithoutWatch, 'run:build:sass'));
