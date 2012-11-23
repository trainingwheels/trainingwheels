<h2>Select a course:</h2>

{{#each course in controller}}
<p>
  <a {{action showCourse course}}>{{course.login}}</a>
</p>
{{/each}}

