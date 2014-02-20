Ext.define 'Base.Storage.Schema',

    extend:'Ext.panel.Panel'

    layout:
        type: 'hbox'
        align:"stretch"
        pack:"start"

    createGrid: (config) ->

        getField = (name, config) ->
            config = config || {}
            Ext.applyIf config, 
                header: name
                dataIndex: name
                width: if name is 'comment' then 150 else 80

        getTextField = (name, config) ->
            config = config || {}
            getField name, Ext.applyIf config, editor: xtype:'textfield'

        fields = config.fields.split ' '

        grid = Ext.create 'Ext.grid.Panel', Ext.applyIf config,
            title:'grid'
            flex:1
            border:false

            addHandler: -> grid.store.add {}
            removeHandler: -> 
                r = grid.selModel.getSelection()[0]
                r.store.remove r

            tbar: [
                text:'add', handler: -> grid.addHandler()
                '-'
                text:'remove', handler: -> grid.removeHandler()
            ]

            plugins: [
                Ext.create 'Ext.grid.plugin.CellEditing', 
                    clicksToEdit: config.clicks || 1
            ]

            listeners: selectionchange: (sm, sel) -> grid.down('[text=remove]').setDisabled !sel.length

            store: 
                idProperty: fields[0]
                fields: fields

            columns: Ext.Array.map fields, (field) ->
                if 'id_' is Ext.util.Format.substr field, 0, 3
                    getField field, hidden: true
                else if field is 'type'
                    getField field, editor:
                        xtype:'combobox'
                        queryMode: 'local'
                        valueField: 'type'
                        displayField: 'type'
                        store: Ext.create 'Ext.data.JsonStore',
                            fields: ['type']
                            data: Ext.Array.map 'string date integer float text'.split(' '), (t) -> type: t

                else if field is 'pk' or field is 'ndx'
                    getField field, width:50
                else 
                    getTextField field

    initComponent: ->

        # create components
        @items = [
            models = @createGrid 
                title:'models'
                fields: 'id_model nick comment'
                width:350
                style: borderRight: "1px solid #99BCE8"
                clicks: 2
        ,
            right = Ext.create 'Ext.panel.Panel', 
                flex:1
                disabled: true
                border:false
                layout:
                    type: 'vbox'
                    align:"stretch"
                    pack:"start"
                items: [
                    fields = @createGrid 
                        style: borderBottom: "1px solid #99BCE8"
                        title:'fields'
                        fields: 'id_field id_model nick comment type pk ndx'
                        addHandler: -> fields.store.add
                            id_model: models.getSelectionModel().getSelection()[0].get 'id_model'
                            type: 'string'

                    relations = @createGrid
                        title:'relations'
                        fields: 'id_relation id_model factor model alias id_related_model'
                        plugins: []
                ]
        ]


        # bind 
        models.on 'selectionchange', (sm, sel) -> 
            right.disable() 
            if sel.length 
                right.enable()
                fields.store.clearFilter()
                fields.store.filter 'id_model', sel[0].get 'id_model'
                relations.store.clearFilter()
                relations.store.filter 'id_model', sel[0].get 'id_model'

        # load data 
        @loadData = (data) -> 
            models.store.loadData data.models
            fields.store.loadData data.fields
            relations.store.loadData data.relations

        # default filter
        fields.store.filter 'id_module', '-1'
        relations.store.filter 'id_module', '-1'

        @bbar = [
            ' '
            xtype:'label'
            text:'Base model manager'
            '->'
            text:'Apply'
        ]

        @callParent arguments

        @on 'render', ->
            relations.view.getHeaderCt().getGridColumns()[3].renderer = (v,e,r) -> 
                model = models.store.findRecord 'id_model', r.get 'id_related_model'
                model.get 'nick'
                
