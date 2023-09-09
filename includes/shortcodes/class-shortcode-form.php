<?php

if (!defined('ABSPATH')) {
    exit;
}


class VATROC_Shortcode_Form extends VATROC_Form
{
    public static function init()
    {
        add_shortcode('vatroc_form', 'VATROC_Shortcode_Form::output_form');
        add_shortcode('vatroc_form_embed', 'VATROC_Shortcode_Form::output_form_embed');
        add_shortcode('vatroc_form_submission_list', 'VATROC_Shortcode_Form::output_submission_list');
        add_shortcode('vatroc_form_field', 'VATROC_Shortcode_Form::output_form_field');
        add_shortcode('vatroc_form_field_card', 'VATROC_Shortcode_Form::output_form_field');
        add_shortcode('vatroc_form_field_internal', 'VATROC_Shortcode_Form::output_form_field_internal');
        add_shortcode('vatroc_form_field_option', 'VATROC_Shortcode_Form::output_form_field_option');
        add_shortcode('vatroc_form_field_option_internal', 'VATROC_Shortcode_Form::output_form_field_option_internal');
        add_shortcode('vatroc_form_field_card_internal', 'VATROC_Shortcode_Form::output_form_field_internal');
        add_action('wp_enqueue_script', 'VATROC_Shortcode_Form::enqueue_script', 1000000001);
    }


    public static function enqueue_script()
    {
        // wp_enqueue_script( 'vatroc-form', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'includes/shortcodes/js/form.js', array( 'jquery' ), null, true );
        // wp_enqueue_style( 'vatroc-form', plugin_dir_url( VATROC_PLUGIN_FILE ) . 'includes/shortcodes/css/form.css' );
    }

    public static function output_form_embed($atts)
    {
        global $post;
        $caller_post = get_post($post);
        if ($atts["form"] != null) {
            $form_post = get_post((int) $atts["form"]);
            $post = get_post($form_post);
            $ret = do_shortcode($form_post->post_content);
            $post = get_post($caller_post);
            return $ret;
        }
    }


    public static function output_form($atts, $content = null)
    {
        $is_autosave = in_array("autosave", $atts);
        $str_autosave = $is_autosave ? "autosave" : null;
        $form_name = "shortcode-form";

        $count = VATROC_Form::count_user_submission(get_the_ID(), get_current_user_ID());
        $limit = $atts['limit'] ?: -1;
        $is_over_limit = $limit != -1 && $count >= $limit;

        // TODO: investigate if this can be put along side the vatroc_form_field below
        $content = preg_replace('@\[vatroc_form_field_card @', "[vatroc_form_field_card_internal ", $content);
        $content = preg_replace('@\[/vatroc_form_field_card]@', "[/vatroc_form_field_card_internal]", $content);
        $ret = '';
        if (isset($_GET["view_all"]) || isset($_GET["view"])) {
            if (isset($_GET["view"])) {
                $read_only_version = isset($_GET["v"]) ? intval($_GET["v"]) : 1;
                $read_only_uid = isset($_GET["u"]) && intval($_GET["u"]) > 0 ? intval($_GET["u"]) : get_current_user_ID();
                $content = preg_replace('@\[vatroc_form_field @', "[vatroc_form_field_internal read_version=$read_only_version read_uid=$read_only_uid form=$form_name $str_autosave ", $content);
                $content = preg_replace('@\[vatroc_form_field_card_internal @', "[vatroc_form_field_card_internal read_version=$read_only_version read_uid=$read_only_uid form=$form_name $str_autosave ", $content);
                $form_data = VATROC_Form::get_submission(get_the_ID(), $read_only_uid, $read_only_version - 1);
                $timestamp = date("Y/m/d H:i:s T", $form_data["timestamp"]);
                $read_only_uid_avatar = VATROC_My::html_my_avatar($read_only_uid);
                $ret_view_form = "";
                if ($form_data != null) {
                    $ret_view_form .= "<div class='view-form'>";
                    $ret_view_form .= "<p>Submssion version: $read_only_version </p>";
                    $ret_view_form .= "<p>Submitted <b>$timestamp</b> by$read_only_uid_avatar</p>";
                    $ret_view_form .= do_shortcode($content);
                    $ret_view_form .= "</div>";
                }
            }
            $ret .= self::output_submission_list($atts, $ret_view_form, true);

            wp_enqueue_style('vatroc-form', plugin_dir_url(VATROC_PLUGIN_FILE) . 'includes/shortcodes/css/form.css');
            wp_enqueue_script('vatroc-form', plugin_dir_url(VATROC_PLUGIN_FILE) . 'includes/shortcodes/js/form.js', array('jquery'), null, true);
            VATROC::enqueue_ajax_object('vatroc-form');
            return $ret;
        } else if ($count > 0) {
            $variant = 'secondary';
            if ($is_over_limit) {
                $variant = 'primary';
            }
            $ret .= do_shortcode(
                "[vatroc_collapse wrapper='card' label='已送出表單 ($count)' variant='$variant']
                [vatroc_form_submission_list]
                [/vatroc_collapse]"
            );
        }

        $required_all = $atts['required'] === "all" ? "required" : null;
        $content = preg_replace('@\[vatroc_form_field @', "[vatroc_form_field_internal form=$form_name $str_autosave $required_all ", $content);
        $content = preg_replace('@\[/vatroc_form_field]@', "[/vatroc_form_field_internal]", $content);
        $submit_label = $atts["submit_label"] ?: "Submit";
        ob_start();
        ?>
        <div class="form-submit-message hidden">
            <h2>
                <?php echo $atts["submit_message"] ?: "The form has been submitted." ?>
            </h2>
        </div>
        <?php if ($is_over_limit): ?>
            <h2>
                <?php echo $atts["limit_message"] ?: "The form has been submitted." ?>
            </h2>
        <?php else: ?>
            <form name=<?php echo $form_name; ?> class="vatroc-form">
                <i>This form is autosaved. Your response will only be submitted after "
                    <?php echo $submit_label; ?>".
                </i>
                <?php echo do_shortcode($content); ?>
                <button>
                    <?php echo $submit_label; ?>
                </button>
            </form>
            <?php
        endif;
        $ret .= ob_get_clean();
        wp_enqueue_style('vatroc-form', plugin_dir_url(VATROC_PLUGIN_FILE) . 'includes/shortcodes/css/form.css');
        wp_enqueue_script('vatroc-form', plugin_dir_url(VATROC_PLUGIN_FILE) . 'includes/shortcodes/js/form.js', array('jquery'), null, true);
        VATROC::enqueue_ajax_object('vatroc-form');
        return $ret;
    }


