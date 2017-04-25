<md-toolbar layout-padding>
    <div class="md-toolbar-tools">
        <md-button ng-click="goHome()" class="md-icon-button">
            <md-icon>
                arrow_back
            </md-icon>
        </md-button>
        <h2 class="md-headline">
            <span>Detalles de solicitud ID {{pad(req.id, 6)}}</span>
        </h2>
        <span flex></span>
        <md-button class="md-icon-button" details-help="" ng-click="showHelp()" aria-label="Help">
            <md-icon>help_outline</md-icon>
            <md-tooltip md-direction="bottom">Ayuda</md-tooltip>
        </md-button>
        <md-button class="md-icon-button" ng-click="logout()" aria-label="Logout">
            <md-icon>exit_to_app</md-icon>
            <md-tooltip md-direction="bottom">Cerrar sesión</md-tooltip>
        </md-button>
    </div>
</md-toolbar>
<main class="main-w-footer">
    <overlay ng-if="overlay"/>
    <md-content class="document-container">
        <div layout="column" layout-align="center center">
            <!-- NOT VALIDATED message for APPLICANTS -->
            <md-card id="validation-card" class="validation-card" ng-if="!req.validationDate && userType(APPLICANT)">
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
            <!-- NOT VALIDATED message for AGENTS -->
            <md-card id="validation-card" class="validation-card" ng-if="!req.validationDate && userType(AGENT)">
                <md-card-content>
                    <p>
                        SOLICITUD NO VALIDADA <br/><br/>
                    </p>
                    <p>
                        Puede editar la información de la solicitud haciendo clic en
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
                        El asociado debe ingresar al sistema usando sus credenciales y una vez esté completamente seguro
                        de proceder con esta solicitud, debe hacer clic en VALIDAR.
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
            <!-- Information of interest -->
            <md-card ng-show="req.status == APPROVED && !loading" md-theme="manual-card" class="margin-16 interest-info-card">
                <md-card-content>
                    <span style="color: #2E7D32" ng-if="!userType(MANAGER)">
                        Puede volver a solicitar un préstamo del tipo {{loanTypes[req.type].DescripcionDelPrestamo}}
                        a partir de la fecha {{dateAvailable}}
                    </span>
                    <span style="color: #2E7D32" ng-if="userType(MANAGER)">
                        Este asociado puede volver a solicitar un préstamo del del tipo {{loanTypes[req.type].DescripcionDelPrestamo}}
                        a partir de la fecha {{dateAvailable}}
                    </span>
                </md-card-content>
            </md-card>
            <md-progress-circular ng-if="loading" aria-label="Loading..." md-mode="indeterminate" md-diameter="60">
            </md-progress-circular>
            <!-- Details card-->
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
                                <!-- User personal data -->
                                <md-button
                                    ng-if="!userType(APPLICANT)"
                                    ng-click="loadUserData(req.userOwner)"
                                    class="md-icon-button">
                                    <md-icon class="md-secondary">
                                        person
                                    </md-icon>
                                    <md-tooltip>Datos del asociado</md-tooltip>
                                </md-button>
                                <!-- Update btn for MANAGERs -->
                                <md-button
                                    class="md-icon-button"
                                    ng-if="showManagerEditBtn()"
                                    ng-click="openManageRequestDialog($event)">
                                    <md-icon class="md-secondary">
                                        edit
                                    </md-icon>
                                    <md-tooltip>
                                        Gestionar solicitud
                                    </md-tooltip>
                                </md-button>
                                <!-- History btn -->
                                <md-button
                                    ng-if="!userType(APPLICANT)"
                                    ng-click="loadHistory()"
                                    class="md-icon-button">
                                    <md-icon class="md-secondary">
                                        history
                                    </md-icon>
                                    <md-tooltip>Historial</md-tooltip>
                                </md-button>
                                <!-- Update btn for AGENTS -->
                                <md-button
                                    class="md-icon-button"
                                    ng-if="showAgentEditBtn()"
                                    ng-click="openUpdateRequestDialog($event)">
                                    <md-icon class="md-secondary">
                                        edit
                                    </md-icon>
                                    <md-tooltip>
                                        Editar solicitud
                                    </md-tooltip>
                                </md-button>
                                <!-- Edit btn -->
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
                                    <!-- User personal data -->
                                    <md-menu-item ng-if="!userType(APPLICANT)">
                                        <md-button
                                            ng-click="loadUserData(req.userOwner)">
                                            <md-icon class="md-secondary">
                                                person
                                            </md-icon>
                                            Datos del asociado
                                        </md-button>
                                    </md-menu-item>
                                    <!-- Update btn for MANAGERs -->
                                    <md-menu-item ng-if="showManagerEditBtn()">
                                        <md-button
                                            ng-click="openManageRequestDialog($event)">
                                            <md-icon class="md-secondary">
                                                edit
                                            </md-icon>
                                            Gestionar solicitud
                                        </md-button>
                                    </md-menu-item>
                                    <!-- History btn -->
                                    <md-menu-item ng-if="!userType(APPLICANT)">
                                        <md-button
                                            ng-click="loadHistory()">
                                            <md-icon class="md-secondary">
                                                history
                                            </md-icon>
                                            Historial
                                        </md-button>
                                    </md-menu-item>
                                    <!-- Update btn for AGENTS -->
                                    <md-menu-item ng-if="showAgentEditBtn()">
                                        <md-button
                                            ng-click="openUpdateRequestDialog($event)">
                                            <md-icon class="md-secondary">
                                                edit
                                            </md-icon>
                                            Editar solicitud
                                        </md-button>
                                    </md-menu-item>
                                    <!-- Edit btn -->
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
                        <md-divider md-inset ng-if="req.validationDate"></md-divider>
                        <md-list-item ng-if="req.validationDate" class="md-2-line noright"
                                      id="request-validation-date">
                            <md-icon class="phone-icon">verified_user</md-icon>
                            <div class="md-list-item-text" layout="column">
                                <h3>
                                    Fecha de validación
                                </h3>
                                <p>
                                    {{req.validationDate}}
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
                                    Bs {{calculatePaymentFee()}} mensualmente
                                </h4>
                                <p>
                                    Durante {{req.due}} {{req.due == 1 ? 'mes' : 'meses consecutivos'}}
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
                                    ng-if="!isDocEditable(doc.type)"
                                    class="md-icon-button">
                                    <md-icon class="md-secondary">
                                        file_download
                                    </md-icon>
                                </md-button>
                                <!-- Agents editable docs -->
                                <md-menu
                                    id="request-docs-actions"
                                    ng-if="isDocEditable(doc.type)">
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

            <md-card class="documents-card" ng-if="req.deductions" id="deductions">
                <md-toolbar class="md-table-toolbar md-default">
                    <div class="md-toolbar-tools">
                        <span>Deducciones adicionales</span>
                    </div>
                </md-toolbar>
                <md-table-container>
                    <table md-table>
                        <thead md-head>
                        <tr md-row>
                            <th md-column><span>Préstamo</span></th>
                            <th md-column><span>Deducción (Bs)</span></th>
                            <th md-column><span>Total (Bs)</span></th>
                        </tr>
                        </thead>
                        <tbody md-body>
                        <tr md-row ng-repeat="(dKey, deduction) in req.deductions">
                            <td md-cell>{{deduction.description}}</td>
                            <td md-cell>{{deduction.amount | number: 2}}</td>
                            <td md-cell>{{totalDeductions(dKey) | number: 2}}</td>
                        </tr>
                        </tbody>
                    </table>
                </md-table-container>
            </md-card>
        </div>
    </md-content>
</main>
<md-divider></md-divider>
