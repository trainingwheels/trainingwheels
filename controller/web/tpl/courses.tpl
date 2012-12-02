<h2>Select a course:</h2>

<div id="tw-course-list">
  {{#each course in controller.content}}
  <a {{action showCourse course}}>
    <div class="course-summary">
      <h3>{{course.title}}</h3>
      <div>
        {{{course.description}}}
      </div>
      <strong>Course machine name</strong>
      <div>
        {{course.course_name}}
      </div>
    </div>
  </a>
  {{/each}}
</div>
