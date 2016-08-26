<md-dialog aria-label="New Request">
    <!-- Dialog title -->
    <md-toolbar>
        <div class="md-toolbar-tools">
            <h2>Nueva de solicitud de préstamo</h2>
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
    <!-- Inputs requested for agents -->
    <md-dialog-content ng-if="userType(1)" layout-padding>
        <form name="agentForm">
            <div layout>
                <md-input-container
                    id="req-amount"
                    flex="100"
                    flex-gt-xs="50"
                    flex-offset="25"
                    flex-offset-xs="0">
                    <label>Monto solicitado (Bs) *</label>
                    <input
                        ng-model="model.reqAmount"
                        type="number"
                        min="100"
                        max="{{maxReqAmount}}"
                        step="100"
                        required
                        name="reqAmount"
                        placeholder="Ej: 300000.25"/>
                    <div ng-messages="agentForm.reqAmount.$error" ng-show="agentForm.reqAmount.$dirty">
                        <div ng-message="required">¡Este campo es obligatorio!</div>
                        <div ng-message="max">Monto máximo: Bs. {{maxReqAmount | number:2}}</div>
                    </div>
                </md-input-container>
            </div>
            <div
                ng-hide="idPicTaken && docPicTaken"
                layout layout-xs="column"
                layout-align="center center"
                layout-align-xs="start start">
                <div id="id-pic" ng-hide="idPicTaken">
                    <md-button ng-click="openIdentityCamera($event)">
                        <md-icon>photo_camera</md-icon>
                        Foto del afiliado (*)
                    </md-button>
                </div>
                <div id="doc-pic" ng-hide="docPicTaken">
                    <md-menu>
                       <md-button ng-click="$mdOpenMenu($event)" aria-label="Request doc">
                           <md-icon>insert_drive_file</md-icon>
                           Documento explicativo
                       </md-button>
                       <md-menu-content>
                           <md-menu-item>
                               <md-button ng-click="openDocCamera($event)">
                                   <md-icon>photo_camera</md-icon>
                                   Tomar foto
                               </md-button>
                           </md-menu-item>
                           <md-menu-item>
                               <md-button
                                   ngf-select="gatherFile($file, $invalidFiles)"
                                   ngf-pattern="'image/*,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheetapplication/vnd.openxmlformats-officedocument.spreadsheetml.template,,application/pdf,application/msword'"
                                   ngf-max-size="4MB">
                                   <md-icon>file_upload</md-icon>
                                   Subir de la PC
                               </md-button>
                           </md-menu-item>
                       </md-menu-content>
                   </md-menu>
                </div>
            </div>
        </form>
        <div layout layout-xs="column" layout-align="center center">
            <!-- ID picture result -->
            <md-card id="id-pic-result" ng-show="idPicTaken">
                <md-card-title>
                    <md-card-title-text>
                        <span class="md-headline">Foto del afiliado</span>
                    </md-card-title-text>
                    <md-button ng-click="deleteIdPic()" class="md-icon-button">
                        <md-icon>delete</md-icon>
                    </md-button>
                </md-card-title>
                <md-card-content layout layout-align="center center">
                    <div style="padding:10px; background:black">
                        <img width="160" height="106" id="idThumbnail"/>
                    </div>
                </md-card-content>
            </md-card>
            <md-card id="doc-pic-result" ng-show="docPicTaken && !file">
                <md-card-title>
                    <md-card-title-text>
                        <span class="md-headline">Foto de la solicitud</span>
                    </md-card-title-text>
                    <md-button ng-click="deleteDocPic()" class="md-icon-button">
                        <md-icon>delete</md-icon>
                    </md-button>
                </md-card-title>
                <md-card-content layout layout-align="center center">
                    <div style="padding:10px; background:black">
                        <img width="160" height="106" id="docThumbnail"/>
                    </div>
                </md-card-content>
            </md-card>
            <md-card id="doc-pic-selection" ng-show="docPicTaken && file">
                <md-card-title>
                <md-card-title-text>
                    <span class="md-headline">{{file.name}}</span>
                    <span class="md-subhead">{{file.description}}</span>
                </md-card-title-text>
                </md-card-title>
                <!-- Add description / Delete doc actions -->
                <md-card-actions ng-hide="uploading" layout layout-align="end center">
                    <md-button class="md-icon-button" ng-click="removeScannedDoc()"><md-icon>delete</md-icon></md-button>
                </md-card-actions>
                <!-- Uploading progress -->
                <md-card-actions ng-show="uploading">
                    <div class="md-padding">
                        <md-progress-linear md-mode="determinate" value="{{file.progress}}"></md-progress-linear>
                    </div>
                </md-card-actions>
            </md-card>
        </div>
        <div ng-repeat="f in errFiles" style="color:red" layout-padding>
            Error en archivo {{f.name}}: {{showError(f.$error, f.$errorParam)}}
        </div>
    </md-dialog-content>
    <!-- Inputs requested for applicants -->
    <md-dialog-content ng-if="userType(3)" layout-padding>
        <form name="applicantForm">
            <div layout>
                <md-input-container
                    id="req-amount"
                    flex="50"
                    flex-xs="100"
                    flex-offset="25"
                    flex-offset-xs="0">
                    <label>Monto solicitado (Bs) *</label>
                    <input
                        ng-model="model.reqAmount"
                        type="number"
                        min="100"
                        max="{{maxReqAmount}}"
                        step="100"
                        name="reqAmount"
                        required
                        placeholder="Ej: 300000.25"/>
                    <div ng-messages="applicantForm.reqAmount.$error" ng-show="applicantForm.reqAmount.$dirty">
                        <div ng-message="required">¡Este campo es obligatorio!</div>
                        <div ng-message="max">Monto máximo: Bs. {{maxReqAmount | number:2}}</div>
                    </div>
                </md-input-container>
            </div>
            <div
                ng-hide="idPicTaken && docPicTaken"
                layout layout-xs="column"
                layout-align="center center"
                layout-align-xs="start start">
                <div id="id-pic" ng-hide="idPicTaken">
                    <md-button
                        ngf-select="gatherIDFile($file, $invalidFiles)"
                        ngf-pattern="'image/*,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheetapplication/vnd.openxmlformats-officedocument.spreadsheetml.template,,application/pdf,application/msword'"
                        ngf-max-size="4MB">
                        <md-icon>file_upload</md-icon>
                        Cédula de identidad (*)
                    </md-button>
                </div>
                <div id="doc-pic" ng-hide="docPicTaken">
                    <md-button
                        ngf-select="gatherDocFile($file, $invalidFiles)"
                        ngf-pattern="'image/*,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheetapplication/vnd.openxmlformats-officedocument.spreadsheetml.template,,application/pdf,application/msword'"
                        ngf-max-size="4MB">
                        <md-icon>file_upload</md-icon>
                        Documento explicativo
                    </md-button>
                </div>
            </div>
        </form>
        <div layout layout-align="center center">
            <!-- ID picture result -->
            <md-card id="id-pic-result" ng-show="idPicTaken">
                <md-card-title>
                <md-card-title-text>
                    <span class="md-headline">{{idFile.name}}</span>
                    <span class="md-subhead">{{idFile.description}}</span>
                </md-card-title-text>
                </md-card-title>
                <!-- Delete file action -->
                <md-card-actions ng-hide="uploading" layout layout-align="end center">
                    <md-button class="md-icon-button" ng-click="deleteIdPic()"><md-icon>delete</md-icon></md-button>
                </md-card-actions>
                <!-- Uploading progress -->
                <md-card-actions ng-show="uploading">
                    <div class="md-padding">
                        <md-progress-linear md-mode="determinate" value="{{idFile.progress}}"></md-progress-linear>
                    </div>
                </md-card-actions>
            </md-card>
            <!-- Doc pic result -->
            <md-card id="doc-pic-selection" ng-show="docPicTaken">
                <md-card-title>
                <md-card-title-text>
                    <span class="md-headline">{{docFile.name}}</span>
                    <span class="md-subhead">{{docFile.description}}</span>
                </md-card-title-text>
                </md-card-title>
                <!-- Delete file action -->
                <md-card-actions ng-hide="uploading" layout layout-align="end center">
                    <md-button class="md-icon-button" ng-click="deleteDocPic()"><md-icon>delete</md-icon></md-button>
                </md-card-actions>
                <!-- Uploading progress -->
                <md-card-actions ng-show="uploading">
                    <div class="md-padding">
                        <md-progress-linear md-mode="determinate" value="{{docFile.progress}}"></md-progress-linear>
                    </div>
                </md-card-actions>
            </md-card>
        </div>
        <div ng-repeat="f in errFiles" style="color:red">
            Error en archivo {{f.name}}: {{showError(f.$error, f.$errorParam)}}
        </div>
    </md-dialog-content>
    <md-dialog-actions>
        <md-button
            id="create-btn"
            ng-hide="uploading"
            ng-click="createNewRequest($event)"
            ng-disabled="missingField()"
            class="md-primary">
            Crear
        </md-button>
        <md-progress-circular ng-show="uploading" md-mode="indeterminate" md-diameter="60"></md-progress-circular>
        <md-button ng-disabled="uploading" ng-click="closeDialog()" class="md-primary">
            Cancelar
        </md-button>
    </md-dialog-actions>
</md-dialog>
