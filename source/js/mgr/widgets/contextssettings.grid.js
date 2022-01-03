CrossContextsSettings.grid.ContextsSettings = function (config) {
    config = config || {};
    this.buttonColumnTpl = new Ext.XTemplate('<tpl for=".">'
        + '<tpl if="action_buttons !== null">'
        + '<ul class="action-buttons">'
        + '<tpl for="action_buttons">'
        + '<li><i class="icon {className} icon-{icon}" title="{text}"></i></li>'
        + '</tpl>'
        + '</ul>'
        + '</tpl>'
        + '</tpl>', {
        compiled: true
    });
    var _this = this;
    var columns = [];
    var fields = ['key', 'xtype', 'namespace', 'area'];
    if (config.contexts) {
        columns = [{
            header: _('key'),
            width: 150,
            sortable: true,
            dataIndex: 'key',
            locked: true,
            id: 'key'
        }, {
            header: _('namespace'),
            width: 100,
            sortable: true,
            dataIndex: 'namespace',
            locked: true,
            hidden: true,
            id: 'namespace'
        }, {
            header: _('area'),
            width: 100,
            sortable: true,
            dataIndex: 'area',
            locked: true,
            hidden: true,
            id: 'area',
        }, {
            renderer: _this.buttonColumnRenderer,
            scope: _this,
            menuDisabled: true,
            locked: true,
            width: 80
        }];
        Ext.each(config.contexts, function (item) {
            columns.push({
                header: item.key,
                width: 200,
                sortable: false,
                dataIndex: item.key,
                id: item.key,
                editable: true,
                renderer: _this.renderDynField.createDelegate(_this, [_this], true)
            });
            fields.push(item.key);
        });
    }
    var cm = new Ext.ux.grid.LockingColumnModel({
        columns: columns,
        getCellEditor: function (colIndex, rowIndex) {
            var rec = config.store.getAt(rowIndex);
            var xt = {xtype: 'textfield'};
            if (typeof (rec) === 'object') {
                xt.xtype = rec.get('xtype');
                if (xt.xtype === 'text-password') {
                    xt.xtype = 'textfield';
                    xt.inputType = 'password';
                }
            }
            var o = MODx.load(xt);
            return new Ext.grid.GridEditor(o);
        }
    });

    Ext.apply(config, {
        id: 'crosscontextssettings-grid-settings',
        url: CrossContextsSettings.config.connectorUrl,
        baseParams: {
            action: 'mgr/contextssettings/getlist',
            columns: fields.toString(),
            namespace: MODx.request.ns || 'core',
            area: MODx.request.area || ''
        },
        colModel: cm,
        fields: fields,
        paging: true,
        pageSize: 10,
        remoteSort: true,
        anchor: '100%',
        view: new Ext.ux.grid.LockingGridView(),
        height: 595,
        autoHeight: false,
        save_action: 'mgr/contextssettings/updatefromgrid',
        autosave: true,
        showActionsColumn: false,
        tbar: [{
            text: _('crosscontextssettings.setting_create'),
            cls: 'primary-button',
            handler: this.createSetting,
        }, {
            xtype: 'modx-combo-namespace',
            name: 'namespace',
            id: 'crosscontextssettings-filter-namespace',
            emptyText: _('namespace_filter'),
            allowBlank: true,
            width: 150,
            listeners: {
                select: {
                    fn: this.filterByNamespace,
                    scope: this
                }
            }
        }, {
            xtype: 'modx-combo-area',
            name: 'area',
            id: 'crosscontextssettings-filter-area',
            emptyText: _('area_filter'),
            baseParams: {
                action: 'system/settings/getAreas'
            },
            width: 250,
            allowBlank: true,
            listeners: {
                select: {
                    fn: this.filterByArea,
                    scope: this
                }
            }
        }, '->', {
            xtype: 'textfield',
            name: 'filter_key',
            id: 'crosscontextssettings-filter-key',
            cls: 'x-form-filter',
            emptyText: _('search_by_key'),
            listeners: {
                change: {
                    fn: this.filterByKey,
                    scope: this
                },
                render: {
                    fn: function (cmp) {
                        new Ext.KeyMap(cmp.getEl(), {
                            key: Ext.EventObject.ENTER,
                            fn: this.blur,
                            scope: cmp
                        });
                    },
                    scope: this
                }
            }
        }, {
            xtype: 'button',
            id: 'crosscontextssettings-filter-clear',
            cls: 'x-form-filter-clear',
            text: _('filter_clear'),
            listeners: {
                click: {
                    fn: this.clearFilter,
                    scope: this
                }
            }
        }]
    });
    CrossContextsSettings.grid.ContextsSettings.superclass.constructor.call(this, config);
};
Ext.extend(CrossContextsSettings.grid.ContextsSettings, MODx.grid.Grid, {
    getMenu: function () {
        var m = [];
        m.push({
            text: _('update'),
            handler: this.updateSetting
        });
        m.push('-');
        m.push({
            text: _('remove'),
            handler: this.removeSetting
        });
        this.addContextMenuItem(m);
    },
    createSetting: function (btn, e) {
        this.createUpdateSetting(btn, e, false);
    },
    updateSetting: function (btn, e) {
        this.createUpdateSetting(btn, e, true);
    },
    createUpdateSetting: function (btn, e, isUpdate) {
        var r = {};
        if (isUpdate) {
            if (!this.menu.record || !this.menu.record.key) {
                return false;
            }
            r = this.menu.record;
        }
        var createUpdateSetting = MODx.load({
            xtype: 'crosscontextssettings-window-contextssetting-create-update',
            isUpdate: isUpdate,
            title: (isUpdate) ? _('update') : _('create'),
            record: r,
            contexts: CrossContextsSettings.config.contexts,
            listeners: {
                success: {
                    fn: this.refresh,
                    scope: this
                }
            }
        });
        createUpdateSetting.fp.getForm().setValues(r);
        createUpdateSetting.show(e.target);
    },
    removeSetting: function () {
        MODx.msg.confirm({
            title: _('remove'),
            text: _('setting_remove_confirm'),
            url: CrossContextsSettings.config.connectorUrl,
            params: {
                action: 'mgr/contextssettings/remove',
                key: this.menu.record.key
            },
            listeners: {
                success: {
                    fn: this.refresh,
                    scope: this
                }
            }
        });
    },
    clearFilter: function () {
        var store = this.getStore();
        store.baseParams.key = '';
        store.baseParams.namespace = '';
        store.baseParams.area = '';
        Ext.getCmp('crosscontextssettings-filter-namespace').reset();
        Ext.getCmp('crosscontextssettings-filter-area').reset();
        Ext.getCmp('crosscontextssettings-filter-key').reset();
        this.getBottomToolbar().changePage(1);
        this.refresh();
    },
    filterByKey: function (tf, newValue) {
        this.getStore().baseParams.key = newValue;
        this.getStore().baseParams.namespace = '';
        this.getBottomToolbar().changePage(1);
        this.refresh();
        return true;
    },
    filterByNamespace: function (cb, rec) {
        this.getStore().baseParams.namespace = rec.data.name;
        this.getStore().baseParams.area = '';
        this.getBottomToolbar().changePage(1);
        this.refresh();

        var acb = Ext.getCmp('crosscontextssettings-filter-area');
        if (acb) {
            var s = acb.store;
            s.baseParams.namespace = rec.data.name;
            s.removeAll();
            s.load();
            acb.setValue('');
        }
    },
    filterByArea: function (cb, rec) {
        this.getStore().baseParams.area = rec.data.v;
        this.getBottomToolbar().changePage(1);
        this.refresh();
    },
    renderDynField: function (v, md, rec, ri, ci, s, g) {
        var r = s.getAt(ri).data;
        v = Ext.util.Format.htmlEncode(v);
        var f;
        if (r.xtype === 'combo-boolean' || r.xtype === 'modx-combo-boolean') {
            f = MODx.grid.Grid.prototype.rendYesNo;
            return f(v, md, rec, ri, ci, s, g);
        } else if (r.xtype === 'datefield') {
            f = Ext.util.Format.dateRenderer(MODx.config.manager_date_format);
            return f(v, md, rec, ri, ci, s, g);
        } else if (r.xtype === 'text-password' || r.xtype === 'modx-text-password') {
            f = MODx.grid.Grid.prototype.rendPassword;
            return f(v, md, rec, ri, ci, s, g);
        } else if (r.xtype.substr(0, 5) === 'combo' || r.xtype.substr(0, 10) === 'modx-combo') {
            var cm = g.getColumnModel();
            var ed = cm.getCellEditor(ci, ri);
            if (!ed) {
                var o = Ext.ComponentMgr.create({xtype: r.xtype || 'textfield'});
                ed = new Ext.grid.GridEditor(o);
                cm.setEditor(ci, ed);
            }
            if (ed.store && !ed.store.isLoaded && ed.config.mode !== 'local') {
                ed.store.load();
                ed.store.isLoaded = true;
            }
            f = Ext.util.Format.comboRenderer(ed.field, v);
            return f(v, md, rec, ri, ci, s, g);
        }
        return v;
    },
    buttonColumnRenderer: function () {
        var values = {
            action_buttons: [
                {
                    className: 'update',
                    icon: 'pencil-square-o',
                    text: _('crosscontextssettings.update')
                },
                {
                    className: 'remove',
                    icon: 'trash-o',
                    text: _('crosscontextssettings.remove')
                }
            ]
        };
        return this.scope.buttonColumnTpl.apply(values);
    },
    onClick: function (e) {
        var t = e.getTarget();
        var elm = t.className.split(' ')[0];
        if (elm === 'icon') {
            var act = t.className.split(' ')[1];
            var record = this.getSelectionModel().getSelected();
            this.menu.record = record.data;
            switch (act) {
                case 'remove':
                    this.removeSetting(record, e);
                    break;
                case 'update':
                    this.updateSetting(record, e);
                    break;
                default:
                    break;
            }
        }
    }
});
Ext.reg('crosscontextssettings-grid-contextssettings', CrossContextsSettings.grid.ContextsSettings);

