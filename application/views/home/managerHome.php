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
            <span>SGDP</span>
        </h2>
        <span flex></span>
        <md-button class="md-icon-button" ng-click="openNewAgentDialog($event)" aria-label="Help">
            <md-icon>account_box</md-icon>
            <md-tooltip md-direction="top">Nuevo usuario gestor</md-tooltip>
        </md-button>
        <md-button class="md-icon-button" ng-click="showHelp()" aria-label="Help">
            <md-icon>help_outline</md-icon>
            <md-tooltip md-direction="top">Ayuda</md-tooltip>
        </md-button>
        <md-button class="md-icon-button" ng-click="logout()" aria-label="Logout">
            <md-icon>exit_to_app</md-icon>
            <md-tooltip md-direction="top">Cerrar sesión</md-tooltip>
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
                                    style="margin:0"
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
                                    style="margin:0"
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
                                    Consultar
                                </md-button>
                            </div>
                        </div>
                        <!-- Query by state -->
                        <div ng-show="model.query == 1" layout="column" layout-padding>
                            <br/>
                            <span>Elija el status</span>
                            <md-input-container
                                style="margin:0">
                                <md-select
                                    md-on-open="onStatusOpen()"
                                    md-on-close="onStatusClose()"
                                    placeholder="Estatus"
                                    ng-model="model.perform[1].status">
                                    <md-option ng-value="status" ng-repeat="(sKey, status) in statuses">{{status}}</md-option>
                                </md-select>
                            </md-input-container>
                            <div layout layout-align="center center">
                                <md-button
                                    ng-disabled="!model.perform[1].status"
                                    ng-click="fetchRequestsByStatus(model.perform[1].status, 1)"
                                    class="md-raised md-primary">
                                    Consultar
                                </md-button>
                            </div>
                        </div>
                        <!-- Query by interval of dates -->
                        <div ng-show="model.query == 2" layout="column">
                            <div layout-padding>
                                <p>Desde</p>
                                <md-datepicker ng-model="model.perform[2].from" md-placeholder="Ingese fecha"></md-datepicker>
                            </div>
                            <div layout-padding>
                                <p>Hasta</p>
                                <md-datepicker ng-model="model.perform[2].to" md-placeholder="Ingese fecha"></md-datepicker>
                            </div>
                            <br />
                            <div layout layout-align="center center">
                                <md-button
                                    ng-disabled="!model.perform[2].from || !model.perform[2].to"
                                    ng-click="fetchRequestsByDateInterval(model.perform[2].from, model.perform[2].to, 2)"
                                    class="md-raised md-primary">
                                    Consultar
                                </md-button>
                            </div>
                        </div>

                        <!-- Query by exact date -->
                        <div ng-show="model.query == 3" layout="column">
                            <div layout-padding>
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
                                    Consultar
                                </md-button>
                            </div>
                        </div>
                        <!-- Query approved amount by interval of dates -->
                        <div ng-show="model.query == 4" layout="column">
                            <div layout-padding>
                                <p>Desde</p>
                                <md-datepicker ng-model="model.perform[4].from" md-placeholder="Ingese fecha"></md-datepicker>
                            </div>
                            <div layout-padding>
                                <p>Hasta</p>
                                <md-datepicker ng-model="model.perform[4].to" md-placeholder="Ingese fecha"></md-datepicker>
                            </div>
                            <div layout layout-align="center center">
                                <md-button
                                    ng-disabled="!model.perform[4].from || !model.perform[4].to"
                                    ng-click="getApprovedAmountByDateInterval(model.perform[4].from, model.perform[4].to)"
                                    class="md-raised md-primary">
                                    Consultar
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
                                    style="margin:0"
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
                                    style="margin:0"
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
                                    ng-disabled="!model.perform[5].id"
                                    ng-click="getApprovedAmountById(5)"
                                    class="md-raised md-primary">
                                    Consultar
                                </md-button>
                            </div>
                        </div>
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
                    <div ng-show="showPendingReq">
                        <div ng-repeat="(rKey, request) in pendingRequests">
                            <md-list-item ng-click="toggleReqList(request)">
                                <p class="sidenavSubtitle">
                                    Solicitud ID &#8470; {{pad(request.id, 6)}}
                                </p>
                                <md-icon ng-class="md-secondary" ng-if="!request.showList">keyboard_arrow_down</md-icon>
                                <md-icon ng-class="md-secondary" ng-if="request.showList">keyboard_arrow_up</md-icon>
                            </md-list-item>
                            <md-divider ng-if="!$last"></md-divider>
                            <div class="slide-toggle" ng-show="request.showList">
                                <md-list-item>
                                    <md-button
                                        class="requestItems"
                                        ng-click="loadUserData(request.userOwner)"
                                        flex>
                                        Datos del afiliado
                                    </md-button>
                                </md-list-item>
                                <md-list-item>
                                    <md-button
                                        flex
                                        ng-click="selectPendingReq(rKey)"
                                        class="requestItems"
                                        ng-class="{'md-primary md-raised' : selectedPendingReq === rKey }">
                                        Detalles de la solicitud
                                    </md-button>
                                </md-list-item>
                            </div>
                        </div>
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
                    <md-list-item id="result-data" ng-click="toggleList()">
                        <p class="sidenavTitle">
                            Préstamos Personales
                        </p>
                        <md-icon ng-class="md-secondary" ng-if="!showList">keyboard_arrow_down</md-icon>
                        <md-icon ng-class="md-secondary" ng-if="showList">keyboard_arrow_up</md-icon>
                    </md-list-item>
                    <md-divider></md-divider>
                    <div class="slide-toggle" ng-show="showList">
                        <md-list-item
                            ng-repeat="(rKey, request) in requests">
                            <md-button
                                flex
                                ng-click="selectRequest(rKey)"
                                class="requestItems"
                                ng-class="{'md-primary md-raised' : selectedReq === rKey }">
                                <md-icon ng-style="getBulbColor(request.status, rKey)">
                                    lightbulb_outline
                                </md-icon> Solicitud ID &#8470; {{pad(request.id, 6)}}
                            </md-button>
                        </md-list-item>
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
                    <div ng-repeat="(rKey, request) in requests">
                        <md-list-item id="result-data" ng-click="toggleReqList(request)">
                            <p class="sidenavTitle">
                                <md-icon ng-if="showResult != 1" ng-style="getBulbColor(request.status, rKey)">
                                    lightbulb_outline
                                </md-icon> Solicitud ID &#8470; {{pad(request.id, 6)}}
                            </p>
                            <md-icon ng-class="md-secondary" ng-if="!request.showList">keyboard_arrow_down</md-icon>
                            <md-icon ng-class="md-secondary" ng-if="request.showList">keyboard_arrow_up</md-icon>
                        </md-list-item>
                        <md-divider ng-if="!$last"></md-divider>
                        <div class="slide-toggle" ng-show="request.showList">
                            <md-list-item>
                                <md-button
                                    class="requestItems"
                                    ng-click="loadUserData(request.userOwner)"
                                    flex>
                                    Datos del afiliado
                                </md-button>
                            </md-list-item>
                            <md-list-item>
                                <md-button
                                    flex
                                    ng-click="selectRequest(rKey)"
                                    class="requestItems"
                                    ng-class="{'md-primary md-raised' : selectedReq === rKey }">
                                    Detalles de la solicitud
                                </md-button>
                            </md-list-item>
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
            <div class="full-contet-height" layout layout-align="center center"
                ng-if="docs.length == 0 && !loadingContent && !showApprovedAmount && !pieloaded">
                <div class="watermark" layout="column" layout-align="center center">
                    <img src="images/ipapedi.png" alt="Ipapedi logo"/>
                </div>
            </div>
            <!-- Content pre loader -->
            <div layout layout-align="center center" class="full-contet-height" ng-if="loadingContent">
                <div layout="column" layout-align="center center">
                    <md-progress-circular md-mode="indeterminate" md-diameter="80"></md-progress-circular>
                </div>
            </div>
            <!-- Pie chart statistics result -->
            <div layout layout-align="center center" class="full-contet-height"
                ng-show="pieloaded && docs.length == 0">
                <div
                    id="piechart-tour"
                    layout="column"
                    layout-align="center center"
                    layout-padding class="md-whiteframe-z2 statistics-card">
                    <div>
                        <span>{{statisticsTitle}}</span>
                        <md-button
                            id="report-btn"
                            flex
                            ng-csv="report"
                            add-bom="true"
                            filename="{{reportName()}}"
                            class="md-icon-button"
                            ng-click="null">
                            <md-icon>assignment</md-icon>
                            <md-tooltip md-direction="top">Generar reporte</md-tooltip>
                        </md-button>
                    </div>
                    <canvas id="piechart" width="300" height="300"></canvas>
                </div>
            </div>
            <!-- Approved amount result -->
            <div layout layout-align="center center" class="full-contet-height" ng-if="showApprovedAmount">
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
                ng-hide="docs.length == 0"
                ng-style="getDocumentContainerStyle()">
                <div layout layout-align="center center">
                    <md-card class="documents-card">
                        <md-card-content>
                            <md-list>
                                <md-list-item
                                    id="request-summary"
                                    class="md-3-line"
                                    class="noright">
                                    <div class="md-list-item-text request-details-wrapper" layout="column">
                                        <h3 class="request-details-title">
                                            Préstamo solicitado el
                                            {{selectedPendingReq == -1 ? requests[selectedReq].creationDate :
                                                pendingRequests[selectedPendingReq].creationDate}}
                                        </h3>
                                        <h4>
                                            Monto solicitado: Bs
                                            {{selectedPendingReq == -1 ? (requests[selectedReq].reqAmount | number:2) :
                                                (pendingRequests[selectedPendingReq].reqAmount | number:2)}}
                                        </h4>
                                        <p>
                                            {{selectedPendingReq == -1 ? requests[selectedReq].comment :
                                                pendingRequests[selectedPendingReq].comment}}
                                        </p>
                                    </div>
                                    <!-- Show only when screen width >= 960px -->
                                    <div
                                        id="request-summary-actions"
                                        hide show-gt-sm
                                        class="md-secondary">
                                        <md-button ng-click="loadHistory()" class="md-icon-button">
                                            <md-icon>history</md-icon>
                                            <md-tooltip>Historial</md-tooltip>
                                        </md-button>
                                        <md-button
                                            class="md-icon-button"
                                            ng-if="(selectedPendingReq == -1
                                                 && requests[selectedReq].status == 'Recibida')
                                                 || (selectedPendingReq != -1 &&
                                                     pendingRequests[selectedPendingReq].status == 'Recibida')"
                                            ng-click="openEditRequestDialog($event)">
                                            <md-icon>edit</md-icon>
                                            <md-tooltip>Editar solicitud</md-tooltip>
                                        </md-button>
                                        <md-button ng-click="downloadAll()" class="md-icon-button">
                                            <md-icon>cloud_download</md-icon>
                                            <md-tooltip>Descargar todo</md-tooltip>
                                        </md-button>
                                    </div>
                                    <!-- Show when screen width < 960px -->
                                    <md-menu
                                        id="request-summary-actions-menu"
                                        hide-gt-sm
                                        class="md-secondary">
                                       <md-button ng-click="$mdOpenMenu($event)" class="md-icon-button" aria-label="More">
                                           <md-icon>more_vert</md-icon>
                                       </md-button>
                                       <md-menu-content>
                                           <md-menu-item>
                                               <md-button ng-click="loadHistory()">
                                                   <md-icon>history</md-icon>
                                                   Historial
                                               </md-button>
                                           </md-menu-item>
                                           <md-menu-item
                                               ng-if="(selectedPendingReq == -1
                                                    && requests[selectedReq].status == 'Recibida')
                                                    || (selectedReq != -1 &&
                                                        pendingRequests[selectedPendingReq].status == 'Recibida')">
                                               <md-button
                                                   ng-click="openEditRequestDialog($event)">
                                                   <md-icon>edit</md-icon>
                                                   Editar
                                               </md-button>
                                           </md-menu-item>
                                           <md-menu-item>
                                               <md-button ng-click="downloadAll()">
                                                   <md-icon>cloud_download</md-icon>
                                                   Descargar todo
                                               </md-button>
                                           </md-menu-item>
                                       </md-menu-content>
                                   </md-menu>
                               </md-list-item>
                                <md-list-item id="request-status-summary" class="md-2-line noright">
                                    <md-icon  ng-style="{'font-size':'36px'}">info_outline</md-icon>
                                    <div class="md-list-item-text" layout="column">
                                       <h3>
                                           Estatus de la solicitud:
                                           {{selectedPendingReq == -1 ? requests[selectedReq].status :
                                                pendingRequests[selectedPendingReq].status}}
                                       </h3>
                                       <h4 ng-if="(selectedPendingReq == -1 &&
                                            requests[selectedReq].reunion)
                                            || (selectedReq != -1 &&
                                                pendingRequests[selectedPendingReq].reunion)">
                                           Reunión &#8470;
                                           {{selectedPendingReq == -1 ? requests[selectedReq].reunion :
                                                pendingRequests[selectedPendingReq].reunion}}
                                       </h4>
                                       <p ng-if="(selectedPendingReq == -1 &&
                                            requests[selectedReq].approvedAmount)
                                            || (selectedReq != -1 &&
                                                pendingRequests[selectedPendingReq].approvedAmount)">
                                           Monto aprobado: Bs
                                           {{selectedPendingReq == -1 ? (requests[selectedReq].approvedAmount | number:2) :
                                                (pendingRequests[selectedPendingReq].approvedAmount | number:2)}}
                                       </p>
                                     </div>
                                </md-list-item>
                                <md-divider></md-divider>
                                <div ng-repeat="(dKey, doc) in docs">
                                    <md-list-item
                                        id="request-docs"
                                        class="md-2-line"
                                        ng-click="downloadDoc(doc)"
                                        class="noright">
                                        <md-icon
                                            ng-if="doc.name !='Identidad'"
                                            ng-style="{'color':'#2196F3', 'font-size':'36px'}">
                                            insert_drive_file
                                        </md-icon>
                                        <md-icon
                                            ng-if="doc.name=='Identidad'"
                                            ng-style="{'color':'#2196F3', 'font-size':'36px'}">
                                            perm_identity
                                        </md-icon>
                                        <div class="md-list-item-text" layout="column">
                                           <h3>{{doc.name}}</h3>
                                           <p>{{doc.description}}</p>
                                         </div>
                                         <md-button
                                            class="md-secondary md-icon-button">
                                            <md-icon>file_download</md-icon>
                                         </md-button>
                                    </md-list-item>
                                    <md-divider ng-if="!$last" md-inset></md-divider>
                                </div>
                            </md-list>
                        </md-card-content>
                    </md-card>
                </div>
                <br/>
            </md-content>
        </main>
        <md-divider></md-divider>
    </div>
</div>
<footer>
    <div layout layout-align="space-around center">
        <span>&copy; IPAPEDI 2016</span>
        <span>
            Desarrollado por <a class="md-accent" href="https://ve.linkedin.com/in/kristopherch" target="_blank">Kristopher Perdomo</a>
        </span>
        <md-button class="md-accent" href="http://www.ipapedi.com" target="_blank">IPAPEDI</md-button>
    </div>
</footer>
</body>
</html>
