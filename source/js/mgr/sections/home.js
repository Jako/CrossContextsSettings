CrossContextsSettings.page.Home = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        buttons: [{
            text: _('help_ex'),
            handler: MODx.loadHelpPane
        }],
        formpanel: 'crosscontextssettings-panel-home',
        components: [{
            xtype: 'crosscontextssettings-panel-home'
        }]
    });
    CrossContextsSettings.page.Home.superclass.constructor.call(this, config);
};
Ext.extend(CrossContextsSettings.page.Home, MODx.Component);
Ext.reg('crosscontextssettings-page-home', CrossContextsSettings.page.Home);
