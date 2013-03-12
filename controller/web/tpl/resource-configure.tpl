<div class="resource-options">
  <div class="resource-title"><span>Title: </span>{{view Ember.TextField valueBinding="title"}}</div>
  <div class="resource-key"><span>Key: </span>{{view Ember.TextField valueBinding="key"}}</div>
  {{#each var in vars}}
    <div class="resource-var">
      <div class="resource-field">
        <span>{{var.key}} : </span>
        {{view Ember.TextField placeholderBinding="var.hint" valueBinding="var.input"}}
      </div>
      <div class="resource-help">
        {{var.help}}
      </div>
    </div>
  {{/each}}
</div>
