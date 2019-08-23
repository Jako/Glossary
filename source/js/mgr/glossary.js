var glossary = function (config) {
    config = config || {};
    Glossary.superclass.constructor.call(this, config);
};
Ext.extend(Glossary, Ext.Component, {
    page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}
});
Ext.reg('glossary', glossary);

Glossary = new glossary();
