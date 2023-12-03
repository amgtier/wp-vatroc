<?php
class VATROC extends VATROC_Constants
{
    public $version = '0.0.1';
    public static $log = false;
    public static $log_enabled = false;
    protected static $_instance = null;
    const LOGIN_PAGE_ID = 484;


    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    public function __construct()
    {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        $this->define('VATROC_ABSPATH', dirname(VATROC_PLUGIN_FILE) . '/');
        add_action('init', array($this, 'includes'), 8);
        self::enqueue_scripts();
    }


    public function includes()
    {
        include_once(VATROC_ABSPATH . 'includes/sso/class-sso.php');
        include_once(VATROC_ABSPATH . 'includes/sso/class-sso-discord-api.php');
        include_once(VATROC_ABSPATH . 'includes/sso/class-sso-discord.php');
        include_once(VATROC_ABSPATH . 'admin/class-admin.php');
        include_once(VATROC_ABSPATH . 'includes/class-atc.php');
        include_once(VATROC_ABSPATH . 'includes/class-devtool.php');
        include_once(VATROC_ABSPATH . 'includes/class-event.php');
        include_once(VATROC_ABSPATH . 'includes/class-form-dao.php');
        include_once(VATROC_ABSPATH . 'includes/class-form.php');
        include_once(VATROC_ABSPATH . 'includes/class-login.php');
        include_once(VATROC_ABSPATH . 'includes/class-my-sso.php');
        include_once(VATROC_ABSPATH . 'includes/class-my.php');
        include_once(VATROC_ABSPATH . 'includes/class-poll.php');
        include_once(VATROC_ABSPATH . 'includes/class-router.php');
        include_once(VATROC_ABSPATH . 'includes/rest-api/class-rest-api.php');
        include_once(VATROC_ABSPATH . 'includes/rest-api/class-rest-mission-control.php');
        include_once(VATROC_ABSPATH . 'includes/rest-api/class-rest-who.php');
        include_once(VATROC_ABSPATH . 'includes/rest-api/class-rest-utils.php');
        include_once(VATROC_ABSPATH . 'includes/shortcodes/class-shortcode-devtool.php');
        include_once(VATROC_ABSPATH . 'includes/shortcodes/class-shortcode-event.php');
        include_once(VATROC_ABSPATH . 'includes/shortcodes/class-shortcode-form.php');
        include_once(VATROC_ABSPATH . 'includes/shortcodes/class-shortcode-homepage.php');
        include_once(VATROC_ABSPATH . 'includes/shortcodes/class-shortcode-my.php');
        include_once(VATROC_ABSPATH . 'includes/shortcodes/class-shortcode-poll.php');
        include_once(VATROC_ABSPATH . 'includes/shortcodes/class-shortcode-redirect.php');
        include_once(VATROC_ABSPATH . 'includes/shortcodes/class-shortcode-roster.php');
        include_once(VATROC_ABSPATH . 'includes/shortcodes/class-shortcode-sso.php');
        include_once(VATROC_ABSPATH . 'includes/vatroc-hook-functions.php');
    }


