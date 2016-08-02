<md-toolbar layout-padding>
    <div class="md-toolbar-tools">
        <h2 flex="10" hide show-gt-xs class="md-headline">
            <span>SGDP</span>
        </h2>
        <h2 hide-gt-xs class="md-headline">
            <span>SGDP</span>
        </h2>
        <span flex></span>
        <md-button class="md-icon-button" ng-click="null" aria-label="Help">
            <md-icon>help_outline</md-icon>
            <md-tooltip md-direction="down">Ayuda</md-tooltip>
        </md-button>
        <md-button class="md-icon-button" ng-click="logout()" aria-label="Logout">
            <md-icon>exit_to_app</md-icon>
            <md-tooltip md-direction="down">Cerrar sesión</md-tooltip>
        </md-button>
    </div>
</md-toolbar>
<main>
    <md-toolbar>
    </md-toolbar>
    <md-content style="background-color:#F5F5F5" class="u-overflow-fix" layout layout-align="center center">
        <md-card class="user-info-card">
            <md-card-header>
                <md-card-avatar>
                    <img class="md-user-avatar" src="images/avatar_circle.png"/>
                </md-card-avatar>
                <md-card-header-text>
                    <div class="md-toolbar-tools">
                        <h2 class="md-headline">{{userName}}</h2>
                    </div>
                </md-card-header-text>
            </md-card-header>
            <md-divider></md-divider>
            <md-progress-linear md-mode="query" ng-if="loading"></md-progress-linear>
            <!-- <md-card-content>
                <div layout>
                    <div flex="30">
                        <md-input-container class="md-block">
                            <label>Cédula</label>
                            <input type="text" ng-model="userData.cedula" readonly />
                        </md-input-container>
                    </div>
                    <div flex="30" flex-offset="5">
                        <md-input-container class="md-block">
                            <label>Ingreso</label>
                            <input type="text" ng-model="userData.ingreso" readonly />
                        </md-input-container>
                    </div>
                    <div flex="30" flex-offset="5">
                        <md-input-container class="md-block">
                            <label>Estado</label>
                            <input type="text" ng-model="userData.status" readonly />
                        </md-input-container>
                    </div>
                </div>
                <div layout>
                    <div flex="30">
                        <md-input-container class="md-block">
                            <label>Sueldo</label>
                            <input type="text" ng-model="userData.sueldo" readonly />
                        </md-input-container>
                    </div>
                    <div flex="30" flex-offset="5">
                        <md-input-container class="md-block">
                            <label>Dependencia</label>
                            <input type="text" ng-model="userData.dependencia" readonly />
                        </md-input-container>
                    </div>
                    <div flex="30" flex-offset="5">
                        <md-input-container class="md-block">
                            <label>Fianzas</label>
                            <input type="text" ng-model="userData.fianzas" readonly />
                        </md-input-container>
                    </div>
                </div>
                <div layout>
                    <div flex="30">
                        <md-input-container class="md-block">
                            <label>Aporte</label>
                            <input type="text" ng-model="userData.pcj_aporte" readonly />
                        </md-input-container>
                    </div>
                    <div flex="30" flex-offset="5">
                        <md-input-container class="md-block">
                            <label>Concurrencia</label>
                            <input type="text" ng-model="userData.concurrencia" readonly />
                        </md-input-container>
                    </div>
                    <div flex="30" flex-offset="5">
                        <md-input-container class="md-block">
                            <label>Carga EGS</label>
                            <input type="text" ng-model="userData.carga_egs" readonly />
                        </md-input-container>
                    </div>
                </div>
                <div layout>
                    <div flex="30">
                        <md-input-container class="md-block">
                            <label>Carga EMI</label>
                            <input type="text" ng-model="userData.carga_emi" readonly />
                        </md-input-container>
                    </div>
                    <div flex="30" flex-offset="5">
                        <md-input-container class="md-block">
                            <label>Carga GMM</label>
                            <input type="text" ng-model="userData.carga_gmm" readonly />
                        </md-input-container>
                    </div>
                    <div flex="30" flex-offset="5">
                        <md-input-container class="md-block">
                            <label>Carga GMS</label>
                            <input type="text" ng-model="userData.carga_gms" readonly />
                        </md-input-container>
                    </div>
                </div>
                <div layout>
                    <div flex="30">
                        <md-input-container class="md-block">
                            <label>Carga HCM</label>
                            <input type="text" ng-model="userData.carga_hcm" readonly />
                        </md-input-container>
                    </div>
                    <div flex="30" flex-offset="5">
                        <md-input-container class="md-block">
                            <label>Carga SEM</label>
                            <input type="text" ng-model="userData.carga_sem" readonly />
                        </md-input-container>
                    </div>
                    <div flex="30" flex-offset="5">
                        <md-input-container class="md-block">
                            <label>Carga SF</label>
                            <input type="text" ng-model="userData.carga_sf" readonly />
                        </md-input-container>
                    </div>
                </div>
            </md-card-content> -->
            <md-card-content ng-if="!loading">
                <div layout layout-padding>
                    <div flex="30">
                        <span class="md-title" style="font-size:16px">Cédula</span>
                        <p>{{userData.cedula}}</p>
                    </div>
                    <div flex="30" flex-offset="5">
                        <span class="md-title" style="font-size:16px">Ingreso</span>
                        <p>{{userData.ingreso}}</p>
                    </div>
                    <div flex="30" flex-offset="5">
                        <span class="md-title" style="font-size:16px">Estado</span>
                        <p>{{userData.status}}</p>
                    </div>
                </div>
                <div layout layout-padding>
                    <div flex="30">
                        <span class="md-title" style="font-size:16px">Sueldo</span>
                        <p>{{userData.sueldo}}</p>
                    </div>
                    <div flex="30" flex-offset="5">
                        <span class="md-title" style="font-size:16px">Dependencia</span>
                        <p>{{userData.dependencia}}</p>
                    </div>
                    <div flex="30" flex-offset="5">
                        <span class="md-title" style="font-size:16px">Fianzas</span>
                        <p>{{userData.fianzas}}</p>
                    </div>
                </div>
                <div layout layout-padding>
                    <div flex="30">
                        <span class="md-title" style="font-size:16px">Aporte</span>
                        <p>{{userData.pcj_aporte}}</p>
                    </div>
                    <div flex="30" flex-offset="5">
                        <span class="md-title" style="font-size:16px">Concurrencia</span>
                        <p>{{userData.concurrencia}}</p>
                    </div>
                    <div flex="30" flex-offset="5">
                        <span class="md-title" style="font-size:16px">Carga EGS</span>
                        <p>{{userData.carga_egs}}</p>
                    </div>
                </div>
                <div layout layout-padding>
                    <div flex="30">
                        <span class="md-title" style="font-size:16px">Carga EMI</span>
                        <p>{{userData.carga_emi}}</p>
                    </div>
                    <div flex="30" flex-offset="5">
                        <span class="md-title" style="font-size:16px">Carga GMM</span>
                        <p>{{userData.carga_gmm}}</p>
                    </div>
                    <div flex="30" flex-offset="5">
                        <span class="md-title" style="font-size:16px">Carga GMS</span>
                        <p>{{userData.carga_gms}}</p>
                    </div>
                </div>
                <div layout layout-padding>
                    <div flex="30">
                        <span class="md-title" style="font-size:16px">Carga HCM</span>
                        <p>{{userData.carga_hcm}}</p>
                    </div>
                    <div flex="30" flex-offset="5">
                        <span class="md-title" style="font-size:16px">Carga SEM</span>
                        <p>{{userData.carga_sem}}</p>
                    </div>
                    <div flex="30" flex-offset="5">
                        <span class="md-title" style="font-size:16px">Carga SF</span>
                        <p>{{userData.carga_sf}}</p>
                    </div>
                </div>
            </md-card-content>
        </md-card>
    </md-content>
</main>
