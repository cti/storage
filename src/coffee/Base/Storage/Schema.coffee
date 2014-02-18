Ext.define 'Base.Storage.Schema',
    extend:'Ext.panel.Panel'
    layout:
        type: 'hbox'
        align:"stretch"
        pack:"start"
    defaults: border: false
    items: [
        title:'models'
        xtype:'grid'
        width:350
        style: borderRight: "1px solid #99BCE8"
        tbar: [
            text:'add'
            '-'
            text:'remove'
            disabled: true
        ]
        columns: [
            header:'nick'
        ,
            header:'comment'
        ]
    ,
        flex:1
        disabled: true
        layout:
            type: 'vbox'
            align:"stretch"
            pack:"start"
        items: [
            title:'fields'
            xtype:'grid'
            border:false
            style: borderBottom: "1px solid #99BCE8"            
            flex:1
            columns: [
                header:'nick'
            ,
                header:'title'
            ,
                header:'type'
            ]
            tbar: [
                text:'add'
                '-'
                text:'remove'
                disabled: true
            ]
        ,
            title:'has one'
            xtype:'grid'
            border:false
            style: borderBottom: "1px solid #99BCE8"            
            flex:1
            columns: [
                header:'nick'
            ,
                header:'comment'
            ]
            tbar: [
                text:'add'
                '-'
                text:'remove'
                disabled: true
            ]
        ,
            title:'has many'
            xtype:'grid'
            border:false
            style: borderBottom: "1px solid #99BCE8"            
            flex:1
            columns: [
                header:'nick'
            ,
                header:'comment'
            ]
            tbar: [
                text:'add'
                '-'
                text:'remove'
                disabled: true
            ]
        ]
    ]
    bbar: [
        '->'
        text:'Apply'
        disabled: true
    ]
