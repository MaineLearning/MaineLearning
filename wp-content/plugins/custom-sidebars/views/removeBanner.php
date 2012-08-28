<?php
include 'tabs.php';

if($this->getCode()): ?>

<h2><?php echo __('Your banner has been removed', 'custom-sidebars'); ?></h2>
<div><?php echo __('Thanks so much for your donation, that stupid banner won\'t disturb you any longer!', 'custom-sidebars'); ?></div>

<?php else: ?>
<h2><?php echo __('Ooops! The code seems to be wrong', 'custom-sidebars'); ?></h2>
<div><?php echo __('You must follow the link as provided in the plugin website to remove your banner.', 'custom-sidebars'); ?></div>
<div><?php echo __('If you did so and it did not work, try to <a href="http://marquex.es/contact" target="_blank">contact the author of the plugin</a>.', 'custom-sidebars'); ?></div>

<?php
endif;
include 'footer.php';