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
            <!-- Show only on width >= 960 screen  -->
            <md-card-content hide show-gt-sm ng-if="!loading">
                <div layout layout-padding>
                    <div flex="30">
                        <span class="md-title user-info-title">Cédula</span>
                        <br/><span>{{userData.cedula}}</span>
                    </div>
                    <div flex="30" flex-offset="5">
                        <span class="md-title user-info-title">Ingreso</span>
                        <br/><span>{{userData.ingreso}}</span>
                    </div>
                    <div flex="30" flex-offset="5">
                        <span class="md-title user-info-title">Estado</span>
                        <br/><span>{{userData.status}}</span>
                    </div>
                </div>
                <div layout layout-padding>
                    <div flex="30">
                        <span class="md-title user-info-title">Sueldo</span>
                        <br/><span>Bs {{userData.sueldo | number:2}}</span>
                    </div>
                    <div flex="30" flex-offset="5">
                        <span class="md-title user-info-title">Dependencia</span>
                        <br/><span>Bs {{userData.dependencia | number:2}}</span>
                    </div>
                    <div flex="30" flex-offset="5">
                        <span class="md-title user-info-title">Fianzas</span>
                        <br/><span>Bs {{userData.fianzas | number:2}}</span>
                    </div>
                </div>
                <div layout layout-padding>
                    <div flex="30">
                        <span class="md-title user-info-title">Aporte</span>
                        <br/><span>{{userData.pcj_aporte}}%</span>
                    </div>
                    <div flex="30" flex-offset="5">
                        <span class="md-title user-info-title">Concurrencia</span>
                        <br/><span ng-style="getConcurranceWarn()"><b>{{userData.concurrencia}}%</b></span>
                    </div>
                    <div flex="30" flex-offset="5">
                        <span class="md-title user-info-title">Carga EGS</span>
                        <br/><span>{{userData.carga_egs}}%</span>
                    </div>
                </div>
                <div layout layout-padding>
                    <div flex="30">
                        <span class="md-title user-info-title">Carga EMI</span>
                        <br/><span>{{userData.carga_emi}}%</span>
                    </div>
                    <div flex="30" flex-offset="5">
                        <span class="md-title user-info-title">Carga GMM</span>
                        <br/><span>{{userData.carga_gmm}}%</span>
                    </div>
                    <div flex="30" flex-offset="5">
                        <span class="md-title user-info-title">Carga GMS</span>
                        <br/><span>{{userData.carga_gms}}%</span>
                    </div>
                </div>
                <div layout layout-padding>
                    <div flex="30">
                        <span class="md-title user-info-title">Carga HCM</span>
                        <br/><span>{{userData.carga_hcm}}%</span>
                    </div>
                    <div flex="30" flex-offset="5">
                        <span class="md-title user-info-title">Carga SEM</span>
                        <br/><span>{{userData.carga_sem}}%</span>
                    </div>
                    <div flex="30" flex-offset="5">
                        <span class="md-title user-info-title">Carga SF</span>
                        <br/><span>{{userData.carga_sf}}%</span>
                    </div>
                </div>
            </md-card-content>
            <!-- Show only on 600px <= width < 960px screen  -->
            <md-card-content hide show-sm ng-if="!loading">
                <div layout layout-padding>
                    <div flex="45">
                        <span class="md-title user-info-title">Cédula</span>
                        <br/><span>{{userData.cedula}}</span>
                    </div>
                    <div flex="45" flex-offset="10">
                        <span class="md-title user-info-title">Ingreso</span>
                        <br/><span>{{userData.ingreso}}</span>
                    </div>
                </div>
                <div layout layout-padding>
                    <div flex="45">
                        <span class="md-title user-info-title">Estado</span>
                        <br/><span>{{userData.status}}</span>
                    </div>
                    <div flex="45"  flex-offset="10">
                        <span class="md-title user-info-title">Sueldo</span>
                        <br/><span>Bs {{userData.sueldo | number:2}}</span>
                    </div>
                </div>
                <div layout layout-padding>
                    <div flex="45">
                        <span class="md-title user-info-title">Dependencia</span>
                        <br/><span>Bs {{userData.dependencia | number:2}}</span>
                    </div>
                    <div flex="45" flex-offset="10">
                        <span class="md-title user-info-title">Fianzas</span>
                        <br/><span>Bs {{userData.fianzas | number:2}}</span>
                    </div>
                </div>
                <div layout layout-padding>
                    <div flex="45">
                        <span class="md-title user-info-title">Aporte</span>
                        <br/><span>{{userData.pcj_aporte}}%</span>
                    </div>
                    <div flex="45" flex-offset="10">
                        <span class="md-title user-info-title">Concurrencia</span>
                        <br/><span ng-style="getConcurranceWarn()"><b>{{userData.concurrencia}}%</b></span>
                    </div>
                </div>
                <div layout layout-padding>
                    <div flex="45">
                        <span class="md-title user-info-title">Carga EGS</span>
                        <br/><span>{{userData.carga_egs}}%</span>
                    </div>
                    <div flex="45" flex-offset="10">
                        <span class="md-title user-info-title">Carga EMI</span>
                        <br/><span>{{userData.carga_emi}}%</span>
                    </div>
                </div>
                <div layout layout-padding>
                    <div flex="45">
                        <span class="md-title user-info-title">Carga GMM</span>
                        <br/><span>{{userData.carga_gmm}}%</span>
                    </div>
                    <div flex="45" flex-offset="10">
                        <span class="md-title user-info-title">Carga GMS</span>
                        <br/><span>{{userData.carga_gms}}%</span>
                    </div>
                </div>
                <div layout layout-padding>
                    <div flex="45">
                        <span class="md-title user-info-title">Carga HCM</span>
                        <br/><span>{{userData.carga_hcm}}%</span>
                    </div>
                    <div flex="45" flex-offset="10">
                        <span class="md-title user-info-title">Carga SEM</span>
                        <br/><span>{{userData.carga_sem}}%</span>
                    </div>
                </div>
                <div layout layout-padding>
                    <div flex>
                        <span class="md-title user-info-title">Carga SF</span>
                        <br/><span>{{userData.carga_sf}}%</span>
                    </div>
                </div>
            </md-card-content>
            <!-- Show only on width < 600px screen  -->
            <md-card-content hide show-xs ng-if="!loading">
                <div layout layout-padding>
                    <div flex="70">
                        <span class="md-title user-info-title">Cédula</span>
                        <br/><span>{{userData.cedula}}</span>
                    </div>
                </div>
                <div layout layout-padding>
                    <div flex>
                        <span class="md-title user-info-title">Ingreso</span>
                        <br/><span>{{userData.ingreso}}</span>
                    </div>
                </div>
                <div layout layout-padding>
                    <div flex>
                        <span class="md-title user-info-title">Estado</span>
                        <br/><span>{{userData.status}}</span>
                    </div>
                </div>
                <div layout layout-padding>
                    <div flex>
                        <span class="md-title user-info-title">Sueldo</span>
                        <br/><span>Bs {{userData.sueldo | number:2}}</span>
                    </div>
                </div>
                <div layout layout-padding>
                    <div flex>
                        <span class="md-title user-info-title">Dependencia</span>
                        <br/><span>Bs {{userData.dependencia | number:2}}</span>
                    </div>
                </div>
                <div layout layout-padding>
                    <div flex>
                        <span class="md-title user-info-title">Fianzas</span>
                        <br/><span>Bs {{userData.fianzas | number:2}}</span>
                    </div>
                </div>
                <div layout layout-padding>
                    <div flex>
                        <span class="md-title user-info-title">Aporte</span>
                        <br/><span>{{userData.pcj_aporte}}%</span>
                    </div>
                </div>
                <div layout layout-padding>
                    <div flex>
                        <span class="md-title user-info-title">Concurrencia</span>
                        <br/><span ng-style="getConcurranceWarn()"><b>{{userData.concurrencia}}%</b></span>
                    </div>
                </div>
                <div layout layout-padding>
                    <div flex>
                        <span class="md-title user-info-title">Carga EGS</span>
                        <br/><span>{{userData.carga_egs}}%</span>
                    </div>
                </div>
                <div layout layout-padding>
                    <div flex>
                        <span class="md-title user-info-title">Carga EMI</span>
                        <br/><span>{{userData.carga_emi}}%</span>
                    </div>
                </div>
                <div layout layout-padding>
                    <div flex>
                        <span class="md-title user-info-title">Carga GMM</span>
                        <br/><span>{{userData.carga_gmm}}%</span>
                    </div>
                </div>
                <div layout layout-padding>
                    <div flex>
                        <span class="md-title user-info-title">Carga GMS</span>
                        <br/><span>{{userData.carga_gms}}%</span>
                    </div>
                </div>
                <div layout layout-padding>
                    <div flex>
                        <span class="md-title user-info-title">Carga HCM</span>
                        <br/><span>{{userData.carga_hcm}}%</span>
                    </div>
                </div>
                <div layout layout-padding>
                    <div flex>
                        <span class="md-title user-info-title">Carga SEM</span>
                        <br/><span>{{userData.carga_sem}}%</span>
                    </div>
                </div>
                <div layout layout-padding>
                    <div flex>
                        <span class="md-title user-info-title">Carga SF</span>
                        <br/><span>{{userData.carga_sf}}%</span>
                    </div>
                </div>
            </md-card-content>
        </md-card>
    </md-content>
</main>
