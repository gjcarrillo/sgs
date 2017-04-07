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
            <!-- New requests -->
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
                        ng-click="null"
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
                ng-if="showWatermark()"
                class="full-content-height"
                layout="column" layout-align="center center">
                <div class="watermark" layout="column" layout-align="center center">
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
            <md-content class="bg document-container">
                <!-- Request by ID -->
                <div ng-if="selectedAction == 2 && !fetching" layout class="margin-16">
                    <div layout="column">
                        <span>Ingrese el ID de la solicitud</span>
                        <md-input-container
                            class="no-vertical-margin"
                            md-no-float>
                            <input
                                placeholder="Ej: 332"
                                type="number"
                                min="1"
                                aria-label="requestId"
                                ng-model="queries[selectedAction]"
                                ng-keyup="!queries[selectedAction] ? null :
                                $event.keyCode == 13 && getRequestById(queries[selectedAction])">
                        </md-input-container>
                    </div>
                    <div layout layout-align="center center">
                        <md-button
                            ng-disabled="!queries[selectedAction]"
                            ng-click="getRequestById(queries[selectedAction])"
                            class="md-raised md-primary">
                            <md-icon>search</md-icon>Consultar
                        </md-button>
                    </div>
                </div>
                <!-- Request by date -->
                <div ng-if="selectedAction == 3 && !fetching" layout class="margin-16">
                    <div layout="column">
                        <p>Desde</p>
                        <md-datepicker class="bg" ng-model="queries[selectedAction].from" md-placeholder="Ingese fecha"></md-datepicker>
                    </div>
                    <div layout="column">
                        <p>Hasta</p>
                        <md-datepicker class="bg" ng-model="queries[selectedAction].to" md-placeholder="Ingese fecha"></md-datepicker>
                    </div>
                    <div layout layout-align="center end">
                        <md-button
                            ng-disabled="!queries[selectedAction].from || !queries[selectedAction].to"
                            ng-click="getRequestsByDate(queries[selectedAction].from, queries[selectedAction].to)"
                            class="md-raised md-primary">
                            <md-icon>search</md-icon>Consultar
                        </md-button>
                    </div>
                </div>
                <!-- Request by status -->
                <div ng-if="selectedAction == 4 && !fetching" layout class="margin-16">
                    <div layout="column">
                        <span>Elija el estatus</span>
                        <md-input-container
                            class="no-vertical-margin"
                            md-no-float>
                            <md-select
                                md-select-fix="queries[selectedAction]"
                                md-on-open="loadStatuses()"
                                placeholder="Estatus"
                                ng-model="queries[selectedAction]">
                                <md-option ng-repeat="(sKey, status) in statuses" ng-value="status">
                                    {{status}}
                                </md-option>
                            </md-select>
                        </md-input-container>
                    </div>
                    <div layout layout-align="center center">
                        <md-button
                            ng-disabled="!queries[selectedAction]"
                            ng-click="getRequestsByStatus(queries[selectedAction])"
                            class="md-raised md-primary">
                            <md-icon>search</md-icon>Consultar
                        </md-button>
                    </div>
                </div>
                <!-- Request by type -->
                <div ng-if="selectedAction == 5 && !fetching" layout class="margin-16">
                    <div layout="column">
                        <span>Elija el tipo de préstamo</span>
                        <md-input-container
                            class="no-vertical-margin"
                            md-no-float>
                            <md-select
                                md-select-fix="queries[selectedAction]"
                                placeholder="Tipo de préstamo"
                                ng-model="queries[selectedAction]">
                                <md-option ng-repeat="(lKey, loanType) in loanTypes" ng-value="lKey">
                                    {{loanType.DescripcionDelPrestamo}}
                                </md-option>
                            </md-select>
                        </md-input-container>
                    </div>
                    <div layout layout-align="center center">
                        <md-button
                            ng-disabled="!queries[selectedAction]"
                            ng-click="getRequestsByType(queries[selectedAction])"
                            class="md-raised md-primary">
                            <md-icon>search</md-icon>Consultar
                        </md-button>
                    </div>
                </div>
                <!-- Requests list -->
                <div class="margin-16" ng-show="!isObjEmpty(requests) && !fetching">
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
                                    <p ng-show="requests[lKey].length == 0">No se encontraron resultados</p>
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
                                                <td md-cell ng-click="goToDetails(request)">{{pad(request.id, 6)}}</td>
                                                <td md-cell ng-click="goToDetails(request)">{{request.creationDate}}</td>
                                                <td md-cell ng-click="goToDetails(request)">{{request.status}}</td>
                                                <td md-cell ng-click="goToDetails(request)">{{request.reqAmount | number:2}}</td>
                                                <td ng-if="!request.validationDate" md-cell ng-click="goToDetails(request)">
                                                    <md-icon style="color: red">
                                                        warning
                                                        <md-tooltip>Solicitud no validada</md-tooltip>
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
                    <md-card ng-if="editableReq.length > 0">
                        <md-toolbar class="md-table-toolbar md-default">
                            <div class="md-toolbar-tools">
                                <span>Solicitudes editables</span>
                            </div>
                        </md-toolbar>
                        <md-table-container>
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
                                    <td md-cell ng-click="goToDetails(request)">{{pad(request.id, 6)}}</td>
                                    <td md-cell ng-click="goToDetails(request)">{{request.creationDate}}</td>
                                    <td md-cell ng-click="goToDetails(request)">{{loanTypes[request.type].DescripcionDelPrestamo}}</td>
                                    <td md-cell ng-click="goToDetails(request)">{{request.reqAmount | number:2}}</td>
                                    <td md-cell ng-click="openEditRequestDialog($event, request)">
                                        <md-icon>
                                            edit
                                            <md-tooltip>Editar solicitud</md-tooltip>
                                        </md-icon>
                                    </td>
                                    <td md-cell ng-click="deleteRequest($event, request)">
                                        <md-icon>
                                            delete
                                            <md-tooltip>Eliminar solicitud</md-tooltip>
                                        </md-icon>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </md-table-container>
                        <md-table-pagination
                            md-label="{page: 'Página:', rowsPerPage: 'Filas por página:', of: 'de'}"
                            md-limit="query.limit"
                            md-limit-options="[5, 10, 15, 20]"
                            md-page="query.page"
                            md-total="{{editableReq.length}}"
                            md-page-select>

                        </md-table-pagination>
                    </md-card>
                </div>

                <!-- Specific type requests list -->
                <div class="margin-16" ng-if="selectedAction == 5 && !fetching">
                    <md-card ng-if="singleType.length > 0">
                        <md-toolbar class="md-table-toolbar md-default">
                            <div class="md-toolbar-tools">
                                <span>Solicitudes de tipo {{loanTypes[queries[selectedAction]].DescripcionDelPrestamo}}</span>
                            </div>
                        </md-toolbar>
                        <md-table-container>
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
                                <tr md-row ng-repeat="(rKey, request) in singleType | limitTo: query.limit: (query.page - 1) * query.limit track by $index">
                                    <td md-cell ng-click="goToDetails(request)">{{pad(request.id, 6)}}</td>
                                    <td md-cell ng-click="goToDetails(request)">{{request.creationDate}}</td>
                                    <td md-cell ng-click="goToDetails(request)">{{request.status}}</td>
                                    <td md-cell ng-click="goToDetails(request)">{{request.reqAmount | number:2}}</td>
                                    <td ng-if="!request.validationDate" md-cell ng-click="goToDetails(request)">
                                        <md-icon style="color: red">
                                            warning
                                            <md-tooltip>Solicitud no validada</md-tooltip>
                                        </md-icon>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </md-table-container>
                        <md-table-pagination
                            md-label="{page: 'Página:', rowsPerPage: 'Filas por página:', of: 'de'}"
                            md-limit="query.limit"
                            md-limit-options="[5, 10, 15, 20]"
                            md-page="query.page"
                            md-total="{{singleType.length}}"
                            md-page-select>

                        </md-table-pagination>
                    </md-card>
                </div>
            </md-content>
        </main>
        <md-divider></md-divider>
    </div>
</div>