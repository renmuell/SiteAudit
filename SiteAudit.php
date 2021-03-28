<?php
/**
 * Plugin Name: Site Audit
 * Plugin URI: http://www.renmuell.com
 * Description:Site Audit
 * Version: 0.0.1
 * Author: Rene Müller
 * Author URI: http://www.renmuell.de
 */

if (!defined('WPINC')) {
	die;
}

define('SITE_AUDIT_VERSION', '0.0.1');

add_action('admin_menu', function () {
    add_menu_page(
        __('Site Audit', 'site-audit'),
        __('Site Audit', 'site-audit'),
        'manage_options',
        'site_audit',
        'site_audit_render',
        'dashicons-forms',
        24
    );

    add_submenu_page(
        'site_audit',
		__('Create'),
		__('Create'),
		'manage_options',
		'site_audit_form',
		'site_audit_form_render'
	);

    add_submenu_page(
        'site_audit',
		__('Settings'),
		__('Settings'),
		'manage_options',
		'site_audit_settings',
		'site_audit_settings_render'
	);
});

function site_audit_render() {
    if (isset($_REQUEST['id']) && !isset($_REQUEST['action'])) {
        site_audit_render_run($_REQUEST['id']);
    } else {
        site_audit_render_table();
    }
}

function site_audit_render_run ($audit_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'site_audit';
    $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $audit_id), ARRAY_A);
    $item["settings"] = maybe_unserialize($item["settings"]);

    $posts = get_posts([
        'posts_per_page' => -1,
        'post_type'      => $item["settings"]["post_types"]
    ]);

    ?>
    <div class="wrap">
    <h2><?= $item["title"] ?> <a class="add-new-h2"
                                href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=site_audit');?>"><?= __('Back')?></a>
    </h2>

    <table>
        <thead>
            <tr>
                <th>
                    Gerät
                </th>
                <?php foreach ($item["settings"]["devices"] as $device) : ?>
                    <th>
                        <?= $device ?>
                    </th>
                <?php endforeach; ?>
            </tr>

            <tr>
                <th>
                    OS
                </th>
                <?php foreach ($item["settings"]["os"] as $os) : ?>
                    <th>
                        <?= $os ?>
                    </th>
                <?php endforeach; ?>
            </tr>


        </hhead>
        <tbody>
            <?php foreach ($posts as $post) : ?>
                <tr>
                    <th>
                        <a href="<?= get_permalink($post->ID) ?>"><?= $post->post_name ?></a>
                    </th>
                    <td>
                        <input type="checkbox"/>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    </div>
    <?php
}

