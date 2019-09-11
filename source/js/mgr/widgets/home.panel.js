Glossary.panel.Home = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        cls: 'container home-panel' + ((Glossary.config.debug) ? ' debug' : ''),
        defaults: {
            collapsible: false,
            autoHeight: true
        },
        items: [{
            html: '<h2>' + _('glossary.management') + '</h2>' + ((Glossary.config.debug) ? '<div class="ribbon top-right"><span>' + _('glossary.debug_mode') + '</span></div>' : ''),
            border: false,
            cls: 'modx-page-header'
        }, {
            defaults: {
                autoHeight: true
            },
            border: true,
            items: [{
                xtype: 'glossary-panel-overview'
            }]
        }, {
            cls: "treehillstudio_about",
            html: '<img width="133" height="40" src="' + Glossary.config.assetsUrl + 'img/mgr/treehill-studio-small.png"' + ' srcset="' + Glossary.config.assetsUrl + 'img/mgr/treehill-studio-small@2x.png 2x" alt="Treehill Studio">',
            listeners: {
                afterrender: function (component) {
                    component.getEl().select('img').on('click', function () {
                        var msg = '<span style="display: inline-block; text-align: center"><img src="' + Glossary.config.assetsUrl + 'img/mgr/treehill-studio.png" srcset="' + Glossary.config.assetsUrl + 'img/mgr/treehill-studio@2x.png 2x" alt="Treehill Studio"><br>' +
                            '© 2018-2019 by <a href="https://treehillstudio.com" target="_blank">treehillstudio.com</a></span>';
                        Ext.Msg.show({
                            title: _('glossary') + ' ' + Glossary.config.version,
                            msg: msg,
                            buttons: Ext.Msg.OK,
                            cls: 'treehillstudio_window',
                            width: 330
                        });
                    });
                }
            }
        }]
    });
    Glossary.panel.Home.superclass.constructor.call(this, config);
};
Ext.extend(Glossary.panel.Home, MODx.Panel);
Ext.reg('glossary-panel-home', Glossary.panel.Home);

Glossary.panel.HomeTab = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        id: 'glossary-panel-' + config.tabtype,
        title: _('glossary.' + config.tabtype),
        items: [{
            html: '<p>' + _('glossary.' + config.tabtype + '_desc') + '</p>',
            border: false,
            cls: 'panel-desc'
        }, {
            layout: 'form',
            cls: 'x-form-label-left main-wrapper',
            defaults: {
                autoHeight: true
            },
            border: true,
            items: [{
                id: 'glossary-panel-' + config.tabtype + '-grid',
                xtype: 'glossary-grid-' + config.tabtype,
                preventRender: true
            }]
        }]
    });
    Glossary.panel.HomeTab.superclass.constructor.call(this, config);
};
Ext.extend(Glossary.panel.HomeTab, MODx.Panel);
Ext.reg('glossary-panel-hometab', Glossary.panel.HomeTab);

Glossary.panel.Overview = function (config) {
    config = config || {};
    this.ident = 'glossary-panel-overview' + Ext.id();
    this.panelOverviewTabs = [{
        xtype: 'glossary-panel-hometab',
        tabtype: 'terms'
    }];
    if (Glossary.config.is_admin) {
        this.panelOverviewTabs.push({
            xtype: 'glossary-panel-settings',
            tabtype: 'settings'
        })
    }
    Ext.applyIf(config, {
        id: this.ident,
        items: [{
            xtype: 'modx-tabs',
            stateful: true,
            stateId: 'glossary-panel-overview',
            stateEvents: ['tabchange'],
            getState: function () {
                return {
                    activeTab: this.items.indexOf(this.getActiveTab())
                };
            },
            autoScroll: true,
            deferredRender: false,
            forceLayout: true,
            defaults: {
                layout: 'form',
                autoHeight: true,
                hideMode: 'offsets'
            },
            items: this.panelOverviewTabs,
            listeners: {
                tabchange: function (o, t) {
                    if (t.tabtype === 'settings') {
                        Ext.getCmp('glossary-grid-system-settings').getStore().reload();
                    } else if (t.xtype === 'glossary-panel-hometab') {
                        if (Ext.getCmp('glossary-panel-' + t.tabtype + '-grid')) {
                            Ext.getCmp('glossary-panel-' + t.tabtype + '-grid').getStore().reload();
                        }
                    }
                }
            }
        }]
    });
    Glossary.panel.Overview.superclass.constructor.call(this, config);
};
Ext.extend(Glossary.panel.Overview, MODx.Panel);
Ext.reg('glossary-panel-overview', Glossary.panel.Overview);
