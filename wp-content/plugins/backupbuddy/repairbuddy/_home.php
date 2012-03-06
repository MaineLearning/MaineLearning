<style> 
	.graybutton {
		background: url(repairbuddy/images/buttons/grays2.png) top repeat-x;
		/* min-width: 158px; */
		width: 220px;
		height: 138px;
		display: block;
		/* float: left; */
		-moz-border-radius: 6px;
		border-radius: 6px;
		border: 1px solid #c9c9c9;
	}
	.graybutton:hover {
		background: url(repairbuddy/images/buttons/grays2.png) bottom repeat-x;
		border: 1px solid #aaaaaa;
	}
	.graybutton:active {
		background: url(repairbuddy/images/buttons/grays2.png) bottom repeat-x;
		border: 1px solid transparent;
	}
	.leftround {
		-moz-border-radius: 4px 0 0 4px;
		border-radius: 4px 0 0 4px;
		border-right: 1px solid #c9c9c9;
	}
	.rightround {
		-moz-border-radius: 0 4px 4px 0;
		border-radius: 0 4px 4px 0;
	}
	.dbonlyicon {
		background: url(repairbuddy/images/buttons/dbonly-icon.png);
		width: 60px;
		height: 60px;
		margin: 15px auto 0 auto;
		display: block;
		float: center;
	}
	.allcontenticon {
		background: url(repairbuddy/images/buttons/allcontent-icon.png);
		width: 60px;
		height: 60px;
		margin: 15px auto 0 auto;
		display: block;
		float: center;
	}
	.restoremigrateicon {
		background: url(repairbuddy/images/buttons/restoremigrate-icon.png);
		width: 60px;
		height: 60px;
		margin: 15px auto 0 auto;
		display: block;
		float: center;
	}
	.bbbutton-text {
		font-family: Georgia, Times, serif;
		font-size: 18px;
		font-style: italic;
		min-width: 158px;
		text-align: center;
		
		/* line-height: 60px; */
		padding: 13px;
		padding-top:  20px;
		
		color: #666666;
		text-shadow: 1px 1px 1px #ffffff;
		clear: both;
	}
	.bbbutton-smalltext {
		font-family: "Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif;
		font-size: 9px;
		font-style: normal;
		text-shadow: 0;
		padding-top: 3px;
	}
	.graywrap {
		float: left;
		margin-left: 60px;
		margin-right: 20px;
		margin-bottom: 35px;
	}
</style>


<div style="text-align: center;">

<?php
ksort( $this->_modules );
foreach( $this->_modules as $priority => $modules ) {
	foreach ( $modules as $module ) {
		if ( $module['mini_mode'] != true ) {
			?><div class="graywrap"><a href="<?php echo $this->page_link( $module['slug'], $module['page'], $module['bootstrap_wordpress'] ); ?>" style="text-decoration: none;" title="<?php echo $module['description']; ?>">
				<div class="graybutton">
					<div class="allcontenticon"></div>
					<div class="bbbutton-text">
						<?php echo $module['title']; ?>
					</div>
				</div>
			</a></div><?php
		}
	}
}
?>
</div>
<br style="clear: both;">


<div style="text-align: center;">
	<h3>Additional Tools</h3><br>
	<?php
	$i = 0;
	foreach( $this->_modules as $priority => $modules ) {
		
		foreach ( $modules as $module ) {
			if ( $module['mini_mode'] == true ) {
				$i++;
				if ( $i > 3 ) {
					$i = 0;
					echo '<br><br><br>';
				}
				?><a href="<?php echo $this->page_link( $module['slug'], $module['page'], $module['bootstrap_wordpress'] ); ?>" class="button<?php if ( $module['subtle'] == true ) { echo '-secondary'; } ?>" title="<?php echo $module['description']; ?>"><?php echo $module['title']; ?></a>&nbsp;&nbsp;&nbsp;&nbsp;<?php
			}
		}
	}
	?>
</div><br><br>