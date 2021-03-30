<?php

include_once(__DIR__ . "/meta_box_devices.php");
include_once(__DIR__ . "/meta_box_general.php");
include_once(__DIR__ . "/meta_box_post_types.php");
include_once(__DIR__ . "/meta_box_roles.php");
include_once(__DIR__ . "/functions.php");

function site_audit_form_render() {

    global $wpdb;
    $table_name = $wpdb->prefix . SITE_AUDIT_TABLE;

    $message = '';
    $notice = '';

    $default = array(
        'id'     => 0,
        'title'  => '',
        'settings' => [
            "devices"    => [
                "id" => -1,
                "os" => "",
                "browser" => [],
                "responsive" => []
            ],
            "post_types" => [],
            "roles"      => []
        ],
        'author' => get_current_user_id()
    );

    if ( isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
         $item = shortcode_atts($default, $_REQUEST);

        $deviceSettings = [];

        if (isset($_REQUEST['devices'])) {
            foreach ($_REQUEST["devices"] as $device) {
                $deviceSetting = [
                    "id" =>  $device,
                    "name" => $_REQUEST['devices_'.$device.'_name'],
                    "os" => $_REQUEST['devices_'.$device.'_os']
                ];
                if (isset($_REQUEST['devices_'.$device.'_browser'])) {
                    $deviceSetting["browser"] = $_REQUEST['devices_'.$device.'_browser'];
                }
                if (isset($_REQUEST['devices_'.$device.'_responsive'])) {
                    $deviceSetting["responsive"] = $_REQUEST['devices_'.$device.'_responsive'];
                }

                $deviceSettings[] = $deviceSetting;
            }
        }
        
        $item["settings"] = maybe_serialize([
            "devices"    => $deviceSettings,
            "post_types" => $_REQUEST["post_types"],
            "roles"      => $_REQUEST["roles"],
        ]);

        $item_valid = site_audit_form_validate($item);
        if ($item_valid === true) {
            if ($item['id'] == 0) {
                $result = $wpdb->insert($table_name, $item);
                $item['id'] = $wpdb->insert_id;
                if ($result) {
                    $message = __('Item was successfully saved', 'site-audit');
                } else {
                    echo $wpdb->last_error;
                    $notice = __('There was an error while saving item', 'site-audit');
                }
            } else {
                $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                if ($result) {
                    $message = __('Item was successfully updated', 'site-audit');
                } else {
                    $notice = __('There was an error while updating item', 'site-audit');
                }
            }
        } else {
            $notice = $item_valid;
        }
    } else {
        $item = $default;
        if (isset($_REQUEST['id'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found', 'site-audit');
            }
        }
    }

    add_meta_box('site_audit_general_meta_box', 'General', 'site_audit_general_meta_box_handler', 'general', 'normal', 'default');
    add_meta_box('site_audit_device_meta_box', 'Devices', 'site_audit_device_meta_box_handler', 'devices', 'normal', 'default');
    add_meta_box('site_audit_posttypes_meta_box', 'Post-Tpyes', 'site_audit_posttypes_meta_box_handler', 'posttypes', 'normal', 'default');
    add_meta_box('site_audit_roles_meta_box', 'Roles', 'site_audit_roles_meta_box_handler', 'roles', 'normal', 'default');

    $settings = get_option("site_audit_settings");

    if (!is_array($settings)) {
        $settings = [
            "devices"    => [],
            "post_types" => [],
            "roles"      => []
        ];
    }

    $item["settings"] = maybe_unserialize($item["settings"]);

    ?>
    <div class="wrap">
        <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
        <h2>
            <?php _e('Site Audit', 'site-audit')?> 
            <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=site_audit');?>">
                <?= __('Back') ?>
            </a>
        </h2>

        <?php if (!empty($notice)): ?>
            <div id="notice" class="error"><p><?php echo $notice ?></p></div>
        <?php endif;?>

        <?php if (!empty($message)): ?>
            <div id="message" class="updated"><p><?php echo $message ?></p></div>
        <?php endif;?>

        <form id="form" method="POST">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
            <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>

            <div class="metabox-holder" id="poststuff">
                <div id="post-body">
                    <div id="post-body-content">

                        <?php do_meta_boxes('general', 'normal', $item); ?>
                        <?php do_meta_boxes('devices', 'normal', ["item" => $item, "settings" => $settings]); ?>
                        <?php do_meta_boxes('posttypes', 'normal', ["item" => $item, "settings" => $settings]); ?>
                        <?php do_meta_boxes('roles', 'normal', ["item" => $item, "settings" => $settings]); ?>

                        <input id="author" name="author" type="hidden" value="<?php echo esc_attr($item['author'])?>">

                        <input type="submit" value="<?php _e('Save')?>" id="submit" class="button-primary" name="submit">
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php
}