    public static function output_submission_list($atts = null, $content = null, $is_view_all = false)
    {
        $form = VATROC_Form::submission_list($atts, $content, $is_view_all);

        $options = explode(",", $atts["options"]);
        $render_options = [];
        if (in_array("archive", $options)) {
            array_push($render_options, self::archive_button());
        }

        global $post;
        $caller_post = get_post($post);
        $post = get_post($form["post_id"]);
        ob_start();
        VATROC::get_template("includes/shortcodes/templates/form/response-list.php", [
            "count" => count($form["all_submissions"]),
            "submissions" => $form["all_submissions"],
            "field_names" => $form["keys"],
            "view_all" => $form["can_view_all"] && $form["uid"] == 0,
            "view_form" => $content,
            "version" => intval($_GET["v"]) ?: -1,
            "options" => $render_options,
        ]);
        $post = get_post($caller_post);
        return ob_get_clean();
    }


    public static function output_form_field($atts)
    {
        $is_admin = VATROC::is_admin();

        ob_start();
        if (VATROC::debug_section()) {
            echo "<div>";
            echo "Please enclose within `[vatroc_form][/vatroc_form]`.";
            echo "</div>";
        }
        return ob_get_clean();
    }

    public static function output_form_field_option($atts)
    {
        $is_admin = VATROC::is_admin();

        ob_start();
        if (VATROC::debug_section()) {
            echo "<div>";
            echo "Please enclose within `[vatroc_form_field_card][/vatroc_form_field_card]`.";
            echo "</div>";
        }
        return ob_get_clean();
    }