    function enqueue_scripts()
    {
        add_action('wp_enqueue_scripts', 'enqueue_scripts', 1000000001);
        wp_enqueue_script('boot2', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js', array('jquery'), '', true);
        wp_enqueue_script('boot3', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js', array('jquery'), '', true);

        wp_enqueue_style('styles', plugin_dir_url(VATROC_PLUGIN_FILE) . 'includes/css/styles.css?v2');
        wp_enqueue_style('flex', plugin_dir_url(VATROC_PLUGIN_FILE) . 'includes/css/flex.css');
    }


    public static function actionLog($user_id, $actionKey, $actionValue)
    {
        global $wpdb;
        $result = $wpdb->insert(
            "{$wpdb->prefix}vatroc_log",
            array(
                'user' => $user_id,
                'key' => $actionKey,
                'value' => $actionValue
            )
        );
        return $result;
    }


    public static function is_admin()
    {
        return current_user_can(self::$admin_options);
    }


    public static function is_staff($uid)
    {
        $user = new WP_User($uid);
        return !empty($user->roles) &&
            is_array($user->roles) &&
            in_array('editor', $user->roles);
    }


    public static function is_priviledged_atc($uid)
    {
        $user = new WP_User($uid);
        return !empty($user->roles) &&
            is_array($user->roles) &&
            in_array('author', $user->roles);
    }


    public static function debug_section($uid = [1])
    {
        return in_array(get_current_user_id(), $uid);
    }

    public static function debug_section_unpriv()
    {
        return isset($_GET['t']);
    }

    public static function dog()
    {
        $log_path = plugin_dir_path(__DIR__) . "dog.txt";
        $prefix = 'debug';
        $identifier = null;
        if (!file_exists($log_path)) {
            fopen($log_path, 'w');
        }

        error_log(sprintf("[%s]%s%s: ", date("Y/m/d H:i:s", time()), $prefix, $identifier), 3, $log_path);
        $s = null;
        foreach (func_get_args() as $param) {
            if (is_array($param)) {
                $s = sprintf("(array)  %s ", urldecode(http_build_query($param)));
            } else if (is_bool($param)) {
                $s = sprintf("(bool)   %s ", $param ? "true" : "false");
            } else if (is_object($param)) {
                $s = sprintf("(object) %s ", urldecode(http_build_query($param)));
            } else {
                $s = sprintf("%s ", $param);
            }
            error_log($s, 3, $log_path);
        }
        error_log("\n", 3, $log_path);
    }


    public static function log($message, $level = 'info', $prefix = null, $identifier = null)
    {
        $log_path = plugin_dir_path(__DIR__) . "LogVATROC.txt";
        if ($level = 'debug') {
            if ($prefix) {
                $prefix = "[" . $prefix . "]";
            }
            if ($identifier) {
                $identifier = "[" . $identifier . "]";
            }
            if (!file_exists($log_path)) {
                fopen($log_path, 'w');
            }
            if (is_array($message)) {
                error_log(sprintf("[%s]%s%s: %s\n", date("Y/m/d H:i:s", time()), $prefix, $identifier, urldecode(http_build_query($message))), 3, $log_path);
            } else if (is_bool($message)) {
                error_log(sprintf("[%s]%s%s: %s\n", date("Y/m/d H:i:s", time()), $prefix, $identifier, $message ? "true" : "false"), 3, $log_path);
            } else {
                error_log(sprintf("[%s]%s%s: %s\n", date("Y/m/d H:i:s", time()), $prefix, $identifier, $message), 3, $log_path);
            }
            if (self::$log_enabled) {
                if (empty(self::$log)) {
                    self::$log = wc_get_logger();
                }
                self::$log->log($level, $message, array('source' => 'vatroc'));
            }
        }
    }


    private function define($name, $value)
    {
        if (!defined($name)) {
            define($name, $value);
        }
    }


    public static function get_template($path, $variables = [])
    {
        extract($variables);
        include(plugin_dir_path(__DIR__) . $path);
    }


    public static function enqueue_ajax_object($handler, $page_id = null, $variables = [])
    {
        $arguments = [
            'ajax_url' => admin_url('admin-ajax.php'),
            'page_id' => $page_id ?: get_the_ID()
        ];

        foreach ($variables as $key => $val) {
            $arguments[$key] = $val;
        }

        wp_localize_script(
            $handler,
            'ajax_object',
            $arguments,
        );
    }


    public static function get_today()
    {
        $m = date('m');
        $m = $m === 0 ? 12 : $m;
        return self::get_date(date('Y'), date('m'), date('d'));
    }


    public static function get_date($y, $m, $d)
    {
        // TODO: fix all this tedious month thing
        $_m = $m % 12;
        $_m = $_m === 0 ? 12 : $_m;
        return sprintf("%04d/%d/%02d", $y, $_m, $d);
    }

    public static function return_redirect($url)
    {
        ob_start();
        ?>
        <script>window.location.replace("<?php echo $url; ?>")</script>
        <?php
        return ob_get_clean();
    }

    public static function valid_200_response($response)
    {
        return isset($response['response']) && $response['response']['code'] === 200 && isset($response['body']);
    }

    public static function valid_response_code($response, $code)
    {
        return isset($response['response']) && $response['response']['code'] === $code && isset($response['body']);
    }

    public static function use_as_enabled()
    {
        $uid = get_current_user_ID();
        if (VATROC::debug_section([1, 2])) {
            if (isset($_REQUEST["uid"])) {
                $uid = intval($_REQUEST["uid"]);
            }
        }
        return $uid;
    }

    public static function show_message_at_render($show_message, $message)
    {
        if ($show_message) {
            return $message;
        } else {
            return null;
        }
    }

    public static function get_current_url()
    {
        return "https://" . $_SERVER[HTTP_HOST] . $_SERVER[REQUEST_URI];
    }

    public static function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+-=';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function dangerously_login($uid)
    {
        if ($uid != null) {
            wp_destroy_current_session();
            wp_clear_auth_cookie();
            wp_set_current_user(0);
            wp_set_auth_cookie($uid);
            wp_set_current_user($uid);
        }
        return;
    }

    public static function generate_uuidv4()
    {
        mt_srand(crc32(serialize(array(microtime(true), 'USER_IP', 'ETC', get_current_user_ID()))));
        return wp_generate_uuid4();
    }

    public static function uuidv4_regex()
    {
        return '[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-4[0-9A-Fa-f]{3}-[89ABab][0-9A-Fa-f]{3}-[0-9A-Fa-f]{12}';
    }
}