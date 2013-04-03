<div class="resource-options">
  <h4 class="resource-type">
    <span class="type-wrapper">{{ type }}</span>
    <button class="resource-remove" {{action "removeResource" resource}}>
      <div class="sync-wrapper">
        <span class="ss-delete"></span>
      </div>
    </button>
  </h4>
  <div class="resource-title"><span>Title: </span>{{view Ember.TextField valueBinding="title"}}</div>
  <div class="resource-key"><span>Key: </span>{{view Ember.TextField valueBinding="key"}}</div>
  <div class="resource-vars">
    {{#each var in vars}}
      <div class="resource-var">
        <div {{bindAttr class="var.css_class"}}>
          <span>{{var.key}} : </span>
          {{view Ember.TextField placeholderBinding="var.hint" valueBinding="var.input"}}
        </div>
        <div class="resource-help">
          {{var.help}}
        </div>
      </div>
    {{/each}}
  </div>
</div>
