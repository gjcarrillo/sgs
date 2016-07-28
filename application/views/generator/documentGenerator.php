<md-toolbar layout-padding>
    <div class="md-toolbar-tools">
        <md-button href="#/home" class="md-icon-button">
            <md-icon>
                arrow_back
            </md-icon>
        </md-button>
        <h2 flex="10" class="md-headline">
            <span>SGDP</span>
        </h2>
        <span flex></span>
        <md-button class="md-fab md-mini md-raised" ng-click="logout()" aria-label="Back">
            <md-icon>exit_to_app</md-icon>
            <md-tooltip md-direction="left">Cerrar sesión</md-tooltip>
        </md-button>
    </div>
</md-toolbar>
<main>
    <md-toolbar class="md-tall">
    </md-toolbar>
    <md-content style="background-color:#F5F5F5" class="u-overflow-fix" layout layout-align="center center">
        <md-card class="history-card">
            <md-card-title>
                <div class="md-toolbar-tools">
                    <h2 class="md-headline">Solicitud de préstamo personal único</h2>
                </div>
            </md-card-title>
            <md-divider></md-divider>
            <md-card-content>
                <div layout>
                    <div flex="30">
                        <md-input-container class="md-block">
                            <label>Primer apellido</label>
                            <input type="text" ng-model="model.lastname"/>
                        </md-input-container>
                    </div>
                    <div flex="30" flex-offset="5">
                        <md-input-container class="md-block">
                            <label>Segundo apellido</label>
                            <input type="text" ng-model="model.surname"/>
                        </md-input-container>
                    </div>
                    <div flex="30" flex-offset="5">
                        <md-input-container class="md-block">
                            <label>Apellido de casada</label>
                            <input type="text" ng-model="model.marriedName"/>
                        </md-input-container>
                    </div>
                </div>
                <div layout>
                    <div flex="45">
                        <md-input-container class="md-block">
                            <label>Primer nombre</label>
                            <input type="text" ng-model="model.firstname"/>
                        </md-input-container>
                    </div>
                    <div flex="45" flex-offset="10">
                        <md-input-container class="md-block">
                            <label>Segundo nombre</label>
                            <input type="text" ng-model="model.middlename"/>
                        </md-input-container>
                    </div>
                </div>
                <div layout>
                    <div flex="30">
                        <md-input-container class="md-block">
                            <label>CI</label>
                            <input type="text" ng-model="id"/>
                        </md-input-container>
                    </div>
                    <div flex="30" flex-offset="5">
                        <md-input-container class="md-block">
                            <label>Fecha</label>
                            <input type="text" ng-model="model.date"/>
                        </md-input-container>
                    </div>
                    <div flex="30" flex-offset="5">
                        <md-input-container class="md-block">
                            <label>Sueldo Básico</label>
                            <input type="text" ng-model="model.basicSalary"/>
                        </md-input-container>
                    </div>
                </div>
                <div layout>
                    <div flex>
                        <md-input-container class="md-block">
                            <label>Dependencia</label>
                            <input type="text" ng-model="model.dependancy"/>
                        </md-input-container>
                    </div>
                </div>
                <div layout>
                    <div flex>
                        <md-input-container class="md-block">
                            <label>Dirección particular</label>
                            <input type="text" ng-model="model.address"/>
                        </md-input-container>
                    </div>
                </div>
                <div layout>
                    <div flex="30">
                        <md-input-container class="md-block">
                            <label>Teléfono</label>
                            <input type="text" ng-model="model.phone"/>
                        </md-input-container>
                    </div>
                    <div flex="30" flex-offset="5">
                        <md-input-container class="md-block">
                            <label>Cantidad Solicitada</label>
                            <input type="text" ng-model="model.requestedAmount"/>
                        </md-input-container>
                    </div>
                    <div flex="30" flex-offset="5">
                        <md-input-container class="md-block">
                            <label>Forma de Pago</label>
                            <input type="text" ng-model="model.paymentMode"/>
                        </md-input-container>
                    </div>
                </div>
            </md-card-content>
            <md-card-actions ng-hide="loading" layout layout-align="end center">
                <md-button ng-click="generatePdfDoc()" class="md-primary">
                    Generar documento
                </md-button>
            </md-card-actions>
            <md-card-actions ng-show="loading" layout layout-align="end center">
                <md-progress-circular md-mode="indeterminate" md-diameter="60"></md-progress-circular>
            </md-card-actions>
        </md-card>
    </md-content>
</main>
