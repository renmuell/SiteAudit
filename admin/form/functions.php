<?php

function site_audit_form_is_device_checked ($device_id, $devices) {
    foreach ($devices as $device) {
        if ($device["id"] == $device_id) {
            return true;
        }
    }
    return false;
}

function site_audit_form_is_browser_checked ($device_id, $browser, $devices) {
    foreach ($devices as $device) {
        if ($device["id"] == $device_id) {
            return in_array($browser, $device["browser"]);
        }
    }
    return false;
}

function site_audit_form_is_responsive_checked ($device_id, $responsive, $devices) {
    foreach ($devices as $device) {
        if ($device["id"] == $device_id) {
            return in_array($responsive, $device["responsive"]);
        }
    }
    return false;
}

function site_audit_form_validate($item) {
    $messages = array();
    if (empty($item['title'])) $messages[] = __('Title is required', 'site-audit');
    if (empty($messages)) return true;
    return implode('<br />', $messages);
}