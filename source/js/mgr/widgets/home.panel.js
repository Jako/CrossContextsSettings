CrossContextsSettings.panel.Home = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        cls: 'container home-panel' + ((CrossContextsSettings.config.debug) ? ' debug' : ''),
        defaults: {
            collapsible: false,
            autoHeight: true
        },
        items: [{
            html: '<h2>' + _('crosscontextssettings') + '</h2>' + ((CrossContextsSettings.config.debug) ? '<div class="ribbon top-right"><span>' + _('crosscontextssettings.debug_mode') + '</span></div>' : ''),
            border: false,
            cls: 'modx-page-header'
        }, {
            defaults: {
                autoHeight: true
            },
            border: true,
            items: [{
                xtype: 'crosscontextssettings-panel-overview'
            }],
            cls: 'crosscontextssettings-panel'
        }, {
            cls: "treehillstudio_about",
            html:
                '<img width="133" height="40" src="' + CrossContextsSettings.config.assetsUrl + 'img/mgr/virtudraft-small.png"' + ' srcset="' + CrossContextsSettings.config.assetsUrl + 'img/mgr/virtudraft-small@2x.png 2x" alt="virtudraft">' +
                '<span style="line-height: 40px;height: 40px;display: inline-block;vertical-align: top;margin: 0 10px;">+</span>' +
                '<img width="133" height="40" src="' + CrossContextsSettings.config.assetsUrl + 'img/mgr/treehill-studio-small.png"' + ' srcset="' + CrossContextsSettings.config.assetsUrl + 'img/mgr/treehill-studio-small@2x.png 2x" alt="Treehill Studio">',
            listeners: {
                afterrender: function (component) {
                    component.getEl().select('img').on('click', function () {
                        var msg = '<span style="display: inline-block; text-align: center;">' +
                            '<img src="' + CrossContextsSettings.config.assetsUrl + 'img/mgr/virtudraft.png" srcset="' + CrossContextsSettings.config.assetsUrl + 'img/mgr/virtudraft@2x.png 2x" alt="virtudraft" style="margin-top: 10px"><br>' +
                            '&copy; 2014-2020 by Virtudraft <a href="https://github.com/virtudraft" target="_blank">github.com/virtudraft</a><br>' +
                            '<img src="' + CrossContextsSettings.config.assetsUrl + 'img/mgr/treehill-studio.png" srcset="' + CrossContextsSettings.config.assetsUrl + 'img/mgr/treehill-studio@2x.png 2x" alt="Treehill Studio" style="margin-top: 10px"><br>' +
                            '&copy; 2021 by <a href="https://treehillstudio.com" target="_blank">treehillstudio.com</a></span>';
                        Ext.Msg.show({
                            title: _('crosscontextssettings') + ' ' + CrossContextsSettings.config.version,
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
    CrossContextsSettings.panel.Home.superclass.constructor.call(this, config);
};
Ext.extend(CrossContextsSettings.panel.Home, MODx.Panel);
Ext.reg('crosscontextssettings-panel-home', CrossContextsSettings.panel.Home);

CrossContextsSettings.panel.HomeTab = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        id: 'crosscontextssettings-panel-' + config.tabtype,
        title: config.title,
        items: [{
            html: '<p>' + config.description + '</p>',
            border: false,
            cls: 'panel-desc'
        }, {
            cls: 'main-wrapper',
            defaults: {
                autoHeight: true
            },
            border: true,
            items: [{
                id: 'crosscontextssettings-panel-' + config.tabtype + '-holder',
                preventRender: true,
                border: false,
                listeners: {
                    afterrender: {
                        fn: function () {
                            this.getContextList(config.callback);
                        },
                        scope: this
                    }
                }
            }]
        }]
    });
    CrossContextsSettings.panel.HomeTab.superclass.constructor.call(this, config);
};
Ext.extend(CrossContextsSettings.panel.HomeTab, MODx.Panel, {
    contexts: [],
    getContextList: function (callback) {
        if (this.contexts.length > 0) {
            if (typeof (this[callback]) === 'function') {
                return this[callback].call(this, this.config);
            }
            return this.contexts;
        }
        return MODx.Ajax.request({
            url: CrossContextsSettings.config.connectorUrl,
            params: {
                action: 'mgr/contexts/getlist'
            },
            listeners: {
                success: {
                    fn: function (r) {
                        if (r.success) {
                            this.contexts = r.results;
                            if (this.contexts.length > 0 && typeof (this[callback]) === 'function') {
                                return this[callback].call(this, this.config);
                            }
                        }
                    },
                    scope: this
                }
            }
        });
    },
    getContextSettingsGrid: function () {
        if (this.contexts.length < 1) {
            return;
        }
        MODx.load({
            xtype: 'crosscontextssettings-grid-contextsettings',
            record: this.contexts,
            preventRender: true,
            applyTo: 'crosscontextssettings-panel-contextsettings-holder'
        });
    },
    getClearCachePanel: function () {
        if (this.contexts.length < 1) {
            return;
        }
        var ccForm = new CrossContextsSettings.panel.ClearCache({
            record: this.contexts,
            preventRender: true
        });
        var holder = Ext.getCmp('crosscontextssettings-panel-clearcache-holder');
        holder.add(ccForm);
        holder.doLayout();
    }
});
Ext.reg('crosscontextssettings-panel-hometab', CrossContextsSettings.panel.HomeTab);

CrossContextsSettings.panel.Overview = function (config) {
    config = config || {};
    this.ident = 'crosscontextssettings-panel-overview' + Ext.id();
    this.panelOverviewTabs = [{
        xtype: 'crosscontextssettings-panel-hometab',
        title: _('crosscontextssettings.contextsettings'),
        description: _('crosscontextssettings.contextsettings_desc'),
        tabtype: 'contextsettings',
        callback: 'getContextSettingsGrid'
    }, {
        xtype: 'crosscontextssettings-panel-hometab',
        title: _('crosscontextssettings.clearcache'),
        description: _('crosscontextssettings.clearcache_desc'),
        tabtype: 'clearcache',
        callback: 'getClearCachePanel'
    }];
    if (CrossContextsSettings.config.is_admin) {
        this.panelOverviewTabs.push({
            xtype: 'crosscontextssettings-panel-settings',
            title: _('crosscontextssettings.settings'),
            description: _('crosscontextssettings.settings_desc'),
            tabtype: 'settings'
        })
    }
    Ext.applyIf(config, {
        id: this.ident,
        items: [{
            xtype: 'modx-tabs',
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
                    if (t.tabtype === 'systemsettings') {
                        Ext.getCmp('crosscontextssettings-grid-systemsettings').getStore().reload();
                    } else if (t.xtype === 'crosscontextssettings-panel-hometab') {
                        if (Ext.getCmp('crosscontextssettings-panel-' + t.tabtype + '-grid')) {
                            Ext.getCmp('crosscontextssettings-panel-' + t.tabtype + '-grid').getStore().reload();
                        }
                    }
                }
            }
        }]
    });
    CrossContextsSettings.panel.Overview.superclass.constructor.call(this, config);
};
Ext.extend(CrossContextsSettings.panel.Overview, MODx.Panel);
Ext.reg('crosscontextssettings-panel-overview', CrossContextsSettings.panel.Overview);
