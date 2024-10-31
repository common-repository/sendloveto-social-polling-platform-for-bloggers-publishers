<?php
/*
Plugin Name: SendLove.to
Plugin URI: http://sendlove.to/
Description: A Plugin for Public Figure Opining in your article content
Version: 1.27
Author: SendLove.to
Author URI: http://sendlove.to/
License: GPL2
*/

/*  Copyright 2011  SendLove.to

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?><?php

// some definition we will use
define( 'SENDLOVE_PLUGIN_NAME', 'SendLove.to');
define( 'SENDLOVE_CURRENT_VERSION', '1.27' );
define( 'SENDLOVE_CURRENT_BUILD', '6' );
define( 'SENDLOVE_DEBUG', false);
define( 'SENDLOVE_I18N_DOMAIN', 'sendlove' );

// load language files
function sendlove_set_lang_file() {
    # set the language file
    $currentLocale = get_locale();
    if(!empty($currentLocale)) {
        $moFile = dirname(__FILE__) . "/lang/" . $currentLocale . ".mo";
        if (@file_exists($moFile) && is_readable($moFile)) {
            load_textdomain(SENDLOVE_I18N_DOMAIN, $moFile);
        }

    }
}
sendlove_set_lang_file();

// create custom plugin settings menu
add_action( 'admin_menu', 'sendlove_create_menu' );

//call register settings function
add_action( 'admin_init', 'sendlove_register_settings' );


register_activation_hook(__FILE__, 'sendlove_activate');
register_deactivation_hook(__FILE__, 'sendlove_deactivate');
register_uninstall_hook(__FILE__, 'sendlove_uninstall');

// activating the default values
function sendlove_activate() {
}

function sendlove_cleanup() {
    unregister_setting( 'sendlove-settings-group', 'sendlove_pulse_inject' );
    unregister_setting( 'sendlove-settings-group', 'sendlove_site_short_name' );
}


// deactivating
function sendlove_deactivate() {
    // Call our global cleanup handler.
    sendlove_cleanup();
}

// uninstalling
function sendlove_uninstall() {
    // Call our global cleanup handler.
    sendlove_cleanup();
}

function sendlove_create_menu() {
    add_submenu_page( "edit-comments.php", __("SendLove.to", SENDLOVE_I18N_DOMAIN), __("SendLove.to", SENDLOVE_I18N_DOMAIN), 9, dirname(__FILE__).'/sendlove_settings_page.php');
}


function sendlove_register_settings() {
    register_setting( 'sendlove-settings-group', 'sendlove_pulse_inject' );
    register_setting( 'sendlove-settings-group', 'sendlove_site_short_name' );
}

// check if debug is activated
function sendlove_debug() {
    # only run debug on localhost
    if ($_SERVER["HTTP_HOST"]=="localhost" && defined('SENDLOVE_DEBUG') && SENDLOVE_DEBUG==true) return true;
}

function sendlove_plugin_action_links($links, $file) {
    $plugin_file = basename(__FILE__);
    if (basename($file) == $plugin_file) {
        $settings_link = '<a href="edit-comments.php?page='.dirname(__FILE__).'/sendlove_settings_page.php">'.__('Settings', SENDLOVE_I18N_DOMAIN).'</a>';
        array_unshift($links, $settings_link);
    }
    return $links;
}
add_filter('plugin_action_links', 'sendlove_plugin_action_links', 10, 2);

function sendlove_manage_dialog($message, $error = false) {
    global $wp_version;

    echo '<div '
        . ( $error ? 'id="sendlove_warning" ' : '')
        . 'class="updated fade'
        . ( (version_compare($wp_version, '2.5', '<') && $error) ? '-ff0000' : '' )
        . '"><p><strong>'
        . $message
        . '</strong></p></div>';
}

function sendlove_is_installed() {
    return get_option('sendlove_site_short_name');
}

function sendlove_pulse_inject() {
    $option = get_option('sendlove_pulse_inject');
    return $option ? $option : "comments";
}

function sendlove_embed_inject() {
    $pulse_method = sendlove_pulse_inject();
    if ( $pulse_method == "comments" || $pulse_method == "content" ) return $pulse_method;
    return 'footer';
}

function sendlove_warning() {
    $page = (isset($_GET['page']) ? $_GET['page'] : null);

    if ( !sendlove_is_installed() && strpos($page, dirname(__FILE__)) !== 0 && !isset($_POST['uninstall']) ) {
        sendlove_manage_dialog('You must <a href="edit-comments.php?page='.dirname(__FILE__).'/sendlove_settings_page.php">enter your details</a> to enable SendLove.to.', true);
    }
}

add_action('admin_notices', 'sendlove_warning');

function sendlove_in_action() {
    if (!is_single()) return false;
    if (!sendlove_is_installed()) return false;
    return true;
}

function sendlove_embed_js() {
    global $sendlove_embed_done;
    if (!sendlove_in_action()||$sendlove_embed_done) return;

    $site_short_name    = strtolower(get_option("sendlove_site_short_name"));
    $content_link       = sendlove_content_link();
    $content_id         = sendlove_content_id();
    $content_title      = cf_json_encode(sendlove_content_title());

    $output = <<<EOD
    <script type="text/javascript">
    // <![CDATA[
        var sendlove_config = {
            site_short_name: '$site_short_name',
            content_link: '$content_link',
            content_id: '$content_id',
            wordpress: true,
            content_title: $content_title
        };

        (function(d, t) {
        var g = d.createElement(t), s = d.getElementsByTagName(t)[0]; g.async = true;
        g.src = '//sendlove.to/widgets/embed.js';
        s.parentNode.insertBefore(g, s);
        }(document, 'script'));
    //]]>
    </script>
EOD;
    $sendlove_embed_done = true;
    return $output;
}

function sendlove_pulse_div() {
    global $sendlove_pulse_div_done;
    if (!sendlove_in_action()||$sendlove_pulse_div_done) return;
    $sendlove_pulse_div_done = true;
    return "<div id='sendlove_pulse'></div>\n<a href='http://sendlove.to' class='sendlove_link'>opinions powered by SendLove.to</a>";
}

function sendlove_content_filter($content) {
    if (!sendlove_in_action()) return $content;
    $content = "<div class='sendlove_content'>" . $content . "</div>";
    if (sendlove_pulse_inject() == "content") $content .= sendlove_pulse_div();
    if (sendlove_embed_inject() == "content") $content .= sendlove_embed_js();
    return $content;
}
add_action('the_content', 'sendlove_content_filter');

function sendlove_echo_embed() {
    echo sendlove_embed_js();
}
if ( sendlove_embed_inject() == 'header' ) add_action('wp_head', 'sendlove_echo_embed');
if ( sendlove_embed_inject() == 'footer' ) add_action('wp_footer', 'sendlove_echo_embed');

function sendlove_comments_template($value) {
    if (sendlove_pulse_inject() == 'comments') echo sendlove_pulse_div();
    if (sendlove_embed_inject() == 'comments') echo sendlove_embed_js();
    return $value;
}
add_filter('comments_template', 'sendlove_comments_template');

function sendlove_content_link() {
    global $post;
    return get_permalink($post);
}

function sendlove_content_title() {
    global $post;
    return strip_tags($post->post_title);
}

function sendlove_content_id() {
    global $post;
    return $post->ID . ' ' . $post->guid;
}

/**
 * JSON ENCODE for PHP < 5.2.0
 * Checks if json_encode is not available and defines json_encode
 * to use php_json_encode in its stead
 * Works on iteratable objects as well - stdClass is iteratable, so all WP objects are gonna be iteratable
 */
