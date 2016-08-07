<md-dialog aria-label="Manage Request">
    <!-- Dialog title -->
    <md-toolbar>
        <div class="md-toolbar-tools">
            <h2>Edición de solicitud</h2>
            <span flex></span>
            <md-button ng-show="!uploading" class="md-icon-button" ng-click="closeDialog()">
                <md-icon aria-label="Close dialog">close</md-icon>
            </md-button>
        </div>
    </md-toolbar>
    <md-dialog-content>
        <div layout layout-padding>
            <div flex="45">
                <md-input-container class="md-block" md-no-float>
                    <label>Comentario</label>
                    <input type="text" ng-model="model.comment" placeholder="Sin comentario"></input>
                </md-input-container>
            </div>
            <div flex="45" flex-offset="10">
                <md-input-container class="md-block">
                    <label>Estado</label>
                    <md-select ng-model="model.status">
                        <md-option ng-value="status" ng-repeat="status in statuses">{{status}}</md-option>
                    </md-select>
                </md-input-container>
             </div>
        </div>
        <div layout layout-padding layout-align="center center">
            <div ng-show="model.status != 'Recibida'" layout layout-align="center center">
                <md-input-container class="md-block" md-no-float>
                    <label>&#8470; de Reunión</label>
                    <input type="number" min="0" ng-model="model.reunion" placeholder="Ej: 325"></input>
                </md-input-container>
            </div>
            <div ng-show="model.status == 'Aprobada'" layout layout-align="center center">
                <md-input-container class="md-block" md-no-float>
                    <label>Monto aprobado (Bs) *</label>
                    <input type="number" required min="0" ng-model="model.approvedAmount" placeholder="Ej: 150000"></input>
                </md-input-container>
            </div>
        </div>
    </md-dialog-content>
    <md-dialog-actions>
        <md-button ng-hide="uploading" ng-disabled="missingField()" ng-click="updateRequest()" class="md-primary">
            Actualizar
        </md-button>
        <md-progress-circular ng-show="uploading" md-mode="indeterminate" md-diameter="60"></md-progress-circular>
        <md-button ng-disabled="uploading" ng-click="closeDialog()" class="md-primary">
            Cancelar
        </md-button>
    </md-dialog-actions>
</md-dialog>
