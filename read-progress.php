<?php
/**
 * Plugin name: ReadProgress
 * Description: ReadProgress offers two reading tools, especially useful for long texts. The first is a progress bar at the top of the window showing the scrolling of the text being read. In addition, it optionally shows the estimated reading time in minutes.
 * Version: 1.0
 * Author: Cau Guanabara
 * Author URI: https://cauguanabara.com.br/dev
 * Text Domain: read_progress
 * Domain Path: /langs
 * License: Wordpress
 */

if (!defined('ABSPATH')) {
    exit;
}

define('RP_URL', plugin_dir_url(__FILE__));

class ReadProgress {
    // Config items
    public $post_types = ['post'];
    public $selector = '.entry-content';
    public $height = '4px';
    public $color = '#DF1616';
    public $use_e_t = '1';
    public $wpm = 200;
    private $config_items = [ 'selector', 'height', 'color','use_e_t', 'wpm' ];

    public function __construct() {
        global $require_zip_plugin;
        if ($require_zip_plugin) {
            $require_zip_plugin->require(
                'ReadProgress', 
                'Form inputs', 
                'https://github.com/caugbr/form-inputs/archive/refs/heads/main.zip', 
                'form-inputs/form-inputs.php'
            );
        }
        $this->set_config();
        add_action('wp_enqueue_scripts', [$this, 'load_assets']);
        add_action('admin_menu', [$this, 'add_admin_page']);
        add_action('plugins_loaded', [$this, 'load_textdomain']);
    }

    /**
     * Load translations
     *
     * @return void
     */
    public function load_textdomain() {
        $path = dirname(plugin_basename(__FILE__)) . '/langs/';
        load_plugin_textdomain('read_progress', false, $path);
    }

    /**
     * Load all assets
     *
     * @return void
     */
    public function load_assets() {
        if (is_singular($this->post_types)) {
            wp_enqueue_script(
                'read-progress-js', 
                RP_URL . 'assets/js/read-progress.js', 
                [], '1.0.0', true
            );
            wp_enqueue_script(
                'read-progress-init-js', 
                RP_URL . 'assets/js/init.js', 
                [], '1.0.0', true
            );
            wp_localize_script('read-progress-js', 'RPConfig', $this->config());
            wp_enqueue_style(
                'read-progress-css', RP_URL . 'assets/css/read-progress.css', 
                [], '1.0.0'
            );
        }
    }

    /**
     * Add admin page
     *
     * @return void
     */
    public function add_admin_page() {
        add_submenu_page(
            'options-general.php',
            __('Read Progress', 'read_progress'),
            __('Read Progress', 'read_progress'),
            'manage_options',
            'rp-options',
            [$this, 'admin_page']
        );
    }

    /**
     * Admin page
     *
     * @return void
     */
    public function admin_page() {
        global $f_inputs;
        $msg = "";
        if (isset($_POST['rp_save'])) {
            check_admin_referer('rp_nonce_action', 'rp_nonce_field');
            update_option('rp_post_types', $_POST['rp_post_types']);
            update_option('rp_selector', $_POST['rp_selector']);
            update_option('rp_height', $_POST['rp_height']);
            update_option('rp_color', $_POST['rp_color']);
            update_option('rp_use_e_t', $_POST['rp_use_e_t'] ? '1' : '0');
            update_option('rp_wpm', $_POST['rp_wpm']);
            $msg = __('Configuration successfully saved!', 'read_progress');
        }
        $this->set_config();
        $ptypes = (array) $this->get_post_types();
        ?>
        <h1 class="wp-heading-inline"><?php _e('Read Progress options', 'read_progress'); ?></h1>
        <?php if (!empty($msg)) { print "<div class='updated' style='margin-left: 0'><p>{$msg}</p></div>"; } ?>
        <form method="post" action="" class="reaction-form">
            <?php wp_nonce_field('rp_nonce_action', 'rp_nonce_field'); ?>
            <p><?php _e('Here you can select the post types that will use the system and format the progress bar.', 'read_progress'); ?></p>
            <?php
            $f_inputs->input_line("select", [
                "id" => "post_types",
                "name" => "rp_post_types[]",
                "value" => $this->post_types,
                "options" => $ptypes,
                "multiple" => true,
                "size" => count($ptypes),
                "description" => __("Select the post types that you want to use the system", 'read_progress'),
            ], __("Post types", 'read_progress'));

            $f_inputs->input_line("text", [
                "id" => "selector",
                "name" => "rp_selector",
                "value" => $this->selector,
                "description" => __("CSS Selector to get the article text element", 'read_progress'),
            ], __("CSS Selector", 'read_progress'));
            
            $f_inputs->input_line("text", [
                "id" => "height",
                "name" => "rp_height",
                "value" => $this->height,
                "description" => __("Height (CSS value) for the progressbar element", 'read_progress'),
            ], __("Progressbar height", 'read_progress'));
            
            $f_inputs->input_line("color", [
                "id" => "color",
                "name" => "rp_color",
                "value" => $this->color,
                "description" => __("Color for the progressbar element", 'read_progress'),
            ], __("Progressbar color", 'read_progress'));
            
            $f_inputs->input_line("switch", [
                "id" => "use_e_t",
                "name" => "rp_use_e_t",
                "value" => $this->use_e_t ? '1' : '0',
                "activeText" => __("Yes", "read_progress"),
                "inactiveText" => __("No", "read_progress"),
                "description" => __("Use the reading time estimative above the article", 'read_progress'),
            ], __("Estimated reading time", 'read_progress'));
            
            $f_inputs->input_line("number", [
                "id" => "wpm",
                "name" => "rp_wpm",
                "value" => $this->wpm,
                "step" => "10",
                "description" => __("Words per minute rate", 'read_progress'),
            ], __("Words per minute", 'read_progress'));
            ?>
            <p>
                <?php submit_button(__('Save', 'read_progress'), 'primary', 'rp_save'); ?>
            </p>
        </form>
        <style>
        </style>
        <?php
    }

    /**
     * Return all post type names, but 'attachment'
     *
     * @return void
     */
    public function get_post_types() {
        $post_types = get_post_types(['public' => true], 'names');
        unset($post_types['attachment']);
        return $post_types;
    }

    /**
     * Get config values from database
     *
     * @return void
     */
    public function set_config() {
        $this->post_types = get_option('rp_post_types', $this->post_types);
        $this->selector = get_option('rp_selector', $this->selector);
        $this->height = get_option('rp_height', $this->height);
        $this->color = get_option('rp_color', $this->color);
        $this->use_e_t = get_option('rp_use_e_t', $this->use_e_t);
        $this->wpm = get_option('rp_wpm', $this->wpm);
    }

    /**
     * Return config values as an array, including some translations
     *
     * @return void
     */
    public function config() {
        $cfg = [
            "strings" => [
                "et_label" => __("Estimated reading time: ", "read_progress"),
                "minutes" => __("minutes", "read_progress"),
                "minute" => __("minute", "read_progress")
            ]
        ];
        foreach ($this->config_items as $key) {
            $cfg[$key] = $this->{$key};
        }
        return $cfg;
    }
}

// Global instance
global $read_progress;
$read_progress = new ReadProgress();