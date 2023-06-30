Glossary.grid.Terms = function (config) {
    config = config || {};
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
    this.ident = 'glossary-terms-' + Ext.id();
    Ext.applyIf(config, {
        id: this.ident + '-glossary-grid-terms',
        url: Glossary.config.connectorUrl,
        baseParams: {
            action: 'mgr/term/getlist'
        },
        fields: ['id', 'term', 'explanation'],
        autoHeight: true,
        paging: true,
        remoteSort: true,
        autoExpandColumn: 'explanation',
        autosave: true,
        save_action: 'mgr/term/updateFromGrid',
        showActionsColumn: false,
        columns: [{
            header: _('glossary.term_term'),
            dataIndex: 'term',
            sortable: true,
            width: 30,
            editor: {
                xtype: 'textfield'
            }
        }, {
            header: _('glossary.term_explanation'),
            dataIndex: 'explanation',
            sortable: true,
            width: 100,
            editor: (typeof MODx.loadRTE === 'undefined' || !Glossary.config.html) ? {xtype: 'textfield'} : false,
            renderer: function (val, meta, record) {
                return (typeof MODx.loadRTE === 'undefined' || !Glossary.config.html) ? Ext.util.Format.htmlEncode(record.data.explanation) : MODx.util.safeHtml(record.data.explanation, '<a><br><i><em><b><strong><p>');
            }
        }, {
            renderer: {
                fn: this.buttonColumnRenderer,
                scope: this
            },
            menuDisabled: true,
            width: 30
        }],
        tbar: [{
            text: _('glossary.term_create'),
            cls: 'primary-button',
            handler: this.createTerm
        }, '->', {
            xtype: 'textfield',
            id: this.ident + '-glossary-filter-search',
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
            id: this.ident + '-glossary-filter-clear',
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
    Glossary.grid.Terms.superclass.constructor.call(this, config);
};
Ext.extend(Glossary.grid.Terms, MODx.grid.Grid, {
    windows: {},
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
                    fn: this.refresh,
                    scope: this
                },
                afterRender: {
                    fn: function (cmp) {
                        cmp.initRTE(cmp.ident + '-glossary-explanation');
                    },
                    scope: this
                }
            }
        });
        createUpdateTerm.fp.getForm().setValues(r);
        createUpdateTerm.show(e.target);
    },
    removeTerm: function () {
        if (!this.menu.record) {
            return false;
        }
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
    clearFilter: function () {
        var s = this.getStore();
        s.baseParams.query = '';
        Ext.getCmp(this.ident + '-glossary-filter-search').reset();
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
    }
});
Ext.reg('glossary-grid-terms', Glossary.grid.Terms);

Glossary.window.CreateUpdateTerm = function (config) {
    config = config || {};
    this.ident = 'glossary-term-create-update-' + Ext.id();
    this.loadedRTEs = [];
    Ext.applyIf(config, {
        id: this.ident,
        url: Glossary.config.connectorUrl,
        action: (config.isUpdate) ? 'mgr/term/update' : 'mgr/term/create',
        width: 700,
        autoHeight: true,
        closeAction: 'close',
        cls: 'modx-window glossary-window',
        keys: [{
            key: Ext.EventObject.ENTER,
            fn: function (keyCode, event) {
                var elem = event.getTarget();
                var component = Ext.getCmp(elem.id);
                if (component instanceof Ext.form.TextArea) {
                    component.append('\n');
                } else if (elem.classList.contains('redactor-in')) {
                    var source = elem.parentElement.querySelector('.redactor-source');
                    $R('#' + source.id, 'insertion.insertText', '\n');
                } else {
                    this.submit();
                }
            },
            scope: this
        }],
        fields: [{
            xtype: 'textfield',
            fieldLabel: _('glossary.term_term'),
            name: 'term',
            anchor: '100%'
        }, {
            xtype: 'textarea',
            id: this.ident + '-glossary-explanation',
            fieldLabel: _('glossary.term_explanation'),
            name: 'explanation',
            height: 300,
            anchor: '100%'
        }, {
            xtype: 'hidden',
            name: 'id'
        }]
    });
    Glossary.window.CreateUpdateTerm.superclass.constructor.call(this, config);
    this.on('deactivate', function () {
        if (Glossary.config.html && MODx.config.use_editor && MODx.loadRTE && this.loadedRTEs) {
            if (typeof tinyMCE !== 'undefined') {
                for (var i = 0; i < this.loadedRTEs.length; ++i) {
                    tinyMCE.execCommand('mceRemoveControl', false, this.loadedRTEs[i]);
                }
            }
        }
    }, this);
};
Ext.extend(Glossary.window.CreateUpdateTerm, MODx.Window, {
    initRTE: function (id) {
        var loadedRTEs = this.loadedRTEs;
        if (loadedRTEs.indexOf(id) === -1) {
            // Avoid "Cannot read property 'getRng' of undefined" in TinyMCE
            setTimeout(function () {
                if (typeof MODx.loadRTE !== 'undefined') {
                    MODx.loadRTE(id);
                    loadedRTEs.push(id);
                }
            }, 200);
        }
    }
});
Ext.reg('glossary-window-term-create-update', Glossary.window.CreateUpdateTerm);
