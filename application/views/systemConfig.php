<md-dialog aria-label="Edit Request">
    <!-- Dialog title -->
    <md-toolbar>
        <div class="md-toolbar-tools">
            <h2>Configuración del sistema</h2>
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
        <md-tabs md-border-bottom="true" md-dynamic-height="true" md-stretch-tabs="always" md-swipe-content="true">
            <md-tab label="Estatus de solicitudes" md-on-select="selectedTab = 1">
                <md-content layout-padding>
                    <div>
                        <p>Estatus del sistema (no editables)</p>
                        <md-chips
                            ng-model="statuses.systemStatuses"
                            readonly="true">
                        </md-chips>
                        <div layout>
                            <p>Estatus adicionales (presione ENTER para ingresarlos)</p>
                            <div layout layout-align="center center">
                                <md-progress-circular
                                    ng-if="statuses.loading"
                                    md-mode="indeterminate"
                                    md-diameter="30">
                                </md-progress-circular>
                            </div>
                        </div>
                        <md-chips
                            id="additional-statuses"
                            ng-model="statuses.newStatuses"
                            readonly="statuses.loading"
                            placeholder="Nuevo estatus"
                            delete-button-label="Delelete status"
                            delete-hint="Borrar Estatus">
                        </md-chips>
                    </div>
                    <!-- Operation error -->
                    <div ng-if="statuses.errorMsg != ''" layout layout-align="center center" class="md-padding">
                        <span style="color:red">{{statuses.errorMsg}}</span>
                    </div>
                </md-content>
            </md-tab>
            <md-tab label="Monto a solicitar" md-on-select="selectedTab = 2">
                <md-content layout-padding>
                    <div layout="column">
                        <div class="grey-color">
                            Monto mínimo (Bs)
                        </div>
                        <md-input-container
                            id="min-amount"
                            md-no-float>
                            <input required type="number" ng-model="amount.min.new" placeholder="Ej: 200000"/>
                        </md-input-container>
                        <div class="grey-color">
                            Monto máximo (Bs)
                        </div>
                        <md-input-container
                            id="max-amount"
                            md-no-float
                            class="no-vertical-margin">
                            <input required type="number" ng-model="amount.max.new" placeholder="Ej: 500000"/>
                        </md-input-container>
                    </div>
                    <div ng-if="amount.errorMsg != ''" layout layout-align="center center" class="md-padding">
                        <span style="color:red">{{amount.errorMsg}}</span>
                    </div>
                </md-content>
            </md-tab>
        </md-tabs>
    </md-dialog-content>
    <md-dialog-actions>
        <md-button
            id="save-statuses"
            ng-if="selectedTab == 1"
            ng-disabled="!updatedStatuses()"
            ng-hide="uploading" ng-click="updateStatuses()"
            class="md-primary">
            Guardar
        </md-button>
        <md-button
            id="save-amounts"
            ng-if="selectedTab == 2"
            ng-disabled="missingField()"
            ng-hide="uploading" ng-click="updateReqAmount()"
            class="md-primary">
            Guardar
        </md-button>
        <md-progress-circular ng-show="uploading" md-mode="indeterminate" md-diameter="60"></md-progress-circular>
        <md-button ng-disabled="uploading" ng-click="closeDialog()" class="md-primary">
            Cancelar
        </md-button>
    </md-dialog-actions>
</md-dialog>