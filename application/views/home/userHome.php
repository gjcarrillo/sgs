<!-- Header -->
<md-toolbar layout-padding>
    <div class="md-toolbar-tools">
        <h2 class="md-headline">
            <span>SGDP</span>
        </h2>
        <span flex></span>
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
     <!-- Actual content -->
    <div ng-hide="requests.length == 0" layout="row">
        <!-- Requests list -->
        <md-content style="background-color: #F5F5F5" ng-style="getSidenavHeight()" flex="30">
            <div layout="column">
                <md-sidenav
                    class="md-sidenav-left"
                    md-component-id="left"
                    md-is-locked-open="true"
                    md-whiteframe="4"
                    ng-style="getSidenavHeight()"
                    md-disable-backdrop>
                    <md-list>
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
                </md-sidenav>
            </div>
        </md-content>
        <!-- Documents container -->
        <md-content class="watermark2" ng-if="docs.length == 0" flex>
            <!-- Watermark -->
            <img src="images/ipapedi.png" alt="Ipapedi logo"/>
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
                        <md-button
                            class="md-icon-button"
                            ng-click="downloadAll()">
                            <md-icon>cloud_download</md-icon>
                            <md-tooltip>Descargar todos los archivos</md-tooltip>
                        </md-button>
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
                                <md-icon ng-if="$first" ng-style="{'color':'#2196F3', 'font-size':'36px'}">perm_identity</md-icon>                                <div class="md-list-item-text" layout="column">
                                   <h3>{{doc.name}}</h3>
                                   <p>{{doc.description}}</p>
                                 </div>
                                 <md-icon>file_download</md-icon>
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
<md-divider></md-divider>
<footer>
    <div layout layout-align="space-around center">
        <span>&copy; IPAPEDI 2016</span>
        <span>Desarrollado por <a class="md-accent" href="https://ve.linkedin.com/in/kristopherch" target="_blank">Kristopher Perdomo</a></span>
        <md-button class="md-accent" href="http://www.ipapedi.com" target="_blank">IPAPEDI</md-button>
    </div>
</footer>
</body>
</html>
