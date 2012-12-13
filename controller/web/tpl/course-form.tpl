<div id="course-form">
  <form>
    <div id="course-form-title">Create a course</div>
  
    <div id="course-fields">
      <div class="field">Title:       {{view Ember.TextField viewName="titleTextField"}}</div>
      <div class="field">Description: {{view Ember.TextField viewName="descriptionTextField"}}</div>
      <div class="field">Name:        {{view Ember.TextField viewName="nameTextField"}}</div>
      <div class="field">Type:        {{view Ember.TextField viewName="typeTextField"}}</div>
      <div class="field">Environment: {{view Ember.TextField viewName="environmentTextField"}}</div>
      <div class="field">Repository:  {{view Ember.TextField viewName="repositoryTextField"}}</div>
      <div class="field">Host:        {{view Ember.TextField viewName="hostTextField"}}</div>
    </div>

    <div id="course-tools">
      <button type="submit" value="Save" {{action saveCourse}}>Create course</button>
    </div>
  </form>
</div>
