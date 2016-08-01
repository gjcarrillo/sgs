<md-dialog aria-label="Edit Request">
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
    <md-dialog-content class="md-padding">
        <!-- File(s) input -->
        <div layout layout-align="center">
             <span>Haga click en el botón para agregar más documentos</span>
             <md-button
                ngf-select="gatherFiles($files, $invalidFiles)"
                multiple
                ngf-pattern="'image/*,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheetapplication/vnd.openxmlformats-officedocument.spreadsheetml.template,,application/pdf,application/msword'"
                ngf-max-size="4MB"
                class="md-raised md-primary md-icon-button">
                <md-icon>file_upload</md-icon>
            </md-button>
        </div>
        <br/>
        <div layout>
            <!-- Files cards. One card for each file. Allows adding a description or individual removal for each one -->
            <div layout-align="center center" ng-repeat="(dKey, doc) in files">
                <md-card>
                    <md-card-title>
                    <md-card-title-text>
                        <span class="md-headline">{{doc.name}}</span>
                        <span class="md-subhead">{{doc.description}}</span>
                    </md-card-title-text>
                    </md-card-title>
                    <!-- Add description / Delete doc actions -->
                    <md-card-actions ng-hide="isDescriptionEnabled(dKey) || uploading" layout="row" layout-align="end center">
                        <md-button class="md-icon-button" ng-click="enableDescription(dKey)"><md-icon>message</md-icon></md-button>
                        <md-button class="md-icon-button" ng-click="removeDoc(dKey)"><md-icon>delete</md-icon></md-button>
                    </md-card-actions>
                    <!-- Add description input -->
                    <md-card-actions ng-show="isDescriptionEnabled(dKey) && !uploading" layout="row" layout-align="center center">
                        <md-input-container md-no-float>
                            <input
                                id="{{dKey}}"
                                type="text"
                                ng-model="doc.description"
                                placeholder="Descripción"
                                ng-keyup="$event.keyCode == 13 && enableDescription(-1)">
                            </input>
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
        <div ng-repeat="f in errFiles" style="color:red">
            Error en archivo {{f.name}}: {{showError(f.$error, f.$errorParam)}}
        </div>
    </md-dialog-content>
    <md-dialog-actions ng-show="!uploading">
        <md-button ng-disabled="files.length < 1" ng-click="updateRequest()" class="md-primary">
            Actualizar
        </md-button>
        <md-button ng-click="closeDialog()" class="md-primary">
            Cancelar
        </md-button>
    </md-dialog-actions>
</md-dialog>
