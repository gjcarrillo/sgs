<md-toolbar layout-padding>
    <div class="md-toolbar-tools">
        <h2 class="md-headline">
            <span>Sesión culminada</span>
        </h2>
    </div>
</md-toolbar>
<main class="main-w-footer">
    <md-content class="bg" layout layout-align="center center">
        <div class="full-content-height" layout="column" layout-align="center center">
            <md-card class="expired-card">
                <md-card-title>
                    <md-card-title-text>
                        <span class="md-headline">Su sesión ha expirado</span>
                        <span class="md-subhead">

                        </span>
                    </md-card-title-text>
                </md-card-title>
                <md-card-content>
                    <div layout layout-padding>
                        <md-card-title-media>
                            <md-icon
                                class="grey-svg"
                                md-svg-src="expired">
                            </md-icon>
                        </md-card-title-media>
                        <p>Por favor vuelva a iniciar sesión haciendo clic en
                            INICIAR SESIÓN.
                        </p>
                    </div>
                </md-card-content>
                <md-card-actions layout layout-align="end center">
                    <md-button ng-click="goToLogin()" class="md-primary">Iniciar sesión</md-button>
                </md-card-actions>
            </md-card>
        </div>
    </md-content>
</main>
<md-divider></md-divider>
