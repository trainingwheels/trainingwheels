define(['ember'], function(Ember) {
  Ember.globalStrings = {
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
  Ember.reloadModels = function(models) {
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

  return Ember;
});
