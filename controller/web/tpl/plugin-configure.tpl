<div class="plugin-options">
  <div class="plugin-name">{{key}}</div>
  {{#each var in vars}}
    <div class="plugin-var">
      <span>{{var.key}} : </span>
      {{view Ember.TextField valueBinding="var.input"}}
    </div>
  {{/each}}
</div>