if(!function_exists('cf_json_encode')) {
    function cf_json_encode($data) {
// json_encode is sending an application/x-javascript header on Joyent servers
// for some unknown reason.
//         if(function_exists('json_encode')) { return json_encode($data); }
//         else { return cfjson_encode($data); }
        return cfjson_encode($data);
    }

    function cfjson_encode_string($str) {
        if(is_bool($str)) {
            return $str ? 'true' : 'false';
        }

        return str_replace(
            array(
                '"'
                , '/'
                , "\n"
                , "\r"
            )
            , array(
                '\"'
                , '\/'
                , '\n'
                , '\r'
            )
            , $str
        );
    }

    function cfjson_encode($arr) {
        $json_str = '';
        if (is_array($arr)) {
            $pure_array = true;
            $array_length = count($arr);
            for ( $i = 0; $i < $array_length ; $i++) {
                if (!isset($arr[$i])) {
                    $pure_array = false;
                    break;
                }
            }
            if ($pure_array) {
                $json_str = '[';
                $temp = array();
                for ($i=0; $i < $array_length; $i++) {
                    $temp[] = sprintf("%s", cfjson_encode($arr[$i]));
                }
                $json_str .= implode(',', $temp);
                $json_str .="]";
            }
            else {
                $json_str = '{';
                $temp = array();
                foreach ($arr as $key => $value) {
                    $temp[] = sprintf("\"%s\":%s", $key, cfjson_encode($value));
                }
                $json_str .= implode(',', $temp);
                $json_str .= '}';
            }
        }
        else if (is_object($arr)) {
            $json_str = '{';
            $temp = array();
            foreach ($arr as $k => $v) {
                $temp[] = '"'.$k.'":'.cfjson_encode($v);
            }
            $json_str .= implode(',', $temp);
            $json_str .= '}';
        }
        else if (is_string($arr)) {
            $json_str = '"'. cfjson_encode_string($arr) . '"';
        }
        else if (is_numeric($arr)) {
            $json_str = $arr;
        }
        else if (is_bool($arr)) {
            $json_str = $arr ? 'true' : 'false';
        }
        else {
            $json_str = '"'. cfjson_encode_string($arr) . '"';
        }
        return $json_str;
    }
}

?>
