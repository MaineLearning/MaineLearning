<fb:fbml>
  <style type="text/css">
  .box_head{
    padding: 5px 0 0 0;
  }
  .wpbook_box_header{
    padding: 1px 6px 0px 8px; 
    border-top: solid 1px #3B5998; 
    background: #d8dfea;
  }
  .wpbook_external_post {
    margin-top: 5px;
    margin-bottom: 10px;
    padding-left: 5px;
    height: 25px;
  }
  .wpbook_external_post a { 
    background-color: white;
    background-repeat: repeat-y;
    background-attachment: scroll;
    background-position: right center;
    border: 1px solid #7F93BC;
    line-height: 11px;
    padding-top: 0pt;
    padding-bottom: 1px;
    padding-left: 5px;
    padding-right: 5px;
  } 

  .wpbook_external_post a:hover {
    color: #ffffff;
    border: 1px solid #3B5998;
    text-decoration: none;
    background-color: #3b5998;
    background-attachment: scroll;
    background-position: right center;
  }  

  .wpbook_header{
    margin-bottom: 5px;
  }
  </style>
  <div id="content">
  <?php 	
  have_posts();
  if (have_posts()) : 
    while (have_posts()) : 
      the_post(); 
      echo '<div class="box_head clearfix" id="post-'.  get_the_ID() .'">';
      echo  '<h3 class="wpbook_box_header">';
      if($show_date_title == "true"){
        the_time($timestamp_date_format); 
        echo(" - ");
      }
      echo '<a href="'. get_permalink() .'" target="_top">'. get_the_title() .'</a></h3>';
      if(($show_custom_header_footer == "header") || ($show_custom_header_footer == "both")){
        echo( '<div id="custom_header">'.custom_header_footer($custom_header,$timestamp_date_format,$timestamp_time_format) .'</div>');
      } // end if for showing customer header
  
    if(($enable_share == "true" || $enable_external_link == "true") && ($links_position == "top")) { 
      echo '<p>';
      if($enable_share == "true"){
        echo '<fb:share-button class="url" href="'. get_permalink() .'" />';
      } // end enable_share = true 
      if($enable_external_link == "true"){
        ?><span class="uiButton uiButtonMedium"><a class="uiButtonText" href="<?php echo get_external_post_url(get_permalink()); ?>" title="View this post outside Facebook at <?php bloginfo('name'); ?>">View post on <?php bloginfo('name'); ?></a></span><?php
      }
        echo '</p>';
    } // end if for enable share, external, top
      
      $wpbook_allowed = array('address' => array(),
                              'a' => array('class' => array (),'href' => array (),'id' => array (),'title' => array (),'rel' => array (),'rev' => array (),'name' => array (),'target' => array()),
                              'abbr' => array('class' => array (),'title' => array ()),
                              'acronym' => array('title' => array ()),
                              'article' => array('align' => array (),'class' => array (),'dir' => array (),'lang' => array(),'style' => array (),'xml:lang' => array(),),
                              'b' => array(),
                              'blockquote' => array('id' => array (),'cite' => array (),'class' => array(),'lang' => array(),'xml:lang' => array()),
                              'br' => array ('class' => array ()),
                              'caption' => array('align' => array (),'class' => array ()),
                              'code' => array ('style' => array()),
                              'del' => array('datetime' => array ()),
                              'dd' => array(),
                              'div' => array('align' => array (),'class' => array (),'dir' => array (),'lang' => array(),'style' => array (),'xml:lang' => array()),
                              'dl' => array(),
                              'dt' => array(),
                              'em' => array(),
                              'h1' => array('align' => array (),'class' => array (),'id'    => array (),'style' => array ()),
                              'h2' => array('align' => array (),'class' => array (),'id'    => array (),'style' => array ()),
                              'h3' => array('align' => array (),'class' => array (),'id'    => array (),'style' => array ()),
                              'h4' => array('align' => array (),'class' => array (),'id'    => array (),'style' => array ()),
                              'h5' => array('align' => array (),'class' => array (),'id'    => array (),'style' => array ()),
                              'h6' => array('align' => array (),'class' => array (),'id'    => array (),'style' => array ()),
                              'i' => array(),
                              'img' => array('alt' => array (),'align' => array (),'border' => array (),'class' => array (),'height' => array (),'hspace' => array (),'longdesc' => array (),'vspace' => array (),'src' => array (),'style' => array (),'width' => array ()),
                              'li' => array ('align' => array (),'class' => array ()),
                              'p' => array('class' => array (),'align' => array (),'dir' => array(),'lang' => array(),'style' => array (),'xml:lang' => array()),
                              'pre' => array('style' => array(),'width' => array ()),
                              'script' => array('type' => array()),
                              'span' => array ('class' => array (),'dir' => array (),'align' => array (),'lang' => array (),'style' => array (),'title' => array (),'xml:lang' => array()),
                              'strike' => array(),'strong' => array(),'sub' => array(),
                              'table' => array('align' => array (),'bgcolor' => array (),'border' => array (),'cellpadding' => array (),'cellspacing' => array (),'class' => array (),'dir' => array(),'id' => array(),'rules' => array (),'style' => array (),'summary' => array (),'width' => array ()),
                              'tbody' => array('align' => array (),'char' => array (),'charoff' => array (),'valign' => array ()),
                              'thead' => array('align' => array (),'char' => array (),'charoff' => array (),'valign' => array (),'class' => array(), 'style' => array()),
                              'td' => array('abbr' => array (),'align' => array (),'axis' => array (),'bgcolor' => array (),
                                            'char' => array (),'charoff' => array (),'class' => array (),'colspan' => array (),'dir' => array(),'headers' => array (),'height' => array (),'nowrap' => array (),'rowspan' => array (),'scope' => array (),'style' => array (),'valign' => array (),'width' => array ()),
                              'th' => array('id' => array(), 'class' => array(), 'style' => array(), 'title' => array(), 'align' => array(), 
                                            'valign' => array(), 'char' => array(), 'charoff' => array(), 'rowspan' => array(), 'colspan' =>array(),
                                            'height' => array(), 'width' => array()),
                              'title' => array(),
                              'tr' => array('align' => array (),'bgcolor' => array (),'char' => array (),'charoff' => array (),'class' => array (),
                                            'style' => array (),'valign' => array ()),
                              'u' => array(),
                              'ul' => array ('class' => array (),'style' => array (),'type' => array ()),
                              'ol' => array ('class' => array (),'start' => array (),'style' => array (),'type' => array ()),
                              'var' => array ());
          
      $content = get_the_content();
      $content = apply_filters('the_content', $content);
      $content = str_replace(']]>', ']]&gt;', $content);

      echo wp_kses($content,$wpbook_allowed);
        
          //the_content();    
      // echo custom footer
      if(($show_custom_header_footer == "footer") || ($show_custom_header_footer == "both")){	
        echo('<div id="custom_footer">'.custom_header_footer($custom_footer,$timestamp_date_format,$timestamp_time_format) .'</div>');
      } // endif for footer 
            
      // get share link 
      if(($enable_share == "true" || $enable_external_link == "true") && ($links_position == "bottom")) { 
        echo '<p>';
        if($enable_share == "true"){
          echo '<fb:share-button class="url" href="'. get_permalink() .'" />';
        } // end enable_share = true 
        if($enable_external_link == "true"){
          ?><span class="uiButton uiButtonMedium"><a class="uiButtonText" href="<?php echo get_external_post_url(get_permalink()); ?>" title="View this post outside Facebook at <?php bloginfo('name'); ?>">View post on <?php bloginfo('name'); ?></a></span><?php
        }
        echo '</p>';
      } // end if for enable share, external, bottom
      echo '</div>';	
    endwhile; // while have posts
  endif; // if have posts	
  echo '</div>';
  echo '</fb:fbml>';  
  
  ?>
