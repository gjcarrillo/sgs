<!-- Toolbar -->
<md-toolbar layout-padding>
    <div class="md-toolbar-tools">
        <md-button ng-click="return()" class="md-icon-button">
            <md-icon>
                arrow_back
            </md-icon>
        </md-button>
        <md-button
            id="nav-panel"
            hide-gt-sm
            class="md-icon-button"
            ng-click="openMenu()"
            aria-label="Open sidenav">
            <md-icon>menu</md-icon>
        </md-button>
        <h2 class="md-headline">
            <span>Gestión de Solicitudes</span>
        </h2>
        <span flex></span>
        <div hide show-gt-sm id="manager-options">
            <md-button class="md-icon-button" ng-click="openManageUserAgentDialog($event)" aria-label="Manage Agents">
                <md-icon>account_box</md-icon>
                <md-tooltip md-direction="bottom">Administrar usuarios agentes</md-tooltip>
            </md-button>
            <md-button class="md-icon-button" ng-click="openConfigDialog($event)" aria-label="System Configuration">
                <md-icon>settings</md-icon>
                <md-tooltip md-direction="bottom">Configuración del sistema</md-tooltip>
            </md-button>
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
                        <md-button manager-help ng-click="showHelp()" aria-label="Tutorial">
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
        <!-- Show when screen width < 960px -->
        <md-menu
            hide-gt-sm>
            <md-button
                id="manager-options-menu"
                ng-click="$mdOpenMenu($event)"
                class="md-icon-button"
                aria-label="More">
                <md-icon class="md-secondary">
                    more_vert
                </md-icon>
            </md-button>
            <md-menu-content>
                <md-menu-item>
                    <md-button ng-click="openManageUserAgentDialog($event)" aria-label="Manage Agents">
                        <md-icon>account_box</md-icon>
                        Administrar agentes
                    </md-button>
                </md-menu-item>
                <md-menu-item>
                    <md-button ng-click="openConfigDialog($event)" aria-label="System Configuration">
                        <md-icon>settings</md-icon>
                        Configuración del sistema
                    </md-button>
                </md-menu-item>
                <md-menu-item>
                    <md-button ng-click="showHelp()" manager-help aria-label="Help">
                        <md-icon>help_outline</md-icon>
                        Ayuda
                    </md-button>
                </md-menu-item>
                <md-menu-item>
                    <md-button ng-click="logout()" aria-label="Logout">
                        <md-icon>exit_to_app</md-icon>
                        Cerrar sesión
                    </md-button>
                </md-menu-item>
            </md-menu-content>
        </md-menu>
    </div>
