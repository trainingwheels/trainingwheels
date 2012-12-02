<div class="course-instructor">
  <h2>Instructor</h2>
  {{#each user in controller}}
    {{#with user}}
      {{view "App.UserSummaryView"}}
    {{/with}}
  {{/each}}
</div>
