<div id="course-form">
  <form>
    <div id="course-form-title">Create a course</div>

    <div id="course-fields">
      <div class="field">Title:       {{view Ember.TextField placeholder="The course title" size="30" viewName="titleTextField"}}</div>
      <div class="field">Description: {{view Ember.TextArea placeholder="A description of your course" rows="5" cols="30" viewName="descriptionTextField"}}</div>
      <div class="field">Short name:  {{view Ember.TextField placeholder="A short name containing only letters and underscores" size="30" viewName="nameTextField"}}</div>
      <div class="field">Type:        {{view Ember.TextField placeholder="drupal" size="30" viewName="typeTextField"}}</div>
      <div class="field">Environment: {{view Ember.TextField value="ubuntu" size="30" viewName="environmentTextField"}}</div>
      <div class="field">Repository:  {{view Ember.TextField value="https://github.com/fourkitchens/trainingwheels-drupal-files-example.git" size="30" viewName="repositoryTextField"}}</div>
      <div class="field">Host:        {{view Ember.TextField value="localhost" size="30" viewName="hostTextField"}}</div>
      <div class="field">User:        {{view Ember.TextField value="none" size="30" viewName="userTextField"}}</div>
      <div class="field">Pass:        {{view Ember.TextField value="none" size="30" viewName="passTextField"}}</div>
    </div>

    <div id="course-tools">
      <button class="submit" type="submit" value="Save" {{action "saveCourse" view}}>Create course</button>
      <button class="submit" type="submit" value="Cancel" {{action "cancelCourseAdd"}}>Cancel</button>
    </div>
  </form>
</div>
