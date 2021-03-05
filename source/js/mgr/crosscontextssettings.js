var crosscontextssettings = function (config) {
    config = config || {};
    Ext.applyIf(config, {});
    crosscontextssettings.superclass.constructor.call(this, config);
    return this;
};
Ext.extend(crosscontextssettings, Ext.Component, {
    page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}
});
Ext.reg('crosscontextssettings', crosscontextssettings);

CrossContextsSettings = new crosscontextssettings();
