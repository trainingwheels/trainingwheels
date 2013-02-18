/*global module:false*/
module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
    clean: ['dist/'],

    jshint: {
      files: ['Gruntfile.js', 'js/src/**/*.js'],
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

    // This task uses the cssmin Node.js project to take all your CSS files in
    // order and concatenate them into a single CSS file named index.css.  It
    // also minifies all the CSS as well.  This is named index.css, because we
    // only want to load one stylesheet in index.html.
    cssmin: {
      'dist/release/index.css': [
        'css/alertify.css',
        'dist/release/style.css'
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
        debugsass: false,
        images: 'images',
        fonts: 'fonts',
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
        fonts: 'fonts',
        relativeassets: true
      }
    },

    uglify: {
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
        files: ['Gruntfile.js', 'sass/**/*.scss'],
        tasks: 'compass:dev compass:prod mincss'
      },
      requirejs: {
        files: ['Gruntfile.js', 'js/src/**/*.js'],
        tasks: 'jshint requirejs concat uglify'
      }
    }

  });

  grunt.loadNpmTasks('grunt-contrib-compass');
  // @see https://github.com/backbone-boilerplate/grunt-bbb/issues/84
  grunt.loadNpmTasks('grunt-contrib-handlebars');
  grunt.loadNpmTasks('grunt-contrib-cssmin');
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-requirejs');
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-concat');

  // Default task.
  grunt.registerTask('default', ['jshint', 'concat', 'uglify']);

  grunt.registerTask('debug', ['clean', 'jshint', 'requirejs', 'concat', 'compass:dev']);

  grunt.registerTask('release', ['debug', 'compass:prod', 'uglify', 'cssmin']);
};
