<div class="plugin-options">
  <div class="plugin-name">{{key}}</div>
  {{#each var in vars}}
    <div class="plugin-var">
      <div class="plugin-field">
        <span>{{var.key}} : </span>
        {{view Ember.TextField valueBinding="var.input"}}
      </div>
      <div class="plugin-help">
        {{var.help}}
      </div>
    </div>
  {{/each}}
</div>
