if ( typeof Object.create !== 'function' ) {
    Object.create = function( obj ) {
        function F() {};
        F.prototype = obj;
        return new F();
    };
}

(function($,window,document,undefined) {

    var I_Multiselect = {

        init: function( options, element ) {

            var self = this;

           $(element).hide();

            self.element = element;
            self.options = $.extend( {},$.fn.installer_multiselect.options, options );
            self.block_data = self.get_data();
            self.block_id = $(self.element).attr('id');

            self.render_button();
            self.set_button_caption();
            self.block = self.generate_block();
            self.set_block_position(self.block);
            self.attach_events();


        },

        render_button: function() {
            var self = this;
            
            self.container = $('<div class="installer-drop-container"></div>')
                .insertAfter($(self.element))
                .attr('id', 'block-' + self.block_id);

            self.button = $('<span></span>')
                .appendTo($(self.container))
                .addClass( 'installer-drop-button' )
                .button({
                    icons: {
                        secondary: self.options.icon
                    }
                });
        },

        set_button_caption: function() {
            var self = this;

            var counts = self.get_checked_options_number();

            self.container.find('span.ui-button-text').text( self.options.button_text.replace('#',counts));

        },

        get_checked_options_number: function() {
            var self = this;

            return $(self.element).find('option').filter(':selected').size();
        },

        get_data: function() {
            var self = this;

            var block_data = new Array();

            $(self.element).find('option').each(function(i) {
                var tmp_data = new Array();
                var id = $(this).val();
                tmp_data['value'] = id;
                tmp_data['selected'] = ( $(this).attr('selected') ) ? true : false;
                tmp_data['caption'] = $(this).text();
                block_data[id] = tmp_data;
            });

            return block_data;

        },

        generate_block: function() {
            var self = this;

            var button_width = $(self.button).outerWidth();

            var block = $('<div></div>')
                .appendTo($(self.container))
                .width(button_width)
                .css({
                    'margin-top': 2
                })
                .hide();

            var data_list = $('<ul></ul>')
                .addClass('ui-widget ui-widget-content ui-corner-all')
                .css({
                    'margin': '0px',
                    'padding': '10px'
                })
                .appendTo($(block));

            for ( var key in self.block_data ) {
                var list_item = $('<li></li>')
                    .appendTo(data_list)
                    .css({
                        'list-style': 'none'
                    });

                var label = $('<label></label>')
                    .attr({
                        for: 'ui-multiselect-' + self.options.checkboxes_name + '-option-' + key,

                    })
                    .appendTo(list_item)
                    .text(self.block_data[key]['caption']);

                var checkbox = $('<input></input>')
                    .attr({
                        type:'checkbox',
                        id: 'ui-multiselect-' + self.options.checkboxes_name + '-option-' + key,
                        name:'multiselect_' + self.options.checkboxes_name,
                        value: self.block_data[key]['value'],
                        checked: self.block_data[key]['selected'],
                    })
                    .prependTo(label);
            }

            return block;

            
        },

        set_block_position: function(block) {
            var self = this;
            self.block.css({
                'z-index': 100,
                'position': 'absolute',
            });

        },

        attach_events: function() {
            var self = this;

            $(self.button).on('click', function(e) {
                e.stopPropagation();
                $(self.block).toggle();
            });

            $('body').on('click', function() {
                $(self.block).hide();
            });

            $(self.block).find( 'input[type=checkbox]' ).on( 'click','',self, function(e) {
                var id = $(this).val();
                var selected = ( $(this).attr('checked') ) ? true : false;
                var option = $(self.element).find('option[value="' + id + '"]');
                if (selected)
                    option.attr('selected', 'selected');
                else
                    option.removeAttr('selected');
                if ( self.options.change_button_text )
                    self.set_button_caption();
            });

            $(self.block).on('click', function(e) {
                e.stopPropagation();
            });
        },
    }

    $.fn.installer_multiselect = function( options ) {
        return this.each(function() {
            var multiselect = Object.create(I_Multiselect);
            multiselect.init( options, this );
        });
    }

    $.fn.installer_multiselect.options = {
        icon: 'ui-icon-triangle-1-s',
        button_text: 'Repository has # type(s)',
        checkboxes_name: 'repository_types',
        change_button_text: true
    }



})(jQuery);