<?php
class CRED_Posts_Controller extends CRED_Abstract_Controller
{
    public function getPosts($get, $post)
    {
        if (!isset($get['form_id']))
        {
            echo '';
            die();
        }
        
        $form_id=intval($get['form_id']);
        $fm=CRED_Loader::get('MODEL/Forms');
        $form_settings=$fm->getFormCustomField($form_id,'form_settings');
        if (!$form_settings)
        {
            echo '';
            die();
        }
        //print_r($form_settings);
        $post_type=$form_settings['post_type'];
        $post_query = new WP_Query(array('post_type'=>$post_type,'posts_per_page'=>-1));
        ob_start();
        if ($post_query->have_posts())
        {
            while ($post_query->have_posts())
            {
                $post_query->the_post();
                ?>
                <option value="<?php the_ID() ?>"><?php the_title(); ?></option>
                <?php
            }
        }
        $output=ob_get_clean();
        echo $output;
        die();
    }
    
    public function suggestPostsByTitle($get, $post)
    {
        if (!isset($get['q']))
        {
            echo '';
            die();
        }
        
        $post_type=null;
        if (isset($get['cred_post_type']))
            $post_type=$get['cred_post_type'];
        $results=CRED_Loader::get('MODEL/Fields')->suggestPostsByTitle($get['q'], $post_type, 20);
        $output='';
        /*foreach ($results as $result)
            $output.=$result->post_title."\n";*/
        $results2=array();
        if (is_array($results))
        {
            foreach ($results as $result)
                $results2[]=array('display'=>$result->post_title, 'val'=>$result->ID);
            $output=json_encode($results2);    
        }
        echo $output;
        die();
    }
    
    public function getPostIDByTitle($get, $post)
    {
        if (!isset($get['q']))
        {
            echo '';
            die();
        }
        $post_type=null;
        if (isset($get['post_type']))
            $post_type=$get['post_type'];
        $post=get_page_by_title( $id_or_title, OBJECT, $post_type );
        $output='';
        if ($post)
            $output=$post->ID;
        echo $output;
        die();
    }
    
    private function renderJsFunction(array $func_data=array())
    {
        ob_start();
        ?>
        <script type='text/javascript'>
        /*<![CDATA[\ */
            <?php foreach ($func_data as $func=>$args) 
            {
                echo $func.'('.implode(',',$args).');';
            }
            ?>
        /*]]>*/
        </script>
        <?php
        return ob_get_clean();
    }
    
    public function deletePost($get, $post)
    {
        global $current_user;
        
        if (
            !array_key_exists('_wpnonce',$get) 
            || 
            !array_key_exists('cred_link_id',$get)
            || 
            !array_key_exists('cred_action',$get)
            ||
            !wp_verify_nonce($get['_wpnonce'],$get['cred_link_id'].'_'.$get['cred_action'])
            )
            die('Security check');
        
        $jsfuncs=array();
        if (array_key_exists('cred_link_id',$_GET))
            $jsfuncs['parent._cred_cred_delete_post_handler']=array('"'.$_GET['cred_link_id'].'"');
        
        if (!isset($get['cred_post_id']))
        {
            //echo json_encode(false);
            $jsfuncs['alert']=array('"'.__('No post defined','wp-cred').'"');
            echo $this->renderJsFunction($jsfuncs);
            die();
        }
        
        
        $post_id=intval($get['cred_post_id']);
        $post=get_post($post_id);
        if ($post)
        {
            if (!current_user_can('delete_own_posts_with_cred') && $current_user->ID == $post->post_author)
            {
                    die('<strong>'.__('Do not have permission (own)','wp-cred').'</strong>');
            }
            if (!current_user_can('delete_other_posts_with_cred') && $current_user->ID != $post->post_author)
            {
                    die('<strong>'.__('Do not have permission (other)','wp-cred').'</strong>');
            }
            $fm=CRED_Loader::get('MODEL/Forms');
            if ($get['cred_action']=='delete')
                $result=$fm->deletePost($post_id,true);  // delete
            elseif ($get['cred_action']=='trash')
                $result=$fm->deletePost($post_id,false); // trash
            else die();
            //echo json_encode($result);
            
            if ($result)
            {
                $jsfuncs['alert']=array('"'.__('Post deleted','wp-cred').'"');
            }
            else
            {
                $jsfuncs['alert']=array('"'.__('Post delete failed','wp-cred').'"');
            }
        }
        echo $this->renderJsFunction($jsfuncs);
        die();
    }
}
?>