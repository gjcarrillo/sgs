<!-- Header -->
<md-toolbar layout-padding>
    <div class="md-toolbar-tools">
        <h2 flex="10" class="md-headline">
            <span>SGDP</span>
        </h2>
        <!-- Search bar -->
        <md-input-container
            md-no-float class="md-accent"
            flex="30"
            flex-offset="25"
            style="padding-bottom:0px;margin-right:25px">
           <md-icon style="color:white" class="material-icons">&#xE8B6;</md-icon>
           <input
               placeholder="Busque solicitudes de préstamo"
               aria-label="Search"
               ng-model="searchInput"
               ng-keyup="$event.keyCode == 13 && fetchRequests(searchInput)"
               style="color:white; padding-left:25px; margin-right:5px; font-size:16px">
               <md-tooltip md-direction="right">Ingrese una cédula. Ej: 11111111</md-tooltip>
        </md-input-container>
        <span flex></span>
        <md-button class="md-fab md-mini md-raised" href="#/generator" aria-label="Generate contract">
            <md-icon style="color:#2196F3">insert_drive_file</md-icon>
            <md-tooltip md-direction="left">Generar PDF</md-tooltip>
        </md-button>
        <md-button class="md-fab md-mini md-raised" ng-click="logout()" aria-label="Back">
            <md-icon>exit_to_app</md-icon>
            <md-tooltip md-direction="left">Cerrar sesión</md-tooltip>
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
                        <div layout layout-align="center">
                            <md-subheader style="color:teal">Lista de solicitudes</md-subheader>
                        </div>
                        <md-divider><md-divider>
                        <md-list-item
                            ng-repeat="(rKey, request) in requests">
                            <md-button
                                flex
                                ng-click="selectRequest(rKey)"
                                ng-class="{'md-primary md-raised' : selectedReq === rKey }">
                                #{{pad(rKey+1, 2)}} - {{request.creationDate}}
                            </md-button>
                        </md-list-item>
                    </md-list>
                </md-sidenav>
            </div>
        </md-content>
        <!-- Documents container -->
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
                        <md-button ng-click="openEditRequestDialog($event)" class="md-icon-button">
                            <md-tooltip>Editar solicitud</md-tooltip>
                            <md-icon>edit</md-icon>
                        </md-button>
                        <md-button ng-click="deleteRequest($event)" class="md-icon-button">
                            <md-tooltip>Eliminar solicitud</md-tooltip>
                            <md-icon>delete</md-icon>
                        </md-icon-button>
                    </div>
                    <md-list>
                        <md-list-item class="md-2-line"class="noright">
                            <md-icon  ng-style="{'font-size':'36px'}">info_outline</md-icon>
                            <div class="md-list-item-text" layout="column">
                               <h3>Estado de la solicitud: {{requests[selectedReq].status}}</h3>
                               <p>{{requests[selectedReq].comment}}</p>
                             </div>
                        </md-list-item>
                        <md-divider></md-divider>
                        <div ng-repeat="(dKey, doc) in docs">
                            <md-list-item
                                class="md-2-line"
                                ng-click="downloadDoc(doc)"
                                class="noright">
                                <md-icon  ng-style="{'color':'#2196F3', 'font-size':'36px'}">insert_drive_file</md-icon>
                                <div class="md-list-item-text" layout="column">
                                   <h3>{{doc.name}}</h3>
                                   <p>{{doc.description}}</p>
                                 </div>
                                 <md-menu class="md-secondary">
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
        <md-button class="md-accent" href="https://github.com/kperdomo1/sgdp" target="_blank">GitHub</md-button>
        <p class="md-body-1">Creado por Kristopher Perdomo</p>
        <md-button class="md-accent" href="http://www.ipapedi.com" target="_blank">IPAPEDI</md-button>
    </div>
</footer>
</body>
</html>
