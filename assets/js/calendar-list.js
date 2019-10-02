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
        this.$calendarTree = $('[data-control=treeview]', this.$sidePanel)

        this.registerHandlers()
    }

    Scoutnet.prototype.registerHandlers = function() {
        $(document).on('open.oc.treeview', '#sidebar-form', this.proxy(this.onSidebarItemClick))
        $(document).on('submenu.oc.treeview', '#sidebar-form', this.proxy(this.onSidebarSubmenuItemClick))
        $(document).on('ajaxSuccess', '#master-tabs form', this.proxy(this.onAjaxSuccess))
        $(document).on('click', '#sidebar-form button[data-control=delete-object]',
            this.proxy(this.onDeleteObject))
        $(document).on('click', '#sidebar-form [data-control=create-calendar]',
            this.proxy(this.onCreateCalendar));
    }

    Scoutnet.prototype.onCreateCalendar = function(e, context, data) {
        var self = this,
            form = this.$sidePanelForm,
            tabId = 'calendar-'+Math.floor(Math.random() * 10000);

        $.oc.stripeLoadIndicator.show()
        form.request('onCreate').done(function(data) {
            self.$masterTabs.ocTab('addTab', data.tabTitle, data.content, tabId, 'oc-icon-calendar new-template')
            
            var tab = self.masterTabsObj.findByIdentifier(tabId);
            var tabPane = self.masterTabsObj.findPaneFromTab(tab);

            self.$calendarTree.treeView('markActive', '');
            self.setPageTitle(data.tabTitle)

            $(tabPane).on('keyup change', '[data-source=title]', self.proxy(self.getCalendarTitle));

            $(tabPane).on('submit', 'form', self.proxy(self.onStoreObject))
        }).always(function(){
            $.oc.stripeLoadIndicator.hide()
        })

        e.stopPropagation()

        return false
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

    Scoutnet.prototype.onStoreObject = function(e) {
        e.preventDefault();
        var form = e.target;
        $(form).request('onSave', { url: form.getAttribute('action') });
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

    Scoutnet.prototype.onUpdateCalendar = function(e) {
        e.preventDefault();
        var form = e.target,
            tabPane = form.closest('.tab-pane'),
            tabId = this.masterTabsObj.findTabFromPane(tabPane).parent().data('tab-id');

        $(form).request('onSave', { url: this.getEditUrl(tabId) });
    };

    Scoutnet.prototype.onAjaxSuccess = function(event, context, data) {
        var form = $(event.currentTarget),
            tabPane = form.closest('.tab-pane')

        var tabId = this.masterTabsObj.findTabFromPane(tabPane).parent().data('tab-id').split('-');

        if (context.handler == 'onSave') {
            this.afterSave(form);
        }

        var tabTitle = data.tabTitle ? data.tabTitle : null;

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

    Scoutnet.prototype.onSidebarSubmenuItemClick = function(e) {
        if ($(e.clickEvent.target).data('control') == 'create-event')
            this.onCreateEvent(e.clickEvent)

        return false
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
                self.$calendarTree.treeView('markActive', modelType + '-' + modelId);
            }
        }).always(function(){
            $.oc.stripeLoadIndicator.hide()
        })
    }

    Scoutnet.prototype.onCreateEvent = function(e) {
        var self = this,
            tabId = 'event-'+Math.floor(Math.random() * 10000),
            form = this.$sidePanelForm,
            calendar = $(e.target).data('parent') !== undefined
                ? $(e.target).data('parent').replace('calendar-', '')
                : null;

        $.oc.stripeLoadIndicator.show()
        form.request('onCreate', {
            url: this.getCreateUrl('event'),
            data: {
               calendar: calendar
            }
        }).done(function(data) {
            self.$masterTabs.ocTab('addTab', data.tabTitle, data.content, tabId, 'oc-icon-calendar new-template')

            var tab = self.masterTabsObj.findByIdentifier(tabId);
            var tabPane = self.masterTabsObj.findPaneFromTab(tab);

            self.$calendarTree.treeView('markActive', '');
            self.setPageTitle(data.tabTitle)

            $(tabPane).on('submit', 'form', self.proxy(self.onStoreObject))
        }).always(function(){
            $.oc.stripeLoadIndicator.hide()
        })


        e.stopPropagation()

        return false
    }

    Scoutnet.prototype.setPageTitle = function(title) {
        $.oc.layout.setPageTitle(title)
    }

    Scoutnet.prototype.onUpdateObject = function(e, eventId) {
        e.preventDefault();

        var form = e.target,
            tabPane = form.closest('.tab-pane'),
            tabId = this.masterTabsObj.findTabFromPane(tabPane).parent().data('tab-id');

        $(form).request('onSave', { url: this.getEditUrl(tabId) });
    }

    Scoutnet.prototype.getEditUrl = function(tabId) {
        return this.$sidePanelForm.data('edit-url')
            .replace('{model}', tabId.split('-')[0])
            .replace('{id}', tabId.split('-')[1]);
    };  

    Scoutnet.prototype.getCreateUrl = function(model) {
        return this.$sidePanelForm.data('create-url')
            .replace('{model}', model);
    };  

    Scoutnet.prototype.onSidebarItemClick = function(e) {
        var self = this,
            item = $(e.relatedTarget),
            form = this.$sidePanelForm,
            tabId = item.data('id');

        // Find if the tab is already opened
        if (this.masterTabsObj.goTo(tabId)) {
            self.$calendarTree.treeView('markActive', tabId);
            return false
        }

        // Open a new tab
        $.oc.stripeLoadIndicator.show()
        
        form.request('onEdit', { url: this.getEditUrl(tabId) }).done(function(data) {
            self.$masterTabs.ocTab('addTab', data.tabTitle, data.content, tabId, 'oc-icon-calendar new-template')

            var tab = self.masterTabsObj.findByIdentifier(tabId);
            var tabPane = self.masterTabsObj.findPaneFromTab(tab);

            self.$calendarTree.treeView('markActive', tabId);
            self.setPageTitle(data.tabTitle)

            $(tabPane).on('keyup change', '[data-source=title]', self.proxy(self.getCalendarTitle));

            $(tabPane).on('submit', 'form', self.proxy(self.onUpdateObject))
        }).always(function() {
            $.oc.stripeLoadIndicator.hide()
        })

        return false
    }

    $(document).ready(function(){
        $.oc.Scoutnet = new Scoutnet()
    })

}(window.jQuery);
