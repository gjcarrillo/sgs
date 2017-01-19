<md-bottom-sheet style="position: fixed"class="md-list md-has-header" ng-cloak>
    <h2 class="md-headline">Detalle de acciones</h2>
  <md-list>
      <div ng-repeat="(aKey, action) in actions">
          <md-list-item
              class="md-2-line noright">
              <div class="md-list-item-text" layout="column">
                 <h3>{{aKey+1}} - {{action.summary}}</h3>
                 <p>{{action.detail}}</p>
               </div>
          </md-list-item>
          <md-divider ng-if="!$last" class="md-inset"></md-divider>
      </div>
  </md-list>
</md-bottom-sheet>
