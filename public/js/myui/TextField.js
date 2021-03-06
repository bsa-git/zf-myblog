/**
 * MYUI, version 1.0
 *
 * Dual licensed under the MIT and GPL licenses.
 *
 * Copyright 2009 Pablo Aravena, all rights reserved.
 * http://pabloaravena.info
 *
 */
MY.TextField = Class.create({
    initialize: function (options) {
        options = options || {};
        this.baseInitialize(options);
        if (options.input) {
            this.render(options.input);
            this.decorate($(options.input));

        }
    },
    baseInitialize: function (options) {
        this.tableGrid = undefined;
        this.options = $H({}).merge(options || {}).toObject();
    },
    /**
     * Renders text field element
     * @param input input element
     */
    render: function (input) {
        this.input = $(input);
        this.id = this.input.id;
        this.name = this.options.name || this.input;
        this.align = this.options.align || 'left';
        this.tabIndex = this.options.tabIndex || null;
        this.initialText = this.options.initialText || null;
        this.required = this.options.required || false;
        this.customValidate = this.options.validate || null;
        this.tooltip = null;
        if (this.initialText)
            this.input.value = this.initialText;
        this.reset();
        if (this.options.type == 'number')
            this.align = 'right';
        this.input.setStyle({textAlign: this.align});
        this.input.observe('focus', function () {
            if (this.initialText != null && this.input.value == this.initialText.strip())
                this.input.value = '';
        }.bind(this));
        // registering validate handler
        this.onBlurHandler = this.validate.bindAsEventListener(this);
        this.input.observe('blur', this.onBlurHandler);
    },
    /**
     * Decorates text field
     * @param element input element
     */
    decorate: function (element) {
        if (this.tabIndex)
            element.setAttribute('tabIndex', this.tabIndex);
        if (this.initialText)
            element.value = this.initialText;
        var width = element.getDimensions().width;
        var height = element.getDimensions().height;
        Element.wrap(element, 'div');
        this.container = element.up();
        this.container.addClassName('my-textfield-container');
        this.container.id = this.id + '_container';
        this.container.setStyle({width: width + 'px', height: height + 'px'});
    },
    /**
     * Validates text field
     */
    validate: function () {
        var input = this.input;
        var result = true;
        if (this.required) {
            if (input.value.strip() == '') {
                input.addClassName('my-textfield-input-error');
                this.tooltip = new MY.ToolTip({
                    parent: input.up(),
                    message: i18n.getMessage('error.required.field', {field: input.name}),
                    type: 'error'
                });
                return false;
            } else {
                input.removeClassName('my-textfield-input-error');
                if (this.tooltip)
                    this.tooltip.remove();
            }
        }

        if (this.customValidate) {
            var errors = [];
            if (!this.customValidate(this.getValue(), errors)) {
                input.addClassName('my-textfield-input-error');
                if (errors.length > 0) {
                    this.tooltip = new MY.ToolTip({
                        parent: input.up(),
                        message: errors.pop(),
                        type: 'error'
                    });
                }
                return false;
            } else {
                input.removeClassName('my-textfield-input-error');
                if (this.tooltip)
                    this.tooltip.remove();
            }
        }
        return result;
    },
    /**
     * Reset text field status removing error
     * signal and tooltip
     */
    reset: function () {
        if (this.input)
            this.input.removeClassName('my-textfield-input-error');
        if (this.tooltip)
            this.tooltip.remove();
        if (this.onBlurHandler)
            Event.stopObserving(this.input, 'blur', this.onBlurHandler);
    },
    /**
     * Retrieves value
     */
    getValue: function () {
        return this.input.value;
    },
    /**
     * Set TableGrid object when text field is embedded inside one
     * @param tableGrid tableGrid object
     */
    setTableGrid: function (tableGrid) {
        this.tableGrid = tableGrid;
    }
});