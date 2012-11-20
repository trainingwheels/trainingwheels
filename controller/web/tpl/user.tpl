<h2>{{user_name}}</h2>
<form action="#" name="tw-delete-user-form" onSubmit="return false;">
  <div>
    <input type="submit" value="delete" />
  </div>
</form>
<form action="#" name="tw-sync-user-form" onSubmit="return false;">
  <div>
    <input type="submit" value="sync" />
  </div>
</form>
<form action="#" name="tw-refresh-user-form" onSubmit="return false;">
  <div>
    <input type="submit" value="refresh" />
  </div>
</form>
<div>Password: {{password}}</div>
<div>Logged in: {{#if logged_in}}yes{{else}}no{{/if}}</div>

<div class="resources">
  <h3>Resources</h3>

  {{#each_with_key resources key="key"}}
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
  {{/each_with_key}}
</div>