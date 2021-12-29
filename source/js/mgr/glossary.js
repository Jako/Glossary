var glossary = function (config) {
    config = config || {};
    glossary.superclass.constructor.call(this, config);
};
Ext.extend(glossary, Ext.Component, {
    initComponent: function () {
        this.stores = {};
        this.ajax = new Ext.data.Connection({
            disableCaching: true,
        });
    }, page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, util: {}, form: {}
});
Ext.reg('glossary', glossary);

Glossary = new glossary();
