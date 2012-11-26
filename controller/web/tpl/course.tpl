<div class="course-view">
  {{#if title}}
    <div class="course-info">
      <h2>{{title}}</h2>
      <div>
        {{{description}}}
      </div>
      <h2>Machine name</h2>
      <div>
        {{course_name}}
      </div>
    </div>
    <div class="course-user-info">
      <h2>Users</h2>
      {{#each user in users}}
        {{#with user}}
          <div class="user">
            <h2>{{user_name}}</h2>
            <div>Password: {{password}}</div>
            <div>Logged in: {{#if logged_in}}yes{{else}}no{{/if}}</div>

            <div class="resources">
              <h3>Resources</h3>

              {{#each_with_key resources key="key"}}
                {{resources.key}}
              {{/each_with_key}}
                  <div class="resource">
                    <h4>{{title}}</h4>
                    <div>Type: {{type}}</div>
                    <div>Key: {{key}}</div>
                    {{#if exists}}
                    <div>Exists: yes</div>
                      <div class="resource-attribs">
                        {{#key_value attribs}}
                          <div>{{key}}: {{value}}</div>
                        {{/key_value}}
                      </div>
                    {{else}}
                      <div>Exists: no</div>
                    {{/if}}
                  </div>

            </div>
          </div>
        {{/with}}
      {{/each}}
    </div>
  {{/if}}
</div>
