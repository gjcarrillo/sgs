<md-bottom-sheet style="position: fixed"class="md-list md-has-header" ng-cloak>
  <md-subheader>Comment Actions</md-subheader>
  <md-list>
    <md-list-item>
      <md-button
          ng-click="listItemClick($index)"
          md-autofocus="$index == 2"
          class="md-list-item-content" >
        <md-icon>add</md-icon>
        <span class="md-inline-list-icon-label">Hola</span>
      </md-button>
    </md-list-item>
    <md-list-item>
      <md-button
          ng-click="listItemClick($index)"
          md-autofocus="$index == 2"
          class="md-list-item-content" >
        <md-icon>add</md-icon>
        <span class="md-inline-list-icon-label">Hola</span>
      </md-button>
    </md-list-item>
  </md-list>
</md-bottom-sheet>
