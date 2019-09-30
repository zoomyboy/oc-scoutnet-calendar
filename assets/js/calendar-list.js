/*
 * Handles the Scoutnet main page.
 */
+function ($) { "use strict";
    var Base = $.oc.foundation.base,
        BaseProto = Base.prototype

    var Scoutnet = function () {
        Base.call(this)

        this.init()
    }

    Scoutnet.prototype = Object.create(BaseProto)
    Scoutnet.prototype.constructor = Scoutnet

    Scoutnet.prototype.init = function() {
        this.$masterTabs = $('#scoutnet-master-tabs')
        this.masterTabsObj = this.$masterTabs.data('oc.tab')
        this.$sidePanel = $('#scoutnet-side-panel')
        this.$calendarTree = $('[data-control=treeview]', this.$sidePanel)

        this.registerHandlers()
    }

    Scoutnet.prototype.registerHandlers = function() {
        $(document).on('open.oc.treeview', 'form.layout[data-content-id=calendar]', this.proxy(this.onSidebarItemClick))
        $(document).on('submenu.oc.treeview', 'form.layout[data-content-id=calendar]', this.proxy(this.onSidebarSubmenuItemClick))
        $(document).on('ajaxSuccess', '#scoutnet-master-tabs form', this.proxy(this.onAjaxSuccess))
        $(document).on('click', 'form.layout[data-content-id=calendar] button[data-control=delete-object]',
            this.proxy(this.onDeleteObject))
        $(document).on('click', 'form.layout[data-content-id=calendar] [data-add-calendar]',
            this.proxy(this.onCreateCalendar));
    }

    Scoutnet.prototype.onCreateCalendar = function(e, context, data) {
        var self = this,
            form = $(e.target).closest('form'),
            tabId = Math.floor(Math.random() * 10000);

        $.oc.stripeLoadIndicator.show()
        form.request('onCreate').done(function(data) {
            self.$masterTabs.ocTab('addTab', data.tabTitle, data.content, tabId, 'oc-icon-calendar new-template')
            
            var tab = self.masterTabsObj.findByIdentifier(tabId);
            var tabPane = self.masterTabsObj.findPaneFromTab(tab);

            self.$calendarTree.treeView('markActive', '');
            self.setPageTitle(data.tabTitle)

            $(tabPane).on('submit', '[data-calendar-form]', self.proxy(self.onStoreCalendar))
        }).always(function(){
            $.oc.stripeLoadIndicator.hide()
        })

        e.stopPropagation()

        return false
    };

    Scoutnet.prototype.onStoreCalendar = function(e) {
        e.preventDefault();
        var form = e.target;
        $(form).request('onStore');
    };

    Scoutnet.prototype.onDeleteObject = function(event, context, data) {
        var form = $('form.layout[data-content-id=calendar]');
        var self = this;

        form.request('onDelete', {
            url: form.data('delete-event-url'),
            complete: function(data) {
                self.updateObjectList()
            }
        });
    };

    Scoutnet.prototype.onAjaxSuccess = function(event, context, data) {
        var form = $(event.currentTarget),
            tabPane = form.closest('.tab-pane')

        var tabTitle = data.tabTitle ? data.tabTitle : null;

        if(tabTitle) {
            this.$masterTabs.ocTab('updateTitle', tabPane, tabTitle)
            this.setPageTitle(tabTitle)
        }

        var tabId = data.model ? data.model.id : tabPane.attr('id');
        this.$masterTabs.ocTab('updateIdentifier', tabPane, 'event-'+tabId)

        this.updateObjectList('event', tabId)
    }

    Scoutnet.prototype.onSidebarSubmenuItemClick = function(e) {
        if ($(e.clickEvent.target).data('control') == 'create-event')
            this.onCreateEvent(e.clickEvent)

        return false
    }

    Scoutnet.prototype.updateObjectList = function(modelType, modelId) {
        var form = $('form[data-content-id=calendar]', this.$sidePanel),
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
            button = $(e.target),
            form = button.closest('form'),
            calendar = button.data('parent') !== undefined
                ? button.data('parent').replace('calendar-', '')
                : null,
            tabId = Math.random()

        $.oc.stripeLoadIndicator.show()
        form.request('onCreate', {
            url: form.data('create-event-url'),
            data: {
               calendar: calendar
            }
        }).done(function(data) {
            var tab = self.$masterTabs.ocTab('addTab', data.tabTitle, data.content, tabId, form.data('type-icon') + ' new-template')
            tab = self.masterTabsObj.findByIdentifier(tabId);

            var tabPane = self.masterTabsObj.findPaneFromTab(tab);

            $(tabPane).on('submit', 'form.layout[data-event-form]', self.proxy(self.onStoreEvent))

            self.$calendarTree.treeView('markActive', '');

            self.setPageTitle(data.tabTitle)
        }).always(function(){
            $.oc.stripeLoadIndicator.hide()
        })

        e.stopPropagation()

        return false
    }

    Scoutnet.prototype.setPageTitle = function(title) {
        $.oc.layout.setPageTitle(title)
    }

    Scoutnet.prototype.onStoreEvent = function(e) {
        e.preventDefault();
        var form = e.target;
        $(form).request('onStore', {
            url: $(form).data('request-url')
        });
    }

    Scoutnet.prototype.onUpdateEvent = function(e, eventId) {
        e.preventDefault();
        var form = e.target;
        $(form).request('onUpdate', {
            url: $(form).data('request-url')
        });
    }

    Scoutnet.prototype.onSidebarItemClick = function(e) {
        var self = this,
            item = $(e.relatedTarget),
            form = item.closest('form'),
            tabId = item.data('id');

        // Find if the tab is already opened
        if (this.masterTabsObj.goTo(tabId)) {
            self.$calendarTree.treeView('markActive', tabId);
            return false
        }

        // Open a new tab
        $.oc.stripeLoadIndicator.show()
        var eventId = item.data('id').replace('event-', '');
        form.request('onEdit', {
            data: {
               event: eventId
            },
            url: form.data('edit-event-url'),
        }).done(function(data) {
            var tab = self.$masterTabs.ocTab('addTab', data.tabTitle, data.content, tabId, form.data('type-icon'))
            tab = self.masterTabsObj.findByIdentifier(tabId);
            var tabPane = self.masterTabsObj.findPaneFromTab(tab);
            $(tabPane).on('submit', 'form.layout[data-event-form]', self.proxy(self.onUpdateEvent, eventId))
            self.$calendarTree.treeView('markActive', tabId);
            self.setPageTitle(data.tabTitle)
        }).always(function() {
            $.oc.stripeLoadIndicator.hide()
        })

        return false
    }

    $(document).ready(function(){
        $.oc.Scoutnet = new Scoutnet()
    })

}(window.jQuery);
