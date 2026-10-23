<?php
if (!defined('ABSPATH')) { exit; }
add_action('wp_enqueue_scripts', function () { wp_enqueue_style('longtech-ready', get_stylesheet_uri(), array(), '1.7.0'); });
add_action('after_setup_theme', function () { add_theme_support('wp-block-styles'); add_theme_support('editor-styles'); add_editor_style('style.css'); });

require_once get_template_directory() . '/catalog.php';

require_once get_template_directory() . '/contact.php';
