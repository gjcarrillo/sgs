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
        <h1 class="md-title" style="font-weight:300; text-align:center">Todos tus documentos. Un solo lugar.</h1>
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
                    <div layout>
                        <span style="color:grey" flex flex-offset="20">
                            Cédula de identidad
                        </span>
                    </div>
                    <!-- Show only on width >= 600px screen -->
                    <div hide show-gt-xs layout>
                        <div flex="5" flex-offset="20">
                            <md-input-container class="md-block">
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
                        </div>
                        <div flex="40" flex-offset="15">
                            <md-input-container md-no-float class="md-block">
                                <input
                                    type="text"
                                    placeholder="Ej: 123456789"
                                    ng-model="model.login"
                                    ng-keyup="$event.keyCode == 13 && login()">
                            </md-input-container>
                        </div>
                    </div>
                    <!-- Show only on width < 600px screen -->
                    <div hide show-xs layout>
                        <div flex="60" flex-offset="20">
                            <div>
                                <md-input-container class="md-block">
                                    <md-select ng-model="idPrefix" aria-label="V or E ID">
                                        <md-option value="V">
                                            V
                                        </md-option>
                                        <md-option value="E">
                                            E
                                        </md-option>
                                    </md-select>
                                </md-input-container>
                            </div>
                            <div>
                                <md-input-container md-no-float class="md-block">
                                    <input
                                        type="text"
                                        placeholder="Ej: 123456789"
                                        ng-model="model.login"
                                        ng-keyup="$event.keyCode == 13 && login()">
                                </md-input-container>
                            </div>
                        </div>
                    </div>
                    <div layout>
                        <span style="color:grey" flex flex-offset="20">
                            Contraseña
                        </span>
                    </div>
                    <div layout>
                        <md-input-container md-no-float class="md-block" flex="60" flex-offset="20">
                            <!-- <md-icon>lock</md-icon> -->
                            <input
                                type="password"
                                placeholder="************"
                                ng-model="model.password"
                                ng-keyup="$event.keyCode == 13 && login()">
                        </md-input-container>
                    </div>
                    <div layout layout-align="center center">
                        <span style="color:red">{{model.loginError}}</span>
                    </div>
                    <div layout layout-align="center center">
                        <md-button ng-click="login()" class="md-raised md-primary">
                            Iniciar sesión
                        </md-button>
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
