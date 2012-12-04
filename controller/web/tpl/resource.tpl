<div class="resource">
  <div class="resource-info">
    <h4>{{title}}</h4>
    <div {{bindAttr class="view.css_class_resources_status status"}}></div>
  </div>
  <div class="resource-details">
    {{#if exists}}
      <dl>
        <dt>Type</dt>
        <dd>{{type}}</dd>
        <dt>Key</dt>
        <dd>{{key}}</dd>
        {{#each attrib in attribsArray}}
          {{#with attrib}}
            <dt>{{title}}</dt>
            <dd>{{value}}</dd>
          {{/with}}
        {{/each}}
      </dl>
    {{else}}
      <div class="resource-attribs-missing">
        Resources not created yet.
      </div>
    {{/if}}
  </div>
</div>
