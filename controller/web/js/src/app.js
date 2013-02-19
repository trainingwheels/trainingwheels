/**
 * @fileoverview Training Wheels Ember.js application instantiation.
 */
define(['ember', 'jquery'], function(Ember, $) {
  var app = Ember.Application.create({LOG_TRANSITIONS: true});

  // Expose the app to the global namespace to make Ember happy.
  window.App = app;

  app.globalStrings = {
    confirmSync: 'Are you sure you want to sync resources to this user? This will overwrite any changes the user has made to their environment.',
    confirmSyncAll: 'Are you sure you want to sync resources to all users? This will overwrite any changes users have made to their environments.'
  };

  /**
   * Helper function to reload an array of models.
   *
   * @param {array} models
   *   An array of modesl to be reloaded.
   * @return {object} a jQuery promise object.
   */
  app.reloadModels = function(models) {
    var def = $.Deferred();
    var promises = [];

    models.forEach(function(model) {
      var p = $.Deferred();
      promises.push(p);
      model.on('didReload', function() {
        p.resolve();
      });
      model.on('becameError', function() {
        p.reject();
      });
      model.reload();
    });

    $.when.apply($, promises).then(
      function() {
        def.resolve();
      },
      function() {
        def.reject();
      }
    );

    return def.promise();
  };

  app.loadBuild = function() {
    var def = $.Deferred();
    // Fetch and permanantly store the plugins definitions for
    // easy form building.
    $.ajax(
      '/rest/course_build',
      {
        success: function(data, textStatus, jqXHR) {
          if (jqXHR.status === 200) {
            Ember.set(app, 'courseBuild', Ember.Object.create(data));
            def.resolve();
          }
          else {
            def.reject();
            throw new Error('Unable to fetch course build information.');
          }
        },
        error: function(jqXHR, textStatus, errorThrown) {
          def.reject();
          throw new Error('Unable to fetch course build information.');
        }
      }
    );

    return def.promise();
  };

  return app;
});
