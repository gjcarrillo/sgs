<md-dialog aria-label="Edit Request">
    <!-- Dialog title -->
    <md-toolbar>
        <div class="md-toolbar-tools">
            <h2>Edición de solicitud</h2>
            <span flex></span>
            <md-button class="md-icon-button" ng-click="closeDialog()">
                <md-icon aria-label="Close dialog">close</md-icon>
            </md-button>
        </div>
    </md-toolbar>
    <md-dialog-content class="md-padding">
        <div layout>
            <!-- Optional comment input -->
            <div flex="45">
                <md-input-container class="md-block" md-no-float>
                    <label>Comentario</label>
                    <textarea type="text" placeholder="Sin comentario"></textarea>
                </md-input-container>
            </div>
            <!-- State selection -->
            <div flex="45" flex-offset="10">
                <md-input-container class="md-block">
                    <label>Estado</label>
                    <md-select ng-model="request.state">
                        <md-option ng-value="state" ng-repeat="state in states">{{state}}</md-option>
                    </md-select>
                </md-input-container>
            </div>
        </div>
        <div layout>
             <div flex layout-align="center">
                 <!-- File(s) input -->
                 <lf-ng-md-file-input
                     multiple
                     progress
                     lf-files="files"
                     lf-browse-label="Buscar"
                     lf-remove-label="Cancelar"
                     lf-caption="{{files.length}} {{files.length === 1 ? 'documento seleccionado' : 'documentos seleccionados'}}"
                     lf-placeholder="Seleccione documentos">
                 </lf-ng-md-file-input>
            </div>
        </div>
        <br/>
        <div layout>
            <!-- Files cards. One card for each file. Allows adding a description or individual removal for each one -->
            <div layout-align="center center" ng-repeat="(dKey, doc) in files">
                <md-card>
                    <md-card-title>
                    <md-card-title-text>
                        <span class="md-headline">{{doc.lfFileName}}</span>
                        <span class="md-subhead">{{doc.description}}</span>
                    </md-card-title-text>
                    </md-card-title>
                    <md-card-actions ng-hide="enabledDescription == dKey" layout="row" layout-align="end center">
                        <md-button class="md-icon-button" ng-click="enabledDescription = dKey"><md-icon>message</md-icon></md-button>
                        <md-button class="md-icon-button" ng-click="removeDoc(dKey)"><md-icon>delete</md-icon></md-button>
                    </md-card-actions>
                    <md-card-actions ng-show="enabledDescription == dKey" layout="row" layout-align="center center">
                        <md-input-container md-no-float>
                            <input type="text"
                                ng-model="doc.description"
                                placeholder="Descripción"
                                ng-keyup="$event.keyCode == 13 && (enabledDescription = -1)">
                            </input>
                        </md-input-container>
                        <md-button class="md-icon-button" ng-click="enabledDescription = -1"><md-icon>send</md-icon></md-button>
                    </md-card-actions>
                </md-card>
            </div>
        </div>
    </md-dialog-content>
    <md-dialog-actions>
        <md-button ng-click="updateRequest()" ng-disabled="files.length < 1" class="md-primary">
            Actualizar
        </md-button>
        <md-button ng-click="closeDialog()" class="md-primary">
            Cancelar
        </md-button>
    </md-dialog-actions>
</md-dialog>
