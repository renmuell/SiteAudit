<?php

include_once(__DIR__ . "/meta_box_post_types.php");
include_once(__DIR__ . "/meta_box_roles.php");
include_once(__DIR__ . "/meta_box-devices.php");

function site_audit_settings_render ( $settings) {

    $settings = get_option("site_audit_settings");

    if (!is_array($settings)) {
        $settings = [
            "devices"    => [],
            "post_types" => [],
            "roles"      => []
        ];
    }

    if (isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
 
        $deviceMaxNubmer = intval($_REQUEST["deviceMaxNubmer"]);
        $settings["devices"] = [];
        for ($i=1; $i <= $deviceMaxNubmer; $i++) { 
           
            if (isset($_REQUEST["device_".$i."_name"])) {
                $settings["devices"][] = [
                    "id"   => $i,
                    "name" => $_REQUEST["device_".$i."_name"],
                    "os"         => $_REQUEST["device_".$i."_os"],
                    "browser"    => array_filter(array_map(function ($item) { return trim($item); } , explode("\n", $_REQUEST["device_".$i."_browser"])), function ($item) { return !empty($item); }),
                    "responsive" => array_filter(array_map(function ($item) { return trim($item); } , explode("\n", $_REQUEST["device_".$i."_responsive"])), function ($item) { return !empty($item); })
                ];
            }
        }

        $settings["post_types"] = $_REQUEST["post_types"];
        $settings["roles"]      = $_REQUEST["roles"];

        update_option("site_audit_settings", $settings);
    }



     add_meta_box('site_audit_settings_devices_meta_box', 'Devices', 'site_audit_settings_devices_meta_box_handler', 'site_audit_settings_devices_meta_box', 'normal', 'default');
     add_meta_box('site_audit_settings_post_types_meta_box', 'Post-Type', 'site_audit_settings_post_types_meta_box_handler', 'site_audit_settings_post_types_meta_box', 'normal', 'default');
     add_meta_box('site_audit_settings_roles_meta_box', 'Roles', 'site_audit_settings_roles_meta_box_handler', 'site_audit_settings_roles_meta_box', 'normal', 'default');

    ?>
    <div class="wrap">

    <h2>
        <?= __('Settings') ?>
    </h2>

    <form id="site-audit-settings" method="Post">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
        <div class="metabox-holder" id="poststuff">
            <div id="post-body">
                <div id="post-body-content">
                    
                    <?php do_meta_boxes('site_audit_settings_devices_meta_box', 'normal', $settings); ?>
                    <?php do_meta_boxes('site_audit_settings_post_types_meta_box', 'normal', $settings); ?>
                    <?php do_meta_boxes('site_audit_settings_roles_meta_box', 'normal', $settings); ?>
                    <input type="submit" value="<?php _e('Save')?>" id="submit" class="button-primary" name="submit">
                </div>
            </div>
        </div>
    </form>

    </div>

    <?php
}
