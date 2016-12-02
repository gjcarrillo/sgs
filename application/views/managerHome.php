<!-- Toolbar -->
<md-toolbar layout-padding>
    <div class="md-toolbar-tools">
        <md-button
            id="nav-panel"
            hide-gt-sm
            class="md-icon-button"
            ng-click="openMenu()"
            aria-label="Open sidenav">
            <md-icon>menu</md-icon>
        </md-button>
        <h2 class="md-headline">
            <span>{{appName}}</span>
        </h2>
        <span flex></span>
        <md-button class="md-icon-button" ng-click="openManageUserAgentDialog($event)" aria-label="Help">
            <md-icon>account_box</md-icon>
            <md-tooltip md-direction="bottom">Administrar usuarios gestores</md-tooltip>
        </md-button>
        <md-button class="md-icon-button" ng-click="showHelp()" aria-label="Help">
            <md-icon>help_outline</md-icon>
            <md-tooltip md-direction="bottom">Ayuda</md-tooltip>
        </md-button>
        <md-button class="md-icon-button" ng-click="logout()" aria-label="Logout">
            <md-icon>exit_to_app</md-icon>
            <md-tooltip md-direction="bottom">Cerrar sesión</md-tooltip>
        </md-button>
    </div>
</md-toolbar>
<div layout>
    <!-- Sidenav -->
    <md-sidenav
        class="md-sidenav-left sidenav-frame"
        md-component-id="left"
        md-is-locked-open="$mdMedia('gt-sm')">
        <md-content class="sidenav-height">
            <md-progress-linear md-mode="query" ng-if="loading"></md-progress-linear>
            <!-- Query selection -->
            <div ng-show="showOptions && !loading">
                <md-list class="sidenavList">
                    <md-list-item id="adv-search">
                        <p class="sidenavTitle">
                            Búsqueda avanzada
                        </p>
                        <md-switch ng-model="showAdvSearch" aria-label="Enable adv search">
                        </md-switch>
                    </md-list-item>
                    <md-divider></md-divider>
                    <div ng-show="showAdvSearch">
                        <!-- Search error -->
                        <div ng-if="fetchError != ''" layout layout-align="center center" class="md-padding">
                            <span style="color:red">{{fetchError}}</span>
                        </div>
                        <div layout="column" layout-align="center center" layout-padding>
                            <md-input-container class="requestItems" style="padding:0;margin:0;padding-bottom:10px">
                                <md-select
                                    md-on-open="onQueryOpen()"
                                    md-on-close="onQueryClose()"
                                    placeholder="Seleccione su consulta"
                                    ng-model="selectedQuery">
                                    <md-optgroup label="Solicitudes">
                                        <md-option ng-value="query.id" ng-repeat="query in queries | filter: {category: 'req' }">
                                            {{query.name}}
                                        </md-option>
                                    </md-optgroup>
                                    <md-optgroup label="Solicitudes por fecha">
                                        <md-option ng-value="query.id" ng-repeat="query in queries | filter: {category: 'date' }">
                                            {{query.name}}
                                        </md-option>
                                    </md-optgroup>
                                    <md-optgroup label="Monto aprobado">
                                        <md-option ng-value="query.id" ng-repeat="query in queries | filter: {category: 'money' }">
                                            {{query.name}}
                                        </md-option>
                                    </md-optgroup>
                                </md-select>
                            </md-input-container>
                        </div>
                        <!-- Query by specific user -->
                        <div ng-show="model.query == 0" layout-padding layout="column">
                            <br/>
                            <div>
                                Ingrese cédula de identidad
                            </div>
                            <div layout layout-align="start start">
                                <md-input-container
                                    class="no-vertical-margin"
                                    md-no-float>
                                    <md-select
                                        md-on-open="onIdOpen()"
                                        md-on-close="onIdClose()"
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
                                       ng-keyup="$event.keyCode == 13 && fetchUserRequests(0)">
                                </md-input-container>
                            </div>
                            <div layout layout-align="center center">
                                <md-button
                                    ng-disabled="!model.perform[0].id"
                                    ng-click="fetchUserRequests(0)"
                                    class="md-raised md-primary">
                                    <md-icon>search</md-icon>Consultar
                                </md-button>
                            </div>
                        </div>
                        <!-- Query by state -->
                        <div ng-show="model.query == 1" layout="column" layout-padding>
                            <br/>
                            <span>Elija el estatus</span>
                            <md-input-container
                                class="no-vertical-margin"
                                md-no-float>
                                <md-select
                                    md-on-open="onStatusOpen()"
                                    md-on-close="onStatusClose()"
                                    placeholder="Estatus"
                                    ng-model="model.perform[1].status">
                                    <md-option ng-repeat="(sKey, status) in statuses" ng-value="mappedStatuses[sKey]">
                                        {{mappedStatuses[sKey]}}
                                    </md-option>
                                </md-select>
                            </md-input-container>
                            <div layout layout-align="center center">
                                <md-button
                                    ng-disabled="!model.perform[1].status"
                                    ng-click="fetchRequestsByStatus(model.perform[1].status, 1)"
                                    class="md-raised md-primary">
                                    <md-icon>search</md-icon>Consultar
                                </md-button>
                            </div>
                        </div>
                        <!-- Query by interval of dates -->
                        <div ng-show="model.query == 2" layout="column" layout-padding>
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
                                    ng-disabled="!model.perform[2].from || !model.perform[2].to"
                                    ng-click="fetchRequestsByDateInterval(model.perform[2].from, model.perform[2].to, 2)"
                                    class="md-raised md-primary">
                                    <md-icon>search</md-icon>Consultar
                                </md-button>
                            </div>
                        </div>

                        <!-- Query by exact date -->
                        <div ng-show="model.query == 3" layout="column" layout-padding>
                            <div>
                                <p>Fecha exacta</p>
                                <md-datepicker
                                    ng-model="model.perform[3].date"
                                    md-placeholder="Ingese fecha"></md-datepicker>
                            </div>
                            <div layout layout-align="center center">
                                <md-button
                                    ng-disabled="!model.perform[3].date"
                                    ng-click="fetchRequestsByExactDate(model.perform[3].date, 3)"
                                    class="md-raised md-primary">
                                    <md-icon>search</md-icon>Consultar
                                </md-button>
                            </div>
                        </div>
                        <!-- Query approved amount by interval of dates -->
                        <div ng-show="model.query == 4" layout="column" layout-padding>
                            <div>
                                <p>Desde</p>
                                <md-datepicker ng-model="model.perform[4].from" md-placeholder="Ingese fecha"></md-datepicker>
                            </div>
                            <div>
                                <p>Hasta</p>
                                <md-datepicker ng-model="model.perform[4].to" md-placeholder="Ingese fecha"></md-datepicker>
                            </div>
                            <div layout layout-align="center center">
                                <md-button
                                    ng-disabled="!model.perform[4].from || !model.perform[4].to || loadingContent"
                                    ng-click="getApprovedAmountByDateInterval(model.perform[4].from, model.perform[4].to)"
                                    class="md-raised md-primary">
                                    <md-icon>search</md-icon>Consultar
                                </md-button>
                            </div>
                        </div>
                        <!-- Query approved amount from spcific user (ID) -->
                        <div ng-show="model.query == 5" layout-padding layout="column">
                            <br/>
                            <div>
                                Ingrese cédula de identidad
                            </div>
                            <div layout layout-align="start start">
                                <md-input-container
                                    class="no-vertical-margin"
                                    md-no-float>
                                    <md-select
                                        md-on-open="onIdOpen()"
                                        md-on-close="onIdClose()"
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
                                       ng-keyup="$event.keyCode == 13 && getApprovedAmountById(5)">
                                </md-input-container>
                            </div>
                            <div layout layout-align="center center">
                                <md-button
                                    ng-disabled="!model.perform[5].id || loadingContent"
                                    ng-click="getApprovedAmountById(5)"
                                    class="md-raised md-primary">
                                    <md-icon>search</md-icon>Consultar
                                </md-button>
                            </div>
                        </div>
                        <md-divider></md-divider>
                    </div>
                    <md-list-item id="approval-report">
                        <p class="sidenavTitle">
                            Reporte de solicitudes
                        </p>
                        <md-switch ng-model="showApprovalReport" aria-label="Enable approval report">
                        </md-switch>
                    </md-list-item>
                    <md-divider></md-divider>
                    <div ng-show="showApprovalReport">
                        <div ng-if="approvalReportError != ''" layout layout-align="center center" class="md-padding">
                            <span style="color:red">{{approvalReportError}}</span>
                        </div>
                        <div layout="column" layout-align="center center" layout-padding>
                            <md-input-container class="requestItems" style="padding:0;margin:0;padding-bottom:10px">
                                <md-select
                                    md-on-open="onQueryOpen()"
                                    md-on-close="onQueryClose()"
                                    placeholder="Seleccione el rango"
                                    ng-model="selectedQuery">
                                    <md-option value="6">Intervalo de fecha</md-option>
                                    <md-option value="7">Semana actual</md-option>
                                </md-select>
                            </md-input-container>
                        </div>
                        <!-- Query approved requests report by interval of dates -->
                        <div ng-show="model.query == 6" layout="column" layout-padding>
                            <div>
                                <p>Desde</p>
                                <md-datepicker ng-model="model.perform[6].from" md-placeholder="Ingese fecha"></md-datepicker>
                            </div>
                            <div>
                                <p>Hasta</p>
                                <md-datepicker ng-model="model.perform[6].to" md-placeholder="Ingese fecha"></md-datepicker>
                            </div>
                            <div layout layout-align="center center">
                                <md-button
                                    ng-disabled="!model.perform[6].from || !model.perform[6].to"
                                    ng-hide="loadingReport"
                                    ng-click="getApprovedReportByDateInterval(model.perform[6].from, model.perform[6].to)"
                                    class="md-raised md-primary">
                                    <md-icon>assignment</md-icon> Generar
                                </md-button>
                                <md-progress-circular ng-if="loadingReport" md-mode="indeterminate">
                            </div>
                        </div>
                        <!-- Query this week's approved requests -->
                        <div ng-show="model.query == 7" layout="column" layout-padding>
                            <div>
                                <p>Reporte de solicitudes cerradas esta semana</p>
                            </div>
                            <div layout layout-align="center center">
                                <md-button
                                    ng-hide="loadingReport"
                                    ng-click="getApprovedReportByCurrentWeek()"
                                    class="md-raised md-primary">
                                    <md-icon>assignment</md-icon> Generar
                                </md-button>
                                <md-progress-circular ng-if="loadingReport" md-mode="indeterminate">
                                </md-progress-circular>
                            </div>
                        </div>
                        <md-divider></md-divider>
                    </div>
                    <!-- Pending requests -->
                    <md-list-item id="pending-req">
                        <p class="sidenavTitle">
                            Solicitudes pendientes
                        </p>
                        <md-switch ng-model="showPendingReq" aria-label="Enable adv search">
                        </md-switch>
                    </md-list-item>
                    <md-divider></md-divider>
                    <div layout layout-align="center center" layout-padding ng-if="loadingContent">
                        <div layout="column" layout-align="center center">
                            <md-progress-circular md-mode="indeterminate" md-diameter="60"></md-progress-circular>
                        </div>
                    </div>
                    <div ng-show="showPendingReq">
                        <div ng-if="isObjEmpty(pendingRequests)">
                            <div layout layout-align="center center" class="md-padding">
                                <p style="color:#4CAF50; text-align:center">
                                    ¡No se han encontrado solicitudes pendientes!
                                </p>
                            </div>
                            <md-divider></md-divider>
                        </div>
                        <md-list class="sidenavList">
                            <div ng-repeat="(rKey, request) in pendingRequests">
                                <md-list-item ng-click="togglePendingList(rKey)">
                                    <p class="sidenavSubtitle">
                                        {{listTitle[rKey]}}
                                    </p>
                                    <md-icon ng-class="md-secondary" ng-if="!showPendingList[rKey]">keyboard_arrow_down</md-icon>
                                    <md-icon ng-class="md-secondary" ng-if="showPendingList[rKey]">keyboard_arrow_up</md-icon>
                                </md-list-item>
                                <md-divider></md-divider>
                                <div class="slide-toggle" ng-show="showPendingList[rKey]">
                                    <div ng-if="request.length == 0">
                                        <div layout layout-align="center center" class="md-padding">
                                            <p style="color:#4CAF50">
                                                ¡No se han encontrado solicitudes de {{listTitle[rKey]}} pendientes!
                                            </p>
                                        </div>
                                        <md-divider></md-divider>
                                    </div>
                                    <div layout="column" layout-align="center" ng-repeat="(lKey, loan) in request">
                                        <md-button
                                            ng-click="selectPendingReq(rKey, lKey)"
                                            class="requestItems"
                                            ng-class="{'md-primary md-raised' : selectedPendingReq === rKey &&
                                                selectedPendingLoan === lKey }">
                                            Solicitud ID &#8470; {{pad(loan.id, 6)}}
                                            <md-icon
                                                ng-if="loan.status === APPROVED_STRING"
                                                style="color:#4CAF50">
                                                check_circle
                                            </md-icon>
                                            <md-icon
                                                ng-if="loan.status === REJECTED_STRING"
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
                </md-list>
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
                    <div id="result-data" ng-repeat="(rKey, request) in requests">
                        <md-list-item ng-click="toggleList(rKey)">
                            <p class="sidenavTitle">
                                {{listTitle[rKey]}}
                            </p>
                            <md-icon ng-class="md-secondary" ng-if="!showList[rKey]">keyboard_arrow_down</md-icon>
                            <md-icon ng-class="md-secondary" ng-if="showList[rKey]">keyboard_arrow_up</md-icon>
                        </md-list-item>
                        <md-divider></md-divider>
                        <div class="slide-toggle" ng-show="showList[rKey]">
                            <div ng-if="request.length == 0" layout layout-align="center center" class="md-padding">
                                <p style="color:#F44336">
                                    Este afiliado no posee solicitudes de {{listTitle[rKey]}}
                                </p>
                            </div>
                            <div layout="column" layout-align="center" ng-repeat="(lKey, loan) in request">
                                <md-button
                                    ng-click="selectRequest(rKey, lKey)"
                                    class="requestItems"
                                    ng-class="{'md-primary md-raised' : selectedReq === rKey &&
                                            selectedLoan === lKey }">
                                    <md-icon ng-style="getBulbColor(loan.status, rKey, lKey)">
                                        lightbulb_outline
                                    </md-icon> Solicitud ID &#8470; {{pad(loan.id, 6)}}
                                </md-button>
                                <md-divider ng-if="$last"></md-divider>
                            </div>
                        </div>
                    </div>
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
                    <div id="result-data" ng-repeat="(rKey, request) in requests">
                        <md-list-item ng-click="toggleList(rKey)">
                            <p class="sidenavTitle">
                                {{listTitle[rKey]}}
                            </p>
                            <md-icon ng-class="md-secondary" ng-if="!showList[rKey]">keyboard_arrow_down</md-icon>
                            <md-icon ng-class="md-secondary" ng-if="showList[rKey]">keyboard_arrow_up</md-icon>
                        </md-list-item>
                        <md-divider></md-divider>
                        <div class="slide-toggle" ng-show="showList[rKey]">
                            <div ng-if="request.length == 0" layout layout-align="center center" class="md-padding">
                                <p style="color:#F44336">
                                    Este afiliado no posee solicitudes de {{listTitle[rKey]}}
                                </p>
                            </div>
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
                                        ng-if="loan.status === APPROVED_STRING && showResult == 1 &&
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
            <!-- Watermark -->
            <div
                class="full-content-height"
                layout layout-align="center center"
                ng-if="!req.docs &&
                    !showApprovedAmount &&
                    !pieloaded &&
                    pieError == ''">
                <div class="watermark" layout="column" layout-align="center center">
                    <img src="images/ipapedi.png" alt="Ipapedi logo"/>
                </div>
            </div>
            <!-- Pie chart statistics result -->
            <div layout layout-align="center center" class="full-content-height"
                ng-show="pieloaded && !req.docs &&  pieError == ''">
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
                ng-if="pieError != '' && !req.docs"
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
            <!-- The actual content -->
            <md-content
                ng-show="req.docs"
                class="document-container">
                <div layout layout-align="center center">
                    <md-card class="documents-card">
                        <md-card-content>
                            <md-list>
                                <md-list-item
                                    id="request-summary"
                                    class="md-3-line noright">
                                    <div class="md-list-item-text request-details-wrapper" layout="column">
                                        <h3 hide-xs class="request-details-title">
                                            Solicitado al {{req.creationDate}}
                                        </h3>
                                        <h3 hide-gt-xs class="request-details-title">
                                            Fecha: {{req.creationDate}}
                                        </h3>
                                        <h4>
                                            Monto solicitado: Bs {{req.reqAmount | number:2}}
                                        </h4>
                                        <p>
                                            {{req.comment}}
                                        </p>
                                    </div>
                                    <!-- Show only when screen width >= 960px -->
                                    <div
                                        id="request-summary-actions"
                                        hide show-gt-sm>
                                        <md-button
                                            ng-if="fetchedRequests() || (selectedPendingReq != '')"
                                            ng-click="loadUserData(req.userOwner)"
                                            class="md-icon-button">
                                            <md-icon class="md-secondary">
                                                person
                                            </md-icon>
                                            <md-tooltip>Datos del afiliado</md-tooltip>
                                        </md-button>
                                        <md-button
                                            ng-click="loadHistory()"
                                            class="md-icon-button">
                                            <md-icon class="md-secondary">
                                                history
                                            </md-icon>
                                            <md-tooltip>Historial</md-tooltip>
                                        </md-button>
                                        <md-button
                                            class="md-icon-button"
                                            ng-if="req.status == RECEIVED_STRING"
                                            ng-click="openEditRequestDialog($event)">
                                            <md-icon class="md-secondary">
                                                edit
                                            </md-icon>
                                            <md-tooltip>
                                                Editar solicitud
                                            </md-tooltip>
                                        </md-button>
                                        <md-button
                                            ng-click="downloadAll()"
                                            class="md-icon-button">
                                            <md-icon class="md-secondary">
                                                cloud_download
                                            </md-icon>
                                            <md-tooltip>
                                                Descargar documentos
                                            </md-tooltip>
                                        </md-button>
                                    </div>
                                    <!-- Show when screen width < 960px -->
                                    <md-menu
                                        id="request-summary-actions-menu"
                                        hide-gt-sm>
                                       <md-button
                                            ng-click="$mdOpenMenu($event)"
                                            class="md-icon-button"
                                            aria-label="More">
                                           <md-icon class="md-secondary">
                                               more_vert
                                           </md-icon>
                                       </md-button>
                                       <md-menu-content>
                                           <md-menu-item>
                                               <md-button ng-click="loadHistory()">
                                                   <md-icon class="md-secondary">
                                                       history
                                                   </md-icon>
                                                   Historial
                                               </md-button>
                                           </md-menu-item>
                                           <md-menu-item
                                               ng-if="req.status == RECEIVED_STRING">
                                               <md-button
                                                   ng-click="openEditRequestDialog($event)">
                                                   <md-icon class="md-secondary">
                                                       edit
                                                   </md-icon>
                                                   Editar
                                               </md-button>
                                           </md-menu-item>
                                           <md-menu-item>
                                               <md-button ng-click="downloadAll()">
                                                   <md-icon class="md-secondary">
                                                       cloud_download
                                                   </md-icon>
                                                   Descargar documentos
                                               </md-button>
                                           </md-menu-item>
                                       </md-menu-content>
                                   </md-menu>
                               </md-list-item>
                                <md-list-item id="request-status-summary" class="md-2-line noright">
                                    <md-icon class="info-icon">info_outline</md-icon>
                                    <div class="md-list-item-text" layout="column">
                                       <h3>
                                           Estatus de la solicitud: {{req.status}}
                                       </h3>
                                       <h4 ng-if="req.reunion">
                                           Reunión &#8470;
                                           {{req.reunion}}
                                       </h4>
                                       <p ng-if="req.approvedAmount">
                                           Monto aprobado: Bs
                                           {{req.approvedAmount | number:2}}
                                       </p>
                                     </div>
                                </md-list-item>
                                <md-divider md-inset></md-divider>
                                <md-list-item class="md-2-line noright"
                                              id="request-payment-due">
                                    <md-icon class="payment-icon">payment</md-icon>
                                    <div class="md-list-item-text" layout="column">
                                        <h3>
                                            Cuotas a pagar
                                        </h3>
                                        <h4>
                                            Bs 12,000.00
                                        </h4>
                                        <p>
                                            Por {{req.due}} meses
                                        </p>
                                    </div>
                                </md-list-item>
                                <md-divider md-inset></md-divider>
                                <md-list-item class="md-2-line noright"
                                              id="request-contact-number">
                                    <md-icon class="phone-icon">phone</md-icon>
                                    <div class="md-list-item-text" layout="column">
                                        <h3>
                                            Número de contacto
                                        </h3>
                                        <h4>
                                            {{req.phone}}
                                        </h4>
                                    </div>
                                </md-list-item>
                                <md-divider></md-divider>
                                <div ng-repeat="(dKey, doc) in req.docs">
                                    <md-list-item
                                        id="request-docs"
                                        class="md-2-line noright"
                                        ng-click="downloadDoc(doc)">
                                        <md-icon
                                            class="docs-icon">
                                            insert_drive_file
                                        </md-icon>
                                        <div class="md-list-item-text" layout="column">
                                           <h3>{{doc.name}}</h3>
                                           <p>{{doc.description}}</p>
                                         </div>
                                         <md-button
                                            class="md-icon-button">
                                            <md-icon class="md-secondary">
                                                file_download
                                            </md-icon>
                                         </md-button>
                                    </md-list-item>
                                    <md-divider ng-if="!$last" md-inset></md-divider>
                                </div>
                            </md-list>
                        </md-card-content>
                    </md-card>
                </div>
            </md-content>
        </main>
        <md-divider></md-divider>
    </div>
</div>
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