</md-toolbar>
<div layout>
    <!-- Sidenav -->
    <md-sidenav
        class="md-sidenav-left sidenav-frame"
        ng-show="contentAvailable"
        md-component-id="left"
        md-is-locked-open="$mdMedia('gt-sm') && contentLoaded">
        <md-content class="sidenav-height">
            <md-progress-linear md-mode="query" ng-if="loading"></md-progress-linear>
            <!-- Query selection -->
            <div ng-show="showOptions && !loading">
                <md-list class="sidenavList">
                    <md-list-item ng-click="togglePanelList(1)">
                        <p class="sidenavTitle">
                            Búsqueda avanzada
                        </p>
                        <md-icon ng-class="md-secondary" ng-if="selectedList != 1">keyboard_arrow_down</md-icon>
                        <md-icon ng-class="md-secondary" ng-if="selectedList == 1">keyboard_arrow_up</md-icon>
                    </md-list-item>
                    <md-divider></md-divider>
                    <div class="slide-toggle"
                         ng-show="selectedList == 1"
                         layout="column"
                         layout-align="center"
                         ng-repeat="query in queries | filter: {category: 'req'}">
                        <md-button
                            ng-click="selectAction(query.id)"
                            class="requestItems"
                            ng-class="{'md-primary md-raised' : selectedAction == query.id}">
                            {{query.name}}
                        </md-button>
                        <md-divider></md-divider>
                    </div>
                </md-list>
                <!-- Reports generation -->
                <md-list class="sidenavList">
                    <md-list-item ng-click="togglePanelList(2)" id="approval-report">
                        <p class="sidenavTitle">
                            Reporte de solicitudes
                        </p>
                        <md-icon ng-class="md-secondary" ng-if="selectedList != 2">keyboard_arrow_down</md-icon>
                        <md-icon ng-class="md-secondary" ng-if="selectedList == 2">keyboard_arrow_up</md-icon>
                    </md-list-item>
                    <md-divider></md-divider>
                    <div class="slide-toggle"
                         ng-show="selectedList == 2"
                         layout="column"
                         layout-align="center"
                         ng-repeat="query in queries | filter: {category: 'report'}">
                        <md-button
                            ng-click="selectAction(query.id)"
                            class="requestItems"
                            ng-class="{'md-primary md-raised' : selectedAction == query.id}">
                            {{query.name}}
                        </md-button>
                        <md-divider></md-divider>
                    </div>
                    <!-- Pending requests -->
                    <md-list id="pending-req">
                        <div layout="column" layout-align="center">
                            <md-button class="sidenavTitle"
                                       ng-click="selectAction('pending')"
                                       ng-class="{'md-primary md-raised white-txt' : selectedAction == 'pending'}">
                                <span>Solicitudes pendientes</span>
                            </md-button>
                        </div>
                    </md-list>
                    <md-divider></md-divider>
            </div>
            <!-- Result for specific user requests query -->
            <div ng-if="showResult == 0">
                <div id="back-to-query" class="md-toolbar-tools md-whiteframe-z1">
                    <md-button ng-click="goBack()" class="md-icon-button">
                        <md-icon>arrow_back</md-icon>
                    </md-button>
                    <span>Atrás</span>
                </div>
                <md-list class="sidenavList">
                    <md-list-item id="user-data" ng-click="loadUserData(fetchId)">
                        <p class="sidenavTitle">
                            Datos del afiliado
                        </p>
                    </md-list-item>
                    <md-divider></md-divider>
                    <md-list-item ng-click="showPie()">
                        <p class="sidenavTitle">
                            Estadísticas
                        </p>
                    </md-list-item>
                    <md-divider></md-divider>
                    <md-list id="result-data">
                        <div layout="column" layout-align="center">
                            <md-button class="sidenavTitle"
                                       ng-click="showRequestList()"
                                       ng-class="{'md-primary md-raised white-txt' : !pieloaded}">
                                <span>Lista de solicitudes</span>
                            </md-button>
                        </div>
                        <md-divider></md-divider>
                    </md-list>
                </md-list>
            </div>
            <!-- Result for multiple users requests query -->
            <div ng-if="fetchedRequests()">
                <div id="back-to-query" class="md-toolbar-tools md-whiteframe-z1">
                    <md-button ng-click="goBack()" class="md-icon-button">
                        <md-icon>arrow_back</md-icon>
                    </md-button>
                    <span>Atrás</span>
                </div>
                <md-list class="sidenavList">
                    <md-list-item ng-click="showPie()">
                        <p class="sidenavTitle">
                            Estadísticas
                        </p>
                    </md-list-item>
                    <md-divider></md-divider>
                    <div id="result-data" ng-repeat="(rKey, request) in requests" ng-if="request.length > 0">
                        <md-list-item ng-click="toggleList(rKey)">
                            <p class="sidenavTitle">
                                {{listTitle[rKey]}}
                            </p>
                            <md-icon ng-class="md-secondary" ng-if="!showList[rKey]">keyboard_arrow_down</md-icon>
                            <md-icon ng-class="md-secondary" ng-if="showList[rKey]">keyboard_arrow_up</md-icon>
                        </md-list-item>
                        <md-divider></md-divider>
                        <div class="slide-toggle" ng-show="showList[rKey]">
                            <div layout="column" layout-align="center" ng-repeat="(lKey, loan) in request">
                                <md-button
                                    ng-click="selectRequest(rKey, lKey)"
                                    class="requestItems"
                                    ng-class="{'md-primary md-raised' : selectedReq === rKey &&
                                            selectedLoan === lKey }">
                                    <md-icon ng-if="showResult !== 1"
                                             ng-style="getBulbColor(loan.status, rKey, lKey)">
                                        lightbulb_outline
                                    </md-icon>
                                    Solicitud ID &#8470; {{pad(loan.id, 6)}}
                                    <md-icon
                                        ng-if="loan.status === PRE_APPROVED_STRING && showResult == 1 &&
                                           model.perform[1].status == RECEIVED_STRING"
                                        style="color:#4CAF50">
                                        check_circle
                                    </md-icon>
                                    <md-icon
                                        ng-if="loan.status === REJECTED_STRING && showResult == 1  &&
                                           model.perform[1].status == RECEIVED_STRING"
                                        style="color:#F44336">
                                        check_circle
                                    </md-icon>
                                </md-button>
                                <md-divider ng-if="$last"></md-divider>
                            </div>
                        </div>
                    </div>
                </md-list>
            </div>
        </md-content>
    </md-sidenav>
    <!-- Content -->
    <div layout="column" flex>
        <main class="main-w-footer">
            <!-- Loader -->
            <div
                ng-if="loadingContent"
                class="full-content-height"
                layout="column" layout-align="center center">
                <md-progress-circular aria-label="Loading..." md-mode="indeterminate" md-diameter="60">
                </md-progress-circular>
            </div>
            <!-- Watermark -->
            <div
                class="full-content-height"
                layout layout-align="center center"
                ng-if="showWatermark()">
                <div class="watermark" layout="column" layout-align="center center">
                    <img src="images/ipapedi.png" alt="Ipapedi logo"/>
                </div>
            </div>
            <!-- Pie chart statistics result -->
            <div layout layout-align="center center" class="full-content-height"
                ng-show="pieloaded">
                <div
                    id="piechart-tour"
                    layout="column"
                    layout-align="center center"
                    layout-padding
                    class="md-whiteframe-z2 statistics-card">
                    <div layout layout-align="center center">
                        <span>{{statisticsTitle}}</span>
                        <md-button
                            id="report-btn"
                            ng-hide="loadingReport"
                            class="md-icon-button"
                            ng-click="generateExcelReport()">
                            <md-icon>assignment</md-icon>
                            <md-tooltip md-direction="top">
                                Generar reporte
                            </md-tooltip>
                        </md-button>
                        <md-progress-circular
                            ng-show="loadingReport"
                            md-mode="indeterminate"
                            md-diameter="40">
                        </md-progress-circular>
                    </div>
                    <canvas id="piechart" width="300" height="300"></canvas>
                </div>
            </div>
            <!-- Pie error -->
            <div
                class="full-content-height md-padding"
                ng-if="pieError != '' && !req"
                layout layout-align="center center">
                <div layout="column" layout-align="center center" class="md-whiteframe-z2 pie-error-card">
                    <span style="color:red">{{pieError}}</span>
                </div>
            </div>
            <!-- Approved amount result -->
            <div layout layout-align="center center" class="full-content-height" ng-if="showApprovedAmount">
                <div
                    layout="column"
                    layout-align="center center"
                    layout-padding class="md-whiteframe-z3 information-card">
                    <span>{{approvedAmountTitle}}</span>
                    <h1 style="font-weight:300" class="md-display-1">Bs {{approvedAmount | number:2}}</h1>
                </div>
            </div>
            <md-content class="bg document-container">
                <!-- Query by request ID -->
                <div ng-show="selectedAction == 10" layout-padding layout="column">
                    <br/>
                    <div>
                        Ingrese el ID de la solicitud
                    </div>
                    <md-input-container
                        class="no-vertical-margin"
                        md-no-float>
                        <input
                            placeholder="Ej: 253"
                            type="number"
                            min="0"
                            aria-label="requestId"
                            ng-model="model.perform[10].id"
                            ng-keyup="$event.keyCode == 13 && fetchRequestById(selectedAction)">
                    </md-input-container>
                    <div layout layout-align="center center">
                        <md-button
                            ng-disabled="!model.perform[10].id"
                            ng-click="fetchRequestById(model.perform[10].id)"
                            class="md-raised md-primary">
                            <md-icon>search</md-icon>Consultar
                        </md-button>
                    </div>
                </div>
                <!-- Query by specific user -->
                <div ng-show="selectedAction == 0 && showResult == null" layout-padding layout="column">
                    <br/>
                    <div>
                        Ingrese cédula de identidad
                    </div>
                    <div layout layout-align="start start">
                        <md-input-container
                            class="no-vertical-margin"
                            md-no-float>
                            <md-select
                                md-select-fix="idPrefix"
                                aria-label="V or E ID"
                                ng-model="idPrefix">
                                <md-option value="V">
                                    V
                                </md-option>
                                <md-option value="E">
                                    E
                                </md-option>
                            </md-select>
                        </md-input-container>
                        <md-input-container
                            class="no-vertical-margin"
                            md-no-float>
                            <input
                                placeholder="Ej: 123456789"
                                type="number"
                                min="0"
                                aria-label="userId"
                                ng-model="model.perform[0].id"
                                ng-keyup="$event.keyCode == 13 && fetchUserRequests(selectedAction)">
                        </md-input-container>
                    </div>
                    <div layout layout-align="center center">
                        <md-button
                            ng-disabled="!model.perform[selectedAction].id"
                            ng-click="fetchUserRequests(selectedAction)"
                            class="md-raised md-primary">
                            <md-icon>search</md-icon>Consultar
                        </md-button>
                    </div>
                </div>
                <!-- Query by status -->
                <div ng-show="selectedAction == 1" layout="column" layout-padding>
                    <br/>
                    <span>Elija el estatus</span>
                    <md-input-container
                        class="no-vertical-margin"
                        md-no-float>
                        <md-select
                            md-select-fix="model.perform[1].status"
                            md-on-open="loadStatuses()"
                            md-on-close="onStatusClose()"
                            placeholder="Estatus"
                            ng-model="model.perform[1].status">
                            <md-option ng-repeat="(sKey, status) in statuses" ng-value="status">
                                {{status}}
                            </md-option>
                        </md-select>
                    </md-input-container>
                    <div layout layout-align="center center">
                        <md-button
                            ng-disabled="!model.perform[selectedAction].status"
                            ng-click="fetchRequestsByStatus(model.perform[selectedAction].status, selectedAction)"
                            class="md-raised md-primary">
                            <md-icon>search</md-icon>Consultar
                        </md-button>
                    </div>
                </div>
                <!-- Query by loan type -->
                <div ng-show="selectedAction == 8" layout="column" layout-padding>
                    <br/>
                    <span>Elija el tipo de solicitud</span>
                    <md-input-container
                        class="no-vertical-margin"
                        md-no-float>
                        <md-select
                            md-select-fix="model.perform[8].loanType"
                            placeholder="Tipo"
                            ng-model="model.perform[8].loanType">
                            <md-option ng-repeat="(lKey, loanType) in loanTypes" ng-value="concept">
                                {{loanType.DescripcionDelPrestamo}}
                            </md-option>
                        </md-select>
                    </md-input-container>
                    <div layout layout-align="center center">
                        <md-button
                            ng-disabled="!model.perform[selectedAction].loanType"
                            ng-click="fetchRequestsByLoanType(model.perform[selectedAction].loanType, selectedAction)"
                            class="md-raised md-primary">
                            <md-icon>search</md-icon>Consultar
                        </md-button>
                    </div>
                </div>
                <!-- Query pending requests -->
                <div ng-show="selectedAction == 9" layout="column" layout-padding>
                    <br/>
                    <span>Lista y estadísticas de solicitudes pendientes</span>
                    <div layout layout-align="center center">
                        <md-button
                            ng-disabled="!model.perform[selectedAction]"
                            ng-click="fetchPendingRequests(selectedAction)"
                            class="md-raised md-primary">
                            <md-icon>search</md-icon>Consultar
                        </md-button>
                    </div>
                </div>
                <!-- Query by interval of dates -->
                <div ng-show="selectedAction == 2" layout="column" layout-padding>
                    <div>
                        <p>Desde</p>
                        <md-datepicker ng-model="model.perform[2].from" md-placeholder="Ingese fecha"></md-datepicker>
                    </div>
                    <div>
                        <p>Hasta</p>
                        <md-datepicker ng-model="model.perform[2].to" md-placeholder="Ingese fecha"></md-datepicker>
                    </div>
                    <br />
                    <div layout layout-align="center center">
                        <md-button
                            ng-disabled="!model.perform[selectedAction].from || !model.perform[selectedAction].to"
                            ng-click="fetchRequestsByDateInterval(
                                model.perform[selectedAction].from,
                                model.perform[selectedAction].to,
                                selectedAction
                             )"
                            class="md-raised md-primary">
                            <md-icon>search</md-icon>Consultar
                        </md-button>
                    </div>
                </div>

                <!-- Query by exact date -->
                <div ng-show="selectedAction == 3" layout="column" layout-padding>
                    <div>
                        <p>Fecha exacta</p>
                        <md-datepicker
                            ng-model="model.perform[3].date"
                            md-placeholder="Ingese fecha"></md-datepicker>
                    </div>
                    <div layout layout-align="center center">
                        <md-button
                            ng-disabled="!model.perform[selectedAction].date"
                            ng-click="fetchRequestsByExactDate(model.perform[selectedAction].date, selectedAction)"
                            class="md-raised md-primary">
                            <md-icon>search</md-icon>Consultar
                        </md-button>
                    </div>
                </div>
                <!-- Query approved amount by interval of dates -->
                <div ng-show="selectedAction == 4" layout="column" layout-padding>
                    <div>
                        <p>Desde</p>
                        <md-datepicker ng-model="model.perform[selectedAction].from" md-placeholder="Ingese fecha"></md-datepicker>
                    </div>
                    <div>
                        <p>Hasta</p>
                        <md-datepicker ng-model="model.perform[selectedAction].to" md-placeholder="Ingese fecha"></md-datepicker>
                    </div>
                    <div layout layout-align="center center">
                        <md-button
                            ng-disabled="!model.perform[selectedAction].from ||
                                !model.perform[selectedAction].to || loadingContent"
                            ng-click="getApprovedAmountByDateInterval(
                                model.perform[selectedAction].from,
                                model.perform[selectedAction].to
                            )"
                            class="md-raised md-primary">
                            <md-icon>search</md-icon>Consultar
                        </md-button>
                    </div>
                </div>
                <!-- Query approved amount from spcific user (ID) -->
                <div ng-show="selectedAction == 5" layout-padding layout="column">
                    <br/>
                    <div>
                        Ingrese cédula de identidad
                    </div>
                    <div layout layout-align="start start">
                        <md-input-container
                            class="no-vertical-margin"
                            md-no-float>
                            <md-select
                                md-select-fix="idPrefix"
                                aria-label="V or E ID"
                                ng-model="idPrefix">
                                <md-option value="V">
                                    V
                                </md-option>
                                <md-option value="E">
                                    E
                                </md-option>
                            </md-select>
                        </md-input-container>
                        <md-input-container
                            class="no-vertical-margin"
                            md-no-float>
                            <input
                                placeholder="Ej: 123456789"
                                type="number"
                                min="0"
                                aria-label="Search"
                                ng-model="model.perform[5].id"
                                ng-keyup="$event.keyCode == 13 && getApprovedAmountById(selectedAction)">
                        </md-input-container>
                    </div>
                    <div layout layout-align="center center">
                        <md-button
                            ng-disabled="!model.perform[selectedAction].id || loadingContent"
                            ng-click="getApprovedAmountById(selectedAction)"
                            class="md-raised md-primary">
                            <md-icon>search</md-icon>Consultar
                        </md-button>
                    </div>
                </div>

                <!-- Query approved requests report by interval of dates -->
                <div ng-show="selectedAction == 6" layout="column" layout-padding>
                    <div>
                        <p>Desde</p>
                        <md-datepicker ng-model="model.perform[selectedAction].from" md-placeholder="Ingese fecha"></md-datepicker>
                    </div>
                    <div>
                        <p>Hasta</p>
                        <md-datepicker ng-model="model.perform[selectedAction].to" md-placeholder="Ingese fecha"></md-datepicker>
                    </div>
                    <div layout layout-align="center center">
                        <md-button
                            ng-disabled="!model.perform[selectedAction].from ||
                                !model.perform[selectedAction].to"
                            ng-hide="loadingReport"
                            ng-click="getClosedReportByDateInterval(
                                model.perform[selectedAction].from,
                                model.perform[selectedAction].to
                            )"
                            class="md-raised md-primary">
                            <md-icon>assignment</md-icon> Generar
                        </md-button>
                        <md-progress-circular ng-if="loadingReport" md-mode="indeterminate">
                    </div>
                </div>
                <!-- Query this week's approved requests -->
                <div ng-show="selectedAction == 7 && showResult == null" layout="column" layout-padding>
                    <div>
                        <p>Reporte de solicitudes cerradas esta semana</p>
                    </div>
                    <div layout layout-align="center center">
                        <md-button
                            ng-hide="loadingReport"
                            ng-click="getClosedReportByCurrentWeek()"
                            class="md-raised md-primary">
                            <md-icon>assignment</md-icon> Generar
                        </md-button>
                        <md-progress-circular ng-if="loadingReport" md-mode="indeterminate">
                        </md-progress-circular>
                    </div>
                </div>

                <!-- Requests list -->
                <div class="margin-16" ng-if="!isObjEmpty(requests) && !loadingContent && !pieloaded">
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
                                    <md-table-pagination ng-show="requests[lKey].length > 0"
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
            </md-content>
        </main>
        <md-divider></md-divider>
    </div>
</div>
