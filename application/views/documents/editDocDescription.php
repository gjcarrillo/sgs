<md-dialog aria-label="Edit Request">
    <div layout-padding>
        <md-input-container md-no-float>
            <input
                type="text"
                md-auto-focus
                ng-keyup="$event.keyCode == 13 && saveEdition()"
                ng-model="doc.description"
                placeholder="Descripción"/>
        </md-input-container>
    </div>
</md-dialog>
