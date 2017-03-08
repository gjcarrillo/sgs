<!-- Header -->
<md-toolbar layout-padding>
    <div layout layout-align="center center" class="md-toolbar-tools">
        <h1 class="md-headline" style="text-align:center">
            <span>Sistema de Gestión de Documentos de Préstamo</span>
        </h1>
    </div>
</md-toolbar>
<!-- Content -->
<main class="main-w-footer">
    <md-content class="bg"
                perspective
                agent-btn="go-agent"
                manager-btn="go-manager"
                agent-help="agent-help"
                manager-help="manager-help">
        <div layout="column" layout-align="center center">
            <h1 class="md-title" style="text-align:center">{{welcomeMsg}}</h1>
            <span style="text-align:center" class="md-subhead">¿Con qué tipo de cuenta desea ingresar al sistema?</span>
        </div>
        <br /><br />
        <md-list layout layout-xs="column" layout-align="center center">
            <md-list-item>
                <md-button class="md-grid-item-content" ng-click="goApplicant()">
                    <div layout="column" layout-align="center center">
                        <md-icon
                            class="perspective-svg"
                            md-svg-src="account-box"></md-icon>
                        <div class="md-grid-text">Afiliado</div>
                    </div>
                </md-button>
            </md-list-item>
            <md-list-item id="go-agent">
                <md-button class="md-grid-item-content" ng-click="goAgent()">
                    <div layout="column" layout-align="center center">
                        <md-icon
                            class="perspective-svg"
                            md-svg-src="assignment"></md-icon>
                        <div class="md-grid-text">Agente</div>
                    </div>
                </md-button>
            </md-list-item>
            <md-list-item id="go-manager">
                <md-button class="md-grid-item-content" ng-click="goManager()">
                    <div layout="column" layout-align="center center">
                        <md-icon
                            class="perspective-svg"
                            md-svg-src="assessment"></md-icon>
                        <div class="md-grid-text">Gerente</div>
                    </div>
                </md-button>
            </md-list-item>
        </md-list>
        <br />
        <!-- HELP -->
        <div layout layout-align="center center">
            <md-card md-theme="help-card" class="help-card">
                <md-card-title>
                    <div layout layout-align="center center">
                        <md-icon style="color:#827717">info_outline</md-icon>
                        <span class="help-title">Tipos de cuenta</span>
                    </div>
                </md-card-title>
                <md-divider></md-divider>
                <md-card-content>
                    <p>
                        <b>AFILIADO</b>: Consulta y crea sus propias solicitudes.
                    </p>
                    <p id="agent-help">
                        <b>AGENTE</b>: Consulta y crea solicitudes para cualquier afiliado.
                    </p>
                    <p id="manager-help">
                        <b>GERENTE</b>: Gestiona las solicitudes del sistema y genera reportes.
                    </p>
                </md-card-content>
            </md-card>
        </div>
    </md-content>
</main>
<md-divider></md-divider>