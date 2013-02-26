<?php
abstract class CRED_Abstract_Controller
{
    protected function redirectTo($url)
    {
        header("location: $url");
    }
}
?>