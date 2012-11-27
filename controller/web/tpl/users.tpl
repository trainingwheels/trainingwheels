This is a user list
{{#each user in controller}}
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
