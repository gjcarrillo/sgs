<md-dialog aria-label="New Request">
    <!-- Dialog title -->
    <md-toolbar>
        <div class="md-toolbar-tools">
            <h2>Nueva de solicitud de préstamo</h2>
            <span flex></span>
            <md-button ng-show="!uploading" class="md-icon-button" ng-click="closeDialog()">
                <md-icon aria-label="Close dialog">close</md-icon>
            </md-button>
        </div>
    </md-toolbar>
    <md-dialog-content layout-padding>
        <div layout layout-align="center center">
            <div ng-hide="idPicTaken">
                <md-button ng-click="openIdentityCamera($event)">
                    <md-icon>photo_camera</md-icon>
                    Foto del afiliado
                </md-button>
            </div>
            <div ng-hide="docPicTaken">
                <md-button ng-click="openDocCamera($event)">
                    <md-icon>photo_camera</md-icon>
                    Foto de la solicitud
                </md-button>
            </div>
        </div>
        <div layout layout-align="center center">
            <md-card ng-show="idPicTaken">
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
            <md-card ng-show="docPicTaken">
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
        </div>
    </md-dialog-content>
    <md-dialog-actions>
        <md-button ng-hide="uploading" ng-click="createNewRequest($event)" ng-disabled="!idPicTaken || !docPicTaken" class="md-primary">
            Crear
        </md-button>
        <md-progress-circular ng-show="uploading" md-mode="indeterminate" md-diameter="60"></md-progress-circular>
        <md-button ng-disabled="uploading" ng-click="closeDialog()" class="md-primary">
            Cancelar
        </md-button>
    </md-dialog-actions>
</md-dialog>
