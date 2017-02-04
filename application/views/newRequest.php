<md-dialog aria-label="New Request">
    <!-- Dialog title -->
    <md-toolbar>
        <div class="md-toolbar-tools">
            <h2>{{title}}</h2>
            <span flex></span>
            <md-button ng-if="!loading" class="md-icon-button" ng-click="showHelp()" aria-label="Help">
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
    <!-- Inputs requested for applicants -->
    <md-dialog-content layout-padding ng-if="!loading">
        <form name="applicantForm">
            <!-- Requested amount -->
            <md-card>
                <div layout="column" class="amount-wrapper">
                    <div
                        layout layout-align="center"
                        class="grey-color">
                        <b>Monto solicitado (Bs)</b>
                    </div>
                    <div layout>
                        <md-input-container
                            id="req-amount"
                            flex="70"
                            flex-xs="100"
                            flex-offset="15"
                            flex-offset-xs="0"
                            md-no-float
                            class="md-icon-right no-vertical-margin">
                            <input
                                ng-model="model.reqAmount"
                                type="number"
                                min="{{minReqAmount}}"
                                max="{{maxReqAmount}}"
                                step="1000"
                                name="reqAmount"
                                required
                                placeholder="Ej: 300000.25"/>
                                <md-icon
                                    class="pointer"
                                    ng-click="setMax()">
                                    all_out
                                    <md-tooltip md-direction="bottom">Max</md-tooltip>
                                </md-icon>
                            <div ng-messages="applicantForm.reqAmount.$error" ng-show="applicantForm.reqAmount.$dirty">
                                <div ng-message="required">¡Este campo es obligatorio!</div>
                                <div ng-message="max">Monto máximo: Bs. {{maxReqAmount | number:2}}</div>
                                <div ng-message="min">Monto mínimo: Bs. {{minReqAmount | number:2}}</div>
                            </div>
                        </md-input-container>
                    </div>
                </div>
            </md-card>
            <!-- Phone number and ID-->
            <div
                layout layout-xs="column"
                layout-padding
                layout-align="center"
                layout-align-xs="start start">
                <div layout="column">
                    <div
                        class="grey-color">
                        <b>Número celular</b>
                    </div>
                    <div layout layout-align="start start" style="max-width:200px">
                        <md-input-container
                                class="no-vertical-margin"
                                md-no-float>
                            <md-select
                                aria-label="telephone operator"
                                md-on-open="onSelectOpen()"
                                md-on-close="onSelectClose()"
                                ng-model="model.tel.operator">
                                <md-option value="0412">0412</md-option>
                                <md-option value="0414">0414</md-option>
                                <md-option value="0424">0424</md-option>
                                <md-option value="0416">0416</md-option>
                                <md-option value="0426">0426</md-option>
                            </md-select>
                        </md-input-container>
                        <md-input-container
                            id="phone-numb"
                            md-no-float
                            class="no-vertical-margin">
                            <input
                                ng-model="model.tel.value"
                                type="number"
                                name="phone"
                                minlength="7"
                                maxlength="7"
                                required
                                placeholder="Ej: 1234567"/>
                            <div ng-messages="applicantForm.phone.$error" ng-show="applicantForm.phone.$dirty">
                                <div ng-message="required">¡Este campo es obligatorio!</div>
                                <div ng-message="maxlength">El número debe tener 7 dígitos.</div>
                            </div>
                        </md-input-container>
                    </div>
                </div>
                <div layout="column">
                    <div
                        class="grey-color">
                        <b>Correo electrónico</b>
                    </div>
                    <div layout class="pointer">
                        <md-input-container
                            id="email"
                            md-no-float
                            class="no-vertical-margin">
                            <input type="email"
                                   name="email"
                                   required
                                   pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$"
                                   ng-model="model.email"
                                   placeholder="ejemplo@dominio.com"/>
                            <div ng-messages="applicantForm.email.$error" ng-show="applicantForm.email.$dirty">
                                <div ng-message="required">¡Este campo es obligatorio!</div>
                                <div ng-message="pattern">Formato: ejemplo@dominio.com</div>
                            </div>
                        </md-input-container>
                    </div>
                </div>
            </div>
            <!-- Payment due & Type of transaction-->
            <div
                layout layout-xs="column"
                layout-align="center"
                layout-align-xs="start start">
                <div class="md-padding" style="padding-top:0" id="payment-due">
                    <p class="grey-color">
                        <b>Plazo para pagar</b>:
                    </p>
                    <md-radio-group ng-model="model.due">
                        <md-radio-button value="24">24 meses</md-radio-button>
                        <md-radio-button value="36">36 meses</md-radio-button>
                        <md-radio-button value="48">48 meses</md-radio-button>
                        <md-radio-button value="60">60 meses</md-radio-button>
                    </md-radio-group>
                </div>
                <div layout="column">
                    <div class="md-padding" style="padding-top:0; padding-bottom: 0" id="loan-type">
                        <p class="grey-color">
                            <b>Tipo de préstamo</b>:
                        </p>
                        <md-radio-group ng-model="model.type">
                            <md-radio-button
                                ng-repeat="TYPE in LOAN_TYPES"
                                ng-value="TYPE"
                                ng-disabled="!allow[TYPE] || opened.hasOpen[TYPE]">
                                {{mapLoanType(TYPE)}}
                                <md-tooltip
                                    ng-if="!allow[TYPE] || opened.hasOpen[TYPE]"
                                    md-direction="bottom">
                                    <span ng-if="(opened.hasOpen && allow[TYPE]) ||
                                        (opened.hasOpen && !allow[TYPE])">
                                        Usted posee una solicitud de {{mapLoanType(TYPE)}} en transcurso.
                                    </span>
                                    <span ng-if="!opened.hasOpen && !allow[TYPE]">
                                        No ha{{span === 1 ? '' : 'n'}}
                                        transcurrido {{span}} {{span == 1 ? 'mes' : 'meses'}}
                                        desde su última otorgación.
                                    </span>
                                </md-tooltip>
                            </md-radio-button>
                        </md-radio-group>
                    </div>
                    <!-- Payment fee -->
                    <md-card md-theme="help-card">
                        <md-card-content>
                            <div layout layout-align="center center">
                                <md-icon style="color:#827717; margin-right:10px">info_outline</md-icon>
                                <span> Cuotas a pagar: Bs. <b>{{calculatePaymentFee()}}</b></span>
                            </div>
                        </md-card-content>
                    </md-card>
                </div>
            </div>
        </form>
    </md-dialog-content>
    <md-dialog-actions ng-if="!loading">
        <md-button
            id="create-btn"
            ng-hide="uploading"
            ng-click="confirmOperation($event)"
            ng-disabled="missingField()"
            class="md-primary">
            {{confirmButton}}
        </md-button>
        <md-button ng-hide="uploading" ng-click="closeDialog()" class="md-primary">
            Cancelar
        </md-button>
        <md-progress-linear ng-show="uploading" md-mode="indeterminate">
        </md-progress-linear>
    </md-dialog-actions>
</md-dialog>
