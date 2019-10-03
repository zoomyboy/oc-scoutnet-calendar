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
    }

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

    Scoutnet.prototype.onSidebarItemClick = function(e) {
        var self = this,
            item = $(e.relatedTarget),
            form = this.$sidePanelForm,
            tabId = item.data('id'),
            parent = $(item).data('parent') !== undefined
                ? $(item).data('parent')
                : null;

        // Find if the tab is already opened
        if (this.masterTabsObj.goTo(tabId)) {
            self.$tree.treeView('markActive', tabId);
            return false
        }

        $.oc.stripeLoadIndicator.show()
        form.request('onEdit', {
            url: this.getEditUrl(tabId),
            data: { parent: parent }
        }).done(function(data) {
            self.$masterTabs.ocTab('addTab', data.env.title, data.content, tabId, data.env.icon)

            var tab = self.masterTabsObj.findByIdentifier(tabId);
            var tabPane = self.masterTabsObj.findPaneFromTab(tab);

            self.$tree.treeView('markActive', tabId);
            self.setPageTitle(data.env.title)

            $(tabPane).on('keyup change', '[data-source=title]', self.proxy(self.getCalendarTitle));

            $(tabPane).on('submit', 'form', self.proxy(self.onUpdateModel))
        }).always(function() {
            $.oc.stripeLoadIndicator.hide()
        })

        return false
    }

    Scoutnet.prototype.onUpdateModel = function(e, eventId) {
        e.preventDefault();
        console.log(this.activeTab());

        $(e.target).request('onSave', { url: this.getEditUrl(this.activeTab()) });
    }

    Scoutnet.prototype.onCreateModel = function(target) {
        var self = this,
            form = this.$sidePanelForm,
            model = $(target).data('model'),
            tabId = model+'-'+Math.floor(Math.random() * 10000),
            parent = $(target).data('parent') !== undefined
                ? $(target).data('parent')
                : null;

        $.oc.stripeLoadIndicator.show()
        form.request('onCreate', {
            url: this.getCreateUrl(model),
            data: { parent: parent }
        }).done(function(data) {
            self.$masterTabs.ocTab('addTab', data.env.title, data.content, tabId, data.env.icon)
            
            var tab = self.masterTabsObj.findByIdentifier(tabId);
            var tabPane = self.masterTabsObj.findPaneFromTab(tab);

            self.$tree.treeView('markActive', '');
            self.setPageTitle(data.env.title)

            $(tabPane).on('keyup change', '[data-source=title]', self.proxy(self.getCalendarTitle));

            $(tabPane).on('submit', 'form', self.proxy(self.onStoreModel))
        }).always(function(){
            $.oc.stripeLoadIndicator.hide()
        })
    };

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
    };


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

    Scoutnet.prototype.afterSave = function(form) {
        var tabPane = form.closest('.tab-pane');
        $(form).find('[data-control=delete-button]').removeClass('hidden');
        $(tabPane).off('submit', 'form');
        $(tabPane).on('submit', 'form', this.proxy(this.onUpdateObject));
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
        }

        this.$masterTabs.ocTab('updateIdentifier', tabPane, tabId.join('-'))

        this.updateObjectList(tabId[0], tabId[1])
    }

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
