Glossary.page.Home = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        formpanel: 'glossary-panel-home',
        components: [{
            xtype: 'glossary-panel-home',
        }]
    });
    Glossary.page.Home.superclass.constructor.call(this, config);
};
Ext.extend(Glossary.page.Home, MODx.Component);
Ext.reg('glossary-page-home', Glossary.page.Home);
