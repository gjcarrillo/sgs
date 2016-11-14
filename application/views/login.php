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
    <div ng-hide="recovery.recoveryView" layout="column" layout-align="center center" layout-padding>
        <!-- <h1 class="md-title" style="font-weight:300; text-align:center">Todos tus documentos. Un solo lugar.</h1> -->
        <span class="md-subhead">Inicie sesión para ingresar al sistema</span>
    </div>
    <md-content>
        <div layout="column" layout-align="center center" style="background:#EEEEEE">
            <md-card class="login-card">
                <md-card-title layout layout-align="center center">
                    <md-card-title-media>
                        <div class="md-media-md card-media">
                            <img ng-src="{{loginImagePath}}" class="md-avatar" alt="Login Image">
                        </div>
                    </md-card-title-media>
                </md-card-title>
                <br/>
                <md-card-content>
                    <div layout flex-xs="90" flex-gt-xs="80">
                        <span
                            style="color:grey"
                            flex-offset-gt-xs="20"
                            flex-offset-xs="10">
                            Cédula de identidad
                        </span>
                    </div>
                    <div layout flex-xs="90" flex-gt-xs="80">
                        <md-input-container
                            flex-offset-gt-xs="20"
                            flex-offset-xs="10"
                            class="md-block">
                            <md-select
                                md-on-open="onIdOpen()"
                                md-on-close="onIdClose()"
                                ng-model="idPrefix"
                                aria-label="V or E ID">
                                <md-option value="V">
                                    V
                                </md-option>
                                <md-option value="E">
                                    E
                                </md-option>
                            </md-select>
                        </md-input-container>
                        <md-input-container
                            md-no-float
                            flex
                            class="md-block">
                            <input
                                type="number"
                                placeholder="Ej: 123456789"
                                ng-model="model.login"
                                ng-keyup="$event.keyCode == 13 && login()">
                        </md-input-container>
                    </div>
                    <div layout flex-xs="90" flex-gt-xs="80">
                        <span
                            style="color:grey"
                            flex-offset-gt-xs="20"
                            flex-offset-xs="10">
                            Contraseña
                        </span>
                    </div>
                    <div layout flex-xs="90" flex-gt-xs="80">
                        <md-input-container
                            md-no-float class="md-block"
                            flex
                            flex-offset-gt-xs="20"
                            flex-offset-xs="10">
                            <input
                                type="password"
                                placeholder="************"
                                ng-model="model.password"
                                ng-keyup="$event.keyCode == 13 && login()">
                        </md-input-container>
                    </div>
                    <div layout layout-align="center center">
                        <md-button ng-hide="loading" ng-click="login()" class="md-raised md-primary">
                            Iniciar sesión
                        </md-button>
                        <md-button
                            aria-label="logging in"
                            ng-if="loading"
                            ng-click="login()"
                            class="md-raised md-primary">
                            <div layout layout-align="center center">
                                <md-progress-circular
                                    md-theme="whiteInput"
                                    md-mode="indeterminate"
                                    md-diameter="30">
                                </md-progress-circular>
                            </div>
                        </md-button>
                    </div>
                    <br/>
                    <div layout layout-align="center center">
                        <span style="color:red; text-align:center">{{model.loginError}}</span>
                    </div>
                    <br/>
                </md-card-content>
            </md-card>

        </div>
    </md-content>
</main>
<md-divider></md-divider>
<footer hide-xs>
    <div layout layout-align="space-around center">
        <span>&copy; IPAPEDI 2016</span>
        <span>Desarrollado por
            <a class="md-accent" href="https://ve.linkedin.com/in/kristopherch" target="_blank">
                Kristopher Perdomo
            </a></span>
        <md-button class="md-accent" href="http://www.ipapedi.com" target="_blank">IPAPEDI</md-button>
    </div>
</footer>
<footer hide-gt-xs>
    <div layout layout-align="center center" layout-padding>
        <span>&copy; <a href="http://www.ipapedi.com" target="_blank">IPAPEDI</a> 2016,
            por <a href="https://ve.linkedin.com/in/kristopherch" target="_blank">Kristopher Perdomo</a></span>
    </div>
</footer>
</body>
</html>
