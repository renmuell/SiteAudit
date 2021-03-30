<?php

include_once(__DIR__ . "/class-site-audit-table.php");
include_once(__DIR__ . "/meta_box_run_status.php");
include_once(__DIR__ . "/meta_box_run_table.php");

function site_audit_render() {
    if (isset($_REQUEST['id']) && !isset($_REQUEST['action'])) {
        site_audit_render_run($_REQUEST['id']);
    } else {
        site_audit_render_table();
    }
}

function site_audit_render_run ($audit_id) {
   
    global $wpdb;
    $table_name = $wpdb->prefix . SITE_AUDIT_TABLE;
    $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $audit_id), ARRAY_A);
    $item["settings"] = maybe_unserialize($item["settings"]);

    add_meta_box('site_audit_run_status_meta_box', 'Stats', 'site_audit_run_status_meta_box_hanlder', 'site_audit_run_status_meta_box', 'normal', 'default');
    add_meta_box('site_audit_run_table_meta_box', 'Audit', 'site_audit_run_table_meta_box_hanlder', 'site_audit_run_table_meta_box', 'normal', 'default');
    
    ?>
    <div class="wrap">    
        <h2><?= $item["title"] ?> <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=site_audit');?>"><?= __('Back')?></a></h2>

        <div class="metabox-holder" id="poststuff">
            <div id="post-body">
                <div id="post-body-content">
                    
                    <?php do_meta_boxes('site_audit_run_status_meta_box', 'normal', $item); ?>

                    <?php do_meta_boxes('site_audit_run_table_meta_box', 'normal', $item); ?>
                </div>
            </div>
        </div>
    

    </div>
    <?php
}

function site_audit_render_table() {

    $wp_site_audit_table = new Site_Audit_Table();
    $wp_site_audit_table->prepare_items();

    $message = '';
    if ('delete' === $wp_site_audit_table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d', 'site-audit'), count($_REQUEST['id'])) . '</p></div>';
    }

    ?>
    <div class="wrap">

        <h2>
            Site Audits
            <a class="add-new-h2"
               href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=site_audit_form');?>">
               <?= __('Create') ?>
            </a>
        </h2>
        <?php echo $message; ?>

        <form id="site-audit-table" method="GET">
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
            <?php $wp_site_audit_table->display(); ?>
        </form>

    </div>
    <?php
}
