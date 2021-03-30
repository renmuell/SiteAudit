<?php

include_once(__DIR__ . "/form/form.php");
include_once(__DIR__ . "/tableAndRun/tableAndRun.php");
include_once(__DIR__ . "/settings/settings.php");

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
