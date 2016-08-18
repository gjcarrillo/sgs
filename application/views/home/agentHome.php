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
        <h2 hide show-gt-xs flex="10" class="md-headline">
            <span>SGDP</span>
        </h2>
        <h2 hide-gt-xs class="md-headline">
            <span>SGDP</span>
        </h2>
        <!-- Search bar -->
        <!-- Show only on width >= 600px screen -->
        <div
            id="search"
            hide show-gt-xs
            layout
            flex-offset="30"
            style="margin-top:25px;">
            <div>
                <md-input-container
                    md-no-float
                    style="padding-bottom:0px">
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
            </div>
            <div>
                <md-input-container
                    md-no-float
                    style="padding-bottom:0px"
                    md-theme="whiteInput">
                   <input
                       placeholder="Ingrese una cédula"
                       aria-label="Search"
                       ng-model="searchInput"
                       ng-keyup="$event.keyCode == 13 && fetchRequests(searchInput)"
                       style="color:white; font-size:16px">
                       <md-tooltip md-direction="right">Ej: 123456789</md-tooltip>
                </md-input-container>
            </div>
        </div>
        <!-- Hide on width >= 600px screen -->
        <div id="search-mobile" hide-gt-xs layout style="margin-top:25px; padding-left:10px;">
            <div>
                <md-input-container
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
            </div>
            <div>
                <md-input-container
                    md-no-float
                    md-theme="whiteInput">
                   <input
                       placeholder="Cédula"
                       aria-label="Search"
                       ng-model="searchInput"
                       ng-keyup="$event.keyCode == 13 && fetchRequests(searchInput)"
                       style="color:white; font-size:16px">
                       <md-tooltip md-direction="right">Ej: 123456789</md-tooltip>
                </md-input-container>
            </div>
        </div>
        <span flex></span>
        <!-- <md-button class="md-fab md-mini md-raised" href="#/generator" aria-label="Generate contract">
            <md-icon style="color:#2196F3">insert_drive_file</md-icon>
            <md-tooltip md-direction="left">Generar PDF</md-tooltip>
        </md-button> -->
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
            <md-list class="sidenavList">
                <md-list-item ng-click="loadUserData()">
                    <p class="sidenavTitle">
                        Datos del afiliado
                    </p>
                </md-list-item>
                <md-divider></md-divider>
                <md-list-item ng-click="toggleList()">
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
                            Solicitud ID &#8470; {{pad(request.id, 6)}}
                        </md-button>
                    </md-list-item>
                </div>
            </md-list>
        </md-content>
    </md-sidenav>
    <!-- Content -->
    <div layout="column" flex>
        <main class="main-w-footer">
            <!-- Search error -->
            <div
                class="full-contet-height"
                ng-if="fetchError != ''"
                layout layout-align="center center"
                class="md-padding">
                <div layout="column" layout-align="center center">
                    <span style="color:red">{{fetchError}}</span>
                </div>
            </div>
            <!-- Watermark -->
            <div
                ng-if="fetchError == '' && docs.length == 0"
                class="full-contet-height"
                layout layout-align="center center">
                <div class="watermark" layout="column" layout-align="center center">
                    <img src="images/ipapedi.png" alt="Ipapedi logo"/>
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
                                <md-list-item id="request-summary" class="md-3-line" class="noright">
                                    <div class="md-list-item-text request-details-wrapper" layout="column">
                                        <h3 class="request-details-title">
                                            Préstamo solicitado el {{requests[selectedReq].creationDate}}
                                        </h3>
                                        <h4>
                                            Monto solicitado: Bs {{requests[selectedReq].reqAmount | number:2}}
                                        </h4>
                                        <p>
                                            {{requests[selectedReq].comment}}
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
                                            ng-if="requests[selectedReq].status == 'Recibida'"
                                            ng-click="openEditRequestDialog($event)">
                                            <md-icon>edit</md-icon>
                                            <md-tooltip>Editar solicitud</md-tooltip>
                                        </md-button>
                                        <md-button ng-click="downloadAll()" class="md-icon-button">
                                            <md-icon>cloud_download</md-icon>
                                            <md-tooltip>Descargar todo</md-tooltip>
                                        </md-button>
                                        <md-button ng-click="deleteRequest($event)" class="md-icon-button">
                                            <md-icon>delete</md-icon>
                                            <md-tooltip>Eliminar solicitud</md-tooltip>
                                        </md-button>
                                    </div>
                                    <!-- Show when screen width < 960px -->
                                    <md-menu
                                        id="request-summary-actions-menu"
                                        hide-gt-sm class="md-secondary">
                                       <md-button
                                            ng-click="$mdOpenMenu($event)"
                                            class="md-icon-button"
                                            aria-label="More">
                                           <md-icon>more_vert</md-icon>
                                       </md-button>
                                       <md-menu-content>
                                           <md-menu-item>
                                               <md-button ng-click="loadHistory()">
                                                   <md-icon>history</md-icon>
                                                   Historial
                                               </md-button>
                                           </md-menu-item>
                                           <md-menu-item ng-if="requests[selectedReq].status == 'Recibida'">
                                               <md-button
                                                   ng-if="requests[selectedReq].status == 'Recibida'"
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
                                           <!-- <md-menu-item>
                                               <md-button ng-click="deleteRequest($event)">
                                                   <md-icon>delete</md-icon>
                                                   Eliminar
                                               </md-icon-button>
                                           </md-menu-item> -->
                                       </md-menu-content>
                                   </md-menu>
                               </md-list-item>
                                <md-list-item id="request-status-summary" class="md-2-line" class="noright">
                                    <md-icon  ng-style="{'font-size':'36px'}">info_outline</md-icon>
                                    <div class="md-list-item-text" layout="column">
                                       <h3>Estado de la solicitud: {{requests[selectedReq].status}}</h3>
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
                                            ng-if="doc.name =='Identidad' || doc.name == 'Solicitud'"
                                            class="md-secondary md-icon-button">
                                            <md-icon>file_download</md-icon>
                                         </md-button>
                                         <md-menu
                                            id="request-docs-actions"
                                            ng-if="doc.name !='Identidad' && doc.name != 'Solicitud'"
                                            class="md-secondary">
                                            <md-button
                                                ng-click="$mdOpenMenu($event)"
                                                class="md-icon-button"
                                                aria-label="More">
                                                <md-icon>more_vert</md-icon>
                                            </md-button>
                                            <md-menu-content>
                                                <md-menu-item>
                                                    <md-button ng-click="editDescription($event, doc)">
                                                        <md-icon>edit</md-icon>
                                                        Descripción
                                                    </md-button>
                                                </md-menu-item>
                                                <md-menu-item>
                                                    <md-button ng-click="downloadDoc(doc)">
                                                        <md-icon>file_download</md-icon>
                                                        Descargar
                                                    </md-button>
                                                </md-menu-item>
                                                <md-menu-item>
                                                    <md-button ng-click="deleteDoc($event, dKey)">
                                                        <md-icon>delete</md-icon>
                                                        Eliminar
                                                    </md-button>
                                                </md-menu-item>
                                            </md-menu-content>
                                        </md-menu>
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
<div ng-hide="requests.length == 0" class="relative">
    <md-button
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
<footer class="footer-frame">
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