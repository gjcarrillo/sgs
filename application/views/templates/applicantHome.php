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
                        ng-click="selectAction('edit')"
                        ng-class="{'md-primary md-raised' : selectedAction == 'edit'}">
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
                ng-if="fetchError == '' && selectedAction != 1"
                class="full-content-height"
                layout="column" layout-align="center center">
                <div ng-if="!loading" class="watermark" layout="column" layout-align="center center">
                    <img src="images/ipapedi.png" alt="Ipapedi logo"/>
                </div>
            </div>

            <!-- Requests list -->
            <md-expansion-panel-group ng-if="selectedAction == 1">
                <md-expansion-panel  class="margin-16" ng-repeat="(lKey, loanType) in loanTypes" md-component-id="{{lKey}}">
                    <md-expansion-panel-collapsed>
                        <div class="md-title">{{loanType.DescripcionDelPrestamo}}</div>
                        <div class="md-summary">Haga clic para desplegar la lista</div>
                        <md-expansion-panel-icon></md-expansion-panel-icon>
                    </md-expansion-panel-collapsed>
                    <md-expansion-panel-expanded class="margin-16">
                        <md-expansion-panel-header>
                            <div class="md-title">{{loanType.DescripcionDelPrestamo}}</div>
                            <div class="md-summary">Haga clic en una fila para ver más detalles de la solicitud</div>
                            <md-expansion-panel-icon></md-expansion-panel-icon>
                        </md-expansion-panel-header>

                        <md-expansion-panel-content>
                            <!-- Table of requests -->
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
                                    <tr md-row ng-repeat="request in requests[lKey] | limitTo: query.limit: (query.page - 1) * query.limit track by $index">
                                        <td md-cell ng-click="null">{{pad(request.id, 6)}}</td>
                                        <td md-cell ng-click="null">{{request.creationDate}}</td>
                                        <td md-cell ng-click="null">{{request.status}}</td>
                                        <td md-cell ng-click="null">{{request.reqAmount | number:2}}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </md-table-container>
                            <md-table-pagination md-label="{page: 'Página:', rowsPerPage: 'Filas por página:', of: 'de'}" md-limit="query.limit" md-limit-options="[5, 10, 15, 20]" md-page="query.page" md-total="{{requests[lKey].length}}" md-page-select></md-table-pagination>
                        </md-expansion-panel-content>

                        <md-expansion-panel-footer>
                            <div flex></div>
                            <md-button class="md-warn" ng-click="$panel.collapse()">Cerrar</md-button>
                        </md-expansion-panel-footer>
                    </md-expansion-panel-expanded>
                </md-expansion-panel>
            </md-expansion-panel-group>

            <!-- The actual content -->
            <md-content
                ng-hide="!req"
                class="document-container request-show">
                <div animate-on-change="req" layout="column" layout-align="center center">
                    <md-card id="validation-card" class="validation-card" ng-if="!req.validationDate">
                        <md-card-content>
                            <p>
                                SOLICITUD NO VALIDADA <br/><br/>
                            </p>
                            <p>
                                Puede editar la información de su solicitud haciendo clic en
                                <md-icon ng-if="!req.validationDate"
                                         ng-click="openEditRequestDialog($event)" class="md-secondary pointer padding-sides">
                                    edit
                                    <md-tooltip>
                                        Editar solicitud
                                    </md-tooltip>
                                </md-icon>

                                o eliminarla haciendo clic en
                                <md-icon ng-click="deleteRequest($event)" class="md-secondary pointer padding-sides">
                                    delete
                                    <md-tooltip>
                                        Eliminar solicitud
                                    </md-tooltip>
                                </md-icon>
                                <br/><br/>
                                Una vez esté completamente seguro de proceder con esta solicitud, haga clic en VALIDAR.
                            </p>
                        </md-card-content>
                        <md-card-actions layout="row" layout-align="end center">
                            <md-button
                                ng-click="validating ? null : validateRequest($event)"
                                class="md-raised"
                                aria-label="Validar">
                                <span ng-if="!validating">
                                    Validar
                                    <md-tooltip>Validar solicitud</md-tooltip>
                                </span>
                                <div ng-if="validating" layout layout-align="center center">
                                    <md-progress-circular
                                        md-mode="indeterminate"
                                        md-diameter="30">
                                    </md-progress-circular>
                                </div>
                            </md-button>
                        </md-card-actions>
                    </md-card>
                    <md-card class="documents-card" ng-class="{'documents-margin' : req.validationDate}">
                        <md-card-content>
                            <md-list>
                                <md-list-item id="request-summary" class="md-2-line noright">
                                    <div class="md-list-item-text request-details-wrapper" layout="column">
                                        <h3 hide-xs class="request-details-title">
                                            Solicitado al {{req.creationDate}}
                                        </h3>
                                        <h3 hide-gt-xs class="request-details-title">
                                            Fecha: {{req.creationDate}}
                                        </h3>
                                        <h4>
                                            Monto solicitado: Bs
                                            {{req.reqAmount | number:2}}
                                        </h4>
                                        <p>
                                            {{req.comment}}
                                        </p>
                                    </div>
                                    <div
                                        id="request-summary-actions"
                                        hide show-gt-sm>
                                        <md-button
                                            class="md-icon-button"
                                            ng-if="!req.validationDate"
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
                                        <md-button
                                            ng-if="!req.validationDate"
                                            ng-click="deleteRequest($event)"
                                            class="md-icon-button">
                                            <md-icon class="md-secondary">
                                                delete
                                            </md-icon>
                                            <md-tooltip>
                                                Eliminar solicitud
                                            </md-tooltip>
                                        </md-button>
                                        <!-- Show when screen width < 960px -->
                                    </div>
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
                                            <md-menu-item ng-if="!req.validationDate">
                                                <md-button ng-click="openEditRequestDialog($event)">
                                                    <md-icon class="md-secondary">
                                                        edit
                                                    </md-icon>
                                                    Editar solicitud
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
                                            <md-menu-item ng-if="!req.validationDate">
                                                <md-button ng-click="deleteRequest($event)">
                                                    <md-icon>delete</md-icon>
                                                    Eliminar solicitud
                                                </md-button>
                                            </md-menu-item>
                                        </md-menu-content>
                                    </md-menu>
                                </md-list-item>
                                <md-list-item
                                    id="request-status-summary"
                                    class="md-2-line noright">
                                    <md-icon class="info-icon">info_outline</md-icon>
                                    <div class="md-list-item-text" layout="column">
                                        <h3>Estatus de la solicitud: {{req.status}}</h3>
                                        <h4 ng-if="req.reunion">
                                            Reunión &#8470; {{req.reunion}}
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
                                            Bs {{calculatePaymentFee()}}
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
                                        <p>
                                            {{req.phone}}
                                        </p>
                                    </div>
                                </md-list-item>
                                <md-divider md-inset></md-divider>
                                <md-list-item class="md-2-line noright"
                                              id="request-email">
                                    <md-icon>mail_outline</md-icon>
                                    <div class="md-list-item-text" layout="column">
                                        <h3>
                                            Correo electrónico
                                        </h3>
                                        <p>
                                            {{req.email}}
                                        </p>
                                    </div>
                                </md-list-item>
                                <md-divider></md-divider>
                                <div ng-repeat="(dKey, doc) in req.docs">
                                    <md-list-item
                                        id="request-docs"
                                        class="md-2-line"
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
<!-- FAB -->
<!--<div ng-show="contentAvailable" class="relative">-->
<!--    <md-button-->
<!--        id="new-req-fab"-->
<!--        ng-click="openNewRequestDialog($event)"-->
<!--        style="margin-bottom:40px"-->
<!--        class="md-fab md-fab-bottom-right"-->
<!--        aria-label="Create request">-->
<!--        <md-tooltip md-direction="top">-->
<!--            Crear una solicitud-->
<!--        </md-tooltip>-->
<!--        <md-icon>add</md-icon>-->
<!--    </md-button>-->
<!--</div>-->