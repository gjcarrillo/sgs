<md-toolbar layout-padding>
    <div class="md-toolbar-tools">
        <h2 class="md-headline">
            <span>Detalles de usuario</span>
        </h2>
        <span flex></span>
        <md-button class="md-icon-button" user-info-help ng-click="showHelp()" aria-label="Help">
            <md-icon>help_outline</md-icon>
            <md-tooltip md-direction="bottom">Ayuda</md-tooltip>
        </md-button>
        <md-button class="md-icon-button" ng-click="logout()" aria-label="Logout">
            <md-icon>exit_to_app</md-icon>
            <md-tooltip md-direction="bottom">Cerrar sesión</md-tooltip>
        </md-button>
    </div>
</md-toolbar>
<main>
    <md-toolbar>
    </md-toolbar>
    <md-content class="u-overflow-fix bg" layout layout-align="center center">
        <md-card id="info-card" class="user-info-card">
            <md-card-header>
                <md-card-avatar>
                    <img class="md-user-avatar" ng-src="{{picture}}"/>
                </md-card-avatar>
                <md-card-header-text>
                    <div class="md-toolbar-tools">
                        <h2 class="md-headline">{{userName}}</h2>
                    </div>
                </md-card-header-text>
            </md-card-header>
            <md-divider></md-divider>
            <md-progress-linear md-mode="query" ng-if="loading"></md-progress-linear>
            <md-card-content ng-if="!loading">
                <div layout layout-xs="column" layout-align="space-around start" layout-align-xs="start start">
                    <!-- User personal data -->
                    <div layout="column" layout-padding>
                        <span class="user-info-heading">DATOS PERSONALES</span>
                        <!-- ID -->
                        <div layout="column">
                            <span class="md-title user-info-title">Cédula</span>
                            <span>{{userData.cedula}}</span>
                        </div>
                        <!-- Admission date -->
                        <div layout="column">
                            <span class="md-title user-info-title">Fecha de ingreso</span>
                            <span>{{userData.ingreso}}</span>
                        </div>
                        <div layout="column">
                            <span class="md-title user-info-title">Estatus</span>
                            <span>{{userData.status}}</span>
                        </div>
                        <!-- Salary -->
                        <div layout="column">
                            <span class="md-title user-info-title">Sueldo</span>
                            <span>Bs. {{userData.sueldo | number:2}}</span>
                        </div>
                        <!-- Concurrence -->
                        <div layout="column">
                            <span class="md-title user-info-title">Concurrencia</span>
                            <span ng-style="getConcurranceWarn()">Bs. {{userData.concurrencia}}</span>
                        </div>
                        <!-- Contribution percentage -->
                        <div layout="column">
                            <span class="md-title user-info-title">Porcentaje de aportes</span>
                            <span>{{userData.pcj_aporte}}%</span>
                        </div>
                    </div>
                    <!-- User contribution data -->
                    <div layout="column">
                        <div layout layout-xs="column" layout-align="space-between start" layout-align-xs="start start">
                            <!-- User personal contribution data -->
                            <div layout="column" layout-padding>
                                <span class="user-info-heading">APORTES PERSONALES (AP)</span>
                                <!-- Balance -->
                                <div layout="column">
                                    <span class="md-title user-info-title">Saldo de estado</span>
                                    <span>Bs. {{userContribution.p_saldo_edo | abs | number:2}}</span>
                                </div>
                                <!-- Pending movements -->
                                <div layout="column">
                                    <span class="md-title user-info-title">Movimientos pendientes</span>
                                    <span>Bs. {{userContribution.p_mov_pendientes | abs | number:2}}</span>
                                </div>
                                <!-- Debt UC/AP -->
                                <div layout="column">
                                    <span class="md-title user-info-title">Deuda UC/AP</span>
                                    <span>Bs. {{userContribution.p_deuda_uc | abs | number:2}}</span>
                                </div>
                                <!-- Available balance -->
                                <div layout="column">
                                    <span class="md-title user-info-title">Saldo disponible</span>
                                    <span>Bs. {{userContribution.p_saldo_disp | abs | number:2}}</span>
                                </div>
                            </div>
                            <!-- User's university contribution data -->
                            <div layout="column" layout-padding>
                                <span class="user-info-heading">APORTES UNIVERSITARIOS (AU)</span>
                                <!-- Balance -->
                                <div layout="column">
                                    <span class="md-title user-info-title">Saldo de estado</span>
                                    <span>Bs. {{userContribution.u_saldo_edo | abs | number:2}}</span>
                                </div>
                                <!-- Pending movements -->
                                <div layout="column">
                                    <span class="md-title user-info-title">Movimientos pendientes</span>
                                    <span>Bs. {{userContribution.u_mov_pendientes | abs | number:2}}</span>
                                </div>
                                <!-- Debt UC/AU -->
                                <div layout="column">
                                    <span class="md-title user-info-title">Deuda UC/AU</span>
                                    <span>Bs. {{userContribution.u_deuda_uc | abs | number:2}}</span>
                                </div>
                                <!-- Available balance -->
                                <div layout="column">
                                    <span class="md-title user-info-title">Saldo disponible</span>
                                    <span>Bs. {{userContribution.u_saldo_disp | abs | number:2}}</span>
                                </div>
                            </div>
                        </div>
                        <md-card md-theme="help-card">
                            <md-card-title layout layout-align="center center">
                                <span class="user-info-heading">TOTAL DE APORTES GLOBALES</span>
                            </md-card-title>
                            <md-divider></md-divider>
                            <md-card-content>
                                <div layout layout-align="center center">
                                    <span>Bs. {{userContribution.totalContribution | abs | number:2}}</b></span>
                                </div>
                            </md-card-content>
                        </md-card>
                    </div>
                </div>
            </md-card-content>
        </md-card>
    </md-content>
</main>
