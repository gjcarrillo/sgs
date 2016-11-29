<!-- Header -->
<md-toolbar layout-padding>
    <div class="md-toolbar-tools">
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
            <span>{{appName}}</span>
        </h2>
        <span flex></span>
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
    <!-- Sidenav -->
    <md-sidenav
        id="requests-list"
        ng-show="contentAvailable"
        class="md-sidenav-left sidenav-frame"
        md-component-id="left"
        md-is-locked-open="$mdMedia('gt-sm') && contentLoaded">
        <md-content class="sidenav-height">
            <!-- Requests list -->
            <md-list class="sidenavList">
                <div ng-repeat="(rKey, request) in requests">
                    <md-list-item ng-click="toggleList(rKey)">
                        <p class="sidenavTitle">
                            {{listTitle[rKey]}}
                        </p>
                        <md-icon ng-class="md-secondary" ng-if="!showList[rKey]">keyboard_arrow_down</md-icon>
                        <md-icon ng-class="md-secondary" ng-if="showList[rKey]">keyboard_arrow_up</md-icon>
                    </md-list-item>
                    <md-divider></md-divider>
                    <div class="slide-toggle" ng-show="showList[rKey]">
                        <div ng-if="request.length == 0">
                            <div layout layout-align="center center" class="md-padding">
                                <p style="color:#F44336">
                                    Usted no posee solicitudes de {{listTitle[rKey]}}
                                </p>
                            </div>
                            <md-divider></md-divider>
                        </div>
                        <div layout="column" layout-align="center" ng-repeat="(lKey, loan) in request">
                            <md-button
                                ng-click="selectRequest(rKey, lKey)"
                                class="requestItems"
                                ng-class="{'md-primary md-raised' : selectedReq == rKey && selectedLoan === lKey }">
                                Solicitud ID &#8470; {{pad(loan.id, 6)}}
                            </md-button>
                            <md-divider ng-if="$last"></md-divider>
                        </div>
                    </div>
                </div>
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
                ng-if="fetchError == '' && !req.docs"
                class="full-content-height"
                layout="column" layout-align="center center">
                <div ng-if="!loading" class="watermark" layout="column" layout-align="center center">
                    <img src="images/ipapedi.png" alt="Ipapedi logo"/>
                </div>
            </div>
            <!-- The actual content -->
            <md-content
                ng-hide="!req.docs"
                class="document-container">
                <div layout="column" layout-align="center center">
                    <md-card class="documents-card">
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
                                    <md-button
                                        id="request-summary-actions"
                                        ng-click="downloadAll()"
                                        class="md-icon-button">
                                        <md-icon class="md-secondary">
                                            cloud_download
                                        </md-icon>
                                        <md-tooltip>
                                            Descargar todo
                                        </md-tooltip>
                                    </md-button>
                               </md-list-item>
                                <md-list-item
                                    id="request-status-summary"
                                    class="md-2-line noright">
                                    <md-icon style="padding-top: 10px">info_outline</md-icon>
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
                                    <md-icon style="color: #546E7A">payment</md-icon>
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
                                    <md-icon style="color: #009688">phone</md-icon>
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
<div class="relative">
    <md-button
        id="new-req-fab"
        ng-click="openNewRequestDialog($event)"
        style="margin-bottom:40px"
        class="md-fab md-fab-bottom-right"
        aria-label="Create request">
        <md-tooltip md-direction="top">
            Crear una solicitud
        </md-tooltip>
        <md-icon>add</md-icon>
    </md-button>
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
