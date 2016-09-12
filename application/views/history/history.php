<md-toolbar layout-padding>
    <div class="md-toolbar-tools" ng-hide="searchEnabled">
        <md-button href="#/home" class="md-icon-button">
            <md-icon>
                arrow_back
            </md-icon>
        </md-button>
        <h2 style="padding-right:10px;" class="md-headline">
            <span>{{appName}}</span>
        </h2>
        <!-- Filter search bar -->
        <div id="filter" hide-xs flex class="search-wrapper">
            <div layout layout-align="center center">
                <input
                    class="search-input"
                    aria-label="Filter"
                    placeholder="Filtre su búsqueda"
                    ng-model="filterInput"
                    type="text" />
                <md-icon class="search-icon">search</md-icon>
            </div>
            <md-tooltip md-direction="right">Puede buscar por nombre, acción o fecha</md-tooltip>
        </div>
        <span hide-gt-xs flex></span>
        <md-button
            id="toggle-search"
            class="md-icon-button"
            hide show-xs
            ng-click="toggleSearch()"
            aria-label="Search">
            <md-icon>search</md-icon>
            <md-tooltip md-direction="bottom">Buscar</md-tooltip>
        </md-button>
        <md-button class="md-icon-button" ng-click="showHelp()" aria-label="Help">
            <md-icon>help_outline</md-icon>
            <md-tooltip md-direction="down">Ayuda</md-tooltip>
        </md-button>
        <md-button class="md-icon-button" ng-click="logout()" aria-label="Logout">
            <md-icon>exit_to_app</md-icon>
            <md-tooltip md-direction="down">Cerrar sesión</md-tooltip>
        </md-button>
    </div>
    <!-- Mobile filter search bar -->
    <div class="md-toolbar-tools" ng-show="searchEnabled">
        <md-button href="#/home" class="md-icon-button">
            <md-icon>
                arrow_back
            </md-icon>
        </md-button>
        <div class="search-wrapper-xs" flex>
            <div layout layout-align="center center">
                <input
                    class="search-input"
                    aria-label="Filter"
                    placeholder="Filtre su búsqueda"
                    ng-model="filterInput"
                    type="text" />
                <md-icon ng-click="toggleSearch()" class="search-icon">close</md-icon>
            </div>
        </div>
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
                            id="action-summary"
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
