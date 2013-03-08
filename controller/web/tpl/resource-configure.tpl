<div class="resource-options">
  <div class="resource-name">{{title}}</div>
  {{#each var in vars}}
    <div class="resource-var">
      <div class="resource-field">
        <span>{{var.key}} : </span>
        {{view Ember.TextField valueBinding="var.input"}}
      </div>
      <div class="resource-help">
        {{var.help}}
      </div>
    </div>
  {{/each}}
</div>
