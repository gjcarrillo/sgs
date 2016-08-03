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
    <md-dialog-content>
        <!-- <div layout>
            <div flex="45">
                <md-input-container class="md-block" md-no-float>
                    <label>Comentario</label>
                    <textarea type="text" ng-model="request.comment" placeholder="Sin comentario"></textarea>
                </md-input-container>
            </div>
            <div flex="45" flex-offset="10">
                <md-input-container class="md-block">
                    <label>Estado</label>
                    <md-select ng-model="request.status">
                        <md-option ng-value="status" ng-repeat="status in statuses">{{status}}</md-option>
                    </md-select>
                </md-input-container>
             </div>
        </div> -->
        <div layout layout-padding layout-align="space-around center">
            <!-- Optional comment input -->
            <div>
                <md-input-container class="md-block" md-no-float>
                    <label>Comentario</label>
                    <textarea type="text" ng-model="request.comment" placeholder="Sin comentario"></textarea>
                </md-input-container>
            </div>
            <!-- File(s) input -->
            <div style="padding-bottom:25px;">
                <md-button
                   ngf-select="gatherFiles($files, $invalidFiles)"
                   multiple
                   ngf-pattern="'image/*,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheetapplication/vnd.openxmlformats-officedocument.spreadsheetml.template,,application/pdf,application/msword'"
                   ngf-max-size="4MB">
                   Documentos adicionales
                   <md-icon>file_upload</md-icon>
               </md-button>
            </div>
        </div>
        <div layout>
            <!-- Files cards. One card for each file. Allows adding a description or individual removal for each one -->
            <div  ng-repeat="(dKey, doc) in files">
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
    <md-dialog-actions>
        <md-button ng-hide="uploading" ng-disabled="allFieldsMissing()" ng-click="updateRequest()" class="md-primary">
            Actualizar
        </md-button>
        <md-progress-circular ng-show="uploading" md-mode="indeterminate" md-diameter="60"></md-progress-circular>
        <md-button ng-disabled="uploading" ng-click="closeDialog()" class="md-primary">
            Cancelar
        </md-button>
    </md-dialog-actions>
</md-dialog>
