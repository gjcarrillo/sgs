<!-- Header -->
<md-toolbar layout-padding>
    <div layout layout-align="center center" class="md-toolbar-tools">
        <h1 class="md-headline" style="text-align:center">
            <span>Validación de Solicitud</span>
        </h1>
    </div>
</md-toolbar>
<!-- Content -->
<main class="main-w-footer">
    <md-content>
        <!-- Loader -->
        <div
            ng-if="loading"
            class="full-content-height bg">
            <div layout layout-align="center" md-padding>
                <md-button class="md-fab md-raised" aria-label="Loading...">
                    <md-progress-circular md-mode="indeterminate" md-diameter="45"></md-progress-circular>
                </md-button>
            </div>
        </div>
        <div
            ng-if="!loading"
            class="full-content-height bg"
            layout="column" layout-align="center center">
            <md-icon
                ng-if="errorMsg"
                class="error-svg"
                md-svg-src="error">
            </md-icon>
            <p ng-if="errorMsg" style="padding: 16px">
                Ha ocurrido un error durante la verificación.
                {{errorMsg}}
            </p>
            <md-icon
                ng-if="!errorMsg"
                style="padding: 16px"
                class="verified-user-svg"
                md-svg-src="verified-user">
            </md-icon>
            <p ng-if="!errorMsg">Solicitud verificada exitosamente.</p>
            <md-button ng-if="!errorMsg" ng-click="go()" class="md-raised md-primary">Ingresar</md-button>
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
