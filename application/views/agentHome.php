<!-- Header -->
<md-toolbar layout-padding>
    <div class="md-toolbar-tools" ng-hide="searchEnabled">
        <md-button
            id="nav-panel"
            ng-show="contentAvailable"
            hide-gt-sm
            class="md-icon-button"
            ng-click="openMenu()"
            aria-label="Open sidenav">
            <md-icon>menu</md-icon>
        </md-button>
        <h2 style="padding-right:10px;" class="md-headline">
            <span>{{appName}}</span>
        </h2>
        <span flex></span>
        <!-- Search bar -->
        <div id="search" hide show-gt-sm class="search-wrapper">
            <div layout layout-align="center center">
                <md-select
                    class="no-md-select"
                    aria-label="V or E ID"
                    ng-model="idPrefix">
                    <md-option value="V">V</md-option>
                    <md-option value="E">E</md-option>
                </md-select>
                <input
                    autofocus
                    class="search-input"
                    placeholder="Ingrese una cédula"
                    aria-label="Search"
                    ng-model="searchInput"
                    ng-keyup="$event.keyCode == 13 &&
                        fetchRequests(searchInput)"
                    type="text"/>
                <md-icon
                    ng-click="fetchRequests(searchInput)"
                    class="search-icon">
                    search
                </md-icon>
            </div>
            <md-tooltip md-direction="right">Ej: 123456789</md-tooltip>
        </div>
        <!-- <md-button class="md-fab md-mini md-raised" href="#/generator" aria-label="Generate contract">
            <md-icon style="color:#2196F3">insert_drive_file</md-icon>
            <md-tooltip md-direction="left">Generar PDF</md-tooltip>
        </md-button> -->
        <md-button
            id="toggle-search"
            class="md-icon-button"
            hide-gt-sm
            ng-click="toggleSearch()"
            aria-label="Search">
            <md-icon>search</md-icon>
            <md-tooltip md-direction="bottom">Buscar</md-tooltip>
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
    <!-- Mobile search bar -->
    <div class="md-toolbar-tools" ng-if="searchEnabled">
        <md-button
            id="nav-panel"
            class="md-icon-button"
            ng-click="toggleSearch()"
            aria-label="Close searchbar">
            <md-icon>arrow_back</md-icon>
        </md-button>
        <div class="search-wrapper-xs" flex>
            <div layout layout-align="center center">
                <md-icon
                    ng-show="contentAvailable"
                    hide-gt-sm
                    ng-click="openMenu()" class="toggle-search-icon">
                    menu
                </md-icon>
                <md-select
                    class="no-md-select"
                    aria-label="V or E ID"
                    ng-model="idPrefix">
                    <md-option value="V">V</md-option>
                    <md-option value="E">E</md-option>
                </md-select>
                <input
                    id="search-input"
                    class="search-input"
                    placeholder="Ingrese una cédula"
                    aria-label="Search"
                    ng-model="searchInput"
                    ng-keyup="$event.keyCode == 13 && fetchRequests(searchInput)"
                    type="text"/>
                <md-icon ng-click="clearSearch()" class="search-icon">close</md-icon>
            </div>
        </div>
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
                    <div ng-if="requests.length == 0" layout layout-align="center center" class="md-padding">
                        <p style="color:#F44336">
                            Este afiliado no posee solicitudes de préstamos personales
                        </p>
                    </div>
                    <div
                        layout="column"
                        layout-align="center"
                        ng-repeat="(rKey, request) in requests">
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
                class="full-content-height md-padding"
                ng-if="fetchError != ''"
                layout layout-align="center center">
                <div layout="column" layout-align="center center" class="md-whiteframe-z2 error-card">
                    <span style="color:red">{{fetchError}}</span>
                </div>
            </div>
            <!-- Watermark -->
            <div
                ng-if="fetchError == '' && docs.length == 0"
                class="full-content-height"
                layout="column" layout-align="center center">
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
                                <md-list-item id="request-summary" class="md-3-line noright">
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
                                    <!-- Show only when screen width >= 960px -->
                                    <div
                                        id="request-summary-actions"
                                        hide show-gt-sm>
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
                                            ng-if="requests[selectedReq].status == 'Recibida'"
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
                                                Descargar todo
                                            </md-tooltip>
                                        </md-button>
                                        <!-- <md-button
                                            ng-click="deleteRequest($event)"
                                            class="md-icon-button">
                                            <md-icon class="md-secondary">
                                                delete
                                            </md-icon>
                                            <md-tooltip>
                                                Eliminar solicitud
                                            </md-tooltip>
                                        </md-button> -->
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
                                                    <md-icon
                                                        class="md-secondary">
                                                        history
                                                    </md-icon>
                                                    Historial
                                                </md-button>
                                            </md-menu-item>
                                            <md-menu-item ng-if="requests[selectedReq].status == 'Recibida'">
                                                <md-button ng-click="openEditRequestDialog($event)">
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
                                <md-list-item id="request-status-summary" class="md-2-line noright">
                                    <md-icon ng-style="{'font-size':'36px'}">info_outline</md-icon>
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
                                        class="md-2-line noright"
                                        ng-click="downloadDoc(doc)">
                                        <md-icon
                                            ng-if="doc.name !='Identidad'"
                                            ng-style="{'color':'#2196F3', 'font-size':'36px'}">
                                            insert_drive_file
                                        </md-icon>
                                        <md-icon
                                            ng-if="doc.name =='Identidad'"
                                            ng-style="{'color':'#2196F3', 'font-size':'36px'}">
                                            perm_identity
                                        </md-icon>
                                        <div class="md-list-item-text" layout="column">
                                            <h3>{{doc.name}}</h3>

                                            <p>{{doc.description}}</p>
                                        </div>
                                        <md-button
                                            ng-if="doc.name =='Identidad'"
                                            class="md-icon-button">
                                            <md-icon class="md-secondary">
                                                file_download
                                            </md-icon>
                                        </md-button>
                                        <md-menu
                                            id="request-docs-actions"
                                            ng-if="doc.name !='Identidad'">
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
                                                    <md-button ng-click="editDescription($event, doc)">
                                                        <md-icon class="md-secondary">
                                                            edit
                                                        </md-icon>
                                                        Descripción
                                                    </md-button>
                                                </md-menu-item>
                                                <md-menu-item>
                                                    <md-button ng-click="downloadDoc(doc)">
                                                        <md-icon class="md-secondary">
                                                            file_download
                                                        </md-icon>
                                                        Descargar
                                                    </md-button>
                                                </md-menu-item>
                                                <md-menu-item>
                                                    <md-button ng-click="deleteDoc($event, dKey)">
                                                        <md-icon class="md-secondary">
                                                            delete
                                                        </md-icon>
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
<div ng-show="contentAvailable" class="relative">
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