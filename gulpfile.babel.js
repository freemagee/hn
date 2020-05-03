/* eslint no-console: 0 */
import gulp from "gulp";
// POST CSS
import postcss from "gulp-postcss";
import cssnano from "cssnano";
import fontMagician from "postcss-font-magician";
import postcssPresetEnv from "postcss-preset-env";
import tailwindcss from "tailwindcss";
// Define other utilities
import notify from "gulp-notify";
import plumber from "gulp-plumber";
import colors from "ansi-colors";
import beeper from "beeper";
import rename from "gulp-rename";
import browserSync from "browser-sync";

// Browsersync init
browserSync.create();
// Common paths
const basePaths = {
  src: "./src/",
  dest: "./static/",
  root: "./",
};
const paths = {
  styles: {
    src: `${basePaths.src}tailwind`,
    files: `${basePaths.src}tailwind/*.css`,
    dest: `${basePaths.dest}css`,
  },
  tailwind: {
    config: `${basePaths.root}tailwind.config.js`,
  },
  twig: {
    templates: `${basePaths.dest}twig/**/*.html.*`,
    html: `${basePaths.dest}twig/**/*.html`,
  },
};
// Error handler
// Heavily inspired by: https://github.com/mikaelbr/gulp-notify/issues/81#issuecomment-100422179
const reportError = function reportErrorFn(error) {
  const messageOriginal = error.messageOriginal ? error.messageOriginal : "";

  notify({
    title: `Task Failed [${error.plugin}]`,
    message: messageOriginal,
    sound: "Sosumi", // See: https://github.com/mikaelbr/node-notifier#all-notification-options-with-their-defaults
  }).write(error);

  beeper(); // Beep 'sosumi' again

  // Inspect the error object
  // console.log(error);

  // Easy error reporting
  // console.log(error.toString());

  // Pretty error reporting
  let report = "";
  const chalk = colors.white.bgRed;

  report += `${chalk("TASK:")} [${error.plugin}]\n`;

  if (error.file) {
    report += `${chalk("FILE:")} ${error.file}\n`;
  }

  if (error.line) {
    report += `${chalk("LINE:")} ${error.line}\n`;
  }

  report += `${chalk("PROB:")} ${error.message}\n`;

  console.error(report);

  // Prevent the 'watch' task from stopping
  this.emit("end");
};
// A change event function, displays which file changed
const changeEvent = (path, type) => {
  const filename = path.split("\\").pop();
  notify(`[watcher] File ${filename} was ${type}, compiling...`).write("");
};

// SASS
// =============================================================================
function styles() {
  const sassConfig = {
    outputStyle: "expanded",
  };
  const fontMagicianConfig = {
    variants: {
      Ubuntu: {
        "400": ["woff2"],
        "400 italic": ["woff2"],
        "700": ["woff2"],
      },
      "Ubuntu Mono": {
        "400": ["woff2"],
      },
    },
    foundries: "google",
    protocol: "https:",
    display: "swap",
  };
  const processors = [
    tailwindcss(),
    fontMagician(fontMagicianConfig),
    postcssPresetEnv(),
    cssnano(),
  ];

  // Taking the path from the paths object
  return (
    gulp
      .src(paths.styles.files)
      // Deal with errors, but prevent Gulp from stopping
      .pipe(
        plumber({
          errorHandler: reportError,
        })
      )
      // Process with PostCSS - autoprefix & minify
      .pipe(postcss(processors))
      // Rename file in stream
      .pipe(
        rename(function(path) {
          // Updates the object in-place
          path.basename = "styles";
        })
      )
      // Finally output a css file
      .pipe(gulp.dest(paths.styles.dest))
      // Inject into browser
      .pipe(
        browserSync.stream({
          match: "**/*.css",
        })
      )
  );
}
const processStyles = gulp.series(styles);
processStyles.description = "Pre-process CSS";
gulp.task("processStyles", processStyles);

// WATCH
// =============================================================================
function watchFiles() {
  gulp
    .watch(
      [
        paths.styles.files,
        paths.tailwind.config,
        paths.twig.templates,
        paths.twig.html,
      ],
      {
        delay: 300,
      },
      gulp.series("processStyles")
    )
    .on("change", (evt) => {
      changeEvent(evt, "changed");
    });
}
const watch = gulp.series(watchFiles);
watch.description = "Keep an eye on asset changes";
gulp.task("watch", watch);

// SERVE
// =============================================================================
const startServer = () => {
  browserSync.init({
    proxy: "http://hn.localhost/",
  });
};
const serve = gulp.series(startServer);
serve.description = "Start a browser sync session mapped to the localhost port";
gulp.task("serve", serve);

// DEVELOP
// =============================================================================
gulp.task("develop", gulp.parallel(serve, watch));

// DEFAULT - does nothing!
// =============================================================================
gulp.task(
  "default",
  () =>
    new Promise((resolve) => {
      const chalk = colors.white.bgBlue;
      const message = `${chalk("Action:")} for task information type gulp -T`;

      console.log(message);
      resolve();
    })
);
