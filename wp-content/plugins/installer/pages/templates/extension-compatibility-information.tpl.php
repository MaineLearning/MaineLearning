<?php
if( !defined('ABSPATH') ) die('Security check');
$css = file_get_contents(WPRC_ASSETS_DIR.'/css/wprc.css');
$jquery = file_get_contents(WPRC_ASSETS_DIR.'/js/jquery.js');
echo '<style type="text/css">'.$css.'</style>';
echo '<script>'.$jquery.'</script>';

echo '<h3 class="compatibility-check-title">'.sprintf(__('Compatibility information of <span>%s</span>','installer'),$check_extension_name).'</h3>';

if($no_compatibility_information)
{
    echo __('<p>There is no compatibility information for specified extension</p>', 'installer');
}
elseif ( ! $no_compatibility_information && is_string($version_found) ) {
	printf( __('<p>There is no compatibility information for specified extension and version but we have found compatibility information for an older version (%s)</p>', 'installer'),
				$version_found 
			);
}
if ( ! $no_compatibility_information )
{


    if (isset($new_right_extensions) && is_array($new_right_extensions))
	{ 
		?>

			<table class="wp-list-table widefat fixed posts" cellspacing="0">
				<thead>
					<tr>
						<th scope="col" id="extension-name" class="manage-column column-title" style=""><?php _e('Extension Name', 'installer'); ?></th>
						<th scope="col" id="reports" class="manage-column column-author" style=""><?php _e('People´s reports', 'installer'); ?></th>
						<th scope="col" style="border-right:none;" id="score" class="manage-column column-score" style=""><?php _e('Compatibility Score', 'installer'); ?></th>
					</tr>
				</thead>
				<tbody id="the-list">

		<?php
		foreach($new_right_extensions AS $key => $extension)
		{ 
			$divisor = ( ( (int)$extension['works'] + (int)$extension['broken'] )  == 0 ) ? 1 : ( (int)$extension['works'] + (int)$extension['broken'] );
			$works_width = ( (int)$extension['works'] * 100) / $divisor;
			$broken_width = ( (int)$extension['broken'] * 100) / $divisor;

			if ( $extension['score'] < 20 )
				$class = 'red';
			elseif ( $extension['score'] < 75 )
				$class = 'yellow';				
			else
				$class='green';

			$row_class = 'row-extension-' . $extension['id'];
			if ( isset( $extension['total'] ) && $extension['total'] )
				$row_class .= ' hidden_row';
			if ( isset( $extension['link'] ) )
				$row_class .= ' link_row';
			?>
					
						<tr class="hentry alternate <?php echo $row_class; ?>"  valign="top">
							<td class="post-title page-title column-title"><?php echo $extension['name']; ?>
								<?php if ( isset( $extension['link'] ) ): ?>
									<p class="extension_version"><?php printf( __( 'Version %s. <a class="%s" href="">Previous</a>', 'installer' ), $extension['version'], 'extension-' . $extension['id'] ); ?></p>
								<?php endif; ?>
								<?php if ( isset( $extension['total'] ) && $extension['total'] ): ?>
									<p class="extension_version"><?php printf( __( 'All versions. <a class="%s" href="">Current</a>', 'installer' ), 'extension-' . $extension['id'] ); ?></p>
								<?php endif; ?>
							</td>			
							<td class="author column-reporter">
								<div class="ratings">
									<p class="rating-text"><?php printf( __( '%d people say it works', 'installer' ), $extension['works'] ); ?><p class="rating-bar-wrap"><span class="rating-bar" style="width:<?php echo $works_width; ?>%; background-color:#04c20d;"></span></p>
									<p class="rating-text"><?php printf( __( "%d people say it's broken", 'installer' ), $extension['broken'] ); ?><p class="rating-bar-wrap"><span class="rating-bar" style="width:<?php echo $broken_width; ?>%; background-color:#fa0404;"></span></p>
								</div>
							</td>
							<td style="border-right:none;" class="categories column-score">
								<div class="score">
									<div class="score-wrap <?php echo $class; ?>">
										<p><?php echo $extension['score']; ?>%</p>
									</div>
									<div class="score-text">
										<p><?php _e( 'Compatibility Score', 'installer' ); ?></p>
									</div>
									<div class="clear"></div>
								</div>
								<div class="clear"></div>
							</td>
						</tr>

		<?php } ?>
	

					</tbody>
				</table>

<script>
	jQuery(document).ready(function($) {
		$('.extension_version > a').click(function(e) {
			e.preventDefault();
			var extension_id_class = $(this).attr('class');
			$('.'+extension_id_class).parents('tr').toggle();
		});
	});
</script>
<style>
.extension_version{
	font-size: 11px;
	font-weight: normal;
}
.hidden_row {
	display:none;
}

.widefat .rating-text,
.widefat .rating-bar-wrap  {
	display:inline-block;
	margin:10px 0;
	font-family:Arial;
}
.widefat .rating-text {
	width:123px;
	margin-right: 15px;
	text-align:right;
	font-size:11px;
}
.widefat .rating-bar-wrap {
	width:100px;
}
.widefat .rating-bar {
	height:6px;
	display: inline-block;
}
.widefat td.column-score {
	background: #F4F4F4;
}
.widefat td.column-reporter {
	padding: 11px 8px 12px 8px;
}
table.fixed {
table-layout: fixed;
}
.compatibility-check-title {
            font-family:Arial,sans-serif;
            font-size:18px;
            margin-bottom:30px;
            text-align:center;
        }
.widefat {
	border-color: #DFDFDF;
background-color: #F9F9F9;
border-spacing: 0;
width: 618px;
clear: both;
margin: 0;
-webkit-border-radius: 3px;
border-radius: 3px;
border-width: 1px;
border-style: solid;
margin: 0 auto;
}
.widefat * {
word-wrap: break-word;
}
.widefat th.sortable, .widefat th.sorted {
padding: 0;
}
.widefat thead tr th, .widefat tfoot tr th, h3.dashboard-widget-title, h3.dashboard-widget-title span, h3.dashboard-widget-title small, .find-box-head {
color: #333;
}
.widget .widget-top, .postbox h3, .stuffbox h3, .widefat thead tr th, .widefat tfoot tr th, h3.dashboard-widget-title, h3.dashboard-widget-title span, h3.dashboard-widget-title small, .find-box-head, .sidebar-name, #nav-menu-header, #nav-menu-footer, .menu-item-handle {
background-color: #F1F1F1;
background-image: -ms-linear-gradient(top,#F9F9F9,#ECECEC);
background-image: -moz-linear-gradient(top,#F9F9F9,#ECECEC);
background-image: -o-linear-gradient(top,#F9F9F9,#ECECEC);
background-image: -webkit-gradient(linear,left top,left bottom,from(#F9F9F9),to(#ECECEC));
background-image: -webkit-linear-gradient(top,#F9F9F9,#ECECEC);
background-image: linear-gradient(top,#F9F9F9,#ECECEC);
}
.widefat th {
text-shadow: rgba(255, 255, 255, 0.8) 0 1px 0;
font-weight: bold !important;
font-size:15px;
}
.widefat td, .widefat th {
border-top-color: white;
border-bottom-color: #DFDFDF;
}
.widefat th {
font-weight: normal;
}
.widefat th, .widefat td {
overflow: hidden;
}
.widefat th {
padding: 7px 7px 8px;
text-align: center;
line-height: 1.3em;
font-size: 14px;
}
.widefat td, .widefat th {
border-width: 1px 0;
border-style: solid;
}
.widefat thead tr th, .widefat tfoot tr th, h3.dashboard-widget-title, h3.dashboard-widget-title span, h3.dashboard-widget-title small, .find-box-head {
color: #333;
}
.alternate, .alt {
background-color: #FCFCFC;
}
.widefat td {
font-size: 12px;
padding: 11px 12px 12px 12px;
vertical-align: middle;

}
.widefat td,
.widefat th {
	border-right: 1px solid #DFDFDF;
}
.widefat tr:last-child {
	border-right:none;
}
.widefat tr {
	background-color: whiteSmoke;
    background-image: -ms-linear-gradient(top,#F9F9F9,whiteSmoke);
    background-image: -moz-linear-gradient(top,#F9F9F9,whiteSmoke);
    background-image: -o-linear-gradient(top,#F9F9F9,whiteSmoke);
    background-image: -webkit-gradient(linear,left top,left bottom,from(#F9F9F9),to(whiteSmoke));
    background-image: -webkit-linear-gradient(top,#F9F9F9,whiteSmoke);
    background-image: linear-gradient(top,#F9F9F9,whiteSmoke);
}
#extension-name {
	width:25%;
}
#reports  {
	width:43%;
}
#score {
	width:32%;
}
.widefat .ratings-bars {
    width:184px;
}
.widefat .ratings-bars p {
    margin-top: 11px;
}
.ratings-text {
width: 118px;
margin-right: 10px;
}
.widefat .ratings-bars {
	width:92px
}
.widefat .ratings-text p {
	font-size: 11px;
font-family: Arial;
margin-top: 14px;
}
.widefat .ratings-text,
.widefat .ratings-bars {
    float:left;
}
.ratings-bars span {
    display:inline-block; 
    height:6px;
    margin: 7px 0;
}
.widefat .post-title {
	font-family:'"Times New Roman", Times, serif';
	font-size:14px;
	text-align:center;
	padding-top:14px;
	color:#28799e;
	font-weight:bold;
}
.widefat .score .score-wrap {
width: 71px;
height: 37px;
float: left;
text-align: center;
font-size: 22px;
}

.widefat . score {
	position: relative;
	top: -16px;
	font-family:Arial;
}
.widefat .score .green {
    background: #069d0d;
}
.widefat .score .yellow {
    background: #e7b00a;
}
.widefat .score .red {
    background: #e00e0e;
}
.widefat .score .score-wrap p {
	margin: 6px 0;
	color:white;
	text-shadow: 2px 2px 0 rgba(0, 0, 0, 0.1);
	font-family: Arial;
}
.widefat .score .score-text p {
	margin-left: 81px;
	font-family: Arial;
	font-size: 12px;
	color: #585454;
	position: relative;
	top: 3px;
	font-weight:bold;
}
.description {
	color:#A0A0A0;
	font-style: italic;
	text-align: center;
}
</style>
			<?php
			
	}
}



?>