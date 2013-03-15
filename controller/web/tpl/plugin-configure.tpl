<div class="plugin-options">
  <h4 class="plugin-name">{{key}}</h4>
  <div class="plugin-vars">
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
</div>
