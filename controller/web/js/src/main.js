/**
 * @fileoverview Main Training Wheels application entry point.
 */
require([
  'ember',
  'ember-data',
  'jquery',
  'handlebars',
  'app',
  'modules/job',
  'modules/resource',
  'modules/user',
  'modules/course',
  'modules/course_add'
], function(Ember, DS, $, Handlebars, app) {
  ////
  // Ember Data Store.
  //
  DS.RESTAdapter.configure('App.UserSummary', {
    sideloadAs: 'users'
  });

  // Plurals are used when formatting the URLs, so if you have a
  // app.Course.CourseSummary, and you attempt to populate it using findAll(),
  // the actual request will be GET /rest/course_summaries
  DS.RESTAdapter.configure('plurals', {
    course_summary: 'course_summaries',
    user_summary: 'user_summaries'
  });

  app.Store = DS.Store.extend({
    revision: 11,
    adapter: DS.RESTAdapter.extend({
      namespace: 'rest'
    })
  });

  app.Router.map(function() {
    this.route('index', { path: '/' });
    this.route('courses', { path: '/courses' });
    this.route('coursesAdd', { path: '/courses/add' });
    this.resource('course', { path: '/courses/:course_id' }, function() {
      this.route('user', { path: '/user/:user_id' });
    });
  });

  app.IndexRoute = Ember.Route.extend({
    redirect: function() {
      this.transitionTo('courses');
    }
  });

  app.CoursesRoute = Ember.Route.extend({
    model: function() {
      return app.CourseSummary.find();
    },
    events: {
      coursesAddAction: function() {
        this.transitionTo('coursesAdd');
      }
    }
  });

  app.CoursesAddRoute = Ember.Route.extend({
    enter: function() {
      // If we navigate directly to /courses/add, we won't have a populated
      // CourseSummary store yet, which causes duplication on /courses when
      // this is saved. Workaround is to make sure that the CourseSummaries
      // are loaded here.
      app.CourseSummary.find();
    },

    model: function() {
      if (typeof app.coursesAddModel === 'undefined') {
        app.coursesAddModel = app.CoursesAddModel.create();
        app.coursesAddModel.resetFormBuildInfo();
      }
      return app.coursesAddModel;
    }
  });

  app.CourseRoute = Ember.Route.extend({
    setupController: function(controller, model) {
      this._super.apply(arguments);
      controller.set('content', app.Course.find(model.id));
      controller.set('course_id', model.id);
      controller.resetUsers();
    }
  });

  app.CourseUserRoute = Ember.Route.extend({
    setupController: function(controller, model) {
      this._super.apply(arguments);
      var userController = this.controllerFor('user');
      userController.set('content', app.User.find(model.id));
      userController.bindResources(model.id);

      var courseController = this.controllerFor('course');
      courseController.selectUser(model.id);

      courseController.set('userController', userController);
    }
  });
});
