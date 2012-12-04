(function(win, $) {
  'use strict';

  // Shorthand for logging easily using cl('message');
  var cl = console.log.bind(console);

  win.App = Ember.Application.create();
  var App = win.App;

  ////
  // Ember Data
  //
  App.store = DS.Store.create({
    revision: 8,
    adapter: DS.RESTAdapter.create({
      bulkCommit: false,
      namespace: 'rest',
      // Plurals are used when formatting the URLs, so if you have a
      // App.CourseSummary, and you attempt to populate it using findAll(),
      // the actual request will be GET /rest/course_summaries
      plurals: {
        course_summary: 'course_summaries',
      },
      // Only required on the main key for the request, so /rest/courses/1
      // shoud return { courses: [{}] }, the first course in an array with
      // the key mapping here to the type.
      mappings: {
        courses: 'App.Course',
        users: 'App.User'
      }
    })
  });

  App.CourseSummary = DS.Model.extend({
    course_name: DS.attr('string'),
    course_type: DS.attr('string'),
    description: DS.attr('string'),
    env_type: DS.attr('string'),
    repo: DS.attr('string'),
    title: DS.attr('string'),
    uri: DS.attr('string')
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
    user_name: DS.attr('string'),
    password: DS.attr('string'),
    logged_in: DS.attr('boolean'),
    course_id: DS.attr('number')
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
    }.property('attribs')
  })

  ////
  // Controllers & Views
  //
  App.ApplicationController = Ember.Controller.extend();
  App.ApplicationView = Ember.View.extend({
    templateName: 'application'
  });

  App.CoursesController = Ember.ArrayController.extend();
  App.CoursesView = Ember.View.extend({
    templateName: 'courses'
  });

  App.CourseController = Ember.ObjectController.extend({
    course_id: 0,
    allUsers: [],
    usersAbove: [],
    userSelected: [],
    usersBelow: [],
    instructor: [],

    refreshCourse: function() {
      alertify.success('Refreshing the course');
    },

    addUser: function() {
      App.store.createRecord(App.UserSummary, {user_name: "newuser", course_id: 1});
      alertify.success('Adding a user');
    },

    selectUser: function(user_id) {
      var user = this.get('allUsers').findProperty('id', user_id);
      var index = this.get('allUsers').indexOf(user);

      this.set('usersAbove', this.get('allUsers').slice(0, index));
      this.set('userSelected', this.get('allUsers').slice(index, index + 1));
      this.set('usersBelow', this.get('allUsers').slice(index + 1, 10000));
    },

    bindUsers: function(course_id) {
      var users = App.store.filter(App.UserSummary, function (data) {
        if (data.get('course_id') == course_id && data.get('user_name') != 'instructor') {
          return true;
        }
      });
      this.set('allUsers', users);
      this.set('usersAbove', users);

      var instructor = App.store.filter(App.UserSummary, function (data) {
        if (data.get('user_name') == 'instructor' && data.get('course_id') == course_id) {
          return true;
        }
      });
      this.set('instructor', instructor);

      // Need to save the course id to the controller for the purposes of
      // deserializing the nested user path later down the line.
      this.set('course_id', course_id);
    }
  });
  App.CourseView = Ember.View.extend({
    templateName: 'course',
    sortOptions: ['name', 'id']
  });

  App.UserSummaryController = Ember.ObjectController.extend({
  });
  App.UserSummaryView = Ember.View.extend({
    templateName: 'user-summary',
    css_class_login_status: 'user-login-status'
  });

  App.UserController = Ember.ObjectController.extend({
    user_logged_in_class: 'user-logged-in',
    resources: [],

    bindResources: function(user_id) {
      var resources = App.store.filter(App.Resource, function (data) {
        if (data.get('user_id') == user_id) {
          return true;
        }
      });
      this.set('resources', resources);
    },

    copyPassword: function(event) {
      alertify.alert('<div id="selected-password">' + event.context + '</div>');
      setTimeout(function () { $('#selected-password').selectText(); }, 50);
    }
  });
  App.UserView = Ember.View.extend({
    templateName: 'user',
    css_class_login_status: 'user-login-status'
  });

  App.ResourceController = Ember.ObjectController.extend();
  App.ResourceView = Ember.View.extend({
    templateName: 'resource',
    css_class_resources_status: 'resource-status',
  });

  ////
  // Router
  //
  App.Router = Ember.Router.extend({
    enableLogging: true,

    goHome: Ember.Route.transitionTo('courses'),
    showCourse: Ember.Route.transitionTo('course.coursePage.index'),

    // #/
    root: Ember.Route.extend({

      // Going to the home page currently redirects you to the list of courses.
      index: Ember.Route.extend({
        route: '/',
        redirectsTo: 'courses'
      }),

      // #/courses
      courses: Ember.Route.extend({
        route: '/courses',

        connectOutlets: function(router) {
          router.get('applicationController').connectOutlet('courses', App.store.findAll(App.CourseSummary));
        }
      }),

      // #/course
      course: Ember.Route.extend({
        route: '/course',

        index: Ember.Route.extend({
          route: '/',
          redirectsTo: 'courses'
        }),

        // #/course/1
        coursePage: Ember.Route.extend({
          route: '/:course_id',

          index: Ember.Route.extend({
            route: '/'
          }),

          showUser: Ember.Route.transitionTo('course.coursePage.userSelected'),

          connectOutlets: function(router, context) {
            var course_id = context.id;
            // When we .find() a course, the hasMany relationships in the store
            // will auto-fill the users summaries, so we can just use .filter() below
            // when we load the users, which doesn't trigger another request.
            // Remember that the data store is not materialized right here, the requests
            // are async and once the data enters the store, the front end is updated. Try
            // this in the console if you want to look at the data:
            // App.store.filter(App.User, function(data) { return true; } ).objectAt(0).toData()
            var course = App.store.find(App.Course, course_id);
            router.get('applicationController').connectOutlet('course', course);
            var courseController = router.get('courseController');
            courseController.bindUsers(course_id);
          },

          // #/course/1/user/bobby
          userSelected: Ember.Route.extend({
            route: '/user/:user_name',
            connectOutlets: function(router, context) {
              var user = App.store.find(App.User, context.id);
              var courseController = router.get('courseController');
              courseController.selectUser(context.id);
              var userController = router.get('userController');
              userController.bindResources(context.id);
            },
            serialize: function(router, user) {
              return {
                user_name: user.get('user_name')
              }
            },
            deserialize: function(router, urlParams) {
              var user_id = router.get('courseController').get('course_id') + '-' + urlParams.user_name;
              return App.store.find(App.User, user_id);
            }
          })
        })
      }),
    })
  })

  App.initialize();

})(window, jQuery);
