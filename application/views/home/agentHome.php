<!-- Header -->
<md-toolbar layout-padding>
    <div class="md-toolbar-tools">
        <h2 hide show-gt-xs flex="10" class="md-headline">
            <span>SGDP</span>
        </h2>
        <h2 hide-gt-xs class="md-headline">
            <span>SGDP</span>
        </h2>
        <!-- Search bar -->
        <!-- Show only on width >= 600px screen -->
        <div hide show-gt-xs layout flex-offset="30" style="margin-top:25px;">
            <div>
                <md-input-container
                    md-no-float
                    class="md-accent">
                    <md-select
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
                    class="md-accent">
                   <input
                       placeholder="Ingrese cédula"
                       aria-label="Search"
                       ng-model="searchInput"
                       ng-keyup="$event.keyCode == 13 && fetchRequests(searchInput)"
                       style="color:white; font-size:16px">
                       <md-tooltip md-direction="right">Ej: 123456789</md-tooltip>
                </md-input-container>
            </div>
        </div>
        <!-- Hide on width >= 600px screen -->
        <div hide-gt-xs layout style="margin-top:25px;padding-left:10px;">
            <div>
                <md-input-container
                    md-no-float
                    class="md-accent">
                    <md-select
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
                    class="md-accent">
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
        <md-button class="md-icon-button" ng-click="null" aria-label="Help">
            <md-icon>help_outline</md-icon>
            <md-tooltip md-direction="down">Ayuda</md-tooltip>
        </md-button>
        <md-button class="md-icon-button" ng-click="logout()" aria-label="Logout">
            <md-icon>exit_to_app</md-icon>
            <md-tooltip md-direction="down">Cerrar sesión</md-tooltip>
        </md-button>
    </div>
</md-toolbar>
<!-- Content -->
<main class="main-w-footer">
    <!-- Pre-loader -->
    <div ng-if="loading" layout layout-align="center center" class="md-padding">
        <md-progress-circular md-mode="indeterminate" md-diameter="80"></md-progress-circular>
    </div>
    <!-- Search error -->
    <div ng-if="fetchError != ''" layout layout-align="center center" class="md-padding">
        <span style="color:red">{{fetchError}}</span>
    </div>
    <!-- Watermark -->
    <div class="watermark" ng-if="requests.length == 0 && !loading">
        <div layout layout-align="center center">
            <img src="images/ipapedi.png" alt="Ipapedi logo"/>
        </div>
    </div>
     <!-- Actual content -->
    <div ng-hide="requests.length == 0" layout="row">
        <!-- Requests list -->
        <md-content style="background-color: #F5F5F5" ng-style="getSidenavHeight()" flex="30">
            <div layout="column" layout-fill flex>
                <md-sidenav
                    class="md-sidenav-left"
                    md-component-id="left"
                    md-is-locked-open="true"
                    md-whiteframe="4"
                    ng-style="getSidenavHeight()"
                    md-disable-backdrop>
                    <md-list>
                        <div class="md-toolbar-tools">
                            <md-button class="md-primary" ng-click="loadUserData()" flex>Datos del afiliado</md-button>
                        </div>
                        <md-divider></md-divider>
                        <div class="md-toolbar-tools">
                            <md-button class="md-primary" flex ng-click="toggleList()">
                                Lista de solicitudes
                            </md-button>
                            <md-button ng-click="toggleList()" class="md-icon-button">
                                <md-icon ng-if="!showList">keyboard_arrow_down</md-icon>
                                <md-icon ng-if="showList">keyboard_arrow_up</md-icon>
                            </md-button>
                        </div>
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
                </md-sidenav>
            </div>
        </md-content>
        <!-- Documents container -->
        <md-content class="watermark2" ng-if="docs.length == 0" flex>
            <!-- Watermark -->
            <img src="images/ipapedi.png" alt="Ipapedi logo"/>
        </md-content>
        <md-content
            flex
            ng-hide="docs.length == 0"
            ng-style="getDocumentContainerStyle()">
            <md-card class="documents-card">
                <md-card-content>
                    <div class="md-toolbar-tools">
                        <h2 class="md-headline">Préstamo solicitado el {{requests[selectedReq].creationDate}}</h2>
                        <span flex></span>
                        <md-button ng-click="loadHistory()" class="md-icon-button">
                            <md-tooltip>Historial</md-tooltip>
                            <md-icon>history</md-icon>
                        </md-button>
                        <md-button
                            ng-if="requests[selectedReq].status == 'Recibida'"
                            ng-click="openEditRequestDialog($event)"
                            class="md-icon-button">
                            <md-tooltip>Editar solicitud</md-tooltip>
                            <md-icon>edit</md-icon>
                        </md-button>
                        <md-button
                            class="md-icon-button"
                            ng-click="null">
                            <md-icon>cloud_download</md-icon>
                            <md-tooltip>Descargar todos los archivos</md-tooltip>
                        </md-button>
                        <md-button ng-click="deleteRequest($event)" class="md-icon-button">
                            <md-tooltip>Eliminar solicitud</md-tooltip>
                            <md-icon>delete</md-icon>
                        </md-icon-button>
                    </div>
                    <md-list>
                        <md-list-item class="md-3-line"class="noright">
                            <md-icon  ng-style="{'font-size':'36px'}">info_outline</md-icon>
                            <div class="md-list-item-text" layout="column">
                               <h3>Estado de la solicitud: {{requests[selectedReq].status}}</h3>
                               <h4 ng-if="requests[selectedReq].reunion">Reunión &#8470; {{requests[selectedReq].reunion}}</h4>
                               <p ng-if="!requests[selectedReq].approvedAmount">
                                   Monto solicitado: Bs {{requests[selectedReq].reqAmount | number:2}}
                               </p>
                               <p ng-if="requests[selectedReq].approvedAmount">
                                   Monto solicitado: Bs {{requests[selectedReq].reqAmount | number:2}} /
                                   Monto aprobado: Bs {{requests[selectedReq].approvedAmount | number:2}}
                               </p>
                             </div>
                        </md-list-item>

                        <md-divider></md-divider>
                        <div ng-repeat="(dKey, doc) in docs">
                            <md-list-item
                                class="md-2-line"
                                ng-click="downloadDoc(doc)"
                                class="noright">
                                <md-icon ng-if="!$first" ng-style="{'color':'#2196F3', 'font-size':'36px'}">insert_drive_file</md-icon>
                                <md-icon ng-if="$first" ng-style="{'color':'#2196F3', 'font-size':'36px'}">perm_identity</md-icon>
                                <div class="md-list-item-text" layout="column">
                                   <h3>{{doc.name}}</h3>
                                   <p>{{doc.description}}</p>
                                 </div>
                                 <md-button ng-if="dKey <= 1" ng-click="downloadDoc(doc)" class="md-icon-button">
                                     <md-icon>file_download</md-icon>
                                 </md-button>
                                 <md-menu ng-if="dKey > 1" class="md-secondary">
                                    <md-button ng-click="$mdOpenMenu($event)" class="md-icon-button" aria-label="More">
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
            <br/>
        </md-content>
    </div>
</main>
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
<md-divider></md-divider>
<footer>
    <div layout layout-align="space-around center">
        <span>&copy; IPAPEDI 2016</span>
        <span>Desarrollado por <a class="md-accent" href="mailto:kperdomo@gmail.com" target="_blank">Kristopher Perdomo</a></span>
        <md-button class="md-accent" href="http://www.ipapedi.com" target="_blank">IPAPEDI</md-button>
    </div>
</footer>
</body>
</html>
