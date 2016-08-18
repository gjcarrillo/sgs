<md-dialog aria-label="Edit Request">
    <!-- Dialog title -->
    <md-toolbar>
        <div class="md-toolbar-tools">
            <h2>Administración de gestores</h2>
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
    <md-dialog-content>
        <md-tabs md-border-bottom="true" md-dynamic-height="true" md-stretch-tabs="always" md-swipe-content="true">
            <md-tab label="Registrar" md-on-select="selectedTab = 1">
                <md-content>
                    <form>
                        <!-- Show only on screen width >= 600 px-->
                        <div hide-xs layout layout-padding>
                            <div layout="column" flex="45">
                                <br/>
                                <div style="color:grey">
                                    Cédula de identidad
                                </div>
                                <div layout layout-align="start start">
                                    <md-input-container
                                        style="margin:0"
                                        md-no-float>
                                        <md-select
                                            md-on-open="onIdOpen()"
                                            md-on-close="onIdClose()"
                                            aria-label="V or E ID"
                                            ng-model="idPrefix">
                                            <md-option value="V">
                                                V
                                            </md-option>
                                            <md-option value="E">
                                                E
                                            </md-option>
                                        </md-select>
                                    </md-input-container>
                                    <md-input-container
                                        id="user-id"
                                        style="margin:0"
                                        md-no-float>
                                       <input
                                           placeholder="Ej: 123456789"
                                           required
                                           aria-label="userId"
                                           ng-model="userId">
                                    </md-input-container>
                                </div>
                            </div>
                            <div layout="column" flex="45" flex-offset="10">
                                <br/>
                                <div style="color:grey">
                                    Contraseña
                                </div>
                                <md-input-container id="user-psw" style="margin:0" class="md-block" md-no-float>
                                    <input required type="password" ng-model="model.psw" placeholder="***********"></input>
                                </md-input-container>
                            </div>
                        </div>
                        <!-- Show only on screen width >= 600 px-->
                        <div hide-xs layout layout-padding>
                            <div layout="column" flex="45">
                                <div style="color:grey">
                                    Nombre
                                </div>
                                <md-input-container id="user-name" style="margin:0" class="md-block" md-no-float>
                                    <input required type="text" ng-model="model.name" placeholder="Ej: Carlos"></input>
                                </md-input-container>
                            </div>
                            <div layout="column" flex="45" flex-offset="10">
                                <div style="color:grey">
                                    Apellido
                                </div>
                                <md-input-container id="user-lastname" style="margin:0" class="md-block" md-no-float>
                                    <input required type="text" ng-model="model.lastname" placeholder="Ej: Gutierrez"></input>
                                </md-input-container>
                            </div>
                        </div>
                        <!-- Show only on screen width < 600 px-->
                        <div hide-gt-xs layout="column" layout-padding>
                            <br/>
                            <div style="color:grey">
                                Cédula de identidad
                            </div>
                            <div layout layout-align="start start">
                                <md-input-container
                                    style="margin:0"
                                    md-no-float>
                                    <md-select
                                        md-on-open="onIdOpen()"
                                        md-on-close="onIdClose()"
                                        aria-label="V or E ID"
                                        ng-model="idPrefix">
                                        <md-option value="V">
                                            V
                                        </md-option>
                                        <md-option value="E">
                                            E
                                        </md-option>
                                    </md-select>
                                </md-input-container>
                            </div>
                            <md-input-container
                                id="user-id-mobile"
                                style="margin:0"
                                md-no-float>
                               <input
                                   placeholder="Ej: 123456789"
                                   required
                                   aria-label="userId"
                                   ng-model="userId">
                            </md-input-container>
                            <br/>
                            <div style="color:grey">
                                Contraseña
                            </div>
                            <md-input-container
                                id="user-psw-mobile"
                                style="margin:0"
                                class="md-block"
                                md-no-float>
                                <input required type="password" ng-model="model.psw" placeholder="***********"></input>
                            </md-input-container>
                            <div style="color:grey">
                                Nombre
                            </div>
                            <md-input-container
                                id="user-name-mobile"
                                style="margin:0"
                                class="md-block"
                                md-no-float>
                                <input required type="text" ng-model="model.name" placeholder="Ej: Carlos"></input>
                            </md-input-container>
                            <div style="color:grey">
                                Apellido
                            </div>
                            <md-input-container
                                id="user-lastname-mobile"
                                style="margin:0"
                                class="md-block"
                                md-no-float>
                                <input required type="text" ng-model="model.lastname" placeholder="Ej: Gutierrez"></input>
                            </md-input-container>
                        </div>
                    </form>
                    <!-- Operation error -->
                    <div ng-if="errorMsg != ''" layout layout-align="center center" class="md-padding">
                        <span style="color:red">{{errorMsg}}</span>
                    </div>
                </md-content>
            </md-tab>
            <md-tab label="Eliminar" md-on-select="selectedTab = 2">
                <md-content>
                    <div layout="column" layout-padding>
                        <span style="color:grey">
                            Por favor escoja el usuario a eliminar
                        </span>
                        <md-input-container
                            id="select-agent"
                            style="margin:0"
                            md-no-float>
                            <md-select
                                placeholder="Elija el usuario"
                                ng-model="selectedUser"
                                md-on-open="onUsersOpen()"
                                md-on-close="onUsersClose()"
                                style="min-width: 200px;">
                                <md-option ng-value="user" ng-repeat="user in userAgents">{{user.display}}</md-option>
                            </md-select>
                        </md-input-container>
                    </div>
                    <div ng-if="errorMsg != ''" layout layout-align="center center" class="md-padding">
                        <span style="color:red">{{errorMsg}}</span>
                    </div>
                </md-content>
            </md-tab>
        </md-tabs>
    </md-dialog-content>
    <md-dialog-actions>
        <md-button
            id ="register-btn"
            ng-disabled="missingField()"
            ng-hide="uploading || selectedTab == 2" ng-click="createNewAgent()"
            class="md-primary">
            Registrar
        </md-button>
        <md-button
            id="remove-btn"
            ng-disabled="!selectedUser"
            ng-hide="uploading || selectedTab == 1" ng-click="deleteAgent()"
            class="md-primary">
            Eliminar
        </md-button>
        <md-progress-circular ng-show="uploading" md-mode="indeterminate" md-diameter="60"></md-progress-circular>
        <md-button ng-disabled="uploading" ng-click="closeDialog()" class="md-primary">
            Cancelar
        </md-button>
    </md-dialog-actions>
</md-dialog>
