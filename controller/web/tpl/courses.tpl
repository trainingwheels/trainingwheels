<h1>Select a course:</h1>

<div id="course-list">
  {{#each course in controller.content}}
    {{#with course}}
      <div class="course-summary">
        <h2><a href="#" {{action showCourse course}}>{{title}}</a></h2>
        <div "course-description">
          {{description}}
        </div>
      </div>
    {{/with}}
  {{/each}}
</div>
