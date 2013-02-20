<?php
/**
 * Shortcode Parser Class, uses same WP Code to parse shortcodes
 * parses internal shortcodes, can parse recursive (internal) shortcodes as well
 * 
**/
class CRED_Shortcode_Parser
{
    public $shortcode_tags=array();    // used to parse shortcodes internally, in same manner as WP    
    public $depth=0;
   
    public function __construct()
    {
        $this->shortcode_tags=array();
        $this->depth=0;
        $this->child_groups=array();
    }
    
    public function add_shortcode($tag, $func) 
    {
        if ( is_callable($func) )
            $this->shortcode_tags[$tag] = $func;
    }

    public function remove_shortcode($tag) 
    {
        unset($this->shortcode_tags[$tag]);
    }

    public function remove_all_shortcodes() 
    {
        $this->shortcode_tags = array();
        $this->depth=0;
    }
    
    // parse shortcodes internally (uses wp code found at shortcodes.php)
    public function do_recursive_shortcode($tag, $content) 
    {
        $this->depth=0;
        $tag=preg_quote($tag);
        $expression = "/\\[$tag((?!\\[$tag).)*\\[\\/$tag\\]/isUS";
        // do a depth-first-like matching to parse recursive shortcodes
        while (preg_match_all($expression, $content, $matches/*,PREG_PATTERN_ORDER*/)) 
        {
            foreach($matches[0] as $match) 
            {
                $shortcode = $this->do_shortcode($match);
                $content = str_replace($match, $shortcode, $content);
            }
            $this->depth++;
        }
        return $content;
    }
    
    public function do_shortcode($content) 
    {
        if (empty($this->shortcode_tags) || !is_array($this->shortcode_tags))
            return $content;

        $pattern = $this->get_shortcode_regex();
        return preg_replace_callback( "/$pattern/s", array(&$this,'do_shortcode_tag'), $content );
    }
    
   public function shortcode_parse_atts($text) 
    {
        $atts = array();
        $pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
        $text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);
        if ( preg_match_all($pattern, $text, $match, PREG_SET_ORDER) ) {
            foreach ($match as $m) {
                if (!empty($m[1]))
                    $atts[strtolower($m[1])] = stripcslashes($m[2]);
                elseif (!empty($m[3]))
                    $atts[strtolower($m[3])] = stripcslashes($m[4]);
                elseif (!empty($m[5]))
                    $atts[strtolower($m[5])] = stripcslashes($m[6]);
                elseif (isset($m[7]) and strlen($m[7]))
                    $atts[] = stripcslashes($m[7]);
                elseif (isset($m[8]))
                    $atts[] = stripcslashes($m[8]);
            }
        } else {
            $atts = ltrim($text);
        }
        return $atts;
    }
    
    public function do_shortcode_tag( $m ) 
    {
        // allow [[foo]] syntax for escaping a tag
        if ( $m[1] == '[' && $m[6] == ']' ) {
            return substr($m[0], 1, -1);
        }

        $tag = $m[2];
        $attr = $this->shortcode_parse_atts( $m[3] );

        if ( isset( $m[5] ) ) {
            // enclosing tag - extra parameter
            return $m[1] . call_user_func( $this->shortcode_tags[$tag], $attr, $m[5], $tag ) . $m[6];
        } else {
            // self-closing tag
            return $m[1] . call_user_func( $this->shortcode_tags[$tag], $attr, null,  $tag ) . $m[6];
        }
    }
    
    public function get_shortcode_regex() 
    {
        $tagnames=array_keys($this->shortcode_tags);
        $tagregexp = join( '|', array_map('preg_quote', $tagnames) );

        // WARNING! Do not change this regex without changing do_shortcode_tag() and strip_shortcode_tag()
        return
              '\\['                              // Opening bracket
            . '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
            . "($tagregexp)"                     // 2: Shortcode name
            . '\\b'                              // Word boundary
            . '('                                // 3: Unroll the loop: Inside the opening shortcode tag
            .     '[^\\]\\/]*'                   // Not a closing bracket or forward slash
            .     '(?:'
            .         '\\/(?!\\])'               // A forward slash not followed by a closing bracket
            .         '[^\\]\\/]*'               // Not a closing bracket or forward slash
            .     ')*?'
            . ')'
            . '(?:'
            .     '(\\/)'                        // 4: Self closing tag ...
            .     '\\]'                          // ... and closing bracket
            . '|'
            .     '\\]'                          // Closing bracket
            .     '(?:'
            .         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
            .             '[^\\[]*+'             // Not an opening bracket
            .             '(?:'
            .                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
            .                 '[^\\[]*+'         // Not an opening bracket
            .             ')*+'
            .         ')'
            .         '\\[\\/\\2\\]'             // Closing shortcode tag
            .     ')?'
            . ')'
            . '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
    }
}
?>