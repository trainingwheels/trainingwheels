
<div id="courses-dashboard">
  
  <h1>Select a course</h1>

  <div id="course-list">
    {{#each course in controller.content}}
      {{#with course}}
        <div class="course-summary">
          <h2>{{#linkTo "course" course}}{{title}}{{/linkTo}}</h2>
          <div class="course-description">
            {{description}}
          </div>
          <div class="course-name">
            <span>Short Name:</span> {{course_name}}
          </div>
          <div class="course-host">
            <span>Host:</span> {{host}}
          </div>
        </div>
      {{/with}}
    {{/each}}
  </div>

  <div id="course-tools">
    <button class="submit" {{action "coursesAddAction"}}>Create a course</button>
  </div>

</div>
