<?php
/**
 * Plugin Name: OpenPanel
 * Description: Activate OpenPanel to start tracking your website.
 * Version: 1.0.0
 * Author: OpenPanel
 * License: GPLv2 or later
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Tested up to: 6.6
 * Text Domain: openpanel
 */

if (!defined('ABSPATH')) { exit; }

final class OP_WP_Proxy {
    const OPTION_KEY   = 'op_wp_proxy_settings';
    const TRANSIENT_JS = 'op_wp_op1_js';
    const OP_JS_URL    = 'https://openpanel.dev/op1.js';
    const REST_NS      = 'openpanel';
    const REST_ROUTE   = '/(?P<path>.*)'; // wildcard path passthrough
    const DEFAULT_API_BASE = 'https://api.openpanel.dev/';
    const CACHE_TIMEOUT = WEEK_IN_SECONDS;

    public function __construct() {
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('wp_enqueue_scripts', [$this, 'inject_inline_sdk'], 0);
        add_action('rest_api_init', [$this, 'register_proxy_route']);
        add_action('admin_init', [$this, 'handle_cache_clear']);
    }

    /** ---------------- Settings ---------------- */
    public function register_settings() {
        register_setting(self::OPTION_KEY, self::OPTION_KEY, [
            'type' => 'array',
            'show_in_rest' => false,
            'sanitize_callback' => function($input) {
                $out = [];
                $out['client_id'] = isset($input['client_id']) ? sanitize_text_field($input['client_id']) : '';
                $out['track_screen'] = !empty($input['track_screen']) ? 1 : 0;
                $out['track_outgoing'] = !empty($input['track_outgoing']) ? 1 : 0;
                $out['track_attributes'] = !empty($input['track_attributes']) ? 1 : 0;
                return $out;
            }
        ]);

        add_settings_section('op_main', __('OpenPanel Settings', 'openpanel'), function() {
            echo '<p>' . esc_html__('Set your OpenPanel Client ID. The SDK and requests are served from your domain to avoid ad blockers.', 'openpanel') . '</p>';
        }, self::OPTION_KEY);

        add_settings_field('client_id', __('Client ID', 'openpanel'), function() {
            $opts = get_option(self::OPTION_KEY);
            printf('<input type="text" name="%s[client_id]" value="%s" class="regular-text" placeholder="op_client_..."/>',
                esc_attr(self::OPTION_KEY),
                isset($opts['client_id']) ? esc_attr($opts['client_id']) : ''
            );
        }, self::OPTION_KEY, 'op_main');

        add_settings_field('toggles', __('Auto-tracking (optional)', 'openpanel'), function() {
            $o = get_option(self::OPTION_KEY);
            // Default track_screen to true if not set
            $track_screen = isset($o['track_screen']) ? !empty($o['track_screen']) : true;
            ?>
            <label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[track_screen]" <?php checked($track_screen); ?>> <?php esc_html_e('Track page views automatically', 'openpanel'); ?></label><br>
            <label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[track_outgoing]" <?php checked(!empty($o['track_outgoing'])); ?>> <?php esc_html_e('Track clicks on outgoing links', 'openpanel'); ?></label><br>
            <label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[track_attributes]" <?php checked(!empty($o['track_attributes'])); ?>> <?php esc_html_e('Track additional page attributes', 'openpanel'); ?></label>
            <?php
        }, self::OPTION_KEY, 'op_main');
    }

    public function add_settings_page() {
        add_options_page(
            'OpenPanel',
            'OpenPanel',
            'manage_options',
            'op-wp-proxy',
            [$this, 'render_settings_page']
        );
    }

