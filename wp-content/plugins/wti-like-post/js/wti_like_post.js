jQuery(document).ready(function(){
     jQuery(".like img, .unlike img").click(function(){
          var task = jQuery(this).attr("rel");
          var post_id = jQuery(this).attr("id");
          
          if(task == "like")
          {
               post_id = post_id.replace("like-", "");
          }
          else
          {
               post_id = post_id.replace("unlike-", "");
          }
          
          jQuery("#status-" + post_id).html("&nbsp;&nbsp;").addClass("loading-img");
          
          jQuery.ajax({
               type: "POST",
               url: blog_url + "/wp-content/plugins/wti-like-post/wti_like.php",
               data: "post_id=" + post_id + "&task=" + task + "&num=" + Math.random(),
               success: function(data){
                    jQuery("#lc-" + post_id).html(data.like);
                    jQuery("#unlc-" + post_id).html(data.unlike);
                    jQuery("#status-" + post_id).removeClass("loading-img").empty().html(data.msg);
               },
               dataType: "json"
          });
     });
});