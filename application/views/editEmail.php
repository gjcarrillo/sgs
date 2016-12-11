<md-dialog aria-label="Edit Email">
    <div layout layout-align="center center" layout-padding>
        <form name="form">
            <md-input-container md-no-float>
                <input type="email"
                       name="email"
                       ng-disabled="loading"
                       md-auto-focus
                       ng-keyup="$event.keyCode == 13 && saveEdition()"
                       required
                       pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$"
                       ng-model="email"
                       placeholder="ejemplo@dominio.com"/>
                <div ng-messages="form.email.$error" ng-show="form.email.$dirty">
                    <div ng-message="required">¡Este campo es obligatorio!</div>
                    <div ng-message="pattern">Formato: ejemplo@dominio.com</div>
                </div>
            </md-input-container>
            <md-button aria-label="Send" ng-disabled="!canSend()" class="md-icon-button" ng-click="saveEdition()">
                <md-icon ng-if="!loading">send</md-icon>
                <md-progress-circular ng-if="loading" md-diameter="30" md-mode="indeterminate"></md-progress-circular>
            </md-button>
        </form>
    </div>
</md-dialog>