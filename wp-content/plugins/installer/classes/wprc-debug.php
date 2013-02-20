<?php
class WPRC_Debug
{
	public static function print_r($array, $file, $line)
	{
		echo '<div style="margin-top:20px;padding:10px;background-color:#EEE;">
                <strong>WPRC_Debug <br>File: '.$file.'; Line: '.$line.'</strong>
              </div>
              <div style="padding:10px;background-color:#F9F9F9;">
                <pre>'; print_r($array); echo '</pre>
              </div>';
	}
}
?>