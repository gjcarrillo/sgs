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
    <!-- Pre-loader -->
    <md-progress-linear md-mode="query" ng-if="loading"></md-progress-linear>
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
                <md-list-item ng-click="toggleList()">
                    <p class="sidenavTitle">
                        Préstamos Personales
                    </p>
                    <md-icon ng-class="md-secondary" ng-if="!showList">keyboard_arrow_down</md-icon>
                    <md-icon ng-class="md-secondary" ng-if="showList">keyboard_arrow_up</md-icon>
                </md-list-item>
                <md-divider></md-divider>
                <div class="slide-toggle" ng-show="showList">
                    <div layout="column" layout-align="center" ng-repeat="(rKey, request) in requests">
                        <md-button
                            ng-click="selectRequest(rKey)"
                            class="requestItems"
                            ng-class="{'md-primary md-raised' : selectedReq === rKey }">
                            Solicitud ID &#8470; {{pad(request.id, 6)}}
                        </md-button>
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
                class="full-content-height"
                ng-if="fetchError != ''"
                layout layout-align="center center"
                class="md-padding">
                <div layout="column" layout-align="center center" class="md-whiteframe-z2 error-card">
                    <span style="color:red">{{fetchError}}</span>
                </div>
            </div>
            <!-- Watermark -->
            <div
                ng-if="fetchError == '' && docs.length == 0"
                class="full-content-height"
                layout layout-align="center center">
                <div class="watermark" layout="column" layout-align="center center">
                    <img src="images/ipapedi.png" alt="Ipapedi logo"/>
                </div>
            </div>
            <!-- The actual content -->
            <md-content
                ng-hide="docs.length == 0"
                class="document-container">
                <div layout layout-align="center center">
                    <md-card class="documents-card">
                        <md-card-content>
                            <md-list>
                                <md-list-item id="request-summary" class="md-3-line" class="noright">
                                    <div class="md-list-item-text request-details-wrapper" layout="column">
                                        <h3 hide-xs class="request-details-title">
                                            Préstamo solicitado el {{requests[selectedReq].creationDate}}
                                        </h3>
                                        <h3 hide-gt-xs class="request-details-title">
                                            Fecha: {{requests[selectedReq].creationDate}}
                                        </h3>
                                        <h4>
                                            Monto solicitado: Bs {{requests[selectedReq].reqAmount | number:2}}
                                        </h4>
                                        <p>
                                            {{requests[selectedReq].comment}}
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
                                    <md-icon  ng-style="{'font-size':'36px'}">info_outline</md-icon>
                                    <div class="md-list-item-text" layout="column">
                                       <h3>Estatus de la solicitud: {{requests[selectedReq].status}}</h3>
                                       <h4 ng-if="requests[selectedReq].reunion">
                                           Reunión &#8470; {{requests[selectedReq].reunion}}
                                       </h4>
                                       <p ng-if="requests[selectedReq].approvedAmount">
                                           Monto aprobado: Bs {{requests[selectedReq].approvedAmount | number:2}}
                                       </p>
                                     </div>
                                </md-list-item>
                                <md-divider></md-divider>
                                <div ng-repeat="(dKey, doc) in docs">
                                    <md-list-item
                                        id="request-docs"
                                        class="md-2-line"
                                        ng-click="downloadDoc(doc)">
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
<di class="relative">
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
