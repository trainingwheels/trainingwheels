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
    revision: 12,
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
      app.coursesAddModel = app.CoursesAddModel.create();
      app.coursesAddModel.resetFormBuildInfo();
      return app.coursesAddModel;
    },

    setupController: function(controller, model) {
      controller.resetValidation();
    }
  });

  app.CourseRoute = Ember.Route.extend({
    setupController: function(controller, model) {
      // We don't use the 'model' hook because that hook is only called when
      // navigating directly to the URL. In the case of navigating from /courses
      // to /courses/1, the linkTo should provide the model, however that model
      // is a CourseSummary, not a Course.
      controller.set('content', app.Course.find(model.id));
      controller.set('course_id', model.id);
      controller.resetUsers();
    }
  });

  app.CourseUserRoute = Ember.Route.extend({
    setupController: function(controller, model) {
      // Once again, we can't use the 'model' hook because of the difference
      // between summaries and actual user objects. See description in app.CourseRoute.
      controller.set('content', app.User.find(model.id));
      controller.bindResources(model.id);
      controller.set('stateManager', app.UserState.create({controller: controller}));

      controller.get('controllers.course').selectUser(model.id);
    }
  });
});
