// Set the require.js configuration.
require.config({

  // Initialize the application with the main application file.
  deps: ['main'],

  paths: {
    libs: '../libs',
    vendor: '../vendor',

    // Libraries.
    jquery: '../libs/jquery/jquery-1.8.3',
    ember: '../libs/ember/ember',
    'ember-data': '../libs/ember-data/ember-data',
    handlebars: '../libs/handlebars/handlebars',
    alertify: '../vendor/alertify/alertify',
    jquery_plugins: './jquery_plugins'
  },

  shim: {
    ember: {
      deps: ['jquery', 'handlebars'],
      exports: 'Ember'
    },

    'ember-data': {
      deps: ['ember'],
      exports: 'DS'
    },

    handlebars: {
      exports: 'Handlebars'
    },

    alertify: {
      exports: 'alertify'
    },

    jquery_plugins: {
      deps: ['jquery'],
      exports: 'jQuery.fn.selectText'
    }
  }
});
