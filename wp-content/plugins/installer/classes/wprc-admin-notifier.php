<?php
/**
 * Admin Notifier Class
 * 
 * Manages Admin Notices
 * 
 * 
 */
class WPRC_AdminNotifier
{

	public static function init()
	{
		if (is_admin())
		{
			add_action('admin_notices',array('WPRC_AdminNotifier','displayMessages'),5);
			add_action('admin_head',array('WPRC_AdminNotifier','addScript'));
			add_action('wp_ajax_wprc-hide-admin-message',array('WPRC_AdminNotifier','hideMessage'));
		}
	}
	
	public static function addScript()
	{
		?>
		<script type='text/javascript'>
			/* <![CDATA[*/
			jQuery(document).ready(function() {
				jQuery('a.wprc-admin-message-hide').live('click',function(event) {
					event.preventDefault();
					jQuery.post(
						ajaxurl,
						{
							action:'wprc-hide-admin-message',
							'wprc-admin-message-id':jQuery(this).parent().parent().attr('id')
						},
						function(response) {
						}
					);
					jQuery(this).parent().parent().fadeOut();
				});
				jQuery('a.wprc-admin-message-link').live('click',function(event) {
					event.preventDefault();
					jQuery.post(
						ajaxurl,
						{
							action:'wprc-hide-admin-message',
							'wprc-admin-message-id':jQuery(this).parent().parent().attr('id')
						},
						function(response) {
						}
					);
				});
			});
			/*]]>*/
		</script>
		<?php
	}
	
	public static function addInstantMessage($msg,$type='')
	{
		$msgs=self::getMessages();
		$msgs['instant_messages'][]=array(
			'text'=>$msg,
			'type'=>$type
		);
		self::saveMessages($msgs);
	}
	
	public static function addMessage($id,$msg,$type='',$hide=true)
	{
		if (!isset($id) || $id==null) return;
		$msgs=self::getMessages();
		$msgs['messages'][$id]=array(
			'text'=>$msg,
			'type'=>$type,
			'hide'=>$hide
		);
		self::saveMessages($msgs);
	}
	
	public static function removeMessage($id)
	{
		if ($id==null || !isset($id)) return;
		
		$msgs=self::getMessages();
		unset($msgs['messages'][$id]);
		self::saveMessages($msgs);
	}
	
	public static function hideMessage()
	{
		$idp=isset($_POST['wprc-admin-message-id'])?$_POST['wprc-admin-message-id']:'';
		$idp=preg_replace('/^wprc-id-/','',$idp);
		if (!isset($idp)) exit;
		
		$msgs=self::removeMessage($idp);
		exit;
	}
	
	private static function getMessages()
	{
		$msgs=get_transient('wprc_admin_messages');
		if (!(isset($msgs) && $msgs!=false))  return array('messages'=>array(),'instant_messages'=>array());
		if (!isset($msgs['messages']) || !isset($msgs['instant_messages']))
		{
			$msgs=array('messages'=>array(),'instant_messages'=>array());
		}

		return (array)$msgs;
	}
	
	private static function saveMessages($msgs)
	{
		if (isset($msgs))
			set_transient('wprc_admin_messages',(array)$msgs);
	}
	
	public static function displayMessages()
	{
		if ( current_user_can( 'manage_options' ) ) {
			$msgs=self::getMessages();
			
			foreach ($msgs['messages'] as $id=>$msg)
			{
				self::displayMessage($id,$msg['text'],$msg['type'],$msg['hide']);
			}
			foreach ($msgs['instant_messages'] as $msg)
			{
				self::displayInstantMessage($msg['text'],$msg['type']);
			}
			// delete instant messages
			$msgs['instant_messages']=array();
			self::saveMessages($msgs);
		}
	}
	
	private static function displayMessage($id,$msg,$type='',$hide=true)
	{
	    if( $type!='error') { ?>
	    	<div class="updated wprc-admin-message" id='<?php echo "wprc-id-".$id; ?>'>
	    <?php } else { ?>
	    	<div class="error wprc-admin-message" id='<?php echo "wprc-id-".$id; ?>'>
	    <?php }
	    echo '<p>' . stripslashes( $msg );
	    if( $hide ) {
	    	echo ' <a href="#" class="wprc-admin-message-hide">'.__( 'Dismiss', 'installer' ).'</a>';
	    } 
	    echo '</p>';
	    ?>
	    </div>
		<?php
	}
	
	private static function displayInstantMessage($msg,$type='')
	{
	    if( $type!='error') { ?>
	    	<div class="updated wprc-admin-instant-message">
	    <?php } else { ?>
	    	<div class="error wprc-admin-instant-message">
	    <?php }
			echo stripslashes( $msg );
		?>
	    </div>
		<?php
	}
}
?>