Glossary.page.Home = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        buttons: [{
            text: _('help_ex'),
            handler: MODx.loadHelpPane
        }],
        formpanel: 'glossary-panel-home',
        components: [{
            xtype: 'glossary-panel-home'
        }]
    });
    Glossary.page.Home.superclass.constructor.call(this, config);
};
Ext.extend(Glossary.page.Home, MODx.Component);
Ext.reg('glossary-page-home', Glossary.page.Home);