function site_audit_render_table() {

    $wp_site_audit_table = new Site_Audit_Table();
    $wp_site_audit_table->prepare_items();

    $message = '';
    if ('delete' === $wp_site_audit_table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d', 'cltd_example'), count($_REQUEST['id'])) . '</p></div>';
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

function site_audit_form_render()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'site_audit'; // do not forget about tables prefix

    $message = '';
    $notice = '';

    // this is default $item which will be used for new records
    $default = array(
        'id'     => 0,
        'title'  => '',
        'settings' => [
            "devices"    => [],
            "os"         => [],
            "browser"    => [],
            "responsive" => [],
            "post_types" => [],
            "roles"      => []
        ],
        'author' => get_current_user_id()
    );

    // here we are verifying does this request is post back and have correct nonce
    if ( isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        // combine our default item with request params
        $item = shortcode_atts($default, $_REQUEST);

        $item["settings"] = maybe_serialize([
            "devices"    => $_REQUEST["devices"],
            "os"         => $_REQUEST["os"],
            "browser"    => $_REQUEST["browser"],
            "responsive" => $_REQUEST["responsive"],
            "post_types" => $_REQUEST["post_types"],
            "roles"      => $_REQUEST["roles"],
        ]);

        // validate data, and if all ok save item to database
        // if id is zero insert otherwise update
        $item_valid = site_audit_form_validate($item);
        if ($item_valid === true) {
            if ($item['id'] == 0) {
                $result = $wpdb->insert($table_name, $item);
                $item['id'] = $wpdb->insert_id;
                if ($result) {
                    $message = __('Item was successfully saved', 'cltd_example');
                } else {
                    echo $wpdb->last_error;
                    $notice = __('There was an error while saving item', 'cltd_example');
                }
            } else {
                $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                if ($result) {
                    $message = __('Item was successfully updated', 'cltd_example');
                } else {
                    $notice = __('There was an error while updating item', 'cltd_example');
                }
            }
        } else {
            // if $item_valid not true it contains error message(s)
            $notice = $item_valid;
        }
    }
    else {
        // if this is not post back we load item to edit or give new one to create
        $item = $default;
        if (isset($_REQUEST['id'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found', 'cltd_example');
            }
        }
    }

    // here we adding our custom meta box
    add_meta_box('site_audit_data_meta_box', 'Audit Data', 'site_audit_data_meta_box_handler', 'audit', 'normal', 'default');

    $settings = get_option("site_audit_settings");

    if (!is_array($settings)) {
        $settings = [
            "devices"    => [],
            "os"         => [],
            "browser"    => [],
            "responsive" => [],
            "post_types" => [],
            "roles"      => []
        ];
    }

    $item["settings"] = maybe_unserialize($item["settings"]);

    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Site Audit', 'cltd_example')?> <a class="add-new-h2"
                                href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=site_audit');?>"><?= __('Back') ?></a>
    </h2>

    <?php if (!empty($notice)): ?>
    <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif;?>
    <?php if (!empty($message)): ?>
    <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif;?>

    <form id="form" method="POST">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
        <?php /* NOTICE: here we storing id to determine will be item added or updated */ ?>
        <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>

        <div class="metabox-holder" id="poststuff">
            <div id="post-body">
                <div id="post-body-content">
                    <?php /* And here we call our custom meta box */ ?>
                    <?php do_meta_boxes('audit', 'normal', $item); ?>

                    <fieldset>
                        <legend>Geräte</legend>

                        <?php foreach ( $settings["devices"]  as $device ) : ?>

                            <div>
                                <input type="checkbox" id="devices_<?= $device ?>" name="devices[]" value="<?= $device ?>" <?= (in_array($device, $item["settings"]["devices"])) ? "checked=checked" : "" ?>>
                                <label for="devices_<?= $device ?>"><?= $device ?></label>
                            </div>

                        <?php endforeach; ?>

                    </fieldset>

                    <fieldset>
                        <legend>Os</legend>

                        <?php foreach ( $settings["os"]  as $os ) : ?>

                            <div>
                                <input type="checkbox" id="os_<?= $os ?>" name="os[]" value="<?= $os ?>" <?= (in_array($os, $item["settings"]["os"])) ? "checked=checked" : "" ?>>
                                <label for="os_<?= $os ?>"><?= $os ?></label>
                            </div>

                        <?php endforeach; ?>

                    </fieldset>

                    <fieldset>
                        <legend>Browser</legend>

                        <?php foreach ( $settings["browser"]  as $browser ) : ?>

                            <div>
                                <input type="checkbox" id="browser_<?= $browser ?>" name="browser[]" value="<?= $browser ?>" <?= (in_array($browser, $item["settings"]["browser"])) ? "checked=checked" : "" ?>>
                                <label for="browser_<?= $browser ?>"><?= $browser ?></label>
                            </div>

                        <?php endforeach; ?>

                    </fieldset>

                    <fieldset>
                        <legend>Responsive</legend>

                        <?php foreach ( $settings["responsive"]  as $responsive ) : ?>

                            <div>
                                <input type="checkbox" id="responsive_<?= $responsive ?>" name="responsive[]" value="<?= $responsive ?>" <?= (in_array($responsive, $item["settings"]["responsive"])) ? "checked=checked" : "" ?>>
                                <label for="responsive_<?= $responsive ?>"><?= $responsive ?></label>
                            </div>

                        <?php endforeach; ?>

                    </fieldset>


                    <fieldset>
                        <legend>Post-Types</legend>

                        <?php foreach ( $settings["post_types"]  as $post_type ) : ?>

                            <div>
                                <input type="checkbox" id="post_types_<?= $post_type ?>" name="post_types[]" value="<?= $post_type ?>" <?= (in_array($post_type, $item["settings"]["post_types"])) ? "checked=checked" : "" ?>>
                                <label for="post_types_<?= $post_type ?>"><?= $post_type ?></label>
                            </div>

                        <?php endforeach; ?>

                    </fieldset>

                    <fieldset>
                        <legend>Roles</legend>

                        <?php foreach ( $settings["roles"]  as $roles ) : ?>

                            <div>
                                <input type="checkbox" id="roles_<?= $roles ?>" name="roles[]" value="<?= $roles ?>" <?= (in_array($roles, $item["settings"]["roles"])) ? "checked=checked" : "" ?>>
                                <label for="roles_<?= $roles ?>"><?= $roles ?></label>
                            </div>

                        <?php endforeach; ?>

                    </fieldset>

                    <input id="author" name="author" type="hidden" value="<?php echo esc_attr($item['author'])?>">

                    <input type="submit" value="<?php _e('Save')?>" id="submit" class="button-primary" name="submit">
                </div>
            </div>
        </div>
    </form>
</div>
<?php
}

/**
 * This function renders our custom meta box
 * $item is row
 *
 * @param $item
 */
function site_audit_data_meta_box_handler($item)
{
    ?>

<table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
    <tbody>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="title"><?php _e('Title', 'cltd_example')?></label>
        </th>
        <td>
            <input id="title" name="title" type="text" style="width: 95%" value="<?php echo esc_attr($item['title'])?>"
                   size="50" class="code" placeholder="<?php _e('Title', 'site-audit')?>" required>
        </td>
    </tr>
    </tbody>
</table>
<?php
}

function site_audit_form_validate($item)
{
    $messages = array();
    if (empty($item['title'])) $messages[] = __('Title is required', 'cltd_example');
    if (empty($messages)) return true;
    return implode('<br />', $messages);
}

function site_audit_settings_render () {

    $settings = get_option("site_audit_settings");

    if (!is_array($settings)) {
        $settings = [
            "devices"    => [],
            "os"         => [],
            "browser"    => [],
            "responsive" => [],
            "post_types" => [],
            "roles"      => []
        ];
    }

    if (isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {

        $settings["devices"]    = array_map(function ($item) { return trim($item); } , explode("\n", $_REQUEST["devices"]));
        $settings["os"]         = array_map(function ($item) { return trim($item); } , explode("\n", $_REQUEST["os"]));
        $settings["browser"]    = array_map(function ($item) { return trim($item); } , explode("\n", $_REQUEST["browser"]));
        $settings["responsive"] = array_map(function ($item) { return trim($item); } , explode("\n", $_REQUEST["responsive"]));
        $settings["post_types"] = $_REQUEST["post_types"];
        $settings["roles"]      = $_REQUEST["roles"];



        update_option("site_audit_settings", $settings);
    }

    $args = array(
        'public'   => true,
        '_builtin' => true
     );
     $output = 'names';
     $operator = 'and';

     $post_types = get_post_types( $args, $output, $operator );

     $roles =  [];
     $editable_roles = get_editable_roles();
     foreach ($editable_roles as $role => $details) {
         $sub['role'] = esc_attr($role);
         $sub['name'] = translate_user_role($details['name']);
         $roles[] = $sub;
     }

    ?>
    <div class="wrap">

    <h2>
        <?= __('Settings') ?>
    </h2>

    <form id="site-audit-settings" method="Post">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>

        <label>Devices</label>
        <br>
        <textarea rows="5" id="devices" name="devices"><?= implode("\n", $settings["devices"]) ?></textarea>
        <br>

        <label>OS</label>
        <br>
        <textarea rows="5" id="os" name="os"><?= implode("\n", $settings["os"]) ?></textarea>
        <br>

        <label>Browser</label>
        <br>
        <textarea rows="5" id="browser" name="browser"><?= implode("\n", $settings["browser"]) ?></textarea>
        <br>

        <label>Responsive</label>
        <br>
        <textarea rows="5" id="responsive" name="responsive"><?= implode("\n", $settings["responsive"]) ?></textarea>
        <br>

        <fieldset>
            <legend>Post-Types</legend>

            <?php if ($post_types) : foreach ( $post_types  as $post_type ) : ?>

                <div>
                    <input type="checkbox" id="<?= $post_type ?>" name="post_types[]" value="<?= $post_type ?>" <?= (in_array($post_type, $settings["post_types"])) ? "checked=checked" : "" ?>>
                    <label for="<?= $post_type ?>"><?= $post_type ?></label>
                </div>

            <?php endforeach; endif; ?>

        </fieldset>

        <fieldset>
            <legend>Roles</legend>

            <?php if ($roles) : foreach ( $roles  as $role ) : ?>

                <div>
                    <input type="checkbox" id="<?= $role["role"] ?>" name="roles[]" value="<?= $role["role"] ?>" <?= (in_array($role["role"], $settings["roles"])) ? "checked=checked" : "" ?>>
                    <label for="<?= $role["role"] ?>"><?= $role["name"] ?></label>
                </div>

            <?php endforeach; endif; ?>

        </fieldset>

        <input type="submit" value="<?php _e('Save')?>" id="submit" class="button-primary" name="submit">

    </form>

    </div>

    <?php
}

function activate_site_audit() {
    global $wpdb;

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $site_audit_db_version = '1.0';
    $charset_collate = $wpdb->get_charset_collate();

    $table_name = $wpdb->prefix . "site_audit";
    $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            created timestamp NOT NULL default CURRENT_TIMESTAMP,
            title text NOT NULL,
            settings longtext NOt NULL,
            author bigint(20) NOT NULL,
            UNIQUE KEY id (id)
            ) $charset_collate;";
    dbDelta($sql);

    $table_name = $wpdb->prefix . "site_audit_checks";
    $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            created timestamp NOT NULL default CURRENT_TIMESTAMP,
            audit_id bigint(20) NOT NULL,
            post_id bigint(20) NOt NULL,
            settings longtext NOt NULL,
            comment text NULL,
            author bigint(20) NOT NULL,
            UNIQUE KEY id (id)
            ) $charset_collate;";
    dbDelta($sql);

    add_option('site_audit_db_version', $site_audit_db_version);
}

function deactivate_site_audit() {
}

register_activation_hook(__FILE__, 'activate_site_audit');
register_deactivation_hook(__FILE__, 'deactivate_site_audit');

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Site_Audit_Table extends WP_List_Table {

    function __construct() {
        global $status, $page;
        parent::__construct(array(
            'singular' => 'Site Audit',
            'plural' => 'Site Audits'
        ));
    }

    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }

    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }

    function column_author($item)
    {
        $user_info = get_userdata($item['author']);
        return $user_info->user_login;
    }

    function column_created($item)
    {
        return date(get_option( 'date_format' ), strtotime($item['created']));;
    }



    function column_title($item)
    {
        $actions = array(
            'edit' => sprintf('<a href="?page=site_audit_form&id=%s">%s</a>', $item['id'], __('Edit')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Delete')),
            'run' => sprintf('<a href="?page=site_audit&id=%s">%s</a>', $item['id'], __('Run'))
        );

        return sprintf('%s %s',
            $item['title'],
            $this->row_actions($actions)
        );
    }

    function extra_tablenav( $which ) {
        if ( $which == "top" ){
        }
        if ( $which == "bottom" ){
        }
    }

    function get_columns() {
        return $columns= array(
            'cb'      => '<input type="checkbox" />', //Render a checkbox instead of text
            'title'   =>__('Title'),
            'author'  => __('Author'),
            'created' => __('Created'),
        );
    }
    public function get_sortable_columns() {
        return $sortable = array(
            'title'   =>'title',
            'author'  =>'author',
            'created' =>'created'
        );
    }

    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    function process_bulk_action()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'site_audit'; // do not forget about tables prefix

        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
            }
        }
    }

    function prepare_items() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'site_audit'; // do not forget about tables prefix

        $per_page = 5; // constant, how much records will be shown per page

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        // here we configure table headers, defined in our methods
        $this->_column_headers = array($columns, $hidden, $sortable);

        // [OPTIONAL] process bulk action if any
        $this->process_bulk_action();

        // will be used in pagination settings
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");

        // prepare query params, as usual current page, order by and order direction
        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged'] - 1) * $per_page) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'id';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';

        // [REQUIRED] define $items array
        // notice that last argument is ARRAY_A, so we will retrieve array
        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);

        // [REQUIRED] configure pagination
        $this->set_pagination_args(array(
            'total_items' => $total_items, // total items defined above
            'per_page' => $per_page, // per page constant defined at top of method
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
        ));
    }
}