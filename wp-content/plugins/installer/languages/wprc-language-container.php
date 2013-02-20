<?php
class WPRC_LanguageContainer
{
    public static function getLanguageArray()
    {
        return array(
        'please_check_the_option' => __('Please check the option','installer'),
        'please_check_the_problem_themes' => __('Please check the problem themes','installer'),
        'please_check_the_problem_plugins' => __('Please check the problem plugins','installer'),
        'select_plugins' => __('Select plugins','installer'),
        'select_themes' => __('Select themes','installer'),
        'please_check_the_child_option' => __('Please check the child option','installer'),
        'warning' => __('Warning', 'installer'),
        'enter_repository_name' => __('Enter repository name please', 'installer'),
        'enter_repository_endpoint_url' => __('Enter repository end point url please', 'installer'),
        
        'select_repositories' => __('Select repositories', 'installer'),
        'search_in_N_repositories' => __('Search in # repositories', 'installer'),
        'free' => __('Free', 'installer'),
        'paid' => __('Paid', 'installer'),
        
        'search_free_or_paid_plugins' => __('Search plugins (Free or Paid)','installer'),
        'search_free_plugins' => __('Search Free plugins','installer'),
        'search_paid_plugins' => __('Search Paid plugins','installer'),
        'search_free_and_paid_plugins' => __('Search Free & Paid plugins','installer'),
        
        'search_free_or_paid_themes' => __('Search themes (Free or Paid)','installer'),
        'search_free_themes' => __('Search Free themes','installer'),
        'search_paid_themes' => __('Search Paid themes','installer'),
        'search_free_and_paid_themes' => __('Search Free & Paid themes','installer'),

        'clear_cache' => __('Clear cache','installer'),
        
        'repository_have_N_types' => __('Repository has # type(-s)', 'installer'),
        'select_extension_types' => __('Choose repository types', 'installer'),
        'choose_repository_types' => __('Choose repository types please', 'installer'),
        'check_compatibility_of_the_theme' => __('Check compatibility status for the theme','installer')
        );
    }
}
?>