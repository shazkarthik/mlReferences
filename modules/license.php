<?php

function mlReferences_license_verify() {
    $contents = get_option('mlReferences_license_contents', '');
    if ($contents === '') {
        update_option('mlReferences_license_status', 'Off', 'no');
        return;
    }
    $url = 'https://plugins.medialeg.ch/licenses';
    $options = array(
        'body' => array(
            'contents' => $contents,
        ),
        'method' => 'POST',
    );
    $response = wp_remote_request($url, $options);
    $body = wp_remote_retrieve_body($response);
    $json = json_decode($body, true);
    $status_code = wp_remote_retrieve_response_code($response);
    $status = mlReferences_license_get_status($json, $status_code);
    $json = json_encode($json);
    update_option('mlReferences_license_json', $json, 'no');
    update_option('mlReferences_license_status', $status, 'no');
}

function mlReferences_license_update($file) {
    $contents = @file_get_contents($file);
    $contents = trim($contents);
    update_option('mlReferences_license_contents', $contents, 'no');
    mlReferences_license_verify();
}

function mlReferences_license_is_valid() {
    $contents = get_option('mlReferences_license_contents', '');
    if ($contents === '') {
        return false;
    }
    $status = get_option('mlReferences_license_status', 'Off');
    if ($status === 'Off') {
        return false;
    }
    return true;
}

function mlReferences_license_get_status($json, $status_code) {
    if ($status_code !== 200) {
        return 'Off';
    }
    if (!$json['url']) {
        return 'Off';
    }
    $url = get_site_url();
    if ($json['url'] !== $url) {
        return 'Off';
    }
    if (!$json['status']) {
        return 'Off';
    }
    if ($json['status'] !== 'On') {
        return 'Off';
    }
    if (!$json['mlReferences_is_enabled']) {
        return 'Off';
    }
    return 'On';
}
