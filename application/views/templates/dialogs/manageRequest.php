<md-dialog aria-label="Manage Request" ng-class="{'wide-dialog' : model.status == PRE_APPROVED_STRING}">
    <!-- Dialog title -->
    <md-toolbar class="md-table-toolbar md-default">
        <div class="md-toolbar-tools">
            <h2>Edición de solicitud</h2>
            <span flex></span>
            <md-button class="md-icon-button" manage-request-help ng-click="showHelp()" aria-label="Help">
                <md-icon>help_outline</md-icon>
                <md-tooltip md-direction="top">Ayuda</md-tooltip>
            </md-button>
            <md-button ng-show="!uploading" class="md-icon-button" ng-click="closeDialog()">
                <md-icon aria-label="Close dialog">close</md-icon>
            </md-button>
        </div>
    </md-toolbar>
    <md-dialog-content ng-if="loading" layout-padding>
        <div layout layout-align="center center">
            <md-progress-circular md-mode="indeterminate" md-diameter="60"></md-progress-circular>
        </div>
    </md-dialog-content>
    <md-dialog-content ng-if="!loading">
        <div layout layout-xs="column" layout-align="start start">
            <div>
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
                                    style="min-width: 100px"
                                    md-select-fix="model.status"
                                    md-on-open="loadStatuses()"
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
                        ng-show="model.status == REJECTED_STRING || model.status == PRE_APPROVED_STRING"
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
                        ng-show="model.status == PRE_APPROVED_STRING"
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
                            <input type="number" required step="1000" min="0" ng-model="model.approvedAmount" placeholder="Ej: 150000"/>
                        </md-input-container>
                    </div>
                </div>
                <div layout layout-align="start center" layout-padding>
                    <div layout="column">
                        <div
                            class="grey-color">
                            Documentos adicionales
                        </div>
                        <div layout class="pointer">
                            <md-input-container
                                id="more-files"
                                ngf-select="gatherFiles($files, $invalidFiles)"
                                multiple
                                ngf-pattern="'image/*,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheetapplication/vnd.openxmlformats-officedocument.spreadsheetml.template,,application/pdf,application/msword'"
                                ngf-max-size="4MB"
                                md-no-float
                                class="md-icon-left no-vertical-margin">
                                <input type="text" readonly ng-model="model.selectedFiles"
                                       class="pointer" placeholder="Clic para subir"/>
                                <md-icon
                                    ng-show="model.files.length == 0"
                                    class="pointer grey-color">
                                    <md-tooltip>
                                        Subir archivo
                                    </md-tooltip>
                                    file_upload
                                </md-icon>
                                <md-icon
                                    ng-show="model.files.length > 0"
                                    ng-click="deleteFiles($event)"
                                    class="grey-color">
                                    <md-tooltip>Descartar todos los archivos</md-tooltip>
                                    delete
                                </md-icon>
                            </md-input-container>
                        </div>
                    </div>
                </div>
            </div>
            <!-- information of interest for cash voucher -->
            <div layout="column" layout-align="start start" ng-if="request.type == LoanTypes.CASH_VOUCHER && model.status == PRE_APPROVED_STRING">
                <md-card class="grayish">
                    <md-card-title>
                        <span class="grey-color"><b>Monto solicitado</b></span>
                    </md-card-title>
                    <md-divider></md-divider>
                    <md-card-content>
                        <div layout="column" layout-align="start">
                            <span>Bs. {{request.reqAmount | number:2}}</span>
                        </div>
                    </md-card-content>
                </md-card>
                <md-card class="grayish">
                    <md-card-title>
                        <span class="grey-color"><b>Cálculo de monto a abonar</b></span>
                    </md-card-title>
                    <md-divider></md-divider>
                    <md-card-content>
                        <div layout="column" layout-align="start">
                            <span>Monto del préstamo {{model.approvedAmount | number:2}}</span>
                            <span>Menos 2% interés {{model.approvedAmount * getInterestRate()/100 | number:2}}</span>
                            <span>Igual a Bs. <b>{{(model.approvedAmount - model.approvedAmount * getInterestRate()/100 | number:2) || '----'}}</b></span>
                        </div>
                    </md-card-content>
                </md-card>
            </div>
            <!-- information of interest for personal loan -->
            <div layout="column" layout-align="start start" ng-if="request.type == LoanTypes.PERSONAL_LOAN && model.status == PRE_APPROVED_STRING">
                <md-card class="grayish">
                    <md-card-title>
                        <span class="grey-color"><b>Monto solicitado</b></span>
                    </md-card-title>
                    <md-divider></md-divider>
                    <md-card-content>
                        <div layout="column" layout-align="start">
                            <span>Bs. {{request.reqAmount | number:2}}</span>
                        </div>
                    </md-card-content>
                </md-card>
                <md-card class="grayish">
                    <md-card-title>
                        <span class="grey-color"><b>Cálculo de monto a abonar</b></span>
                    </md-card-title>
                    <md-divider></md-divider>
                    <md-card-content>
                        <div layout="column" layout-align="start">
                            <span>Monto del préstamo {{model.approvedAmount | number:2}}</span>
                            <span>Menos abono (20%) deudas gastos médicos {{(calculateMedicalDebtContribution() | number:2) || '----'}}</span>
                            <span>Menos saldo de préstamo anterior {{(model.data.lastLoanBalance | number:2) || '----'}}</span>
                            <span>Más cuota de préstamo anterior {{(model.data.lastLoanFee | number:2) || '----'}}</span>
                            <span>Menos intereses préstamo nuevo {{(calculateNewInterest() | number:2) || '----'}}</span>
                            <span>Igual a Bs. <b>{{(calculateLoanAmount() | number:2) || '----'}}</b></span>
                        </div>
                    </md-card-content>
                </md-card>
            </div>
        </div>
        <div layout layout-xs="column" layout-padding>
            <!-- Files cards. One card for each file. Allows adding a description or individual removal for each one -->
            <div ng-repeat="(dKey, doc) in model.files">
                <md-card id="file-card">
                    <md-card-title>
                        <md-card-title-text>
                            <span class="md-headline truncate">{{doc.name}}</span>
                            <span class="md-subhead wrap-text">{{doc.description}}</span>
                        </md-card-title-text>
                    </md-card-title>
                    <!-- Add description / Delete doc actions -->
                    <md-card-actions ng-hide="isDescriptionEnabled(dKey) || uploading" layout="row" layout-align="end center">
                        <md-button class="md-icon-button" ng-click="enableDescription(dKey)">
                            <md-icon>
                                <md-tooltip>Agregar descripción</md-tooltip>
                                message
                            </md-icon>
                        </md-button>
                        <md-button class="md-icon-button" ng-click="removeDoc(dKey)">
                            <md-icon>
                                <md-tooltip>Descartar archivo</md-tooltip>
                                delete
                            </md-icon>
                        </md-button>
                    </md-card-actions>
                    <!-- Add description input -->
                    <md-card-actions
                        ng-show="isDescriptionEnabled(dKey) && !uploading"
                        layout="row"
                        layout-align="center center">
                        <md-input-container md-no-float>
                            <textarea
                                id="{{dKey}}"
                                type="text"
                                ng-model="doc.description"
                                placeholder="Descripción"
                                ng-keyup="$event.keyCode == 13 && enableDescription(-1)">
                            </textarea>
                        </md-input-container>
                        <md-button class="md-icon-button" ng-click="enableDescription(-1)"><md-icon>send</md-icon></md-button>
                    </md-card-actions>
                    <!-- Uploading progress -->
                    <md-card-actions ng-show="uploading">
                        <div class="md-padding">
                            <md-progress-linear md-mode="determinate" value="{{doc.progress}}"></md-progress-linear>
                        </div>
                    </md-card-actions>
                </md-card>
            </div>
        </div>
        <div ng-repeat="f in errFiles" style="color:red" layout-padding>
            Error en archivo {{f.name}}: {{showError(f.$error, f.$errorParam)}}
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
