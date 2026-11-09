Glossary.util.richtextKey = function (keyCode, event) {
    var elem = event.getTarget();
    // Textareas and rich text or code editors handle the Enter key themselves
    if (elem.tagName === 'TEXTAREA' || elem.isContentEditable) {
        return;
    }
    this.submit();
};

// MODX sets Ext.KeyMap.prototype.stopEvent to true, so the key map of a window
// prevents the default Enter action in textareas and rich text or code editors
Glossary.util.initRichtextKey = function (win) {
    win.on('afterrender', function () {
        this.getKeyMap().stopEvent = false;
    }, win);
};
