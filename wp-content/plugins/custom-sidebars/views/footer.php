<?php
global $workingcode;
?>
<div id="csfooter">
<div style="max-width: 400px;text-align:center;overflow:hidden; margin: 10px auto;padding:5px; background:#fff; border-radius: 3px; border:1px solid #DFDFDF" id="<?php echo $workingcode; ?>">
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
    <script type="text/javascript">
        pp = {
            wc: "#<?php echo $workingcode ?>",
            dc: "<?php echo $this->getCode() ?>"
        }
    </script>
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="3TUE42ZMC9JJJ">
<input type="image" src="https://www.paypalobjects.com/es_ES/ES/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal. La forma rápida y segura de pagar en Internet.">
<img alt="" border="0" src="https://www.paypalobjects.com/es_ES/i/scr/pixel.gif" width="1" height="1">
</form>
<p style="margin:0;"><?php _e('Do you like this free plugin? Support its development with a donation and <b>get rid of this banner</b> :)','custom-sidebars'); ?></p>
</div>
</div>