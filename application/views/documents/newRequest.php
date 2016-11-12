<md-dialog aria-label="New Request">
    <!-- Dialog title -->
    <md-toolbar>
        <div class="md-toolbar-tools">
            <h2>Nueva solicitud de préstamo</h2>
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

    <!-- Inputs requested for applicants -->
    <md-dialog-content layout-padding>
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
                                min="100"
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
                                <div ng-message="maxlength">El número debe tener 10 dígitos.</div>
                            </div>
                        </md-input-container>
                    </div>
                </div>
                <!-- Upload for agent users -->
                <div ng-if="userType(3)" layout="column">
                    <div
                        class="grey-color">
                        <b>Cédula de identidad</b>
                    </div>
                    <div layout class="pointer">
                        <md-input-container
                            id="id-pic"
                            ngf-select="gatherIDFile($file, $invalidFiles)"
                            ngf-pattern="'image/*,application/pdf,application/msword'"
                            ngf-max-size="4MB"
                            md-no-float
                            class="no-vertical-margin">
                            <input type="text" readonly ng-model="model.idFile.name"
                                class="pointer" placeholder="Clic para subir"/>
                            <md-icon
                                ng-hide="idPicTaken"
                                class="pointer grey-color">
                                file_upload
                            </md-icon>
                            <md-icon
                                ng-show="idPicTaken"
                                ng-click="deleteIdPic($event)"
                                class="grey-color">
                                delete
                            </md-icon>
                        </md-input-container>
                    </div>
                </div>
                <!-- Uploads for agent users -->
                <div ng-if="userType(1)" layout="column">
                    <div
                        class="grey-color">
                        <b>Foto del afiliado</b>
                    </div>
                    <div layout class="pointer" ng-click="openIdentityCamera($event)">
                        <md-input-container
                            id="id-pic"
                            md-no-float
                            class="no-vertical-margin">
                            <input type="text" readonly ng-model="model.idFile"
                                class="pointer" placeholder="Clic aquí"/>
                            <md-icon
                                ng-hide="idPicTaken"
                                class="pointer grey-color">
                                photo_camera
                            </md-icon>
                            <md-icon
                                ng-show="idPicTaken"
                                ng-click="deleteIdPic($event)"
                                class="grey-color">
                                delete
                            </md-icon>
                        </md-input-container>
                    </div>
                </div>
            </div>
            <div ng-repeat="f in errFiles" style="color:red">
                Error en archivo {{f.name}}: {{showIdError(f.$error, f.$errorParam)}}
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
                <div class="md-padding" style="padding-top:0"id="loan-type">
                    <p class="grey-color">
                        <b>Tipo de préstamo</b>:
                    </p>
                    <md-radio-group ng-model="model.type">
                        <md-radio-button value="pp">Préstamo Personal</md-radio-button>
                        <md-radio-button value="vc">Vale de Caja</md-radio-button>
                    </md-radio-group>
                </div>
            </div>
        </form>
    </md-dialog-content>
    <md-dialog-actions>
        <md-button
            id="create-btn"
            ng-hide="uploading"
            ng-click="confirmCreation($event)"
            ng-disabled="missingField()"
            class="md-primary">
            Crear
        </md-button>
        <md-button ng-hide="uploading" ng-click="closeDialog()" class="md-primary">
            Cancelar
        </md-button>
        <md-progress-linear ng-show="uploading" md-mode="determinate"
            value="{{model.idFile.progress}}"></md-progress-linear>
    </md-dialog-actions>
</md-dialog>
