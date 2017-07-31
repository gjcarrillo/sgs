<!-- Header -->
<md-toolbar layout-padding>
    <div class="md-toolbar-tools">
        <md-button ng-click="goBack()" class="md-icon-button">
            <md-icon>
                arrow_back
            </md-icon>
        </md-button>
        <md-button
            id="nav-panel"
            ng-show="contentAvailable"
            hide-gt-sm
            class="md-icon-button"
            ng-click="openMenu()"
            aria-label="Open sidenav">
            <md-icon>menu</md-icon>
        </md-button>
        <h2 class="md-headline">
            Solicitudes Pre-Aprobadas
        </h2>
        <span flex></span>
        <md-menu>
            <md-button
                ng-click="$mdOpenMenu($event)"
                class="md-icon-button"
                aria-label="Help">
                <md-icon>
                    help_outline
                </md-icon>
                <md-tooltip md-direction="bottom">Ayuda</md-tooltip>
            </md-button>
            <md-menu-content>
                <md-menu-item>
                    <md-button ng-click="showHelp()" aria-label="Tutorial">
                        <md-icon>live_help</md-icon>
                        Diálogo de ayuda
                        <md-tooltip md-direction="bottom">Ayuda</md-tooltip>
                    </md-button>
                </md-menu-item>
                <md-menu-item>
                    <md-button ng-click="downloadManual()">
                        <md-icon class="md-secondary">
                            file_download
                        </md-icon>
                        Descargar manual
                    </md-button>
                </md-menu-item>
            </md-menu-content>
        </md-menu>
        <md-button class="md-icon-button" ng-click="logout()" aria-label="Logout">
            <md-icon>exit_to_app</md-icon>
            <md-tooltip md-direction="bottom">Cerrar sesión</md-tooltip>
        </md-button>
    </div>
</md-toolbar>
<main class="main-w-footer">
    <!-- Loader -->
    <div
        ng-if="loading"
        class="full-content-height center-vertical">
        <div layout layout-align="center" md-padding>
            <md-button class="md-fab md-raised" aria-label="Loading...">
                <md-progress-circular md-mode="indeterminate" md-diameter="45"></md-progress-circular>
            </md-button>
        </div>
    </div>
    <!-- Overlay -->
    <overlay ng-if="overlay"/>
    <md-content ng-if="!loading" class="bg">
        <div class="margin-16" id="requests-group">
            <md-expansion-panel-group md-component-id="requests">
                <md-expansion-panel ng-repeat="(lKey, loanType) in loanTypes" md-component-id="{{lKey}}">
                    <md-expansion-panel-collapsed class="pointer">
                        <div>Solicitudes de {{loanType.DescripcionDelPrestamo}}</div>
                        <span flex></span>
                        <md-expansion-panel-icon></md-expansion-panel-icon>
                    </md-expansion-panel-collapsed>
                    <md-expansion-panel-expanded>
                        <md-expansion-panel-header>
                            <div class="md-title">{{loanType.DescripcionDelPrestamo}}</div>
                            <div class="md-summary"></div>
                            <md-expansion-panel-icon></md-expansion-panel-icon>
                        </md-expansion-panel-header>

                        <md-expansion-panel-content>
                            <!-- Table of requests -->
                            <p ng-show="requests[lKey].length == 0">No se encontraron resultados</p>
                            <md-table-container ng-show="requests[lKey].length > 0">
                                <table md-table md-row-select ng-model="selected">
                                    <thead md-head>
                                    <tr md-row>
                                        <th md-column><span>ID</span></th>
                                        <th md-column><span>Fecha</span></th>
                                        <th md-column><span>Estatus</span></th>
                                        <th md-column><span>Solicitante</span></th>
                                        <th md-column><span>Monto solicitado</span></th>
                                        <th md-column><span>Monto aprobado</span></th>
                                    </tr>
                                    </thead>
                                    <tbody md-body>
                                    <tr md-row ng-repeat="(rKey, request) in requests[lKey] | limitTo: query.limit: (query.page - 1) * query.limit track by $index">
                                        <td md-cell ng-click="goToDetails(request)"><a>{{pad(request.id, 6)}}</a></td>
                                        <td md-cell ng-click="goToDetails(request)">{{request.creationDate}}</td>
                                        <td md-cell ng-click="goToDetails(request)">{{request.status}}</td>
                                        <td md-cell ng-click="loadUserData(request.userOwner)">
                                            <a>{{request.userOwner}}</a>
                                            <md-tooltip>{{request.userOwnerName}}</md-tooltip>
                                        </td>
                                        <td md-cell ng-click="goToDetails(request)">{{request.reqAmount | number:2}}</td>
                                        <td md-cell ng-click="goToDetails(request)">
                                            {{(request.approvedAmount | number:2) || '----'}}
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </md-table-container>
                            <md-table-pagination ng-if="requests[lKey].length > 0"
                                                 md-label="{page: 'Página:', rowsPerPage: 'Solicitudes por página:', of: 'de'}"
                                                 md-limit="query.limit"
                                                 md-limit-options="[5, 10, 15, 20]"
                                                 md-page="query.page"
                                                 md-total="{{requests[lKey].length}}"
                                                 md-page-select>

                            </md-table-pagination>
                        </md-expansion-panel-content>

                        <md-expansion-panel-footer>
                            <div flex></div>
                            <md-button class="md-warn" ng-click="$panel.collapse()">Cerrar</md-button>
                        </md-expansion-panel-footer>
                    </md-expansion-panel-expanded>
                </md-expansion-panel>
            </md-expansion-panel-group>
        </div>
    </md-content>
</main>
<md-divider></md-divider>