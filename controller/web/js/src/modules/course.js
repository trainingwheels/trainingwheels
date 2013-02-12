/**
 * @fileoverview Course models, views, and controllers.
 */
define([
  'ember',
  'ember-data',
  'jquery',
  'alertify',
  'app',
], function(Ember, DS, $, alertify, app) {
  app.CourseSummary = DS.Model.extend({
    course_name: DS.attr('string'),
    course_type: DS.attr('string'),
    description: DS.attr('string'),
    env_type: DS.attr('string'),
    repo: DS.attr('string'),
    title: DS.attr('string'),
    uri: DS.attr('string'),
    host: DS.attr('string'),
    user: DS.attr('string'),
    pass: DS.attr('string'),
    didCreate: function() {
      alertify.success('Course "' + this.get('title') + '" created.');
    },
    becameError: function() {
      alertify.error('There was an error creating course "' + this.get('title') + '".');
    }
  });

  app.Course = app.CourseSummary.extend({
    users: DS.hasMany('App.UserSummary'),
    // TODO: Replace with hasOne when PR https://github.com/emberjs/data/pull/475 gets in.
    instructor: DS.hasMany('App.UserSummary'),
    didLoad: function() {
      alertify.success('Course "' + this.get('title') + '" loaded.');
    }
  });

  app.CoursesAddController = Ember.ObjectController.extend({
    saveCourse: function(view) {
      var newCourse = {
        title: view.get('titleTextField').get('value'),
        description: view.get('descriptionTextField').get('value'),
        course_name: view.get('nameTextField').get('value'),
        course_type: view.get('typeTextField').get('value'),
        env_type: view.get('environmentTextField').get('value'),
        repo: view.get('repositoryTextField').get('value'),
        host: view.get('hostTextField').get('value'),
        user: view.get('userTextField').get('value'),
        pass: view.get('passTextField').get('value'),
      }
      var model = app.CourseSummary.createRecord(newCourse);
      model.store.commit();
      this.transitionToRoute('courses');
    },
    cancelCourseAdd: function() {
      this.transitionToRoute('courses');
    }
  });

  app.CoursesAddView = Ember.View.extend({
    templateName: 'course-form',
  });

  app.CourseController = Ember.ObjectController.extend({
    course_id: 0,
    allUserSummaries: [],
    userSummariesAbove: [],
    userSummariesBelow: [],
    usersInFlight: [],
    userSelected: [],
    instructor: [],
    instructorSummary: [],
    instructorSelected: [],
    userController: {},

    refreshCourse: function() {
      alertify.success('Refreshing the course');
    },

    /**
     * Helper function to remove the adding throbber once all
     * users have been added or errored out.
     */
    userAdded: function(name) {
      var inFlight = this.get('usersInFlight');
      delete inFlight[inFlight.indexOf(name)];
      inFlight.compact();
      if (inFlight.toArray().length === 0) {
        this.set('adding', false);
      }
    },

    addUser: function() {
      var self = this;
      var newUserName = self.get('newUserName');

      self.set('adding', true);
      self.set('newUserName', '');

      var course_id = self.get('course_id');
      var model = app.UserSummary.createRecord({user_name: newUserName, course_id: course_id, resource_status: "resource-missing"});
      model.store.commit();
      self.get('usersInFlight').push(newUserName);
      model.on('didCreate', function() {
        self.userAdded(newUserName);
      });
      model.on('becameError', function() {
        self.userAdded(newUserName);
      });

      self.resetUsers();
      self.transitionToRoute('course');
    },

    selectUser: function(user_id) {
      var pieces = user_id.split('-');
      var user = this.get('allUserSummaries').findProperty('id', user_id);

      if (pieces[1] === 'instructor') {
        this.set('instructorSelected', this.get('instructor'));
        this.set('instructorSummary', []);

        this.set('userSummariesAbove', this.get('allUserSummaries'));
        this.set('userSummariesBelow', []);
        this.set('userSelected', []);
      }
      else {
        if (this.get('instructorSelected').get('length') > 0) {
          this.set('instructorSummary', this.get('instructor'));
          this.set('instructorSelected', []);
        }

        var index = this.get('allUserSummaries').indexOf(user);
        this.set('userSummariesAbove', this.get('allUserSummaries').slice(0, index));
        this.set('userSelected', this.get('allUserSummaries').slice(index, index + 1));
        this.set('userSummariesBelow', this.get('allUserSummaries').slice(index + 1, 10000));
      }
    },

    syncAll: function(callback) {
      var course_id = this.get('course_id');

      // Collect all of the students to have their resources synced.
      var users = app.UserSummary
        .filter(function (data) {
          if (data.get('course_id') == course_id && data.get('is_student')) {
            return true;
          }
        })
        .map(function(item, index, enumerable) {
          return item.get('user_name');
        });

      // Create the sync job.
      var job = app.Job.createRecord({
        course_id: this.controllerFor('course').get('course_id'),
        type: 'resource',
        action: 'resourceSync',
        params: JSON.stringify({
          source_user: 'instructor',
          target_users: users
        })
      });
      job.store.commit();
      job.on('didCreate', function(record) {
        app.JobComplete(job, callback);
      });
      job.on('becameError', function(record) {
        app.JobError(callback);
      });
    },

    /**
     * Helper function to reload user data from the server.
     *
     * @param {bool} instructor
     *   true if the instructor user should be reloaded.
     *   Defaults to true.
     */
    reloadUsers: function(instructor, callback, errorCallback) {
      var users;
      var course_id = this.get('course_id');

      if (typeof instructor === 'undefined') {
        instructor = true;
      }

      // Find the already loaded users so we can reload them.
      var users = app.User.filter(function(data) {
        if (data.get('course_id') != course_id) {
          return false;
        }

        if (!instructor && data.get('user_name') === 'instructor') {
          return false;
        }

        return true;
      });

      var models = users.toArray();
      var model = this.get('model');
      models.push(model);
      var promise = app.reloadModels(models);
      $.when(promise).then(callback, errorCallback);
    },

    resetUsers: function() {
      var course_id = this.get('course_id');
      var users = app.UserSummary.filter(function (data) {
        if (data.get('course_id') == course_id && data.get('user_name') != 'instructor') {
          return true;
        }
      });
      this.set('allUserSummaries', users);
      this.set('userSummariesAbove', users);
      this.set('userSummariesBelow', []);
      this.set('userSelected', []);

      var instructor = app.UserSummary.filter(function (data) {
        if (data.get('user_name') == 'instructor' && data.get('course_id') == course_id) {
          return true;
        }
      });
      this.set('instructor', instructor);
      this.set('instructorSummary', instructor);
      this.set('instructorSelected', []);
    },

    // This collapses all the displayed users and returns to /courses/x
    returnToCourse: function() {
      this.resetUsers();
      this.transitionToRoute('course');
    }
  });

  app.CourseView = Ember.View.extend({
    templateName: 'course',
    sortOptions: ['name', 'id'],

    css_class_syncing: function() {
      return 'sync-wrapper' + (this.get('syncing') ? ' syncing' : '');
    }.property('syncing'),

    syncAll: function(user_name) {
      var self = this;
      alertify.confirm(app.globalStrings.confirmSyncAll, function confirmedSync(e) {
        if (e) {
          self.set('syncing', true);

          self.controller.syncAll(function usersSynced(err) {
            if (!err) {
              self.controller.reloadUsers(false,
                function usersReloaded() {
                  self.set('syncing', false);
                  alertify.success("Successfully synced resources from 'instructor' to all users.");
                },
                function reloadError() {
                  self.set('syncing', false);
                  alertify.error("There was a problem syncing resources to one or more users.");
                }
              );
            }
            else {
              self.set('syncing', false);
              alertify.error(err);
            }
          });
        }
      });
    }
  });
});
