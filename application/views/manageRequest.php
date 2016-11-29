<md-dialog aria-label="Manage Request">
    <!-- Dialog title -->
    <md-toolbar>
        <div class="md-toolbar-tools">
            <h2>Edición de solicitud</h2>
            <span flex></span>
            <md-button class="md-icon-button" ng-click="showHelp()" aria-label="Help">
                <md-icon>help_outline</md-icon>
                <md-tooltip md-direction="top">Ayuda</md-tooltip>
            </md-button>
            <md-button ng-show="!uploading" class="md-icon-button" ng-click="closeDialog()">
                <md-icon aria-label="Close dialog">close</md-icon>
            </md-button>
        </div>
    </md-toolbar>
    <md-dialog-content>
        <div layout layout-xs="column" layout-padding>
            <div flex="45" flex-xs="100">
                <div layout="column">
                    <div>
                        <span class="grey-color">
                            Comentario
                        </span>
                    </div>
                    <md-input-container
                            id="comment"
                            class="md-block no-vertical-margin"
                            md-no-float>
                        <textarea type="text" ng-model="model.comment" placeholder="Sin comentario"></textarea>
                    </md-input-container>
                </div>
            </div>
            <div flex="45" flex-offset="10" flex-xs="100" flex-offset-xs="0">
                <div layout="column">
                    <div>
                        <span class="grey-color">
                            Estatus
                        </span>
                    </div>
                    <md-input-container
                            id="status"
                            class="md-block no-vertical-margin"
                            md-no-float>
                        <md-select
                            aria-label="Nuevo Estatus"
                            ng-model="model.status">
                            <md-option ng-value="status" ng-repeat="status in mappedStatuses">{{status}}</md-option>
                        </md-select>
                    </md-input-container>
                </div>
             </div>
        </div>
        <div layout layout-xs="column" layout-padding layout-align-gt-xs="center center">
            <div
                ng-show="model.status != RECEIVED_STRING"
                layout-align-gt-xs="center center"
                flex-xs="100">
                <div>
                    <span class="grey-color">
                        &#8470; de Reunión
                    </span>
                </div>
                <md-input-container
                        id="reunion"
                        class="md-block no-vertical-margin"
                        md-no-float>
                    <input type="number" min="0" ng-model="model.reunion" placeholder="Ej: 325"/>
                </md-input-container>
            </div>
            <div
                ng-show="model.status == APPROVED_STRING"
                layout-align-gt-xs="center center"
                flex-xs="100">
                <div>
                    <span class="grey-color">
                        Monto aprobado (Bs) *
                    </span>
                </div>
                <md-input-container
                        id="approved-amount"
                        class="md-block no-vertical-margin"
                        md-no-float>
                    <input type="number" required min="0" ng-model="model.approvedAmount" placeholder="Ej: 150000"/>
                </md-input-container>
            </div>
        </div>
    </md-dialog-content>
    <md-dialog-actions>
        <md-button
            id="edit-btn"
            ng-hide="uploading"
            ng-disabled="missingField()"
            ng-click="verifyEdition()"
            class="md-primary">
            Actualizar
        </md-button>
        <md-button ng-hide="uploading" ng-click="closeDialog()" class="md-primary">
            Cancelar
        </md-button>
        <md-progress-linear ng-show="uploading" md-mode="indeterminate"></md-progress-linear>
    </md-dialog-actions>
</md-dialog>