    public static function output_form_field_internal($atts, $content = null)
    {
        if (in_array("hide_on_read", $atts)) {
            return;
        }
        $page_id = get_the_ID();
        $uid = get_current_user_ID();
        $str_autosave = in_array("autosave", $atts) ? "autosave" : null;
        $is_read_only = isset($atts["read_version"]);
        $is_required = in_array("required", $atts);
        $read_only_idx = 0;
        $read_only_uid = $uid;

        $form_data = VATROC_Form::get_draft($page_id, $uid);
        if ($is_read_only != null) {
            $read_only_idx = intval($atts["read_version"]) - 1;
            $read_only_uid = isset($atts["read_uid"]) &&
                intval($atts["read_uid"]) > 0 ? intval($atts["read_uid"]) : $uid;
            $form_data = VATROC_Form::get_submission($page_id, $read_only_uid, $read_only_idx);
        }

        if (!isset($atts["name"])) {
            if (VATROC::debug_section([1, 2, 503])) {
                return "<div class='input-box'>Name is missing from a form field.</div>";
            }
            return;
        }

        $name = $atts["name"];
        $content = preg_replace('@\[vatroc_form_field_option @', "[vatroc_form_field_option_internal name=$name ", $content);

        ob_start();
        $input_classes = [
            "input-box"
        ];
        echo "<div class='" . implode(' ', $input_classes) . "'>";
        switch ($atts["type"]) {
            case "text":
                echo VATROC::get_template("includes/shortcodes/templates/form/text.php", [
                    "label" => $atts["label"],
                    "placeholder" => $atts["placeholder"],
                    "autosave" => $str_autosave,
                    "read_only" => $is_read_only,
                    "form" => $atts["form"],
                    "name" => $atts["name"],
                    "value" => $form_data[$atts["name"]],
                    "disabled" => $is_read_only ? "disabled" : null,
                    "required" => $is_required ? "required" : null,
                ]);
                break;
            case "textarea":
                echo VATROC::get_template("includes/shortcodes/templates/form/textarea.php", [
                    "label" => $atts["label"],
                    "placeholder" => $atts["placeholder"],
                    "autosave" => $str_autosave,
                    "read_only" => $is_read_only,
                    "form" => $atts["form"],
                    "name" => $atts["name"],
                    "value" => $form_data[$atts["name"]],
                    "disabled" => $is_read_only ? "disabled" : null,
                    "required" => $is_required ? "required" : null,
                ]);
                break;
            case "number":
                echo VATROC::get_template("includes/shortcodes/templates/form/number.php", [
                    "label" => $atts["label"],
                    "placeholder" => $atts["placeholder"],
                    "autosave" => $str_autosave,
                    "read_only" => $is_read_only,
                    "form" => $atts["form"],
                    "name" => $atts["name"],
                    "value" => $form_data[$atts["name"]],
                    "disabled" => $is_read_only ? "disabled" : null,
                    "required" => $is_required ? "required" : null,
                ]);
                break;
            case "options":
                echo VATROC::get_template("includes/shortcodes/templates/form/options.php", [
                    "label" => $atts["label"],
                    "placeholder" => $atts["placeholder"],
                    "autosave" => $str_autosave,
                    "read_only" => $is_read_only,
                    "form" => $atts["form"],
                    "name" => $atts["name"],
                    // "value" => $form_data[  $atts[ "name" ] ],
                    "value" => "1,2",
                    "disabled" => $is_read_only ? "disabled" : null,
                    // "options" => $atts[ "options "],
                    "choices" => $atts["choices"],
                    "required" => $is_required ? "required" : null,
                ]);
                echo do_shortcode($content);
                break;
            case "date":
                echo VATROC::get_template("includes/shortcodes/templates/form/date.php", [
                    "label" => $atts["label"],
                    "placeholder" => $atts["placeholder"],
                    "autosave" => $str_autosave,
                    "read_only" => $is_read_only,
                    "form" => $atts["form"],
                    "name" => $atts["name"],
                    "value" => $form_data[$atts["name"]],
                    "disabled" => $is_read_only ? "disabled" : null,
                    "required" => $is_required ? "required" : null,
                ]);
                break;
            case "toggle":
                echo VATROC::get_template("includes/shortcodes/templates/form/toggle.php", [
                    "label" => $atts["label"],
                    "placeholder" => $atts["placeholder"],
                    "autosave" => $str_autosave,
                    "read_only" => $is_read_only,
                    "form" => $atts["form"],
                    "name" => $atts["name"],
                    "value" => $form_data[$atts["name"]] == "true",
                    "disabled" => $is_read_only ? "disabled" : null,
                    "required" => $is_required ? "required" : null,
                ]);
                break;
            default:
                if (isset($atts["label"])) {
                    $_label = $atts["label"] . ($is_required ? "<span class=required>*</span>" : null);
                    echo "<label><p class='input-label'>$_label</p></label>";

                }
                if ($is_read_only) {
                    $content = do_shortcode($content);
                    $content = preg_replace('@\<button @', "<button disabled ", $content);
                    $content = preg_replace('@\<a @', "<a disabled ", $content);
                }
                echo $content;
                break;
        }
        echo "</div>";

        return ob_get_clean();
    }

    public static function output_form_field_option_internal($atts, $content = null)
    {
        return VATROC::get_template("includes/shortcodes/templates/form/option/option.php", [
            "label" => $atts["label"],
            "name" => $atts["name"],
            "value" => null,
        ]);
    }

    public static function archive_button()
    {
        ob_start();
        ?>
        <a href="#" class="btn btn-default" disabled>Archive</a>
        <?php
        return ob_get_clean();
    }
}
;

VATROC_Shortcode_Form::init();