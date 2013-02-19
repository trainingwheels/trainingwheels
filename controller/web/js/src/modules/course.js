/**
 * @fileoverview Course models, views, and controllers.
 */
define([
  'ember',
  'ember-data',
  'jquery',
  'alertify',
  'app'
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
    /**
     * Helper to disable the submit button if the form is invalid.
     *
     * @return
     *   true if the form is invalid, else false.
     */
    form_is_invalid: function() {
      if (!this.get('titleValid') || !this.get('courseNameValid')) {
        return true;
      }

      return false;
    }.property(
      'titleValid',
      'courseNameValid'
    ),

    /**
     * Title field and helper properties.
     */
    title: null,
    titleValid: true,
    titleErrors: [],
    css_class_title: function() {
      return 'field' + (this.get('titleValid') ? '' : ' invalid clearfix');
    }.property('titleValid'),
    validateTitle: function() {
      this.set('titleValid', true);
      this.set('titleErrors', []);

      if (this.get('title') === null || this.get('title').length === 0) {
        this.set('titleValid', false);
        this.get('titleErrors').push('The course title is required.');
        return;
      }
    }.observes('title'),

    /**
     * Description.
     */
    description: null,

    /**
     * Course name field and helper properties.
     */
    courseName: null,
    courseNameValid: true,
    courseNameErrors: [],
    css_class_short_name: function() {
      return 'field' + (this.get('courseNameValid') ? '' : ' invalid clearfix');
    }.property('courseNameValid'),
    validateShortName: function() {
      this.set('courseNameValid', true);
      this.set('courseNameErrors', []);

      // Bail if the field is empty...
      if (this.get('courseName') === null || this.get('courseName').length === 0) {
        this.set('courseNameValid', false);
        this.get('courseNameErrors').push('The course short name is required.');
        return;
      }

      // Course short names are limited to 11 characters because
      // of MySQL's 16 character user name limit. When we create
      // the mysql user it will be course_name + '_UNIX_UID' where
      // UNIX_UID is a four digit number (i.e. 1001).
      if (this.get('courseName').length > 11) {
        this.set('courseNameValid', false);
        this.get('courseNameErrors').push('Course short names cannot be more than 11 characters long.');
      }

      // Ensure that the course name contains only letters and underscores.
      if (!this.get('courseName').match(/^\w+$/)) {
        this.set('courseNameValid', false);
        this.get('courseNameErrors').push('Course short names can only contain letters, numbers, and underscores.');
      }
    }.observes('courseName'),

    /**
     * Course type.
     */
    courseType: null,

    /**
     * Environment type. We do want to ultimately support multiple environments,
     * but right now, Ubuntu is the only option. Hide this from the user.
     */
    envType: 'ubuntu',

    /**
     * Plugins.
     */
    plugins: [],

    /**
     * Bundles.
     */
    bundles: [],

    /**
     * Repository.
     */
    repo: 'https://github.com/fourkitchens/trainingwheels-drupal-files-example.git',

    /**
     * Host.
     */
    host: 'localhost',

    /**
     * User.
     */
    user: null,

    /**
     * Pass.
     */
    pass: null,

    /**
     * Confirms the form is valid and if so submits, creating a new course.
     */
    saveCourse: function(view) {
      // Prevent saving the course if the form is invalid.
      this.validateTitle();
      this.validateShortName();
      if (this.get('form_is_invalid')) {
        alertify.error('The course form contains invalid data. Double check your settings.');
        return;
      }
      var newCourse = {
        title: this.get('title'),
        description: this.get('description'),
        course_name: this.get('courseName'),
        course_type: this.get('courseType'),
        env_type: this.get('envType'),
        repo: this.get('repo'),
        host: this.get('host'),
        user: this.get('user'),
        pass: this.get('pass')
      };
      var model = app.CourseSummary.createRecord(newCourse);
      model.store.commit();
      this.transitionToRoute('courses');
    },

    cancelCourseAdd: function() {
      this.transitionToRoute('courses');
    },

    toggleBundle: function(bundle) {
    },

    togglePlugin: function(plugin, remove) {
      if (typeof remove === 'undefined') {
        remove = true;
      }
      this.get('plugins').find(function(item, index, enumerable) {
        if (item.get('pluginClass') == plugin.get('pluginClass')) {
          if (remove) {
            item.set('enabled', !item.get('enabled'));
          }
          else if (!remove && !item.get('enabled')) {
            item.set('enabled', true);
          }
          return true;
        }
        return false;
      });
    }
  });

  app.CoursesAddView = Ember.View.extend({
    templateName: 'course-form',

    courseFormNext: function() {
      var $activeSection = $('.course-section.active');
      var $nextSection = $activeSection.next();
      var $activeNav = $('.course-form-nav-item.active');
      var $nextNav = $activeNav.next();
      $activeSection.removeClass('active');
      $nextSection.addClass('active');
      $activeNav.removeClass('active');
      $nextNav.addClass('active');
    },

    courseFormPrevious: function() {
      var $activeSection = $('.course-section.active');
      var $nextSection = $activeSection.prev();
      var $activeNav = $('.course-form-nav-item.active');
      var $nextNav = $activeNav.prev();
      $activeSection.removeClass('active');
      $nextSection.addClass('active');
      $activeNav.removeClass('active');
      $nextNav.addClass('active');
    }
  });

  app.CourseController = Ember.ObjectController.extend({
    course_id: 0,
    allUserSummaries: [],
    userSummariesAbove: [],
    userSummariesBelow: [],
    userSelected: [],
    instructor: [],
    instructorSummary: [],
    instructorSelected: [],
    userController: {},

    refreshCourse: function() {
      alertify.success('Refreshing the course');
    },

    addUser: function() {
      var newUserName = this.get('newUserName');
      this.set('newUserName', '');
      var course_id = this.get('course_id');
      var model = app.UserSummary.createRecord({user_name: newUserName, course_id: course_id, resource_status: "resource-missing"});
      model.store.commit();
      this.resetUsers();
      this.transitionToRoute('course');
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
      users = app.User.filter(function(data) {
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
