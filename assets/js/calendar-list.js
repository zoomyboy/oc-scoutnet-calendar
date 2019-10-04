/*
 * Handles the Scoutnet main page.
 */
+function ($) { "use strict";
    String.prototype.ucfirst = function() {
        var f = this.charAt(0)
        .toUpperCase()
        return f + this.substr(1)
    };

    var Base = $.oc.foundation.base,
        BaseProto = Base.prototype

    var Scoutnet = function () {
        Base.call(this)

        this.init()
    }

    Scoutnet.prototype = Object.create(BaseProto)
    Scoutnet.prototype.constructor = Scoutnet

    Scoutnet.prototype.init = function() {
        this.$masterTabs = $('#master-tabs')
        this.masterTabsObj = this.$masterTabs.data('oc.tab')
        this.$sidePanel = $('#side-panel');
        this.$sidePanelForm = $('#sidebar-form');
        this.$tree = $('[data-control=treeview]', this.$sidePanel)

        this.registerHandlers()
    }

    Scoutnet.prototype.registerHandlers = function() {
        $(document).on('open.oc.treeview', '#sidebar-form', this.proxy(this.onSidebarItemClick))
        $(document).on('submenu.oc.treeview', '#sidebar-form', this.proxy(this.onNewSubentry))
        $(document).on('ajaxSuccess', '#master-tabs form', this.proxy(this.onAjaxSuccess))
        $(document).on('click', '#sidebar-form button[data-control=delete-object]',
            this.proxy(this.onDeleteObject))
        $(document).on('click', '#sidebar-form .control-toolbar [data-control=create-model]',
            this.proxy(this.onNewEntry));
        this.$masterTabs.on('initTab.oc.tab', this.proxy(this.onInitTab))
    }

    Scoutnet.prototype.onInitTab = function(e, data) {
        if ($(e.target).attr('id') != 'master-tabs') return

        var $secondaryPanel = $('.control-tabs.secondary-tabs', data.pane);
        $secondaryPanel.addClass('secondary-content-tabs');
    };

    Scoutnet.prototype.onNewEntry = function(event) {
        console.log(event.target);
        this.onCreateModel(event.target);

        event.stopPropagation()
        return false
    };

    Scoutnet.prototype.onNewSubentry = function(event) {
        this.onCreateModel(event.relatedTarget);

        event.stopPropagation()
        return false
    };

    Scoutnet.prototype.renderForm = function(submitEvent, request, tabId, parent, urlResolver) {
        parent = typeof parent !== "undefined" ? parent : null;
        var form = this.$sidePanelForm,
            self = this;

        // Find if the tab is already opened
        if (this.masterTabsObj.goTo(tabId)) {
            self.$tree.treeView('markActive', tabId);
            return false
        }

        $.oc.stripeLoadIndicator.show()
        form.request(request, {
            url: urlResolver(tabId),
            data: { parent: parent }
        }).done(function(data) {
            self.$masterTabs.ocTab('addTab', data.env.title, data.content, tabId, data.env.icon)

            var tab = self.masterTabsObj.findByIdentifier(tabId);
            var tabPane = self.masterTabsObj.findPaneFromTab(tab);

            self.$tree.treeView('markActive', tabId);
            self.setPageTitle(data.env.title)

            $(tabPane).on('keyup change', '[data-source=title]', self.proxy(self.getCalendarTitle));

            $(tabPane).on('submit', 'form', self.proxy(submitEvent));

            $(tabPane).on('click', '[data-control=sync]', self.proxy(self.onSync));
        }).always(function() {
            $.oc.stripeLoadIndicator.hide()
        })
    };

    Scoutnet.prototype.onSync = function(e) {
        var self = this,
            form = this.$sidePanelForm,
            f = $(e.target).closest('form');


        new Promise(function(resolve, reject) {
            $('#sync-confirmation').html($('#sync-confirmation').html().replace(/{calendar}/g, f.find('#Form-field-Calendar-title').val()));

            $('#sync-confirmation').on('click', '[data-confirm]', function() { resolve(); });
            $('#sync-confirmation').on('click', '[data-abort]', function() { reject(); });

            $('#sync-confirmation').modal('show');
        }).then(function() {
            $('#sync-running').modal('show');

            form.request('onSync', {
                url: self.getEditUrl(self.activeTab())
            }).done(function(data) {
                self.updateObjectList();
                $('#sync-running').modal('hide');
            });

        }).catch(function() {});
    };

    Scoutnet.prototype.onSidebarItemClick = function(e) {
        var self = this,
            item = $(e.relatedTarget),
            tabId = item.data('id'),
            parent = $(item).data('parent');

        this.renderForm(self.onUpdateModel, 'onEdit', tabId, parent, function(tabId) {
            return self.getEditUrl(tabId);
        });
    }

    Scoutnet.prototype.onCreateModel = function(target) {
        var self = this,
            model = $(target).data('model'),
            tabId = model+'-'+Math.floor(Math.random() * 10000),
            parent = $(target).data('parent');

        this.renderForm(self.onStoreModel, 'onCreate', tabId, parent, function(tabId) {
            return self.getCreateUrl(model);
        });
    };

    Scoutnet.prototype.onUpdateModel = function(e, eventId) {
        e.preventDefault();

        $(e.target).request('onSave', { url: this.getEditUrl(this.activeTab()) });
    }

    Scoutnet.prototype.onStoreModel = function(e) {
        e.preventDefault();
        var form = e.target;
        $(form).request('onSave', { url: this.getCreateUrl(this.activeTab().split('-')[0]) });
    };

    Scoutnet.prototype.getCalendarTitle = function(e) {
        $.oc.stripeLoadIndicator.show();

        var $form = $(e.target).closest('form');
        var target = $form.find('[data-target=title]');

        $form.request('onGetTitle').done(function(data) {
            target.val(data);
        }).error(function() {})
        .always(function() {
            $.oc.stripeLoadIndicator.hide();
        });

        e.stopPropagation();
        return false;
    };

    Scoutnet.prototype.afterSave = function(form) {
        var tabPane = form.closest('.tab-pane');
        $(form).find('[data-control=delete-button]').removeClass('hidden');
        $(form).find('[data-control=sync]').removeClass('hidden');
        $(tabPane).off('submit', 'form');
        $(tabPane).on('submit', 'form', this.proxy(this.onUpdateModel));
    };

    Scoutnet.prototype.onAjaxSuccess = function(event, context, data) {
        var form = $(event.currentTarget),
            tabPane = form.closest('.tab-pane')

        var tabId = this.activeTab().split('-');

        if (context.handler == 'onSave') {
            this.afterSave(form);
        }

        var tabTitle = data.env && data.env.title ? data.env.title : null;

        if(tabTitle) {
            this.$masterTabs.ocTab('updateTitle', tabPane, tabTitle)
            this.setPageTitle(tabTitle)
        }

        if (data.model) {
            tabId[1] = data.model.id;
            this.$masterTabs.ocTab('updateIdentifier', tabPane, tabId.join('-'))
            this.updateObjectList(tabId[0], tabId[1])
        }
    }

    Scoutnet.prototype.onDeleteObject = function(event, context, data) {
        var form = this.$sidePanelForm;
        var self = this;

        form.request('onDelete', {
            url: form.data('delete-event-url'),
            complete: function(data) {
                self.updateObjectList()
            }
        });
    };

    Scoutnet.prototype.updateObjectList = function(modelType, modelId) {
        var form = this.$sidePanelForm,
            self = this

        var data = modelType ? {modelType: modelType, modelId: modelId} : {};

        $.oc.stripeLoadIndicator.show()
        form.request('calendarList::onUpdate', {
            data: data,
            complete: function(data) {
                $('button[data-control=delete-object]', form).trigger('oc.triggerOn.update')
                self.$tree.treeView('markActive', modelType + '-' + modelId);
            }
        }).always(function(){
            $.oc.stripeLoadIndicator.hide()
        })
    }

    Scoutnet.prototype.setPageTitle = function(title) {
        $.oc.layout.setPageTitle(title)
    }

    Scoutnet.prototype.activeTab = function() {
        return this.$masterTabs.find('ul.nav.nav-tabs > li.active').attr('data-tab-id');
    };

    Scoutnet.prototype.getEditUrl = function(tabId) {
        return this.$sidePanelForm.data('edit-url')
            .replace('{model}', tabId.split('-')[0])
            .replace('{id}', tabId.split('-')[1]);
    };  

    Scoutnet.prototype.getCreateUrl = function(model) {
        return this.$sidePanelForm.data('create-url')
            .replace('{model}', model);
    };  

    $(document).ready(function(){
        $.oc.Scoutnet = new Scoutnet()
    })

}(window.jQuery);
