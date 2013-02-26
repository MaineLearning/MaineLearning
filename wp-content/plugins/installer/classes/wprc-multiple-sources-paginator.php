<?php
/**
 * WPRC_MultipleSourcesPaginator
 * 
 * This class responsible for calculation of valid items number on page in case of getting of items from several items sources.
 */  
class WPRC_MultipleSourcesPaginator
{
    /**
     * Calculate items number on each page
     * 
     * If real number of pages is more than specified per_page parameter function return 'items overflow' Exception
     * 
     * @param int total items number
     * @param int number of items per page
     * @param int number of pages
     *
     * @return array array with calculated items number per page. Keys of array are number of pages, value of array represents items number on this page
     */  
    public static function calculateItemsPerPage($total_items, $per_page, $pages_number)
    {
        // check real number of page
        // if real number of pages is more than specified per_page parameter function return items overflow error 
        if(ceil($total_items/$pages_number) > $per_page)
        {
            throw new Exception('Items overflow error');
        }
        
        $pages = array();
        for($page=1; $page<=$pages_number; $page++)
        {
          //  echo 'PAGE#'.$page.'<br>';
            $n = ($page-1)*$per_page; // first item on a page
        //    echo 'n='.$n.'<br>';
            
        //    echo '('.$n.' + '.$per_page.') > '.$total_items.' : ';
            if(($n + $per_page) > $total_items)
            {
        //        echo 'TRUE    ('.$total_items.' - '.$n.')';
                $items_on_page = $total_items - $n; 
               
                if($items_on_page < 0)
                {
                    $items_on_page = 0;
                }  
            }
            else
            {
       //         echo 'FALSE';
                $items_on_page = $per_page;
            }
            
       //     echo '<hr>';
            $pages[$page] = $items_on_page;
        }
        return $pages;
    }
}
?>