    public function handle_cache_clear() {
        if (isset($_POST['op_clear_cache']) && current_user_can('manage_options')) {
            if (wp_verify_nonce($_POST['_wpnonce'], 'op_clear_cache_nonce')) {
                delete_transient(self::TRANSIENT_JS);
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-success is-dismissible"><p>' . 
                         esc_html__('OpenPanel cache cleared successfully. The latest op1.js will be fetched on the next page load.', 'openpanel') . 
                         '</p></div>';
                });
            }
        }
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>OpenPanel</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields(self::OPTION_KEY);
                do_settings_sections(self::OPTION_KEY);
                submit_button(__('Save Settings', 'openpanel'));
                ?>
            </form>
            
            <hr style="margin: 2rem 0;">
            
            <h2><?php esc_html_e('Cache Management', 'openpanel'); ?></h2>
            <p><?php esc_html_e('Clear the cached op1.js file to force fetch the latest version from OpenPanel.', 'openpanel'); ?></p>
            
            <form method="post" action="">
                <?php wp_nonce_field('op_clear_cache_nonce'); ?>
                <input type="submit" name="op_clear_cache" class="button button-secondary" value="<?php esc_attr_e('Clear Cache & Force Refresh', 'openpanel'); ?>">
            </form>
            
            <?php
            // Check if transient exists and get expiration info
            $cached_js = get_transient(self::TRANSIENT_JS);
            $timeout_option = '_transient_timeout_' . self::TRANSIENT_JS;
            $cached_time = get_option($timeout_option);
            
            if ($cached_js !== false && $cached_time) {
                $time_remaining = $cached_time - time();
                if ($time_remaining > 0) {
                    echo '<p style="margin-top:1rem;color:#666;">' . 
                         sprintf(esc_html__('Cache expires in %s', 'openpanel'), human_time_diff(time(), $cached_time)) . 
                         '</p>';
                } else {
                    echo '<p style="margin-top:1rem;color:#666;">' . 
                         esc_html__('Cache has expired and will refresh on next page load.', 'openpanel') . 
                         '</p>';
                }
            } else {
                echo '<p style="margin-top:1rem;color:#666;">' . 
                     esc_html__('No cached version found. op1.js will be fetched on next page load.', 'openpanel') . 
                     '</p>';
            }
            ?>
            
            <p style="margin-top:1rem;color:#666;">
                <?php esc_html_e('The plugin fetches and inlines op1.js (cached for 1 week). If fetching fails, it falls back to the CDN script.', 'openpanel'); ?>
            </p>
        </div>
        <?php
    }

    /** ---------------- Inline SDK ---------------- */
    public function inject_inline_sdk() {
        if (is_admin()) return;

        $opts = get_option(self::OPTION_KEY);
        $clientId = isset($opts['client_id']) ? trim($opts['client_id']) : '';
        if ($clientId === '') return; // don’t inject if not configured

        $apiUrl = untrailingslashit( rest_url(self::REST_NS . '/') );

        $init = [
            'clientId'           => $clientId,
            'apiUrl'             => $apiUrl,
            'trackScreenViews'   => isset($opts['track_screen']) ? !empty($opts['track_screen']) : true,
            'trackOutgoingLinks' => !empty($opts['track_outgoing']),
            'trackAttributes'    => !empty($opts['track_attributes']),
        ];

        $bootstrap = "(function(){window.op=window.op||function(){(window.op.q=window.op.q||[]).push(arguments)};window.op('init'," . wp_json_encode($init) . ");})();";

        $op_js = get_transient(self::TRANSIENT_JS);
        if ($op_js === false) {
            $res = wp_remote_get(self::OP_JS_URL, ['timeout' => 8]);
            if (!is_wp_error($res) && 200 === wp_remote_retrieve_response_code($res)) {
                $op_js = wp_remote_retrieve_body($res);
                set_transient(self::TRANSIENT_JS, $op_js, self::CACHE_TIMEOUT);
            }
        }

        wp_register_script('op-inline-stub', false, [], null, true);
        wp_enqueue_script('op-inline-stub');

        wp_add_inline_script('op-inline-stub', $bootstrap, 'before');

        if (!empty($op_js)) {
            wp_add_inline_script('op-inline-stub', $op_js, 'after');
        } else {
            wp_enqueue_script('openpanel-op1', self::OP_JS_URL, [], null, true);
        }
    }

    /** ---------------- Proxy Route ---------------- */
    public function register_proxy_route() {
        register_rest_route(self::REST_NS, self::REST_ROUTE, [
            'methods'  => \WP_REST_Server::ALLMETHODS,
            'permission_callback' => '__return_true',
            'callback' => [$this, 'proxy_request'],
            'args'     => [
                'path' => [
                    'description' => 'Remaining path to forward',
                    'required' => false,
                ],
            ],
        ]);
    }

    public function proxy_request(\WP_REST_Request $request) {
        // Handle CORS preflight quickly
        if (strtoupper($request->get_method()) === 'OPTIONS') {
            $resp = new \WP_REST_Response(null, 204);
            $this->add_cors_headers($resp);
            return $resp;
        }

        $path = ltrim($request->get_param('path') ?? '', '/');
        $target = rtrim(self::DEFAULT_API_BASE, '/') . '/' . $path;

        $method = strtoupper($request->get_method());
        $body   = $request->get_body();

        $incoming = $this->collect_request_headers();

        $query = $request->get_query_params();
        if (!empty($query)) {
            $target = add_query_arg($query, $target);
        }

        $args = [
            'method'  => $method,
            'timeout' => 10,
            'headers' => $incoming,
            'body'    => in_array($method, ['POST','PUT','PATCH','DELETE'], true) ? $body : null,
        ];

        $res = wp_remote_request($target, $args);

        if (is_wp_error($res)) {
            $resp = new \WP_REST_Response(['error' => 'proxy_failed', 'message' => $res->get_error_message()], 502);
            $this->add_cors_headers($resp);
            $resp->header('Cache-Control', 'no-store');
            return $resp;
        }

        $code = wp_remote_retrieve_response_code($res);
        $headers = wp_remote_retrieve_headers($res);
        $bodyOut = wp_remote_retrieve_body($res);

        $resp = new \WP_REST_Response($bodyOut, $code);
        // Filter hop-by-hop headers
        if (is_array($headers)) {
            foreach ($headers as $k => $v) {
                $lk = strtolower($k);
                if (in_array($lk, ['transfer-encoding','connection','content-encoding'], true)) continue;
                $resp->header($k, $v);
            }
        }
        if (!$resp->get_headers()['Content-Type'] ?? true) {
            $resp->header('Content-Type', 'application/json; charset=utf-8');
        }
        $resp->header('Cache-Control', 'no-store');
        $this->add_cors_headers($resp);
        return $resp;
    }

    private function add_cors_headers(\WP_REST_Response $resp) {
        $origin = get_site_url();
        $resp->header('Access-Control-Allow-Origin', $origin);
        $resp->header('Access-Control-Allow-Credentials', 'true');
        $resp->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $resp->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $resp->header('Vary', 'Origin');
    }

    private function collect_request_headers() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (strpos($name, 'HTTP_') === 0) {
                $header = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                // remove hop-by-hop
                $lk = strtolower($header);
                if (in_array($lk, ['host','content-length','accept-encoding','connection','keep-alive','transfer-encoding','upgrade','via'], true)) {
                    continue;
                }
                $headers[$header] = $value;
            }
        }
        if (!isset($headers['User-Agent'])) {
            $headers['User-Agent'] = 'OpenPanel-WP-Proxy';
        }
        return $headers;
    }
}

new OP_WP_Proxy();
