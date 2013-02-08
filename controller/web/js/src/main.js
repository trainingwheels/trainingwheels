require([
  'ember-shim',
  'ember-data',
  'jquery',
  'handlebars',
  'modules/job',
  'modules/resource',
  'modules/user',
  'modules/course'
], function(Ember, DS, $, Handlebars, Job, Resource, User, Course) {
  var TW = Ember.Application.create({LOG_TRANSITIONS: true});

  TW.Job = Job;
  TW.Resource = Resource;
  TW.User = User;
  TW.Course = Course;

  ////
  // Ember Data Store.
  //
  DS.RESTAdapter.configure('TW.User.UserSummary', {
    sideloadAs: 'users'
  });

  // Plurals are used when formatting the URLs, so if you have a
  // TW.Course.CourseSummary, and you attempt to populate it using findAll(),
  // the actual request will be GET /rest/course_summaries
  DS.RESTAdapter.configure('plurals', {
    course_summary: 'course_summaries',
    user_summary: 'user_summaries'
  });

  TW.Store = DS.Store.extend({
    revision: 11,
    adapter: DS.RESTAdapter.extend({
      namespace: 'rest',
    })
  });

  TW.Router.map(function() {
    this.route('index', { path: '/' });
    this.route('courses', { path: '/courses' });
    this.route('coursesAdd', { path: '/courses/add' });
    this.resource('course', { path: '/courses/:course_id' }, function() {
      this.route('user', { path: '/user/:user_id' });
    });
  });

  TW.IndexRoute = Ember.Route.extend({
    redirect: function() {
      this.transitionTo('courses');
    }
  });

  TW.CoursesRoute = Ember.Route.extend({
    model: function() {
      return TW.Course.CourseSummary.find();
    },
    events: {
      coursesAddAction: function() {
        this.transitionTo('coursesAdd');
      }
    }
  });

  TW.CoursesAddRoute = Ember.Route.extend({
    enter: function() {
      // If we navigate directly to /courses/add, we won't have a populated
      // CourseSummary store yet, which causes duplication on /courses when
      // this is saved. Workaround is to make sure that the CourseSummaries
      // are loaded here.
      Course.CourseSummary.find();
    }
  });

  TW.CourseRoute = Ember.Route.extend({
    setupController: function(controller, model) {
      this._super.apply(arguments);
      controller.set('content', Course.Course.find(model.id));
      controller.set('course_id', model.id);
      controller.resetUsers();
    }
  });

  TW.CourseUserRoute = Ember.Route.extend({
    setupController: function(controller, model) {
      this._super.apply(arguments);
      var userController = this.controllerFor('user');
      userController.set('content', User.User.find(model.id));
      userController.bindResources(model.id);

      var courseController = this.controllerFor('course');
      courseController.selectUser(model.id);

      courseController.set('userController', userController);
    }
  });

  // Expose the application to the Ember namespace so Ember can
  // do its magic.
  Ember.TW = TW;
});
