CrossContextsSettings.panel.ClearCache = function (config) {
    config = config || {};

    var checkboxes = [];
    Ext.each(config.contexts, function (ctx) {
        if (ctx.key !== '') {
            checkboxes.push({
                xtype: 'xcheckbox',
                id: 'crosscontextssettings-clearcache-' + ctx.key,
                boxLabel: ctx.name || ctx.key,
                name: 'ctxs[' + ctx.key + ']',
                inputValue: '1'
            });
        }
    });
    Ext.applyIf(config, {
        id: 'crosscontextssettings-clearcache-panel',
        bodyStyle: 'margin: 10px 10px 0',
        url: CrossContextsSettings.config.connectorUrl,
        baseParams: {
            action: 'mgr/contexts/clearcache'
        },
        buttonAlign: 'left',
        items: [{
            xtype: 'xcheckbox',
            boxLabel: 'All',
            listeners: {
                check: {
                    fn: function (el) {
                        Ext.each(config.contexts, function (ctx) {
                            var checkbox = Ext.getCmp('crosscontextssettings-clearcache-' + ctx.key);
                            if (checkbox) {
                                checkbox.setValue(el.getValue());
                            }
                        });
                    },
                    scope: this
                }
            }
        }, {
            xtype: 'panel',
            id: 'crosscontextssettings-clearcache-contexts',
            anchor: '100%',
            items: checkboxes
        }],
        buttons: [{
            text: _('clear_cache'),
            cls: 'primary-button',
            scope: this,
            handler: function () {
                this.submit();
            }
        }],
        listeners: {
            success: {
                fn: this.success,
                scope: this
            }
        }
    });

    CrossContextsSettings.panel.ClearCache.superclass.constructor.call(this, config);
};
Ext.extend(CrossContextsSettings.panel.ClearCache, MODx.FormPanel, {
    success: function (o) {
        this.getForm().reset();
        MODx.msg.status({
            title: _('success'),
            message: o.result.message || _('refresh_success'),
            dontHide: false
        });
    }
});
Ext.reg('crosscontextssettings-panel-clearcache', CrossContextsSettings.panel.ClearCache);
