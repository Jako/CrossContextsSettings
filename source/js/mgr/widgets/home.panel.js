CrossContextsSettings.panel.Home = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        cls: 'container home-panel' + ((CrossContextsSettings.config.debug) ? ' debug' : '') + ' modx' + CrossContextsSettings.config.modxversion,
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
            cls: 'crosscontextssettings-panel',
            items: [{
                xtype: 'crosscontextssettings-panel-overview'
            }]
        }, {
            cls: "treehillstudio_about",
            html: '<img width="146" height="40" src="' + CrossContextsSettings.config.assetsUrl + 'img/mgr/virtudraft-small.png"' + ' srcset="' + CrossContextsSettings.config.assetsUrl + 'img/mgr/virtudraft-small@2x.png 2x" alt="virtudraft">' +
                '<span style="line-height: 40px;height: 40px;display: inline-block;vertical-align: top;margin: 0 10px;">+</span>' +
                '<img width="146" height="40" src="' + CrossContextsSettings.config.assetsUrl + 'img/mgr/treehill-studio-small.png"' + ' srcset="' + CrossContextsSettings.config.assetsUrl + 'img/mgr/treehill-studio-small@2x.png 2x" alt="Treehill Studio">',
            listeners: {
                afterrender: function () {
                    this.getEl().select('img').on('click', function () {
                        var msg = '<span style="display: inline-block; text-align: center">' +
                            '<img src="' + CrossContextsSettings.config.assetsUrl + 'img/mgr/virtudraft.png" srcset="' + CrossContextsSettings.config.assetsUrl + 'img/mgr/virtudraft@2x.png 2x" alt="virtudraft" style="margin-top: 10px"><br>' +
                            '&copy; 2014-2020 by Virtudraft <a href="https://github.com/virtudraft" target="_blank">github.com/virtudraft</a><br>' +
                            '<img src="' + CrossContextsSettings.config.assetsUrl + 'img/mgr/treehill-studio.png" srcset="' + CrossContextsSettings.config.assetsUrl + 'img/mgr/treehill-studio@2x.png 2x" alt="Treehill Studio" style="margin-top: 10px"><br>' +
                            '&copy; 2021-2024 by <a href="https://treehillstudio.com" target="_blank">treehillstudio.com</a></span>';
                        Ext.Msg.show({
                            title: _('crosscontextssettings') + ' ' + CrossContextsSettings.config.version,
                            msg: msg,
                            buttons: Ext.Msg.OK,
                            cls: 'treehillstudio_window',
                            width: 358
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
            layout: 'form',
            cls: 'x-form-label-left main-wrapper',
            defaults: {
                autoHeight: true
            },
            border: true,
            items: [{
                id: 'crosscontextssettings-panel-' + config.tabtype + '-' + config.contenttype,
                xtype: 'crosscontextssettings-' + config.contenttype + '-' + config.tabtype,
                preventRender: true,
                contexts: CrossContextsSettings.config.contexts,
                listeners: {
                    afterrender: function () {
                        this.store.on('load', function () {
                            var tbarHeight = this.getTopToolbar().getHeight();
                            var lockedHdHeight = this.getView().lockedHd.getHeight();
                            var lockedBodyHeight = this.getView().lockedBody.getHeight();
                            var bbarHeight = this.getBottomToolbar().getHeight();
                            this.setHeight(tbarHeight + lockedHdHeight + lockedBodyHeight + bbarHeight + 14);
                        }, this);
                    }
                }
            }]
        }],
    });
    CrossContextsSettings.panel.HomeTab.superclass.constructor.call(this, config);
};
Ext.extend(CrossContextsSettings.panel.HomeTab, MODx.Panel);
Ext.reg('crosscontextssettings-panel-hometab', CrossContextsSettings.panel.HomeTab);

CrossContextsSettings.panel.Overview = function (config) {
    config = config || {};
    this.ident = 'crosscontextssettings-panel-overview-' + Ext.id();
    this.panelOverviewTabs = [{
        xtype: 'crosscontextssettings-panel-hometab',
        title: _('crosscontextssettings.contextssettings'),
        description: _('crosscontextssettings.contextssettings_desc'),
        tabtype: 'contextssettings',
        contenttype: 'grid'
    }, {
        xtype: 'crosscontextssettings-panel-hometab',
        title: _('crosscontextssettings.clearcache'),
        description: _('crosscontextssettings.clearcache_desc'),
        tabtype: 'clearcache',
        contenttype: 'panel'
    }];
    if (CrossContextsSettings.config.is_admin) {
        this.panelOverviewTabs.push({
            xtype: 'crosscontextssettings-panel-settings'
        });
    }
    Ext.applyIf(config, {
        id: this.ident,
        items: [{
            xtype: 'modx-tabs',
            border: true,
            stateful: true,
            stateId: 'babel-panel-overview',
            stateEvents: ['tabchange'],
            getState: function () {
                return {
                    activeTab: this.items.indexOf(this.getActiveTab())
                };
            },
            autoScroll: true,
            deferredRender: true,
            forceLayout: false,
            defaults: {
                layout: 'form',
                autoHeight: true,
                hideMode: 'offsets'
            },
            items: this.panelOverviewTabs,
            listeners: {
                tabchange: function (o, t) {
                    if (t.xtype === 'crosscontextssettings-panel-settings') {
                        if (Ext.getCmp('crosscontextssettings-grid-system-settings')) {
                            Ext.getCmp('crosscontextssettings-grid-system-settings').getStore().reload();
                        }
                    } else if (t.xtype === 'crosscontextssettings-panel-hometab') {
                        MODx.request.ns = '';
                        MODx.request.area = '';
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
