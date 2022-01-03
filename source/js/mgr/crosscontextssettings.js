var crosscontextssettings = function (config) {
    config = config || {};
    crosscontextssettings.superclass.constructor.call(this, config);
};
Ext.extend(crosscontextssettings, Ext.Component, {
    initComponent: function () {
        this.stores = {};
        this.ajax = new Ext.data.Connection({
            disableCaching: true,
        });
    }, page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, util: {}, form: {}
});
Ext.reg('crosscontextssettings', crosscontextssettings);

CrossContextsSettings = new crosscontextssettings();
