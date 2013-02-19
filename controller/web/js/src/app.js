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

  // Fetch and permanantly store the plugins definitions for
  // easy form building.
  $.ajax(
    '/rest/course_build',
    {
      success: function(data, textStatus, jqXHR) {
        if (jqXHR.status === 200) {
          // Create arrays from the JSON objects so handlebars can iterate over
          // the plugins, bundles, and resources correctly.
          var plugins = Ember.Object.create(data.plugins);
          plugins.A = $.map(data.plugins, function(plugin, pluginClass) {
            plugin.pluginClass = pluginClass;
            return plugin;
          });
          var bundles = Ember.Object.create(data.bundles);
          bundles.A = $.map(data.bundles, function(bundle, bundleClass) {
            bundle.bundleClass = bundleClass;
            return bundle;
          });
          var resources = Ember.Object.create(data.resources);
          resources.A = $.map(data.resources, function(resource, resourceClass) {
            resource.resourceClass = resourceClass;
            return resource;
          });
          Ember.set(app, 'courseBuild', Ember.Object.create({
            plugins: plugins,
            bundles: bundles,
            resources: resources
          }));
        }
        else {
          throw new Error('Unable to fetch course build information.');
        }
      },
      error: function(jqXHR, textStatus, errorThrown) {
        throw new Error('Unable to fetch course build information.');
      }
    }
  );

  return app;
});
