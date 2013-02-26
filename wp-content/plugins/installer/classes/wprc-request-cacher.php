<?php
class WPRC_RequestCacher
{
    private $model = null;
    private $option_last_cleanup_date = 'wprc_last_cleanup_date';

    /**
     * @var Cleanup period in seconds
     */
    private $cleanup_period = 7200;

    public function __construct()
    {
        $this->model = WPRC_Loader::getModel('cached-requests');
    }

    /**
     * Cache api request
     *
     * @param $server_url
     * @param $action
     * @param $args
     * @param $results
     * @return bool
     */
    public function cacheApiRequest($server_url, $action, $args, $results)
    {
        $cache_action = $this->checkAction($action);

        if(!$cache_action)
        {
            return false;
        }
       
        if (is_wp_error($results) || $results==false) return false;
        
        return $this->model->cacheRequest($server_url, $action, $args, $results);
    }

    /**
     * Get cached request results
     *
     * @param $server_url
     * @param $action
     * @param $args
     * @return bool
     */
    public function getCachedApiRequest($server_url, $action, $args)
    {
        $cache_action = $this->checkAction($action);

        if(!$cache_action)
        {
            $msg=sprintf("Cacher action: %s, cache_action: %s",$action,($cache_action)?'YES':'NO');
            WPRC_Functions::log($msg,'api','api.log');
            return false;
        }

        
        $foo = $this->model->getCachedRequest($server_url, $action, $args);
        $msg=sprintf("Cacher model get: %s",($foo==false)?'NO':'YES');
        WPRC_Functions::log($msg,'api','api.log');
        return $foo;
    }

    /**
     * Return actions names which need to be cached
     *
     * @return array
     */
    private function getCachedActions()
    {
        return array('query_plugins', 'query_themes');
    }

    /**
     * Check is action need to be cached
     *
     * @param $action
     */
    private function checkAction($action)
    {
        $actions = $this->getCachedActions();

        if(in_array($action, $actions))
        {
            return true;
        }

        return false;
    }

    /**
     * Clean cache and optimize the cache table after records deleting
     */
    public function cleanCache()
    {
        $last_cleanup_date = get_transient($this->option_last_cleanup_date);

        if(!$last_cleanup_date)
        {
            // clean up the cache
            $this->model->cleanCache();

            set_transient($this->option_last_cleanup_date, time(), $this->cleanup_period);
            $this->setLastCleanupDate();
        }
    }

    /**
     * Return last cleanup date
     *
     * @return mixed
     */
    public function getLastCleanupDate()
    {
        return get_option($this->option_last_cleanup_date);
    }

    /**
     * Set last cleanup date
     *
     * @return bool
     */
    private function setLastCleanupDate()
    {
        return update_option($this->option_last_cleanup_date, time());
    }
}
?>