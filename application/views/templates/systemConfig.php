<md-dialog aria-label="Edit Request">
    <!-- Dialog title -->
    <md-toolbar>
        <div class="md-toolbar-tools">
            <h2>Configuración del sistema</h2>
            <span flex></span>
            <md-button class="md-icon-button" config-help ng-click="showHelp()" aria-label="Help">
                <md-icon>help_outline</md-icon>
                <md-tooltip md-direction="top">Ayuda</md-tooltip>
            </md-button>
            <md-button ng-show="!uploading" class="md-icon-button" ng-click="closeDialog()">
                <md-icon aria-label="Close dialog">close</md-icon>
            </md-button>
        </div>
    </md-toolbar>
    <md-dialog-content>
        <md-tabs md-border-bottom="true" md-dynamic-height="true" md-stretch-tabs="never" md-swipe-content="true">
            <md-tab label="Estatus de solicitudes" md-on-select="selectedTab = 1">
                <md-content layout-padding>
                    <div>
                        <div layout layout-xs="column">
                            <div flex="45" flex-xs="100">
                                <p>Estatus del sistema (no editables)</p>
                                <md-chips
                                    ng-model="statuses.systemStatuses"
                                    readonly="true">
                                </md-chips>
                            </div>
                            <div flex="45" flex-xs="100" flex-offset="10" flex-offset-xs="0">
                                <div layout>
                                    <p>Estatus en uso (no editables)</p>
                                    <div layout layout-align="center center">
                                        <md-progress-circular
                                            ng-if="statuses.loading"
                                            md-mode="indeterminate"
                                            md-diameter="30">
                                        </md-progress-circular>
                                    </div>
                                </div>
                                <md-chips
                                    ng-model="statuses.inUse"
                                    readonly="true">
                                </md-chips>
                            </div>
                        </div>
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
                    <form name="amountsForm">
                        <div layout="column">
                            <div layout>
                                <span class="grey-color">Monto mínimo (Bs)</span>
                                <md-progress-circular
                                    ng-if="amount.min.loading"
                                    md-mode="indeterminate"
                                    md-diameter="30">
                                </md-progress-circular>
                            </div>
                            <md-input-container
                                id="min-amount"
                                md-no-float>
                                <input required name="minAmount"
                                       type="number"
                                       ng-max="amount.max.new"
                                       ng-model="amount.min.new"
                                       placeholder="Ej: 200000"/>
                                <div ng-messages="amountsForm.minAmount.$error" ng-show="amountsForm.minAmount.$dirty">
                                    <div ng-message="required">¡Este campo es obligatorio!</div>
                                    <div ng-message="max">Monto máximo: Bs. {{amount.max.new | number:2}}</div>
                                </div>
                            </md-input-container>
                            <div layout>
                                <span class="grey-color">Monto máximo (Bs)</span>
                                <md-progress-circular
                                    ng-if="amount.max.loading"
                                    md-mode="indeterminate"
                                    md-diameter="30">
                                </md-progress-circular>
                            </div>
                            <md-input-container
                                id="max-amount"
                                md-no-float
                                class="no-vertical-margin">
                                <input required name="maxAmount"
                                       type="number"
                                       ng-min="amount.min.new"
                                       ng-model="amount.max.new"
                                       placeholder="Ej: 500000"/>
                                <div ng-messages="amountsForm.maxAmount.$error" ng-show="amountsForm.maxAmount.$dirty">
                                    <div ng-message="required">¡Este campo es obligatorio!</div>
                                    <div ng-message="min">Monto mínimo: Bs. {{amount.min.new | number:2}}</div>
                                </div>
                            </md-input-container>
                        </div>
                    </form>
                    <div ng-if="amount.errorMsg != ''" layout layout-align="center center" class="md-padding">
                        <span style="color:red">{{amount.errorMsg}}</span>
                    </div>
                </md-content>
            </md-tab>
            <md-tab label="Lapsos" md-on-select="selectedTab = 3">
                <md-content layout-padding>
                    <div layout="column">
                        <div layout>
                            <span class="grey-color">Lapso (meses) entre solicitudes</span>
                            <md-progress-circular
                                ng-if="span.loading"
                                md-mode="indeterminate"
                                md-diameter="30">
                            </md-progress-circular>
                        </div>
                        <md-input-container
                            id="min-span"
                            md-no-float>
                            <input required type="number" step="1" ng-model="span.newValue" placeholder="Ej: 3"/>
                        </md-input-container>
                    </div>
                    <div ng-if="span.errorMsg != ''" layout layout-align="center center" class="md-padding">
                        <span style="color:red">{{span.errorMsg}}</span>
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
        <md-button
            id="save-span"
            ng-if="selectedTab == 3"
            ng-disabled="missingSpan()"
            ng-hide="uploading" ng-click="updateRequestsSpan()"
            class="md-primary">
            Guardar
        </md-button>
        <md-progress-circular ng-show="uploading" md-mode="indeterminate" md-diameter="60"></md-progress-circular>
        <md-button ng-disabled="uploading" ng-click="closeDialog()" class="md-primary">
            Cancelar
        </md-button>
    </md-dialog-actions>
</md-dialog>