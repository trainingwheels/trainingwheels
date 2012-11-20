(function($) {
  tw = {};

  /**
   * Basic template containing a spinner.
   */
  tw.spinnerTemplate = Handlebars.compile($('#spinner-tpl').html());

  /**
   * User model.
   */
  tw.UserModel = Backbone.RelationalModel.extend({
    urlRoot : "/rest/user",
    idAttribute : 'userid'
  });

  /**
   * Course model.
   */
  tw.CourseModel = Backbone.RelationalModel.extend({
    urlRoot : "/rest/course",
    idAttribute : "courseid",
    relations : [{
      type : Backbone.HasMany,
      key : 'users',
      relatedModel : tw.UserModel,
      reverseRelation : {
        key : 'courseid',
        includeInJSON : 'courseid'
      }
    }]
  });

  /**
   * Courses collection.
   */
  tw.CourseCollection = Backbone.Collection.extend({
    url: '/rest/course',
    model: tw.CourseModel
  });

  /**
   * User view.
   */
  tw.UserView = Backbone.View.extend({
    tagName: 'div',
    className: 'user',

    template: Handlebars.compile($('#user-tpl').html()),

    events: {
      'submit form[name=tw-delete-user-form]': 'deleteUserSubmit',
      'submit form[name=tw-sync-user-form]': 'syncResourcesUserSubmit',
      'submit form[name=tw-refresh-user-form]': 'refreshUserSubmit'
    },

    initialize: function() {
      _.bindAll(this, 'render', 'deleteUserSubmit', 'destroy', 'sync', 'syncResourcesUserSubmit', 'refreshUserSubmit');
      this.model.bind('change', this.render);
      this.model.bind('reset', this.render);
      this.model.bind('destroy', this.destroy);
      this.model.bind('sync', this.sync);
    },

    render: function() {
      var json = this.model.toJSON();
      var tpl = '';
      if (typeof this.model.get('userid') == 'undefined') {
        tpl = tw.spinnerTemplate();
      }
      else {
        tpl = this.template(json);
        this.$('.twspin').spin(false);
      }
      return this.$el.html(tpl);
    },

    deleteUserSubmit: function() {
      this.model.destroy();
    },

    destroy: function() {
      this.$el.spin('large');
    },

    sync: function(model, resp, options) {
      if (resp.result == 'deleted') {
        this.unbind();
        this.remove();
      }
      else if (resp.result == 'resources-sync') {
        this.model.unset('result', {silent: true});
        this.model.unset('action', {silent: true});
        this.model.unset('sync_from', {silent: true});
      }
      this.$el.spin(false);
    },

    syncResourcesUserSubmit: function() {
      var options = {
        action: 'resources-sync',
        sync_from: 'instructor',
        sync_resources: '*'
      };
      this.$el.spin('large');
      this.model.save(options, {wait: true});
    },

    refreshUserSubmit: function() {
      this.model.fetch();
      this.$el.spin('large');
    }
  });

  /**
   * Course summary view.
   */
  tw.CourseSummaryView = Backbone.View.extend({
    tagName: 'div',
    className: 'course-summary',

    template: Handlebars.compile($('#course-summary-tpl').html()),

    events: {
      'click': 'summaryClick'
    },

    initialize: function() {
      _.bindAll(this, 'render', 'summaryClick');
      this.model.bind('change', this.render);
      this.model.bind('reset', this.render);
    },

    render: function() {
      return this.$el.html(this.template(this.model.toJSON()));
    },

    summaryClick: function() {
      this.$el.html('');
      this.$el.spin('large');
      tw.app.navigate('course/' + this.model.get('courseid'), {trigger: true});
    }
  });

  /**
   * Course view.
   */
  tw.CourseView = Backbone.View.extend({
    el: $('#tw-app'),
    template: Handlebars.compile($('#course-tpl').html()),

    events: {
      'submit form[name=tw-add-user-form]': 'newUserSubmit'
    },

    initialize: function() {
      _.bindAll(this, 'render', 'addUser', 'newUserSubmit');
      this.model.bind('change', this.render);
      this.model.bind('reset', this.render);
      this.model.bind('add:users', this.addUser);
    },

    render: function() {
        return this.$el.html(this.template(this.model.toJSON()));
    },

    addUser: function(user) {
      var userView = new tw.UserView({model: user});
      this.$('#user-list').prepend($(userView.render()));
      this.$('.twspin').spin('large');
    },

    newUserSubmit: function() {
      var user_name = this.$('#add-username').val();
      var userModel = new tw.UserModel({user_name: user_name, courseid: this.model});
      userModel.save();
    }
  });

  /**
   * App view.
   */
  tw.AppView = Backbone.View.extend({
    el: $('#tw-app'),
    template: Handlebars.compile($('#app-tpl').html()),

    initialize: function() {
      _.bindAll(this, 'render', 'renderCourseSummary');
      this.model.bind('change', this.render);
      this.model.bind('reset', this.render);
      this.render();
    },

    render: function() {
      $('#tw-app').spin(false);
      this.$el.html(this.template());
      this.model.forEach(this.renderCourseSummary);
      return this.$el.html();
    },

    renderCourseSummary: function(course_model) {
      var courseSummaryView = new tw.CourseSummaryView({model: course_model});
      this.$('#tw-course-list').append($(courseSummaryView.render()));
    }
  });

  /**
   * Router.
   */
  tw.Router = Backbone.Router.extend({
    routes: {
      '' : 'main',
      'course/:courseid' : 'course_page'
    },

    main: function() {
      var courseCollection = new tw.CourseCollection();
      var appView = new tw.AppView({model: courseCollection});
      courseCollection.fetch();
    },

    course_page: function(courseid) {
      var courseModel = tw.CourseModel.findOrCreate({courseid : courseid});
      var courseView = new tw.CourseView({model: courseModel});
      courseModel.fetch();
    }
  });

  /**
   * Application.
   */
  tw.app = null;
  tw.bootstrap = function() {
    tw.app = new tw.Router();
    Backbone.history.start({pushState: true});
  }

  tw.bootstrap();
  $('#tw-app').spin('large');

})(jQuery);
