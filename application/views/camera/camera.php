<!-- Camera dialog -->
<md-dialog aria-label="Camera">
    <div layout layout-align="start center">
        <md-button ng-click="closeDialog()" class="md-icon-button" aria-label="Back">
            <md-icon>arrow_back</md-icon>
        </md-button>
        <h2 class="md-title">Cámara</h2>
    </div>
    <md-dialog-content layout="column" layout-align="center">
        <div ng-hide="picTaken" layout-padding style="background:black">
            <webcam
                on-stream="onStream(stream)"
                on-error="onError(err)"
                on-streaming="onSuccess()"
                channel="channel">
                <div class="alert alert-error" ng-show="webcamError">
                    <span>La cámara no pudo iniciarse. ¿Le ha otorgado acceso?</span>
                </div>
            </webcam>
        </div>
        <div ng-show="picTaken" layout-padding style="background:black">
            <canvas id="snapshot"></canvas>
        </div>
        <md-dialog-actions ng-hide="picTaken" layout layout-align="center center">
            <md-button ng-click="takePicture()" class="md-fab md-mini" aria-label="Take Pic">
                <md-icon>photo_camera</md-icon>
                <md-tooltip>Tomar foto</md-tooltip>
            </md-button>
        </md-dialog-actions>
        <md-dialog-actions ng-show="picTaken">
            <md-button ng-click="savePic()" class="md-primary">
                Guardar
            </md-button>
            <md-button ng-click="deletePic()" class="md-primary">
                Cancelar
            </md-button>
        </md-dialog-actions>
    </md-dialog-content>
</md-dialog>
