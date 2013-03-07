/**
 * @fileoverview The 'Add Course' form.
 */
define([
  'ember',
  'ember-data',
  'jquery',
  'alertify',
  'app'
], function(Ember, DS, $, alertify, app) {

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
      var self = this;
      self.get('bundles').find(function(item, index, enumerable) {
        if (item.get('bundleClass') == bundle.get('bundleClass')) {
          if (!item.get('enabled')) {
            item.set('enabled', true);
            $.map(item.get('plugins'), function(plugin, pluginClass) {
              self.togglePlugin(plugin, false);
            });
          }
          else {
            item.set('enabled', false);
            $.map(item.get('plugins'), function(plugin, pluginClass) {
              self.togglePlugin(plugin);
            });
          }
          return true;
        }
        return false;
      });
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
});
