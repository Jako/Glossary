Glossary.grid.Terms = function (config) {
    /* action button renderer */
    this.buttonColumnTpl = new Ext.XTemplate('<tpl for=".">'
        + '<tpl if="action_buttons !== null">'
        + '<ul class="action-buttons">'
        + '<tpl for="action_buttons">'
        + '<li><i class="icon {className} icon-{icon}" title="{text}"></i></li>'
        + '</tpl>'
        + '</ul>'
        + '</tpl>'
        + '</tpl>', {
        compiled: true
    });

    config = config || {};
    Ext.applyIf(config, {
        id: 'glossary-grid-terms',
        url: Glossary.config.connectorUrl,
        baseParams: {
            action: 'mgr/term/getlist'
        },
        autosave: true,
        save_action: 'mgr/term/updateFromGrid',
        fields: ['id', 'term', 'explanation'],
        autoHeight: true,
        paging: true,
        remoteSort: true,
        autoExpandColumn: 'explanation',
        columns: [{
            header: _('glossary.term'),
            dataIndex: 'term',
            sortable: true,
            width: 30,
            editor: {
                xtype: 'textfield'
            }
        }, {
            header: _('glossary.explanation'),
            dataIndex: 'explanation',
            sortable: true,
            width: 100,
            editor: {
                xtype: 'textfield'
            }
        }, {
            renderer: {
                fn: this.buttonColumnRenderer,
                scope: this
            },
            width: 20
        }],
        tbar: [{
            text: _('glossary.term_create'),
            cls: 'primary-button',
            handler: this.createTerm,
            scope: this
        }, '->', {
            xtype: 'textfield',
            id: 'glossary-filter-search',
            emptyText: _('search') + '…',
            submitValue: false,
            listeners: {
                change: {
                    fn: this.search,
                    scope: this
                },
                render: {
                    fn: function (cmp) {
                        new Ext.KeyMap(cmp.getEl(), {
                            key: Ext.EventObject.ENTER,
                            fn: function () {
                                this.fireEvent('change', this);
                                this.blur();
                                return true;
                            },
                            scope: cmp
                        });
                    },
                    scope: this
                }
            }
        }, {
            xtype: 'button',
            id: 'glossary-filter-clear',
            cls: 'x-form-filter-clear',
            text: _('filter_clear'),
            listeners: {
                click: {
                    fn: this.clearFilter,
                    scope: this
                }
            }
        }]
    });
    Glossary.grid.Terms.superclass.constructor.call(this, config)
};
Ext.extend(Glossary.grid.Terms, MODx.grid.Grid, {
    getMenu: function () {
        var m = [];
        m.push({
            text: _('glossary.term_update'),
            handler: this.updateTerm
        });
        m.push('-');
        m.push({
            text: _('glossary.term_remove'),
            handler: this.removeTerm
        });
        this.addContextMenuItem(m);
    },
    createTerm: function (btn, e) {
        this.createUpdateTerm(btn, e, false);
    },
    updateTerm: function (btn, e) {
        this.createUpdateTerm(btn, e, true);
    },
    createUpdateTerm: function (btn, e, isUpdate) {
        var r;
        if (isUpdate) {
            if (!this.menu.record || !this.menu.record.id) {
                return false;
            }
            r = this.menu.record;
        } else {
            r = {};
        }
        var createUpdateTerm = MODx.load({
            xtype: 'glossary-window-term-create-update',
            isUpdate: isUpdate,
            title: (isUpdate) ? _('glossary.term_update_long') : _('glossary.term_create_long'),
            record: r,
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    },
                    scope: this
                },
                afterRender: {
                    fn: function (c) {
                        this.initTinyMCE(c);
                    },
                    scope: createUpdateTerm
                }
            }
        });
        createUpdateTerm.fp.getForm().setValues(r);
        createUpdateTerm.show(e.target);
    },
    removeTerm: function () {
        MODx.msg.confirm({
            title: _('glossary.term_remove'),
            text: _('glossary.term_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/term/remove',
                id: this.menu.record.id
            },
            listeners: {
                success: {
                    fn: this.refresh,
                    scope: this
                }
            }
        });
    },
    search: function (tf) {
        var s = this.getStore();
        s.baseParams.query = tf.getValue();
        this.getBottomToolbar().changePage(1);
        this.refresh();
    },
    buttonColumnRenderer: function () {
        var values = {
            action_buttons: [
                {
                    className: 'update',
                    icon: 'pencil-square-o',
                    text: _('glossary.term_update')
                },
                {
                    className: 'remove',
                    icon: 'trash-o',
                    text: _('glossary.term_remove')
                }
            ]
        };
        return this.buttonColumnTpl.apply(values);
    },
    onClick: function (e) {
        var t = e.getTarget();
        var elm = t.className.split(' ')[0];
        if (elm === 'icon') {
            var act = t.className.split(' ')[1];
            var record = this.getSelectionModel().getSelected();
            this.menu.record = record.data;
            switch (act) {
                case 'remove':
                    this.removeTerm(record, e);
                    break;
                case 'update':
                    this.updateTerm(record, e);
                    break;
                default:
                    break;
            }
        }
    },
    clearFilter: function () {
        var s = this.getStore();
        s.baseParams.query = '';
        Ext.getCmp('glossary-filter-search').reset();
        this.getBottomToolbar().changePage(1);
        this.refresh();
    }
});
Ext.reg('glossary-grid-terms', Glossary.grid.Terms);

Glossary.window.CreateUpdateTerm = function (config) {
    config = config || {};
    this.ident = config.ident || 'cuterm' + Ext.id();
    Ext.applyIf(config, {
        url: Glossary.config.connectorUrl,
        action: (config.isUpdate) ? 'mgr/term/update' : 'mgr/term/create',
        autoHeight: true,
        width: 700,
        closeAction: 'close',
        fields: [{
            xtype: 'textfield',
            fieldLabel: _('glossary.term'),
            name: 'term',
            anchor: '100%'
        }, {
            xtype: 'textarea',
            id: this.ident + '-glossary-explanation',
            fieldLabel: _('glossary.explanation'),
            name: 'explanation',
            anchor: '100%'
        }, {
            xtype: 'textfield',
            name: 'id',
            hidden: true
        }]
    });
    Glossary.window.CreateUpdateTerm.superclass.constructor.call(this, config);
};
Ext.extend(Glossary.window.CreateUpdateTerm, MODx.Window, {
    initTinyMCE: function (c) {
        if (typeof MODx.loadRTE !== 'undefined' && Glossary.config.html) {
            MODx.loadRTE(c.ident + '-glossary-explanation');
        }
    }
});
Ext.reg('glossary-window-term-create-update', Glossary.window.CreateUpdateTerm);
