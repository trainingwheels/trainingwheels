<div id="course-form">
  <form>
    <div id="course-form-title">Create a course</div>

    <div id="course-fields">
      <div class="field">Title:       {{view Ember.TextField value="New" viewName="titleTextField"}}</div>
      <div class="field">Description: {{view Ember.TextField value="New" viewName="descriptionTextField"}}</div>
      <div class="field">Name:        {{view Ember.TextField value="new" viewName="nameTextField"}}</div>
      <div class="field">Type:        {{view Ember.TextField value="drupal" viewName="typeTextField"}}</div>
      <div class="field">Environment: {{view Ember.TextField value="ubuntu" viewName="environmentTextField"}}</div>
      <div class="field">Repository:  {{view Ember.TextField value="https://github.com/fourkitchens/trainingwheels-drupal-files-example.git" viewName="repositoryTextField"}}</div>
      <div class="field">Host:        {{view Ember.TextField value="localhost" viewName="hostTextField"}}</div>
      <div class="field">User:        {{view Ember.TextField value="none" viewName="userTextField"}}</div>
      <div class="field">Pass:        {{view Ember.TextField value="none" viewName="passTextField"}}</div>
    </div>

    <div id="course-tools">
      <button class="submit" type="submit" value="Save" {{action saveCourse}}>Create course</button>
    </div>
  </form>
</div>
