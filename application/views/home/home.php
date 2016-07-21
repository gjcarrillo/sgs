<md-content style="height: 400px">
<div class="relative">
    <md-fab-speed-dial class="md-fab-bottom-right md-scale" md-open="isOpen" ng-mouseenter="isOpen=true" ng-mouseleave="isOpen=false" md-direction="up" class="md-fling">
        <md-fab-trigger>
            <md-button class="md-fab md-accent" aria-label="Menu..."><md-icon>menu</md-icon></md-button>
        </md-fab-trigger>
        <md-fab-actions>
            <md-button class="md-fab md-mini md-raised" aria-label="Add Document">
                <md-icon style="color:teal">add</md-icon>
            </md-button>
            <md-button class="md-fab md-mini md-raised" aria-label="Remove Document">
                <md-icon style="color:red">delete</md-icon>
            </md-button>
        </md-fab-actions>
    </md-fab-speed-dial>
</div>
<div layout="column" layout-fill>
    <md-sidenav
        class="md-sidenav-left"
        md-component-id="left"
        md-is-locked-open="true"
        md-whiteframe="4"
        md-disable-backdrop>
        <md-list>
            <md-list-item>
                <md-button flex class="md-raised md-primary">
                    13/08/2015
                </md-button>
            </md-list-item>
            <md-list-item>
                <md-button flex>
                    19/04/2014
                </md-button>
            </md-list-item>

        </md-list>
    </md-sidenav>
</div>
</md-content>
