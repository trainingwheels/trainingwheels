<div id="course-form">
  <form>
    <div id="course-form-title">Create a course</div>

    <div id="course-fields">
      <div {{bindAttr class="css_class_title"}}>
        Title
        {{view Ember.TextField placeholder="Course title" size="30" viewName="titleTextField" valueBinding="title"}}
        <ul id="title-errors" class="errors">
          {{#each error in controller.titleErrors}}
          <li class="error">{{error}}</li>
          {{/each}}
        </ul>
      </div>
      <div class="field"><span>Description</span> {{view Ember.TextArea placeholder="Describe course" rows="5" cols="30" viewName="descriptionTextField" valueBinding="description"}}</div>
      <div {{bindAttr class="css_class_short_name"}}>
        Short name
        {{view Ember.TextField required="required" placeholder="Contains only letters and underscores" size="30" valueBinding="courseName"}}
        <ul id="short-name-errors" class="errors">
          {{#each error in controller.courseNameErrors}}
          <li class="error">{{error}}</li>
          {{/each}}
        </ul>
      </div>
      <div class="field">Type        {{view Ember.TextField placeholder="drupal" size="30" viewName="typeTextField" valueBinding="courseType"}}</div>
      <div class="field">Environment {{view Ember.TextField value="ubuntu" size="30" viewName="environmentTextField" valueBinding="envType"}}</div>
      <div class="field">Repository  {{view Ember.TextField value="https://github.com/fourkitchens/trainingwheels-drupal-files-example.git" size="30" viewName="repositoryTextField" valueBinding="repo"}}</div>
      <div class="field">Host        {{view Ember.TextField value="localhost" size="30" viewName="hostTextField" valueBinding="host"}}</div>
      <div class="field">User        {{view Ember.TextField value="none" size="30" viewName="userTextField" valueBinding="user"}}</div>
      <div class="field">Pass        {{view Ember.TextField value="none" size="30" viewName="passTextField" valueBinding="pass"}}</div>
    </div>

    <div id="course-tools">
      <button class="submit" type="submit" value="Save" {{bindAttr disabled="form_is_invalid"}} {{action "saveCourse" view}}>Create course</button>
      <hr>
      <button class="cancel" type="submit" value="Cancel" {{action "cancelCourseAdd"}}>View all courses</button>
    </div>
  </form>
</div>
