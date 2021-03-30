<?php

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
        $table_name = $wpdb->prefix . SITE_AUDIT_TABLE;

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
        $table_name = $wpdb->prefix . SITE_AUDIT_TABLE;

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