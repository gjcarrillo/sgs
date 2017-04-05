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
            Mis Solicitudes
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
                    <md-button applicant-help ng-click="showHelp()" aria-label="Tutorial">
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
<div layout>
    <!-- Loader -->
    <div>
        <div
            ng-if="loading"
            class="full-content-height center-vertical">
            <div layout layout-align="center" md-padding>
                <md-button class="md-fab md-raised" aria-label="Loading...">
                    <md-progress-circular md-mode="indeterminate" md-diameter="45"></md-progress-circular>
                </md-button>
            </div>
        </div>
        <md-divider></md-divider>
    </div>
    <!-- Overlay -->
    <overlay ng-if="overlay"/>
    <!-- Sidenav -->
    <md-sidenav
        id="requests-list"
        ng-show="contentAvailable"
        class="md-sidenav-left sidenav-frame"
        md-component-id="left"
        md-is-locked-open="$mdMedia('gt-sm') && contentLoaded">
        <md-content class="sidenav-height">
            <!-- Queries list -->
            <md-list class="sidenavList">
                <md-list-item ng-click="togglePanelList(1)">
                    <p class="sidenavTitle">
                        Consultar
                    </p>
                    <md-icon ng-class="md-secondary" ng-if="selectedList != 1">keyboard_arrow_down</md-icon>
                    <md-icon ng-class="md-secondary" ng-if="selectedList == 1">keyboard_arrow_up</md-icon>
                </md-list-item>
                <md-divider></md-divider>
                <div class="slide-toggle" ng-show="selectedList == 1" layout="column" layout-align="center" ng-repeat="query in queryList">
                    <md-button
                        ng-click="selectAction(query.id)"
                        class="requestItems"
                        ng-class="{'md-primary md-raised' : selectedAction == query.id}">
                        {{query.text}}
                    </md-button>
                    <md-divider></md-divider>
                </div>
            </md-list>

            <!-- New requests list -->
            <md-list class="sidenavList">
                <md-list-item ng-click="togglePanelList(2)">
                    <p class="sidenavTitle">
                        Nueva Solicitud
                    </p>
                    <md-icon ng-class="md-secondary" ng-if="selectedList != 2">keyboard_arrow_down</md-icon>
                    <md-icon ng-class="md-secondary" ng-if="selectedList == 2">keyboard_arrow_up</md-icon>
                </md-list-item>
                <md-divider></md-divider>
                <div class="slide-toggle" ng-show="selectedList == 2" layout="column" layout-align="center" ng-repeat="(lKey, loanType) in loanTypes">
                    <md-button
                        ng-click="openNewRequestDialog($event, lKey)"
                        class="requestItems"
                        ng-class="{'md-primary md-raised' : selectedAction == 'N' + lKey}">
                        {{loanType.description}}
                    </md-button>
                    <md-divider></md-divider>
                </div>
            </md-list>

            <!-- Refinancing request list -->
            <md-list class="sidenavList">
                <md-list-item ng-click="togglePanelList(3)">
                    <p class="sidenavTitle">
                        Refinanciamiento
                    </p>
                    <md-icon ng-class="md-secondary" ng-if="selectedList != 3">keyboard_arrow_down</md-icon>
                    <md-icon ng-class="md-secondary" ng-if="selectedList == 3">keyboard_arrow_up</md-icon>
                </md-list-item>
                <md-divider></md-divider>
                <div class="slide-toggle" ng-show="selectedList == 3" layout="column" layout-align="center" ng-repeat="(lKey, loanType) in loanTypes">
                    <md-button
                        ng-click="openRefinancingRequestDialog($event, lKey)"
                        class="requestItems"
                        ng-class="{'md-primary md-raised' : selectedAction == 'R' + lKey}">
                        {{loanType.description}}
                    </md-button>
                    <md-divider></md-divider>
                </div>
            </md-list>

            <!-- Edit requests -->
            <md-list class="sidenavList">
                <div layout="column" layout-align="center">
                    <md-button
                        class="sidenavTitle"
                        ng-click="selectAction('edit')"
                        ng-class="{'md-primary md-raised white-txt' : selectedAction == 'edit'}">
                        <span>Editar solicitudes</span>
                    </md-button>
                </div>
                <md-divider></md-divider>
            </md-list>
        </md-content>
    </md-sidenav>
    <!-- Content -->
    <div layout="column" flex>
        <main class="main-w-footer">
            <!-- Search error -->
            <div
                class="full-content-height md-padding"
                ng-if="fetchError != ''"
                layout layout-align="center center">
                <div layout="column" layout-align="center center" class="md-whiteframe-z2 error-card">
                    <span style="color:red">{{fetchError}}</span>
                </div>
            </div>
            <!-- Watermark -->
            <div
                ng-if="fetchError == '' && selectedAction != 1 && selectedAction != 'edit' && !loading && !fetching"
                class="full-content-height"
                layout="column" layout-align="center center">
                <div ng-if="!loading" class="watermark" layout="column" layout-align="center center">
                    <img src="images/ipapedi.png" alt="Ipapedi logo"/>
                </div>
            </div>
            <div
                ng-if="fetching"
                class="full-content-height"
                layout="column" layout-align="center center">
                    <md-progress-circular aria-label="Loading..." md-mode="indeterminate" md-diameter="60">
                    </md-progress-circular>
            </div>
            <!-- Requests list -->
            <md-content class="bg document-container">
                <div class="margin-16" ng-show="selectedAction == 1 && !fetching">
                    <md-expansion-panel-group md-component-id="requests">
                        <md-expansion-panel ng-repeat="(lKey, loanType) in loanTypes" md-component-id="{{lKey}}">
                            <md-expansion-panel-collapsed>
                                <div>Solicitudes de {{loanType.description}}</div>
                                <span flex></span>
                                <md-expansion-panel-icon></md-expansion-panel-icon>
                            </md-expansion-panel-collapsed>
                            <md-expansion-panel-expanded>
                                <md-expansion-panel-header>
                                    <div class="md-title">{{loanType.description}}</div>
                                    <div class="md-summary">Haga clic en una fila para ver más detalles de la solicitud</div>
                                    <md-expansion-panel-icon></md-expansion-panel-icon>
                                </md-expansion-panel-header>

                                <md-expansion-panel-content>
                                    <!-- Table of requests -->
                                    <p ng-show="requests[lKey].length == 0">Usted aún no posee solicitudes</p>
                                    <md-table-container ng-show="requests[lKey].length > 0">
                                        <table md-table md-row-select ng-model="selected">
                                            <thead md-head>
                                            <tr md-row>
                                                <th md-column><span>ID</span></th>
                                                <th md-column><span>Fecha</span></th>
                                                <th md-column><span>Estatus</span></th>
                                                <th md-column><span>Monto solicitado</span></th>
                                            </tr>
                                            </thead>
                                            <tbody md-body>
                                            <tr md-row ng-repeat="(rKey, request) in requests[lKey] | limitTo: query.limit: (query.page - 1) * query.limit track by $index">
                                                <td md-cell ng-click="goToDetails(lKey, rKey)">{{pad(request.id, 6)}}</td>
                                                <td md-cell ng-click="goToDetails(lKey, rKey)">{{request.creationDate}}</td>
                                                <td md-cell ng-click="goToDetails(lKey, rKey)">{{request.status}}</td>
                                                <td md-cell ng-click="goToDetails(lKey, rKey)">{{request.reqAmount | number:2}}</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </md-table-container>
                                    <md-table-pagination ng-if="requests[lKey].length > 0"
                                                         md-label="{page: 'Página:', rowsPerPage: 'Filas por página:', of: 'de'}"
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
                <!-- Editable requests list -->
                <div class="margin-16" ng-if="selectedAction == 'edit' && !fetching">
                    <!--<div layout layout-align="center center">-->
                    <!--    <div layout="column" layout-align="center center" class="md-whiteframe-z2 error-card">-->
                    <!--        <span>-->
                    <!--            Las solicitudes editables son sólo aquellas-->
                    <!--            que no han sido validadas.-->
                    <!--        </span>-->
                    <!--        <span ng-if="editableReq.length == 0" style="color:red">-->
                    <!--            <br/>Usted no posee solicitudes editables.-->
                    <!--        </span>-->
                    <!--    </div>-->
                    <!--</div>-->
                    <md-card ng-show="showMsg" md-theme="manual-card" class="margin-16">
                        <md-card-content layout layout-align="space-between start">
                            <span style="color: #2E7D32">
                                Le recordamos que las solicitudes editables son aquellas que aún no han sido validadas.
                            </span>
                            <md-button ng-click="showMsg = !showMsg" class="md-icon-button">
                                <md-icon>close</md-icon>
                                <md-tooltip>Cerrar</md-tooltip>
                            </md-button>
                        </md-card-content>
                    </md-card>
                    <div ng-if="editableReq.length == 0" class="margin-16">
                        <p style="color:red">
                            Usted no posee solicitudes editables.
                        </p>
                    </div>
                    <md-table-container ng-if="editableReq.length > 0">
                        <table md-table md-row-select ng-model="selected">
                            <thead md-head>
                            <tr md-row>
                                <th md-column><span>ID</span></th>
                                <th md-column><span>Fecha</span></th>
                                <th md-column><span>Tipo</span></th>
                                <th md-column><span>Monto solicitado</span></th>
                            </tr>
                            </thead>
                            <tbody md-body>
                            <tr md-row ng-repeat="(rKey, request) in editableReq | limitTo: query.limit: (query.page - 1) * query.limit track by $index">
                                <td md-cell ng-click="goToDetails(req.type, rKey)">{{pad(request.id, 6)}}</td>
                                <td md-cell ng-click="goToDetails(req.type, rKey)">{{request.creationDate}}</td>
                                <td md-cell ng-click="goToDetails(req.type, rKey)">{{request.status}}</td>
                                <td md-cell ng-click="goToDetails(req.type, rKey)">{{request.reqAmount | number:2}}</td>
                                <td md-cell ng-click="openEditRequestDialog($event, req.type)">
                                    <md-icon>
                                        edit
                                        <md-tooltip>Editar solicitud</md-tooltip>
                                    </md-icon>
                                </td>
                                <td md-cell ng-click="null">
                                    <md-icon>
                                        delete
                                        <md-tooltip>Eliminar solicitud</md-tooltip>
                                    </md-icon>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </md-table-container>
                    <md-table-pagination ng-if="requests[lKey].length > 0"
                                         md-label="{page: 'Página:', rowsPerPage: 'Filas por página:', of: 'de'}"
                                         md-limit="query.limit"
                                         md-limit-options="[5, 10, 15, 20]"
                                         md-page="query.page"
                                         md-total="{{requests[lKey].length}}"
                                         md-page-select>

                    </md-table-pagination>
                </div>
            </md-content>
        </main>
        <md-divider></md-divider>
    </div>
</div>