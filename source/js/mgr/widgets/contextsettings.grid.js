CrossContextsSettings.grid.ContextSettings = function (config) {
    config = config || {};

    var columns = [];
    var fields = [];
    var contexts = [];
    var _this = this;

    if (config.record) {
        fields.push('key');
        fields.push('xtype');
        fields.push('namespace');
        fields.push('area');
        columns.push({
            header: _('key'),
            width: 150,
            sortable: true,
            dataIndex: 'key',
            locked: true,
            id: 'key'
        });
        columns.push({
            header: _('namespace'),
            width: 100,
            sortable: true,
            dataIndex: 'namespace',
            locked: true,
            id: 'namespace'
        });
        columns.push({
            header: _('area'),
            width: 100,
            sortable: true,
            dataIndex: 'area',
            locked: true,
            id: 'area',
            hidden: true
        });
        Ext.each(config.record, function (item) {
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
            contexts.push(item.key);
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

    Ext.applyIf(config, {
        id: 'crosscontextssettings-grid-settings',
        url: CrossContextsSettings.config.connectorUrl,
        baseParams: {
            action: 'mgr/contextsettings/getlist',
            columns: fields.toString(),
            namespace: MODx.request['ns'] ? MODx.request['ns'] : 'core',
            area: MODx.request['area']
        },
        colModel: cm,
        fields: fields,
        paging: true,
        pageSize: 10,
        remoteSort: true,
        anchor: '100%',
        view: new Ext.ux.grid.LockingGridView({
            syncHeights: true
        }),
        height: 595,
        autoHeight: false,
        save_action: 'mgr/contextsettings/updatefromgrid',
        autosave: true,
        tbar: [
            {
                text: _('setting_create'),
                scope: this,
                cls: 'primary-button',
                handler: {
                    xtype: 'crosscontextssettings-window-contextsetting-create',
                    blankValues: true,
                    contexts: contexts
                }
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
                emptyText: _('search_by_key') + '...',
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
            }
        ]
    });
    CrossContextsSettings.grid.ContextSettings.superclass.constructor.call(this, config);
};
Ext.extend(CrossContextsSettings.grid.ContextSettings, MODx.grid.Grid, {
    getMenu: function () {
        return [{
            text: _('remove'),
            handler: this.removeSetting
        }];
    },
    removeSetting: function () {
        MODx.msg.confirm({
            title: _('remove'),
            text: _('setting_remove_confirm'),
            url: CrossContextsSettings.config.connectorUrl,
            params: {
                action: 'mgr/contextsettings/remove',
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
        store.baseParams.namespace = '';
        store.baseParams.area = '';
        Ext.getCmp('crosscontextssettings-filter-namespace').reset();
        var acb = Ext.getCmp('crosscontextssettings-filter-area');
        if (acb) {
            acb.store.baseParams.namespace = '';
            acb.store.load();
            acb.reset();
        }
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
        this.getStore().baseParams.namespace = rec.data['name'];
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
        this.getStore().baseParams.area = rec.data['v'];
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
    }
});
Ext.reg('crosscontextssettings-grid-contextsettings', CrossContextsSettings.grid.ContextSettings);

CrossContextsSettings.window.CreateContextSetting = function (config) {
    config = config || {};
    var valueTextareas = [];
    Ext.each(config.contexts, function (item) {
        valueTextareas.push({
            xtype: 'textarea',
            fieldLabel: _('value') + ' (' + item + ')',
            name: 'value[' + item + ']',
            anchor: '100%'
        });
    });
    Ext.applyIf(config, {
        title: _('setting_create'),
        width: 600,
        url: CrossContextsSettings.config.connectorUrl,
        baseParams: {
            action: 'mgr/contextsettings/create',
            contexts: Ext.util.JSON.encode(config.contexts)
        },
        fields: [{
            layout: 'column',
            border: false,
            defaults: {
                layout: 'form',
                labelAlign: 'top',
                anchor: '100%',
                border: false
            },
            items: [{
                columnWidth: .5,
                items: [{
                    xtype: 'textfield',
                    fieldLabel: _('key'),
                    name: 'key',
                    id: 'crosscontextssettings-key',
                    maxLength: 100,
                    anchor: '100%'
                }, {
                    xtype: 'label',
                    forId: 'crosscontextssettings-key',
                    html: _('key_desc'),
                    cls: 'desc-under'
                }, {
                    xtype: 'textfield',
                    fieldLabel: _('name'),
                    name: 'name',
                    id: 'crosscontextssettings-name',
                    anchor: '100%'
                }, {
                    xtype: 'label',
                    forId: 'crosscontextssettings-name',
                    html: _('name_desc'),
                    cls: 'desc-under'
                }, {
                    xtype: 'textarea',
                    fieldLabel: _('description'),
                    name: 'description',
                    id: 'crosscontextssettings-description',
                    allowBlank: true,
                    anchor: '100%'
                }, {
                    xtype: 'label',
                    forId: 'crosscontextssettings-description',
                    html: _('description_desc'),
                    cls: 'desc-under'
                }]
            }, {
                columnWidth: .5,
                items: [{
                    xtype: 'modx-combo-xtype-spec',
                    fieldLabel: _('xtype'),
                    description: MODx.expandHelp ? '' : _('xtype_desc'),
                    id: 'crosscontextssettings-xtype',
                    anchor: '100%'
                }, {
                    xtype: 'label',
                    forId: 'crosscontextssettings-xtype',
                    html: _('xtype_desc'),
                    cls: 'desc-under'
                }, {
                    xtype: 'modx-combo-namespace',
                    fieldLabel: _('namespace'),
                    name: 'namespace',
                    id: 'crosscontextssettings-namespace',
                    value: 'core',
                    anchor: '100%'
                }, {
                    xtype: 'label',
                    forId: 'crosscontextssettings-namespace',
                    html: _('namespace_desc'),
                    cls: 'desc-under'
                }, {
                    xtype: 'textfield',
                    fieldLabel: _('area_lexicon_string'),
                    description: _('area_lexicon_string_msg'),
                    name: 'area',
                    id: 'crosscontextssettings-area',
                    anchor: '100%'
                }, {
                    xtype: 'label',
                    forId: 'crosscontextssettings-area',
                    html: _('area_lexicon_string_msg'),
                    cls: 'desc-under'
                }]
            }]
        }, {
            layout: 'form',
            labelAlign: 'top',
            anchor: '100%',
            border: false,
            style: 'padding-bottom: 15px',
            items: valueTextareas
        }],
        keys: []
    });
    CrossContextsSettings.window.CreateContextSetting.superclass.constructor.call(this, config);
    this.on('show', function () {
        this.reset();
        this.setValues({
            namespace: Ext.getCmp('modx-filter-namespace').value,
            area: Ext.getCmp('modx-filter-area').value
        });
    }, this);
};
Ext.extend(CrossContextsSettings.window.CreateContextSetting, MODx.Window);
Ext.reg('crosscontextssettings-window-contextsetting-create', CrossContextsSettings.window.CreateContextSetting);