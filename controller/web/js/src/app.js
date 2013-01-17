(function(win, $) {
  'use strict';

  // Shorthand for logging easily using cl('message');
  var cl = console.log.bind(console);

  win.App = Ember.Application.create({LOG_TRANSITIONS: true});
  var App = win.App;

  ////
  // Ember Data Store and Models.
  //
  DS.RESTAdapter.configure('App.UserSummary', {
    sideloadAs: 'users'
  });

  // Plurals are used when formatting the URLs, so if you have a
  // App.CourseSummary, and you attempt to populate it using findAll(),
  // the actual request will be GET /rest/course_summaries
  DS.RESTAdapter.configure('plurals', {
    course_summary: 'course_summaries',
    user_summary: 'user_summaries'
  });

  App.Store = DS.Store.extend({
    revision: 11,
    adapter: DS.RESTAdapter.extend({
      namespace: 'rest',
    })
  });

  App.CourseSummary = DS.Model.extend({
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

  App.Course = App.CourseSummary.extend({
    users: DS.hasMany('App.UserSummary'),
    // TODO: Replace with hasOne when PR https://github.com/emberjs/data/pull/475 gets in.
    instructor: DS.hasMany('App.UserSummary'),
    didLoad: function() {
      alertify.success('Course "' + this.get('title') + '" loaded.');
    }
  });

  App.UserSummary = DS.Model.extend({
    course: DS.belongsTo('App.Course'),
    user_name: DS.attr('string'),
    password: DS.attr('string'),
    logged_in: DS.attr('boolean'),
    course_id: DS.attr('number'),
    resource_status: DS.attr('string'),
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

  App.User = App.UserSummary.extend({
    resources: DS.hasMany('App.Resource')
  });

  App.Resource = DS.Model.extend({
    key: DS.attr('string'),
    title: DS.attr('string'),
    exists: DS.attr('boolean'),
    status: DS.attr('string'),
    type: DS.attr('string'),
    user_id: DS.attr('string'),
    attribs: DS.attr('string'),
    attribsArray: function() {
      return $.parseJSON(this.get('attribs'));
    }.property('attribs'),
    css_class_resource_status: function() {
      return 'resource-status ss-folder ' + this.get('status');
    }.property('status'),
  });

  App.Job = DS.Model.extend({
    course_id: DS.attr('number'),
    type: DS.attr('string'),
    action: DS.attr('string'),
    params: DS.attr('string'),
  });

  ////
  // Controllers & Views
  //
  App.CoursesAddController = Ember.ObjectController.extend({
    // It seems like there should be a way to get the view parameter
    // without passing it from the {{action}}.
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
      // There should be a better way to commit the new record.
      var model = App.CourseSummary.createRecord(newCourse);
      model.store.commit();
      this.transitionToRoute('courses');
    }
  });
  App.CoursesAddView = Ember.View.extend({
    templateName: 'course-form',
  });

  App.CourseController = Ember.ObjectController.extend({
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
      var model = App.UserSummary.createRecord({user_name: newUserName, course_id: 1});
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

    resetUsers: function() {
      var course_id = this.get('course_id');
      var users = App.UserSummary.filter(function (data) {
        if (data.get('course_id') == course_id && data.get('user_name') != 'instructor') {
          return true;
        }
      });
      this.set('allUserSummaries', users);
      this.set('userSummariesAbove', users);
      this.set('userSummariesBelow', []);
      this.set('userSelected', []);

      var instructor = App.UserSummary.filter(function (data) {
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
  App.CourseView = Ember.View.extend({
    templateName: 'course',
    sortOptions: ['name', 'id']
  });

  App.UserSummaryController = Ember.ObjectController.extend();
  App.UserSummaryView = Ember.View.extend({
    templateName: 'user-summary',
  });

  App.UserController = Ember.ObjectController.extend({
    user_logged_in_class: 'user-logged-in',
    resources: [],

    bindResources: function(user_id) {
      var resources = App.Resource.filter(function (data) {
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

    syncUser: function(user_name, callback) {
      var job = App.Job.createRecord({
        course_id: this.controllerFor('course').get('course_id'),
        type: 'resource',
        action: 'resourceSync',
        params: JSON.stringify({
          source_user: 'instructor',
          target_users: [ user_name ]
        })
      });
      job.store.commit();
      job.on('didCreate', function(record) {
        // Artificially defer the delete callback so ember can
        // finish updating the model before we remove it.
        setTimeout(function() {
          job.deleteRecord();
          job.store.commit();
        }, 1);
        callback(null);
      });
      job.on('becameError', function(record) {
        callback('Job could not be executed.');
      });
    },

    collapseUser: function() {
      var courseController = this.controllerFor('course');
      courseController.resetUsers();
      this.transitionToRoute('course');
    }
  });
  App.UserView = Ember.View.extend({
    templateName: 'user',
    syncUser: function(user_name) {
      // I'm not super happy about this implementation, but I don't see a way
      // to target an element within the view in a clean way without creating
      // a sub-view.
      var $e = $('.ss-sync', $('#' + this.elementId));
      $e.addClass('syncing')
      this.controller.syncUser(user_name, function userSynced(err) {
        $e.removeClass('syncing')
        if (!err) {
          alertify.success("Successfully synced resources from 'instructor' to '" + user_name + "'.");
        }
        else {
          alertify.error(err);
        }
      });
    }
  });

  App.ResourceController = Ember.ObjectController.extend();
  App.ResourceView = Ember.View.extend({
    templateName: 'resource',
  });

  ////
  // Router
  //
  App.Router.map(function() {
    this.route('index', { path: '/' });
    this.route('courses', { path: '/courses' });
    this.route('coursesAdd', { path: '/courses/add' });
    this.resource('course', { path: '/courses/:course_id' }, function() {
      this.route('user', { path: '/user/:user_id' });
    });
  });

  App.IndexRoute = Ember.Route.extend({
    redirect: function() {
      this.transitionTo('courses');
    }
  });

  App.CoursesRoute = Ember.Route.extend({
    model: function() {
      return App.CourseSummary.find();
    },
    events: {
      coursesAddAction: function() {
        this.transitionTo('coursesAdd');
      }
    }
  });

  App.CoursesAddRoute = Ember.Route.extend({
    enter: function() {
      // If we navigate directly to /courses/add, we won't have a populated
      // CourseSummary store yet, which causes duplication on /courses when
      // this is saved. Workaround is to make sure that the CourseSummaries
      // are loaded here.
      App.CourseSummary.find();
    }
  });

  App.CourseRoute = Ember.Route.extend({
    setupController: function(controller, model) {
      this._super.apply(arguments);
      controller.set('content', App.Course.find(model.id));
      controller.set('course_id', model.id);
      controller.resetUsers();
    }
  });

  App.CourseUserRoute = Ember.Route.extend({
    setupController: function(controller, model) {
      this._super.apply(arguments);
      var userController = this.controllerFor('user');
      userController.set('content', App.User.find(model.id));
      userController.bindResources(model.id);

      var courseController = this.controllerFor('course');
      courseController.selectUser(model.id);

      courseController.set('userController', userController);
    }
  });

})(window, jQuery);
