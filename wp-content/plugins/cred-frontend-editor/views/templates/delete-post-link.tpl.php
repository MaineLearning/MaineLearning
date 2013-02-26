<?php if (!defined('ABSPATH'))  die('Security check'); ?>

<div style='display:none'>
<?php if ($include_js) : ?>
<script type='text/javascript'>
function _cred_cred_delete_post_handler(link, isFromLink)
{
    var ltext='';
    
    if (typeof isFromLink=='undefined')
        isFromLink=false;
        
    if (isFromLink) // callback from link click
    {
        var go=confirm("<?php _e('Are you sure you want to delete this post?','wp-cred'); ?>");
        if (!go) return false;
        
        if (link.text)
            ltext=link.text;
        else if (link.innerText)
            ltext=link.innerText;
            
        // static storage of refernce texts of related post delete links
        _cred_cred_delete_post_handler.refs=_cred_cred_delete_post_handler.refs || {};
        if (!_cred_cred_delete_post_handler.refs[link.id])
            _cred_cred_delete_post_handler.refs[link.id]=ltext;
        if (link.text)
            link.text='<?php _e('Deleting..','wp-cred'); ?>';
        else if (link.innerText)
            link.innerText='<?php _e('Deleting..','wp-cred'); ?>';
        
        if (-1==link.href.indexOf('cred_link_id='))
            link.href+=(link.href.indexOf('?')!=-1)?'&cred_link_id='+link.id:'?cred_link_id='+link.id;
        return true;
    }
    else // callback from iframe return function
    {
        var linkel=document.getElementById(link);
        if (linkel.text)
            linkel.text=_cred_cred_delete_post_handler.refs[link];
        else if (linkel.innerText)
            linkel.innerText=_cred_cred_delete_post_handler.refs[link];
            
        if (linkel.className.indexOf('cred-refresh-after-delete')>=0)
            // refresh current page
            location.reload();
    }
}
</script>
<?php endif; ?>
<?php $iframehandle=$link_id.'_iframe'; ?>
<iframe name='<?php echo $iframehandle; ?>' id='<?php echo $iframehandle; ?>' src=''>
</iframe>
</div>
<a href='<?php echo $link; ?>' <?php if ($link_atts!==false) echo $link_atts; ?> id='<?php echo $link_id; ?>' target='<?php echo $iframehandle; ?>' onclick='return _cred_cred_delete_post_handler(this, true);'><?php echo $text; ?></a>
