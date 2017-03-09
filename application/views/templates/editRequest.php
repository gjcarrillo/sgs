<md-dialog aria-label="Edit Request">
    <!-- Dialog title -->
    <md-toolbar>
        <div class="md-toolbar-tools">
            <h2>Edición de solicitud</h2>
            <span flex></span>
            <md-button class="md-icon-button" agent-update-help ng-click="showHelp()" aria-label="Help">
                <md-icon>help_outline</md-icon>
                <md-tooltip md-direction="top">Ayuda</md-tooltip>
            </md-button>
            <md-button ng-show="!uploading" class="md-icon-button" ng-click="closeDialog()">
                <md-icon aria-label="Close dialog">close</md-icon>
            </md-button>
        </div>
    </md-toolbar>
    <md-dialog-content>
        <div
            layout layout-xs="column"
            layout-padding
            layout-align-gt-xs="space-around center">
            <!-- Optional comment input -->
            <div layout="column">
                <div
                    class="grey-color">
                    Comentario
                </div>
                <md-input-container
                        id="comment"
                        class="no-vertical-margin"
                        md-no-float>
                    <textarea type="text" ng-model="comment" placeholder="Sin comentario"></textarea>
                </md-input-container>
            </div>
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
                        <input type="text" readonly ng-model="selectedFiles"
                            class="pointer" placeholder="Clic para subir"/>
                        <md-icon
                            ng-show="files.length == 0"
                            class="pointer grey-color">
                            <md-tooltip>
                                Subir archivo
                            </md-tooltip>
                            file_upload
                        </md-icon>
                        <md-icon
                            ng-show="files.length > 0"
                            ng-click="deleteFiles($event)"
                            class="grey-color">
                            <md-tooltip>Descartar todos los archivos</md-tooltip>
                            delete
                        </md-icon>
                    </md-input-container>
                </div>
            </div>
        </div>
        <div layout layout-xs="column" layout-padding>
            <!-- Files cards. One card for each file. Allows adding a description or individual removal for each one -->
            <div ng-repeat="(dKey, doc) in files">
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
            ng-disabled="allFieldsMissing()"
            ng-click="updateRequest()"
            class="md-primary">
            Actualizar
        </md-button>
        <md-button ng-hide="uploading" ng-click="closeDialog()" class="md-primary">
            Cancelar
        </md-button>
        <md-progress-linear ng-show="uploading" md-mode="indeterminate"></md-progress-linear>
    </md-dialog-actions>
</md-dialog>
