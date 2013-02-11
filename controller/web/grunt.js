/*global module:false*/
module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
    clean: ['dist/'],

    lint: {
      files: ['grunt.js', 'js/src']
    },

    jshint: {
      options: {
        scripturl: true
      }
    },

    concat: {
      dist: {
        src: [
          'js/vendor/almond/almond.js',
          'dist/debug/require.js'
        ],
        dest: 'dist/debug/require.js',
        separator: ';'
      }
    },

    // This task uses the MinCSS Node.js project to take all your CSS files in
    // order and concatenate them into a single CSS file named index.css.  It
    // also minifies all the CSS as well.  This is named index.css, because we
    // only want to load one stylesheet in index.html.
    mincss: {
      'dist/release/index.css': [
        'css/alertify.css',
        'dist/release/style.css',
        'fonts/ss-symbolicons-block/ss-symbolicons-block.css',
        'fonts/ss-standard/ss-standard.css'
      ]
    },

    compass: {
      dev: {
        src: 'sass',
        dest: 'dist/debug',
        linecomments: true,
        forcecompile: true,
        requre: [
          'aurora',
          'animation'
        ],
        debugsass: true,
        images: 'images',
        relativeassets: true
      },
      prod: {
        src: 'sass',
        dest: 'dist/release',
        outputstyle: 'compressed',
        linecomments: false,
        forcecompile: true,
        requre: [
          'aurora',
          'animation'
        ],
        debugsass: false,
        images: 'images',
        relativeassets: true
      }
    },

    min: {
      'dist/release/require.js': [
        'dist/debug/require.js'
      ]
    },

    requirejs: {
      options: {
        mainConfigFile: 'js/src/config.js',
        out: 'dist/debug/require.js',

        // Root application module.
        name: 'config',

        // Do not wrap everything in an IIFE.
        wrap: false,

        optimize: 'none',

        paths: {
          handlebars: '../vendor/handlebars/handlebars-1.0.rc.1'
        }
      }
    },

    watch: {
      compass: {
        files: ['grunt.js', 'sass/**/*.scss'],
        tasks: 'compass:dev compass:prod mincss'
      },
      requirejs: {
        files: ['grunt.js', 'js/src/**/*.js'],
        tasks: 'lint requirejs concat min'
      }
    },

    uglify: {}
  });

  grunt.loadNpmTasks('grunt-compass');
  // @see https://github.com/backbone-boilerplate/grunt-bbb/issues/84
  grunt.loadNpmTasks('grunt-contrib-handlebars');
  grunt.loadNpmTasks('grunt-contrib-mincss');
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-requirejs');

  // Default task.
  grunt.registerTask('default', 'lint concat min');

  grunt.registerTask('debug', 'clean lint requirejs concat compass:dev');

  grunt.registerTask('release', 'debug compass:prod min mincss');
};
