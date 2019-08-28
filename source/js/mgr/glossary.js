var glossary = function (config) {
    config = config || {};
    glossary.superclass.constructor.call(this, config);
};
Ext.extend(glossary, Ext.Component, {
    page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}
});
Ext.reg('glossary', glossary);

Glossary = new glossary();
