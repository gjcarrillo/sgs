<md-dialog aria-label="Edit Request">
    <div layout layout-align="center center" layout-padding>
        <md-input-container md-no-float>
            <input
                type="text"
                md-auto-focus
                ng-keyup="$event.keyCode == 13 && saveEdition()"
                ng-model="doc.description"
                placeholder="Descripción"/>
        </md-input-container>
        <md-button class="md-icon-button" ng-click="saveEdition()"><md-icon>send</md-icon></md-button>
    </div>
</md-dialog>