CrossContextsSettings.window.CreateUpdateContextsSetting = function (config) {
    config = config || {};
    this.ident = 'crosscontextssettings-window-contextssettings-create-update-' + Ext.id();
    var contextsSettings = [];
    Ext.each(config.contexts, function (item) {
        contextsSettings.push({
            xtype: config.record.xtype || 'textarea',
            fieldLabel: _('value') + ' (' + item.key + ')',
            name: item.key,
            hiddenName: item.key,
            anchor: '100%'
        });
    });
    Ext.applyIf(config, {
        id: this.ident,
        url: CrossContextsSettings.config.connectorUrl,
        action: (config.isUpdate) ? 'mgr/contextssettings/update' : 'mgr/contextssettings/create',
        width: 600,
        autoHeight: true,
        closeAction: 'close',
        cls: 'modx-window crosscontextssettings-window modx' + CrossContextsSettings.config.modxversion,
        contexts: Ext.util.JSON.encode(config.contexts),
        fields: [{
            layout: 'column',
            items: [{
                columnWidth: .5,
                layout: 'form',
                items: [{
                    xtype: 'textfield',
                    fieldLabel: _('key'),
                    name: 'key',
                    id: this.ident + '-key',
                    maxLength: 100,
                    anchor: '100%'
                }, {
                    xtype: 'label',
                    forId: this.ident + '-key',
                    html: _('key_desc'),
                    cls: 'desc-under'
                }, {
                    xtype: 'modx-combo-namespace',
                    fieldLabel: _('namespace'),
                    name: 'namespace',
                    hiddenName: 'namespace',
                    id: this.ident + '-namespace',
                    anchor: '100%'
                }, {
                    xtype: 'label',
                    forId: this.ident + '-namespace',
                    html: _('namespace_desc'),
                    cls: 'desc-under'
                }]
            }, {
                columnWidth: .5,
                layout: 'form',
                items: [{
                    xtype: 'modx-combo-xtype-spec',
                    fieldLabel: _('xtype'),
                    name: 'xtype',
                    hiddenName: 'xtype',
                    description: MODx.expandHelp ? '' : _('xtype_desc'),
                    id: this.ident + '-xtype',
                    anchor: '100%'
                }, {
                    xtype: 'label',
                    forId: this.ident + '-xtype',
                    html: _('xtype_desc'),
                    cls: 'desc-under'
                }, {
                    xtype: 'textfield',
                    fieldLabel: _('area_lexicon_string'),
                    name: 'area',
                    id: this.ident + '-area',
                    anchor: '100%'
                }, {
                    xtype: 'label',
                    forId: this.ident + '-area',
                    html: _('area_lexicon_string_msg'),
                    cls: 'desc-under'
                },]
            }]
        }, {
            layout: 'form',
            labelAlign: 'top',
            anchor: '100%',
            border: false,
            style: 'padding-bottom: 15px',
            items: contextsSettings
        }],
        keys: []
    });
    CrossContextsSettings.window.CreateUpdateContextsSetting.superclass.constructor.call(this, config);
    if (!config.isUpdate) {
        this.on('show', function () {
            this.reset();
            this.setValues({
                namespace: Ext.getCmp('modx-filter-namespace').value,
                area: Ext.getCmp('modx-filter-area').value
            });
        }, this);
    }
};
Ext.extend(CrossContextsSettings.window.CreateUpdateContextsSetting, MODx.Window);
Ext.reg('crosscontextssettings-window-contextssetting-create-update', CrossContextsSettings.window.CreateUpdateContextsSetting);