<?php
/*
Plugin Name: YouTube Live Video
Description: Display YouTube auto live video on your site.
Version: 1.0
Author: Microweb Global (PVT) LTD
License: GPL2
Author URI: www.microweb.global
Company:  Microweb Global
*/


function enqueue_popup_script() {
    global $pagenow;

    if ($pagenow === 'options-general.php' && isset($_GET['page']) && $_GET['page'] === 'youtube-live-video-settings') {
        wp_enqueue_script('popup-script', plugin_dir_url(__FILE__) . 'popup.js', array('jquery'), '1.0', true);
    }
}
function remove_all_banners() {
    remove_all_actions('admin_notices');
}

add_action('admin_init', 'remove_all_banners');

add_action('admin_enqueue_scripts', 'enqueue_popup_script');
function youtube_live_video_menu() {
    add_menu_page(
        'YouTube Live Video Settings',
        'YouTube Live Video',
        'manage_options',
        'youtube-live-video-settings',
        'youtube_live_video_settings_page'
    );
}
function is_plugin_integrity_ok() {
    $plugin_directory = plugin_dir_path(__FILE__);

    $php_files = get_php_files_in_directory($plugin_directory);

    foreach ($php_files as $file) {
        $file_content = file_get_contents($file);

        $actual_hash = hash('sha256', $file_content);

        $expected_hash = get_expected_hash_for_file($file);

        if ($actual_hash !== $expected_hash) {
            return false;
        }
    }

    return true;
}

function get_php_files_in_directory($directory) {
    $php_files = array();

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $php_files[] = $file->getPathname();
        }
    }

    return $php_files;
}

function get_expected_hash_for_file($file) {

    return hash('sha256', file_get_contents($file));
}

if (isset($_POST['submit'])) {
    if (!is_plugin_integrity_ok()) {
        die('Plugin integrity check failed. Please reinstall the plugin.');
    }
}


add_action('admin_menu', 'youtube_live_video_menu');
function youtube_live_video_settings_page() {
    ?>
    <div class="wrap">
        <div>
            <h2>Custom YouTube Live Video Settings</h2>
            <h3>Plugin Developed by Microweb Global (PVT) LTD</h3>
            <div style="margin-bottom: 20px;">
                <img src="https://media.licdn.com/dms/image/D560BAQFiTEsFjMiy4g/company-logo_200_200/0/1682276360757?e=2147483647&v=beta&t=btygzcEDfXABJnHW8hx7DNbzwDE46BefRsjdIsYFkk8" alt="Developer's Logo" style="width: 200px; height: auto;" />
            </div>
            <h4>Digital Empowerment at its Finest</h4>
            <h4>Website: <a href="https://www.microweb.global" target="_blank">www.microweb.global</a></h4>
            <h4>Email: <a href="mailto:contact@microweb.global">contact@microweb.global</a></h4>
        </div>

        <form method="post" action="options.php">
            <?php
            settings_fields('youtube_live_video_settings');
            do_settings_sections('youtube-live-video-settings');
            submit_button();

            ?>
            
            <h3>License Key Verification:</h3>
            <p>Enter your license key below:</p>
            <input type="text" name="license_key" value="<?php echo esc_attr(get_option('license_key')); ?>" />
            <?php
            submit_button('Verify License');
            
            if (is_license_key_valid()) {
                echo '<p style="color: green;">License key verified successfully!</p>';
            } else {
                echo '<p style="color: red;">Invalid license key. Please check and try again.</p>';
            }
            ?>
        </form>

        <div style="margin-top: 20px;">
            <h3>Shortcode:</h3>
            <p>Use the following shortcode to display the YouTube live video:</p>
            <pre>[youtube_live_video]</pre>
        </div>
    </div>
    <?php
}

function is_license_key_valid() {
    $hardcoded_license_key = 'OlakTechnologies4234';
    $entered_license_key = get_option('license_key');
    
    return ($entered_license_key === $hardcoded_license_key);
}

function validate_license_key($input) {
    $hardcoded_license_key = 'OlakTechnologies4234';

    if ($input === $hardcoded_license_key) {
        return $input;
    } else {
        add_settings_error('license_key', 'invalid_license_key', 'Invalid license key. Please check and try again.', 'error');
        return get_option('license_key');
    }
}

function register_license_key_setting() {
    register_setting('youtube_live_video_settings', 'license_key', 'validate_license_key');
}

add_action('admin_init', 'register_license_key_setting');


function youtube_live_video_settings() {
    register_setting(
        'youtube_live_video_settings',
        'youtube_live_video_link',
        array(
            'type'              => 'string',
            'sanitize_callback' => 'validate_youtube_live_video_link'
        )
    );

    add_settings_section(
        'youtube_live_video_section',
        'YouTube Live Video Settings',
        'youtube_live_video_section_callback',
        'youtube-live-video-settings'
    );

    add_settings_field(
        'youtube_live_video_link',
        'YouTube Live Video Link',
        'youtube_live_video_link_callback',
        'youtube-live-video-settings',
        'youtube_live_video_section'
    );
}

function validate_youtube_live_video_link($input) {
    if (strpos($input, 'watch?v=') === false) {
        add_settings_error(
            'youtube_live_video_link',
            'invalid_link',
            'Please enter a valid YouTube live video link containing "watch?v=".',
            'error'
        );
        return get_option('youtube_live_video_link'); 
    }

    return esc_url_raw($input);
}

add_action('admin_init', 'youtube_live_video_settings');
function youtube_live_video_section_callback() {
    echo '<p>Enter the YouTube live video link below.    Example format: https://www.youtube.com/watch?v=OsI7-EOka3k</p>';
}

function youtube_live_video_link_callback() {
    $value = get_option('youtube_live_video_link');
    echo '<input type="text" id="youtube_live_video_link" name="youtube_live_video_link" value="' . esc_attr($value) . '" style="width: 100%;" />';
}
function youtube_live_video_shortcode() {
    $video_link = get_option('youtube_live_video_link');

    if (empty($video_link)) {
        return 'No YouTube live video link provided.';
    }

    $embed_url = convert_live_url_to_embed_url($video_link);

    ob_start();
    ?>
    <div id="youtube-live-video">
        <iframe width="560" height="515" src="<?php echo esc_url($embed_url); ?>" frameborder="0" allowfullscreen referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>

<script>
    console.log('YouTube Live Video URL:', '<?php echo esc_js($embed_url); ?>');
</script>
    <?php
    return ob_get_clean();
}
add_shortcode('youtube_live_video', 'youtube_live_video_shortcode');


function convert_live_url_to_embed_url($live_url) {
    $video_id = get_youtube_video_id($live_url);

    $embed_url = strpos($video_id, '/embed/') !== false ? $video_id : str_replace('watch?v=', 'embed/', $video_id);

    return "https://www.youtube.com/embed/{$embed_url}";
}

function get_youtube_video_id($url) {
    $video_id = '';
    $url_parts = parse_url($url);

    if (isset($url_parts['query'])) {
        parse_str($url_parts['query'], $query_params);

        if (isset($query_params['v'])) {
            $video_id = $query_params['v'];
        }
    } elseif (isset($url_parts['path'])) {
        $path_segments = explode('/', trim($url_parts['path'], '/'));

        if (!empty($path_segments)) {
            $video_id = end($path_segments);
        }
    }

    return $video_id;
}
add_shortcode('youtube_live_video', 'youtube_live_video_shortcode');
