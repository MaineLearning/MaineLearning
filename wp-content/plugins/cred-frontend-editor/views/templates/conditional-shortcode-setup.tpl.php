<?php if (!defined('ABSPATH'))  die('Security check'); ?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<?php
// include jquery from wp-admin an styles
wp_enqueue_script('jquery-');
wp_print_scripts('jquery');
wp_enqueue_style('wp-admin');
wp_enqueue_style('colors-fresh');
wp_print_styles('wp-admin');
wp_print_styles('colors-fresh');
wp_enqueue_style('cred_cred_style_gfields', CRED_ASSETS_URL.'/css/gfields.css');
wp_print_styles('cred_cred_style_gfields');
?>
<!-- templates -->
<script id='condtional-term-template' type='text/html-template'>
<tr class='expression-term-single row2'>
	<td class='cell2'>
		<select class='expression-term'>
		</select>
	</td>
	<td class='cell2'>
		<select class='expression-comparison-op'>
			<option value='eq'>=</value>
			<option value='ne'>&lt;&gt;</value>
			<option value='gt'>&gt;</value>
			<option value='lt'>&lt;</value>
			<option value='gte'>&gt;=</value>
			<option value='lte'>&lt;=</value>
		</select>
	</td>
	<td class='cell2'>
		<input type='text' size='7' class='expression-term-value' value='' />
		<span class='date-value'>
			<input type='text' size='7' class='expression-term-date-value' value='TODAY()' />
			<select class='expression-term-date-format'>
				<option value='d/m/Y'>d/m/Y</option>
				<option value='m/d/Y'>m/d/Y</option>
			</select>
		</span>
	</td>
	<td class='cell2'>
		<select class='expression-logical-op'>
			<option value='AND'>AND</value>
			<option value='OR'>OR</value>
		</select>
	</td>
	<td class='cell2'>
		<a class='remove-option' href='javascript:void(0);' title='<?php _e( 'Remove term', 'wp-cred' ); ?>'></a>
	</td>
</tr>
</script>
<!-- templates end -->

