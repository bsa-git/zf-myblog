/* 
 * MyTableGrid - Class
 * 
 * Dual licensed under the MIT and GPL licenses.
 * 
 * Copyright 2009 Pablo Aravena, all rights reserved.
 * http://pabloaravena.info
 * 
 */

MY.ComboBox = Class.create(MY.Autocompleter, {
    initialize: function (options) {
        this.baseInitialize(options);
        this.options.minChars = this.options.minChars || 0;
        this.options.all = function (instance) {
            var currentValue = instance.element.value.strip();
            var result = [];
            var text = '';
            var value = '';
            var items = [];
            if (instance.options.items) {
                items = instance.options.items;
            } else if (instance.options.url) {
                var parameters = instance.options.parameters;
                if (instance.options.getParameters) {
                    var moreParams = instance.options.getParameters();
                    for (var p in moreParams)
                        parameters[p] = moreParams[p];
                }
                new Ajax.Request(instance.options.url, {
                    onSuccess: function (transport) {
                        items = instance.options.items = transport.responseText.evalJSON();
                        //============= MyChange-begin ============//
                        if(items.unexpected_message){// Server error
                            var msg = items.unexpected_message.messages.join('\n').stripScripts().stripTags();
                            alert(msg);
                        }
                        //============= MyChange-end ============//
                    },
                    asynchronous: false,
                    parameters: parameters
                });
            }
            var listTextPropertyName = instance.options.listTextPropertyName;
            var listValuePropertyName = instance.options.listValuePropertyName;
            for (var i = 0; i < items.length; i++) {
                if (typeof (items[i]) == 'object') {
                    text = items[i][listTextPropertyName];
                    value = items[i][listValuePropertyName];
                } else {
                    text = items[i];
                    value = items[i];
                }
                if (currentValue == text)
                    instance.index = i;
                result.push('<li id="' + value + '">' + text + '</li>');
            }
            return '<ul>' + result.join('') + '</ul>';
        };
    },
    _keyPress: function (event) {
        if (event.keyCode == Event.KEY_DOWN && !this.active) {
            event.stop();
            this.changed = false;
            this.showAll();
        }
    },
    render: function ($super, input) {
        $super(input);
        this.element.observe('keydown', this._keyPress.bindAsEventListener(this));
    },
    /**
     * Show all elements
     */
    showAll: function () {
        if (!this.active) {
            if (!this.update) {
                //============= MyChange-begin ============//
                // не работает в IE 6,7
                // было - document.body.insert('<div id="'+this.id+'_update" class="my-autocompleter-list shadow"></div>');
                $(document.body).insert('<div id="' + this.id + '_update" class="my-autocompleter-list shadow"></div>');
                //============= MyChange-end ============
                this.update = $(this.id + '_update');
            }
            this.element.focus();
            this.element.select();
            this.hasFocus = true;
            this.active = true;
            this.getAllChoices();
            if (this.index >= 0)
                this._syncScroll(this._getEntry(this.index), true);
        } else {
            this.options.onHide(this.element, this.update);
        }
    },
    /**
     * Retrieves all choices
     */
    getAllChoices: function () {
        this.updateChoices(this.options.all(this));
    },
    decorate: function (element) {
        var self = this;
        var width = element.getDimensions().width;
        var height = element.getDimensions().height;
        Element.wrap(element, 'div'); // auto complete container
        element.setStyle({
            width: (width - 25) + 'px'
        });
        var container = element.up();
        container.addClassName('my-autocompleter');
        container.id = this.id + '_container';
        container.setStyle({
            width: width + 'px',
            height: height + 'px'
        });
        var comboBoxBtn = new Element('div');
        comboBoxBtn.addClassName('my-combobox-button gradient');
        container.insert(comboBoxBtn);
        comboBoxBtn.observe('click', function (event) {
            self.showAll();
            event.stop();
        });
    }
});

