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
                    <form name="userForm">
                        <div layout layout-xs="column" layout-padding>
                            <div layout="column" flex="45" flex-xs="100">
                                <br/>
                                <div class="grey-color">
                                    Cédula de identidad
                                </div>
                                <div layout layout-align-gt-xs="start start" flex-xs="100">
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
                                        flex-xs="100"
                                        md-no-float>
                                       <input
                                           placeholder="Ej: 123456789"
                                           required
                                           name="id"
                                           aria-label="userId"
                                           ng-model="userId"/>
                                       <div ng-messages="userForm.id.$error" ng-show="userForm.id.$dirty">
                                           <div ng-message="required">¡Este campo es obligatorio!</div>
                                       </div>
                                    </md-input-container>
                                </div>
                            </div>
                            <div layout="column" flex="45" flex-xs="100" flex-offset="10" flex-offset-xs="0">
                                <br/>
                                <div class="grey-color">
                                    Contraseña
                                </div>
                                <md-input-container id="user-psw" style="margin:0" class="md-block" md-no-float>
                                    <input
                                        required
                                        name="psw"
                                        type="password"
                                        ng-model="model.psw"
                                        placeholder="***********"/>
                                    <div ng-messages="userForm.psw.$error" ng-show="userForm.psw.$dirty">
                                        <div ng-message="required">¡Este campo es obligatorio!</div>
                                    </div>
                                </md-input-container>
                            </div>
                        </div>
                        <div layout layout-xs="column" layout-padding>
                            <div layout="column" flex="45" flex-xs="100">
                                <div class="grey-color">
                                    Nombre
                                </div>
                                <md-input-container id="user-name" style="margin:0" class="md-block" md-no-float>
                                    <input name="name" required type="text" ng-model="model.name" placeholder="Ej: Carlos"/>
                                    <div ng-messages="userForm.name.$error" ng-show="userForm.name.$dirty">
                                        <div ng-message="required">¡Este campo es obligatorio!</div>
                                    </div>
                                </md-input-container>
                            </div>
                            <div layout="column" flex="45" flex-xs="100" flex-offset="10" flex-offset-xs="0">
                                <div class="grey-color">
                                    Apellido
                                </div>
                                <md-input-container id="user-lastname" style="margin:0" class="md-block" md-no-float>
                                    <input
                                        name="lastname"
                                        required
                                        type="text"
                                        ng-model="model.lastname"
                                        placeholder="Ej: Gutierrez"/>
                                    <div ng-messages="userForm.lastname.$error" ng-show="userForm.lastname.$dirty">
                                        <div ng-message="required">¡Este campo es obligatorio!</div>
                                    </div>
                                </md-input-container>
                            </div>
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
                        <span class="grey-color">
                            Por favor escoja el usuario a eliminar
                        </span>
                        <md-input-container
                            id="select-agent"
                            style="margin:0"
                            md-no-float>
                            <md-select
                                placeholder="Elija el usuario"
                                ng-model="selectedUser"
                                md-on-open="fetchAllAgents()"
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