<!-- logic -->
<script type='text/javascript'>
/* <![CDATA[ */
(function($, cred_main){

    $.fn.slideFadeDown = function(speed, easing, callback) {
        easing = easing || 'linear';
        return this.each(function(){$(this).stop(true).animate({opacity: 'show', height: 'show'}, speed, easing, function() {
            if (jQuery.browser.msie) { this.style.removeAttribute('filter'); }
            if (jQuery.isFunction(callback)) { callback(); }
            });
        });
    };
    $.fn.slideFadeUp = function(speed, easing, callback) {
        easing = easing || 'linear';
        return this.each(function(){$(this).stop(true).animate({opacity: 'hide', height: 'hide'}, speed, easing, function() {
            if (jQuery.browser.msie) { this.style.removeAttribute('filter'); }
            if (jQuery.isFunction(callback)) { callback(); }
            });
        });
    };

    $(function(){

        var fields, content, match, tmpl, overlay
        postfields=[],
        genericfields=[],
        conditionalfields=[],
        pattern1=/\[cred\-field[^\[\]]*field=[\"\']([\d\w\-_]+)[\"\'][^\[\]]*\]/g,
        pattern2=/\[cred\-generic\-field[^\[\]]*field=[\"\']([\d\w\-_]+)[\"\'][^\[\]]*type=[\"\'](numeric|email|date|text|textfield|textarea|wysiwyg|url|phone|checkbox|checkboxes|radio|select)[\"\'][^\[\]]*\]/g,
        pattern3=/\[cred\-generic\-field[^\[\]]*type=[\"\'](numeric|email|date|text|textfield|textarea|wysiwyg|url|phone|checkbox|checkboxes|radio|select)[\"\'][^\[\]]*field=[\"\']([\d\w\-_]+)[\"\'][^\[\]]*\]/g
        ;

        fields=cred_main.getFieldData();
        content=cred_main.getContent();
        tmpl=$('#condtional-term-template').html();
        overlay=$('<div class="overlay-disabled">&nbsp;</div>');

        // parse content for allowed fields for conditionals
        // parse post fields and taxonomies
        while (match=pattern1.exec(content))
        {
            if (
                fields.post_fields[match[1]] &&
                fields.post_fields[match[1]]['type']!='skype' &&
                fields.post_fields[match[1]]['type']!='image' &&
                fields.post_fields[match[1]]['type']!='file' &&
                fields.post_fields[match[1]]['data']['repetitive']!='1'
                )
                type=fields.post_fields[match[1]]['type'];
            else if (
                fields.custom_fields[match[1]] &&
                fields.custom_fields[match[1]]['type']!='skype' &&
                fields.custom_fields[match[1]]['type']!='image' &&
                fields.custom_fields[match[1]]['type']!='file' &&
                fields.custom_fields[match[1]]['data']['repetitive']!='1'
                )
                type=fields.custom_fields[match[1]]['type'];
            else if (
                fields.parents[match[1]] &&
                fields.parents[match[1]]['type']!='skype' &&
                fields.parents[match[1]]['type']!='image' &&
                fields.parents[match[1]]['type']!='file' &&
                fields.parents[match[1]]['data']['repetitive']!='1'
                )
                type=fields.parents[match[1]]['type'];
            else if ( fields.taxonomies[match[1]] )
                type='taxonomy';
            else continue;
            postfields.push({field:match[1],type:type});
        }
        // parse generic fields variation 1
        while (match=pattern2.exec(content))
        {
            genericfields.push({field:match[1], type:match[2]});
        }
        // parse generic fields variation 2
        while (match=pattern3.exec(content))
        {
            genericfields.push({field:match[2],type:match[1]});
        }

        conditionalfields=conditionalfields.concat(postfields).concat(genericfields);
        postfields=null;
        genericfields=null;
        fields=null;

        var map={};
        for (var ii=0; ii<conditionalfields.length; ii++)
        {
            map[conditionalfields[ii].field]=conditionalfields[ii].type;
        }

        var useCustom=false;
        function refreshExpression()
        {
            if (useCustom) return;
            var expr='';
            var expr2;
            $('#terms tbody tr').each(function(){
                var term=$(this).find('.expression-term');
                var op=$(this).find('.expression-comparison-op');
                var val=$(this).find('.expression-term-value');
                var term1=term.val();
                var op1=op.val();
                var val1=val.val();
                var date,fornat;

                // these fields have multiple values and comparison is not possible
                if (
                    (map[term1]=='checkbox' ||
                    map[term1]=='checkboxes' ||
                    map[term1]=='select' ||
                    map[term1]=='taxonomy')
                )
                {
                    $('option',op).filter(function(){
                        var op1=$(this).attr('value');
                        if ((op1=='lt' || op1=='gt' || op1=='gte' || op1=='lte' ||
                                op1=='<' || op1=='>' || op1=='>=' || op1=='<='))
                        return true;
                        return false;
                    }).attr('disabled','disabled');
                    if ((op1=='lt' || op1=='gt' || op1=='gte' || op1=='lte' ||
                    op1=='<' || op1=='>' || op1=='>=' || op1=='<='))
                    {
                        op.val('eq');
                        op1='eq';
                    }
                }
                else
                {
                    $('option',op).removeAttr('disabled');
                }

                if (map[term1]=='date')
                {
                    $(this).find('.expression-term-value').hide();
                    $(this).find('.date-value').show();
                    date=$(this).find('.expression-term-date-value').val();
                    format=$(this).find('.expression-term-date-format').val()
                    if (date!='TODAY()')
                        val1="DATE('"+date+"','"+format+"')";
                    else
                        val1=date;
                }
                else
                {
                    $(this).find('.expression-term-value').show();
                    $(this).find('.date-value').hide();
                    val1="'"+val1+"'";
                }
                expr2='';
                expr2+='$('+term1+')';
                expr2+=' '+op1+' ';
                expr2+=' '+val1+' ';
                expr2='('+expr2+')';
                expr+=expr2;
                if (!$(this).is(':last-child'))
                    expr+=' '+$(this).find('.expression-logical-op').val()+' ';
            });
            $('#_conditional_expression').val(expr);
        }

        $('#terms').on('change', '.expression-term', refreshExpression);
        $('#terms').on('change', '.expression-comparison-op', refreshExpression);
        $('#terms').on('change', '.expression-term-value', refreshExpression);
        $('#terms').on('change', '.expression-term-date-value', refreshExpression);
        $('#terms').on('change', '.expression-term-date-format', refreshExpression);
        $('#terms').on('change', '.expression-logical-op', refreshExpression);

        $('#container').on('click','.remove-option',function(){
            var option=$(this).closest("tr");
            option.fadeOut('slow',function(){
                $(this).remove();
            });
            refreshExpression();
        });

        $('#container').on('click','.add-option',function(){
            var term=$(tmpl);
            var sel=term.find('.expression-term');
            for (var ii=0; ii<conditionalfields.length; ii++)
                sel.append('<option value="'+conditionalfields[ii].field+'">'+conditionalfields[ii].field+'</option>');
            $('#terms tbody').append(term);
            if (conditionalfields[0].type!='date')
                term.find('.date-value').hide();
            term.hide().fadeIn('slow');
        });

        $('#useCustomExpression').change(function(){
            if ($(this).is(':checked'))
            {
                refreshExpression();
                useCustom=true;
                overlay.appendTo($('#mygui')).fadeIn('fast');
                $('#_expression_container').slideFadeDown('fast');
            }
            else
            {
                useCustom=false;
                refreshExpression();
                overlay.fadeOut('fast',function(){
                    $(this).remove();
                });
                $('#_expression_container').slideFadeUp('fast');
            }
        });
        $('#useCustomExpression').trigger('change');

        // add first term
        $('#container .add-option').trigger('click');

        // cancel
        $('#container').on('click', '#cancel',function(event){
            event.preventDefault();
            window.parent.jQuery('#TB_closeWindowButton').trigger('click');
            return false;
        });

        // submit
        $('#container').on('click', '#submit',function(event){
            event.preventDefault();
            refreshExpression();
            var shortcode1="\n"+'[cred-show-group if="'+$('#_conditional_expression').val().replace(/\"/g,"'")+'"  mode="'+$('#_fx').val()+'"]'+"\n";
            var shortcode2="\n"+'[/cred-show-group]'+"\n";
            cred_main.wrapOrPaste(shortcode1, shortcode2);
            window.parent.jQuery('#TB_closeWindowButton').trigger('click');
            return false;
        });
    });
})(jQuery, window.parent.cred_cred);
/* ]]> */
</script>
</head>
<body id='cred_conditional_group' class="wp-core-ui">
<div class='cred-header'><?php _e('Conditional Group','wp-cred'); ?></div>
    <p class="cred-header-tip">
		<strong><?php _e("Tip:",'wp-cred'); ?></strong> <?php _e("Make a selection in the editor and the conditional group will wrap around it when inserted.",'wp-cred'); ?>
    </p>
    <!-- container -->
    <div id='container'>
    <form>

        <div>
            <?php _e('Show/Hide Effect:','wp-cred'); ?>
            <select id='_fx'>
            <option value='fade-slide'><?php _e('Fade-Slide','wp-cred'); ?></option>
            <option value='slide'><?php _e('Slide','wp-cred'); ?></option>
            <option value='fade'><?php _e('Fade','wp-cred'); ?></option>
            <option value='none'><?php _e('Use (user-defined) CSS','wp-cred'); ?></option>
            </select>
        </div>

        <div class='mysep'></div>

		<p class="custom-expression-container">
			<label class='cred-label'><input type='checkbox' class='cred-checkbox' id='useCustomExpression' value='1' />
        	    <span><?php _e('Use my Custom Expression','wp-cred'); ?></span>
        	</label>
        </p>

        <div id='_expression_container'>
            <strong><?php _e('Expression:','wp-cred'); ?></strong>
			<span><?php _e('Check Documentation for details and examples','wp-cred'); ?></span>
            <textarea id='_conditional_expression' style='position:relative;width:90%;overflow-y:auto;' rows='5'></textarea>
        </div>

		 <div class='mysep'></div>

		<div id='mygui'>
            <table id='terms'>
                <thead>
					<tr>
						<td><strong><?php _e( 'Field', 'wp-cred' ); ?></strong></td>
						<td><strong><?php _e( 'Operator', 'wp-cred' ); ?></strong></td>
						<td><strong><?php _e( 'Value', 'wp-cred' ); ?></strong></td>
						<td><strong><?php _e( 'Connect', 'wp-cred' ); ?></strong></td>
						<td></td>
					</tr>
                </thead>
                <tbody>
                </tbody>
            </table>
			<p class="add-option-wrapper">
				<a href='javascript:void(0);' class='add-option button' title='<?php _e( 'Add term', 'wp-cred' ); ?>'><?php _e( 'Add term', 'wp-cred' ); ?></a>
			</p>
        </div>

        <p class="cred-buttons-holder">
			<a href='javascript:void(0);' id='cancel' class='button' title='<?php _e('Cancel','wp-cred'); ?>'><?php _e('Cancel','wp-cred'); ?></a>
			<input id='submit' type='button' class='button button-primary' value='<?php _e('Insert','wp-cred'); ?>' />
        </p>
    </form>
    </div>
<a class='cred-help-link-white' style='position:absolute;top:10px;right:10px' href='<?php echo $help['conditionals']['link']; ?>' target='<?php echo $help_target; ?>' title="<?php echo $help['conditionals']['text']; ?>"><?php echo $help['conditionals']['text']; ?></a>
</body>
</html>