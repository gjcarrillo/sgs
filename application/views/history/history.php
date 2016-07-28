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
        <!-- Search bar -->
        <md-input-container md-no-float class="md-accent" flex="30" flex-offset="20" style="padding-bottom:0px;margin-right:25px">
           <md-icon style="color:white" class="material-icons">&#xE8B6;</md-icon>
           <input
           placeholder="Filtre su búsqueda"
           aria-label="Search"
           ng-model="filterInput"
           style="color:white; padding-left:25px; margin-right:5px; font-size:16px">
           <md-tooltip md-direction="right">Puede buscar por nombre, acción o fecha</md-tooltip>
        </md-input-container>
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
                    <h2 class="md-headline">Historial de acciones</h2>
                </div>
            </md-card-title>
            <md-divider></md-divider>
            <md-progress-linear md-mode="query" ng-if="loading"></md-progress-linear>
            <md-card-content>
                <md-list>
                    <div ng-repeat="(hKey, hist) in history | filter:filterInput">
                        <md-list-item
                            class="md-3-line"
                            ng-click="showListBottomSheet(hist)"
                            class="noright">
                            <md-icon  ng-style="{'color':'#2196F3', 'font-size':'36px'}">account_circle</md-icon>
                            <div class="md-list-item-text" layout="column">
                               <h3>{{hist.userResponsable}}</h3>
                               <h4>{{hist.title}}</h4>
                               <p>{{hist.date}}</p>
                             </div>
                             <md-button aria-label="See details" class="md-icon-button">
                                 <md-icon aria-label="Eye icon" ng-click="null" class="md-secondary">remove_red_eye</md-icon>
                             </md-button>
                        </md-list-item>
                        <md-divider ng-if="!$last" class="md-inset"></md-divider>
                    </div>
                </md-list>
            </md-card-content>
        </md-card>
    </md-content>
</main>
