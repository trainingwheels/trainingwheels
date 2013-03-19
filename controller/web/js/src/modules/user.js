/**
 * @fileoverview User models, views, and controllers.
 */
define([
  'ember',
  'ember-data',
  'jquery',
  'alertify',
  'app',
  'jquery_plugins'
], function(Ember, DS, $, alertify, app) {
  app.UserSummary = DS.Model.extend({
    course: DS.belongsTo('App.Course'),
    user_name: DS.attr('string'),
    password: DS.attr('string'),
    logged_in: DS.attr('boolean'),
    course_id: DS.attr('number'),
    resource_status: DS.attr('string'),
    is_student: function() {
      return this.get('user_name') !== 'instructor';
    }.property('user_name'),
    css_class_login_status: function() {
      return 'user-login-status ss-user ' + (this.get('logged_in') ? 'logged_in' : 'logged_out');
    }.property('logged_in'),
    css_class_resource_overview_status: function() {
      return 'resource-status ss-folder ' + this.get('resource_status');
    }.property('resource_status'),
    didCreate: function() {
      alertify.success('User "' + this.get('user_name') + '" created.');
    },
    becameError: function() {
      alertify.error('There was an error creating user "' + this.get('user_name') + '".');
    }
  });

  app.User = app.UserSummary.extend({
    resources: DS.hasMany('App.Resource')
  });

  app.UserSummaryController = Ember.ObjectController.extend();

  app.UserSummaryView = Ember.View.extend({
    templateName: 'user-summary'
  });

  app.UserState = Ember.StateManager.extend({
    enableLogging: true,
    controller: null,
    initialState: 'root.loaded',
    states: {
      root: Ember.State.create({
        loaded: Ember.State.create({
          setup: function(manager) {
            manager.get('controller').set('syncing', false);
          }
        }),
        sync: Ember.State.create({
          setup: function(manager) {
            manager.get('controller').set('syncing', true);
          },
          reloading: Ember.State.create({
            didReload: Ember.State.create({
              setup: function(manager) {
                alertify.success("Successfully synced resources from 'instructor' to '" + manager.get('controller.user_name') + "'.");
                manager.get('controller').set('syncing', false);
                manager.transitionTo('root.loaded');
              }
            }),
            becameError: Ember.State.create({
              setup: function(manager) {
                alertify.error('The sync job was successful, but the reload failed.');
                manager.get('controller').set('syncing', false);
              }
            })
          }),
          becameError: Ember.State.create({
            setup: function(manager) {
              alertify.error('The sync job was unsuccessful');
              manager.get('controller').set('syncing', false);
            }
          })
        }),
        reloading: Ember.State.create({
          didReload: Ember.State.create(),
          becameError: Ember.State.create()
        })
      })
    }
  }),

  app.CourseUserController = Ember.ObjectController.extend({
    needs: 'course',
    user_logged_in_class: 'user-logged-in',
    resources: [],
    // An instance of App.UserState, set by the router.
    stateManager: null,
    syncing: false,

    bindResources: function(user_id) {
      var self = this;
      var resources = app.Resource.filter(function (data) {
        if (data.get('user_id') == user_id) {
          return true;
        }
      });
      this.set('resources', resources);
    },

    copyPassword: function(password) {
      alertify.alert('<div id="selected-password">' + password + '</div>');
      setTimeout(function () { $('#selected-password').selectText(); }, 50);
    },

    // Reloading a user may be done by a 'refresh' task or when the user has
    // it's resources sync'd from the instructor.
    reloadModels: [],
    reloadModelsError: false,
    reloadUser: function() {
      var self = this;
      this.stateManager.transitionTo('reloading');
      this.set('reloadModels', []);
      this.set('reloadModelsError', false);
      this.reloadModels.pushObject(this.get('model'));
      this.reloadModels.pushObject(this.get('controllers.course.content'));

      var count = this.reloadModels.length;
      this.reloadModels.forEach(function(model) {
        model.on('didReload', function() {
          if (count === 1 && self.get('reloadModelsError') === false) {
            self.get('stateManager').transitionTo('didReload');
          }
          count = count - 1;
        });
        model.on('becameError', function() {
          self.set('reloadModelsError', true);
          self.get('stateManager').transitionTo('becameError');
        });
        model.reload();
      });
    },

    syncUser: function() {
      var self = this;
      this.get('stateManager').transitionTo('root.sync');

      var job = app.Job.createRecord({
        course_id: this.get('controllers.course.course_id'),
        type: 'resource',
        action: 'resourceSync',
        params: JSON.stringify({
          source_user: 'instructor',
          target_users: [ this.get('user_name') ]
        })
      });
      job.store.commit();
      job.on('didCreate', function(record) {
        self.reloadUser();

        // Artificially defer the delete so Ember can
        // finish updating the model before we remove it.
        setTimeout(function() {
          job.deleteRecord();
          job.store.commit();
        }, 1);
      });
      job.on('becameError', function(record) {
        self.get('stateManager').transitionTo('becameError');
      });
    },

    collapseUser: function() {
      this.get('controllers.course').resetUsers();
      this.transitionToRoute('course');
    }
  });

  app.CourseUserView = Ember.View.extend({
    templateName: 'user',

    css_class_syncing: function() {
      return 'sync-wrapper' + (this.get('controller.syncing') ? ' syncing' : '');
    }.property('controller.syncing'),

    syncUser: function() {
      var self = this;
      alertify.confirm(app.globalStrings.confirmSync, function syncConfirmed(e) {
        if (e) {
          self.get('controller').syncUser();
        }
      });
    }
  });
});
