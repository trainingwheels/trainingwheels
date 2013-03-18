/*global module:false*/
module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
    clean: ['dist/'],

    jshint: {
      options: {
        scripturl: true
      },
      prod: {
        files: {
          src: ['Gruntfile.js', 'js/src/**/*.js']
        }
      },
      dev: {
        options: {
          debug: true
        },
        files: {
          src: ['Gruntfile.js', 'js/src/**/*.js']
        }

      }
    },

    concat: {
      options: {
        separator: ';'
      },
      dev: {
        src: [
          'js/vendor/almond/almond.js',
          'dist/debug/require.js'
        ],
        dest: 'dist/debug/require.js'
      },
      prod: {
        src: [
          'js/vendor/almond/almond.js',
          'dist/release/require.js'
        ],
        dest: 'dist/release/require.js'
      }
    },

    cssmin: {
      compress: {
        files: {
          'dist/release/index.css': [
            'css/alertify.css',
            'dist/release/style.css'
          ]
        }
      }
    },

    compass: {
      options: {
        sassDir: 'sass',
        require: [
          'aurora',
          'animation'
        ],
        force: true,
        imagesDir: 'images',
        fontsDir: 'fonts',
        relativeAssets: true
      },
      dev: {
        options: {
          cssDir: 'dist/debug',
          outputStyle: 'expanded',
          noLineComments: false
        }
      },
      prod: {
        options: {
          cssDir: 'dist/release',
          outputStyle: 'compressed',
          noLineComments: true
        }
      }
    },

    uglify: {
      dist: {
        files: {
          'dist/release/require.js': ['dist/release/require.js']
        }
      }
    },

    requirejs: {
      options: {
        mainConfigFile: 'js/src/config.js',

        // Root application module.
        name: 'config',

        // Do not wrap everything in an IIFE.
        wrap: false,

        // We do uglifying as a separate step.
        optimize: 'none',

        paths: {
          handlebars: '../vendor/handlebars/handlebars'
        }
      },
      dev: {
        options: {
          out: 'dist/debug/require.js',
          // For debugging purposes, this makes the files appear separately
          // in Chrome.
          useSourceUrl: true
        }
      },
      prod: {
        options: {
          out: 'dist/release/require.js',
          useSourceUrl: false
        }
      }
    },

    watch: {
      compass: {
        files: ['sass/**/*.scss'],
        tasks: ['compass:dev']
      },
      requirejs: {
        files: ['js/src/**/*.js'],
        tasks: ['jshint:dev', 'requirejs:dev', 'concat:dev']
      },
      // When Gruntfile.js changes, we don't know whether we should run compass
      // or requirejs tasks, so do both.
      gruntfile: {
        files: ['Gruntfile.js'],
        tasks: ['compass:dev', 'compass:prod', 'cssmin', 'requirejs:dev', 'concat:dev']
      }
    }
  });

  // Load Plugins.
  grunt.loadNpmTasks('grunt-contrib-compass');
  grunt.loadNpmTasks('grunt-contrib-handlebars');
  grunt.loadNpmTasks('grunt-contrib-cssmin');
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-requirejs');
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-concat');

  // Our custom tasks.
  grunt.registerTask('debug', ['clean', 'jshint:dev', 'requirejs:dev', 'concat:dev', 'compass:dev']);
  grunt.registerTask('release', ['debug', 'jshint:prod', 'requirejs:prod', 'concat:prod', 'compass:prod', 'uglify', 'cssmin']);

  // Default task that is run when no arguments are passed.
  grunt.registerTask('default', ['release']);
};
