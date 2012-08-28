<?php
$tabconfig = '';
$tabdefaults = '';
$tabedit = FALSE;

if(!empty($_GET['p'])){
	if($_GET['p']=='defaults')
		$tabdefaults = 'nav-tab-active';
	else if($_GET['p']=='edit')
		$tabedit = TRUE;
	else if($_GET['p']=='removebanner')
		$tabdonation = TRUE;
	else
		$tabconfig = 'nav-tab-active';	
		
}
else		
		$tabconfig = 'nav-tab-active';	
?>
<div id="icon-themes" class="icon32"><br /></div>
<h2 class="nav-tab-wrapper">
<a class="nav-tab <?php echo $tabconfig; ?>" href="themes.php?page=customsidebars"><?php _e('Custom Sidebars','custom-sidebars'); ?></a>
<a class="nav-tab <?php echo $tabdefaults; ?>" href="themes.php?page=customsidebars&p=defaults"><?php _e('Default Sidebars','custom-sidebars'); ?></a>
<?php do_action('cs_additionalTabs'); ?>
<?php if($tabedit): ?>
<a class="nav-tab nav-tab-active" href="#"><?php _e('Edit Sidebar','custom-sidebars'); ?></a>
<?php endif; ?>
<?php if($tabdonation): ?>
<a class="nav-tab nav-tab-active" href="#"><?php _e('Thanks for donate','custom-sidebars'); ?></a>
<?php endif; ?>
</h2>
<?php $this->message(); ?>	
