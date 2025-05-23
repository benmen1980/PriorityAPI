<?php
/**
 * @package     PriorityAPI
 * @author      Ante Laca <ante.laca@gmail.com>
 * @copyright   2018 Roi Holdings
 */

namespace PriorityAPI;

class API
{
    private static $instance; // api instance
    protected static $prefix; // options prefix

    private function __construct()
    {
        // set prefix for options, supports multisite
        static::$prefix = sprintf('p18a_%s_', get_current_blog_id());

        // autoloader
        spl_autoload_register(function ($class) {

            // check namespace
            if (false === strpos($class, 'PriorityAPI')) {
                return;
            }


            $file = str_replace('\\', DIRECTORY_SEPARATOR, $class);
            $file = P18A_CLASSES_DIR . strtolower(basename($file)) . '.php';

            if (file_exists($file)) {
                include_once $file;
            }

        });

    }

    /**
     * PriorityAPI initialize
     *
     */
    public static function instance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;

    }

    private function __clone()
    {
    }

    /**
     *  Run PriorityAPI
     *
     */
    public function run()
    {
        return is_admin() ? $this->backend() : $this->frontend();
    }

    /**
     * PriorityAPI Admin
     *
     */
    private function backend()
    {
        register_activation_hook(P18A_SELF, [$this, 'activation']);
        register_deactivation_hook(P18A_SELF, [$this, 'deactivation']);

        // load language
        load_plugin_textdomain('p18a', false, plugin_basename(P18A_DIR) . '/languages');

        // init admin
        add_action('init', function () {

            if ($this->get('repost') && wp_verify_nonce($this->get('request'), 'repost')) {

                $data = $GLOBALS['wpdb']->get_row('SELECT url, request_method, json_request  FROM ' . $GLOBALS['wpdb']->prefix . 'p18a_logs WHERE id = ' . $this->get('repost'));

                $args = [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode($this->option('username') . ':' . $this->option('password')),
                        'content-type' => 'application/json',
                    ],
                    'method' => $data->request_method,
                    'sslverify' => $this->option('sslverify', false)
                ];

                // data
                if (in_array(strtolower($data->request_method), ['post', 'patch'])) {
                    $args['body'] = $data->json_request;
                }

                $url = stripslashes($data->url);

                $response = wp_remote_request($url, $args);

                $response_code = wp_remote_retrieve_response_code($response);
                $response_message = wp_remote_retrieve_response_message($response);
                $response_body = wp_remote_retrieve_body($response);

                if ($response_code >= 400) {
                    $response_body = strip_tags($response_body);
                }

                $response_body = $this->decodeHebrew($response_body);

                $GLOBALS['wpdb']->insert($GLOBALS['wpdb']->prefix . 'p18a_logs', [
                    'blog_id' => get_current_blog_id(),
                    'timestamp' => current_time('mysql'),
                    'url' => $url,
                    'request_method' => $data->request_method,
                    'json_request' => $data->json_request,
                    'json_response' => $response_body,
                    'json_status' => (($response_code >= 200 && $response_code < 300)) ? 1 : 0
                ]);

                $this->notify('Repost performed');


            }

            // admin page
            add_action('admin_menu', function () {


                add_menu_page(P18A_PLUGIN_NAME, P18A_PLUGIN_NAME, 'manage_options', P18A_PLUGIN_ADMIN_URL, function () {

                    switch ($this->get('tab')) {

                        case 'transaction-log':

                            include P18A_ADMIN_DIR . 'transaction_log.php';

                            // add modal window
                            add_thickbox();

                            break;

                        case 'test-unit':
                            include P18A_ADMIN_DIR . 'test_unit.php';
                            break;

                        default:
                            include P18A_ADMIN_DIR . 'settings.php';
                    }

                });

            });

            // admin actions
            add_action('admin_init', function () {
                // enqueue admin styles
                wp_enqueue_style('p18a-admin-css', P18A_ASSET_URL . 'style.css');

                // enqueue admin scripts
                wp_enqueue_script('p18a-admin-js', P18A_ASSET_URL . 'admin.js', ['jquery']);
                wp_localize_script('p18a-admin-js', 'P18A', [
                    'nonce' => wp_create_nonce('p18a_request'),
                    'working' => __('Working', 'p18a'),
                    'json_response' => __('JSON Response', 'p18a'),
                    'json_request' => __('JSON Request', 'p18a'),
                ]);

            });

            // save settings
            if ($this->post('p18a-save-settings') && wp_verify_nonce($this->post('p18a-nonce'), 'save-settings')) {

                $this->updateOption('priority-version', $this->post('p18a-version'));
                $this->updateOption('application', $this->post('p18a-application'));
                $this->updateOption('environment', $this->post('p18a-environment'));
                $this->updateOption('language', $this->post('p18a-language'));
                $this->updateOption('url', $this->post('p18a-url'));
                $this->updateOption('username', $this->post('p18a-username'));
                $this->updateOption('password', $this->post('p18a-password'));
                $this->updateOption('sslverify', $this->post('p18a-sslverify'));

                // API
                $this->updateOption('X-App-Id', $this->post('p18a-X-App-Id'));
                $this->updateOption('X-App-Key', $this->post('p18a-X-App-Key'));

                $this->notify('Settings saved');

            }

            // handle ajax request
            add_action('wp_ajax_p18a_request', function () {

                // check nonce
                check_ajax_referer('p18a_request', 'nonce');

                parse_str($_POST['data'], $data);

                $methods = ['post', 'get', 'patch', 'delete'];

                // check api action
                if (!in_array($data['p18a-api-action'], $methods)) {
                    exit(__('Request error', 'p18a') . ': ' . __('unsupported method', 'p18a') . ' ' . $data['p18a-api-action']);
                }

                $args = [];

                if (in_array($data['p18a-api-action'], ['post', 'patch'])) {
                    $args['body'] = $data['p18a-json-request'];
                }

                $response = $this->makeRequest($data['p18a-api-action'], $data['p18a-url-addition'], $args, true);

                exit(json_encode(['status' => $response['status'], 'data' => $response['body'], 'headers' => $response['code'] . ' ' . $response['message']]));

            });

            /*
            * Create a column. And maybe remove some of the default ones
            * @param array $columns Array of all user table columns {column ID} => {column Name}
            */
            add_filter('manage_users_columns', function ($columns) {


                // unset( $columns['posts'] ); // maybe you would like to remove default columns
                $columns['registration_date'] = 'Registration date'; // add new

                return $columns;

            });

            /*
            * Fill our new column with the registration dates of the users
            * @param string $row_output text/HTML output of a table cell
            * @param string $column_id_attr column ID
            * @param int $user user ID (in fact - table row ID)
            */
            add_filter('manage_users_custom_column', function ($row_output, $column_id_attr, $user) {

                $date_format = 'j M, Y H:i';

                switch ($column_id_attr) {
                    case 'registration_date' :
                        return date($date_format, strtotime(get_the_author_meta('registered', $user)));
                        break;
                    default:
                }

                return $row_output;

            }, 10, 3);

            /*
            * Make our "Registration date" column sortable
            * @param array $columns Array of all user sortable columns {column ID} => {orderby GET-param}
            */
            add_filter('manage_users_sortable_columns', function ($columns) {


                return wp_parse_args(array('registration_date' => 'registered'), $columns);
            });


        });

    }


    /**
     * PriorityAPI frontend
     *
     */
    private function frontend()
    {
        //  API endpoints
    }


    /**
     *  PriorityAPI activation
     *
     */
    public function activation()
    {
        $table = $GLOBALS['wpdb']->prefix . 'p18a_logs';

        $sql = "CREATE TABLE $table (
            id  INT AUTO_INCREMENT,
            blog_id INT,
            timestamp DATETIME,
            url VARCHAR(1000),
            request_method VARCHAR(8),
            json_request LONGTEXT,
            json_response LONGTEXT,
            json_status TINYINT,
            PRIMARY KEY  (id)
        )";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta($sql);
    }


    /**
     *  PriorityAPI deactivation
     */
    public function deactivation()
    {
      //  housekeeping
      //  $GLOBALS['wpdb']->query('DELETE FROM ' . $GLOBALS['wpdb']->prefix . 'options WHERE option_name LIKE "' . API::optionPrefix() . '%"');
          $GLOBALS['wpdb']->query('DROP TABLE IF EXISTS ' . $GLOBALS['wpdb']->prefix . 'p18a_logs;');
    }


    /**
     * Make request
     *
     * @param [type] $method
     * @param [type] $url_addition
     * @param array $options
     */
    public function makeRequest($method, $url_addition = null, $options = [], $log = true)
    {
        $max_retries    = 3;
        $retry_count    = 0;
        $timeout_error  = 'cURL error 28: Connection timed out after';

        $url = sprintf('https://%s/odata/Priority/%s/%s/%s',
            $this->option('url'),
            $this->option('application'),
            $this->option('environment'),
            is_null($url_addition) ? '' : stripslashes($url_addition)
        );

        $args_base = [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->option('username') . ':' . $this->option('password')),
                'Content-Type'  => 'application/json',
                'X-App-Id'      => $this->option('X-App-Id'),
                'X-App-Key'     => $this->option('X-App-Key'),
            ],
            'timeout'   => 45,
            'method'    => strtoupper($method),
            'sslverify' => $this->option('sslverify', false)
        ];

        $args = array_merge($args_base, $options);

        do {
            $response = wp_remote_request($url, $args);

            $response_code    = wp_remote_retrieve_response_code($response);
            $response_message = wp_remote_retrieve_response_message($response);
            $response_body    = wp_remote_retrieve_body($response);

            if ($response_code >= 400) {
                $response_body = strip_tags($response_body);
            }

            $response_body_decoded = $this->decodeHebrew($response_body);

            $response_data = [
                'url'      => $url,
                'args'     => $args,
                'method'   => strtoupper($method),
                'body'     => $response_body_decoded,
                'body_raw' => $response_body,
                'code'     => $response_code,
                'status'   => ($response_code >= 200 && $response_code < 300) ? 1 : 0,
                'message'  => (is_wp_error($response) ? $response->get_error_message() : ($response_message ?: ''))
            ];

            // Retry logic if timeout error detected
            if (
                !$response_data['status'] &&
                isset($response_data['message']) &&
                strpos($response_data['message'], $timeout_error) !== false &&
                $retry_count < $max_retries
            ) {
                $retry_count++;
                wp_mail(
                    'elisheva.g@simplcy.co.il',
                    'Priority API Timeout Retry Attempt',
                    sprintf(
                        "Retry attempt #%d due to timeout error while making a request to the Priority API.\n\nURL: %s\nMethod: %s\n\nError Message:\n%s",
                        $retry_count,
                        $url,
                        strtoupper($method),
                        $response_data['message']
                    )
                );
                sleep(1);
            } else {
                break;
            }

        } while ($retry_count <= $max_retries);

        // Log the request if enabled
        if ($log) {
            $GLOBALS['wpdb']->insert($GLOBALS['wpdb']->prefix . 'p18a_logs', [
                'blog_id'        => get_current_blog_id(),
                'timestamp'      => current_time('mysql'),
                'url'            => $url_addition,
                'request_method' => strtoupper($method),
                'json_request'   => isset($args['body']) ? $this->decodeHebrew($args['body']) : '',
                'json_response'  => $response_data['body'] ?: ($response_data['message'] . ' ' . $response_data['code']),
                'json_status'    => $response_data['status']
            ]);
        }

        return $response_data;
    }
    /**
     * t149 Send Email Error
     */
    public function sendEmailError($email_list, $subject = '', $error = '')
    {

        $emails = []; // email list deprishiated by Roy 25.04.22
        array_push($emails, get_bloginfo('admin_email'));
        $emails=apply_filters('simplyct_sendEmail',$emails);
        if (!$emails) return;
        if ($emails && !is_array($emails)) {
            $pattern = "/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i";
            preg_match_all($pattern, $emails, $result);
            $emails = $result[0];
        }
        $to = array_unique($emails);
        $headers = [
            'content-type: text/html'
        ];
        wp_mail($to, get_bloginfo('name') . ' ' . $subject, $error, $headers);
    }

    // decode unicode hebrew text
    public function decodeHebrew($string)
    {
        return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
            return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE');
        }, $string);
    }

    /**
     * Add option
     *
     * @param string $option
     * @param string $value
     * @param string $deprecated
     * @param mixed $autoload
     */
    protected function addOption($option, $value = '', $deprecated = '', $autoload = 'yes')
    {
        return add_option(static::$prefix . $option, $value, $deprecated, $autoload);
    }


    /**
     * Get option
     *
     * @param string $option
     * @param mixed $default
     */
    public function option($option, $default = false)
    {
        return get_option(static::$prefix . $option, $default);
    }


    /**
     * Update option
     *
     * @param string $option
     * @param string $value
     * @param boolean $autoload
     */
    protected function updateOption($option, $value, $autoload = false)
    {
        return update_option(static::$prefix . $option, $value, $autoload);
    }


    /**
     * Remove option
     *
     * @param mixed $option
     */
    protected function removeOption($option)
    {
        return delete_option(static::$prefix . $option);
    }


    /**
     * Print admin notice
     *
     * @param mixed $msg
     * @param mixed $type
     */
    public function notify($msg, $type = 'success', $preformatted = true)
    {
        add_action('admin_notices', function () use ($msg, $type, $preformatted) {

            if ($preformatted) {
                echo '<div class="notice notice-' . $type . ' is-dismissible"><p><strong>' . __(ucfirst($type), 'p18a') . '</strong>: ' . __($msg, 'p18a') . '</p></div>';
            } else {
                echo '<div class="notice notice-' . $type . '"><p>' . $msg . '</p></div>';
            }

        });

    }


    /**
     * Filtered post variable
     *
     * @param mixed $key
     * @param mixed $filter
     * @param mixed $options
     */
    protected function post($key, $filter = null, $options = null)
    {
        if (is_null($filter)) {
            return isset($_POST[$key]) ? $_POST[$key] : null;
        }

        return filter_var($_POST[$key], filter_id($filter), $options);
    }


    /**
     * Filtered get variable
     *
     * @param mixed $key
     * @param mixed $filter
     * @param mixed $options
     */
    protected function get($key, $filter = null, $options = null)
    {
        if (is_null($filter)) {
            return isset($_GET[$key]) ? $_GET[$key] : null;
        }

        return filter_var($_GET[$key], filter_id($filter), $options);
    }

    /**
     * Get options prefix
     */
    public static function optionPrefix()
    {
        return static::$prefix;
    }

    /**
     * change_key variable
     *
     * @param mixed $array
     * @param mixed $old_key
     * @param mixed $new_key
     */
    public static function change_key($array, $old_key, $new_key)
    {
        if (!array_key_exists($old_key, $array))
            return $array;
        $keys = array_keys($array);
        $keys[array_search($old_key, $keys)] = $new_key;
        return array_combine($keys, $array);
    }
}
