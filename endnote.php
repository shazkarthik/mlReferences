<?php

/*
Plugin Name: EndNote
Plugin URI: http://www.medialeg.ch
Description: ...coming soon...
Author: Reto Schneider
Version: 1.0
Author URI: http://www.medialeg.ch
*/

$GLOBALS['endnote'] = array();

function endnote_get_directory($items)
{
    $directory = endnote_get_file($items);
    if (!@is_dir($directory)) {
        @mkdir($directory, 0777, true);
    }
    return $directory;
}

function endnote_get_file($items)
{
    array_unshift($items, 'files');
    array_unshift($items, rtrim(plugin_dir_path(__FILE__), '/'));
    return implode(DIRECTORY_SEPARATOR, $items);
}

function endnote_get_items($xml)
{
    $items = array(array('ID', 'Type of Document', 'Title', 'Year'));
    foreach (@simplexml_load_string($xml)->xpath('//xml/records/record') as $key => $value) {
        try {
            $rec_number = (string) array_pop($value->xpath('rec-number'));
        } catch (Exception $exception) {
            return array(sprintf('endnote_get_items() - %s', $exception->getMessage()), $items);
        }
        try {
            $ref_type = (string) array_pop($value->xpath('ref-type'))->attributes()['name'];
        } catch (Exception $exception) {
            return array(sprintf('endnote_get_items() - %s', $exception->getMessage()), $items);
        }
        try {
            $title = (string) array_pop($value->xpath('titles/title/style'));
        } catch (Exception $exception) {
            return array(sprintf('endnote_get_items() - %s', $exception->getMessage()), $items);
        }
        try {
            $year = (string) array_pop($value->xpath('dates/year/style'));
        } catch (Exception $exception) {
            return array(sprintf('endnote_get_items() - %s', $exception->getMessage()), $items);
        }
        $items[] = array($rec_number, $ref_type, $title, $year);
    }

    return array(array(), $items);
}

function endnote_register_deactivation_hook()
{
    rmdir(endnote_get_directory(array()));
}

function endnote_register_activation_hook()
{
    endnote_register_deactivation_hook();

    endnote_get_directory(array());
}

function endnote_init()
{
    if (!session_id()) {
        session_start();
    }
    if (get_magic_quotes_gpc()) {
        $temporary = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
        while (list($key, $value) = each($temporary)) {
            foreach ($value as $k => $v) {
                unset($temporary[$key][$k]);
                if (is_array($v)) {
                    $temporary[$key][stripslashes($k)] = $v;
                    $temporary[] = &$temporary[$key][stripslashes($k)];
                } else {
                    $temporary[$key][stripslashes($k)] = stripslashes($v);
                }
            }
        }
        unset($temporary);
    }
    add_action('wp_enqueue_scripts', 'endnote_scripts');
    add_action('wp_enqueue_scripts', 'endnote_styles');
}

function endnote_admin_init()
{
    add_action('admin_print_scripts', 'endnote_scripts');
    add_action('admin_print_styles', 'endnote_styles');
}

function endnote_scripts()
{
    wp_enqueue_script('all_js', sprintf('%s/resources/all.js', plugins_url('/endnote')), array('jquery'));
}

function endnote_styles()
{
    wp_enqueue_style('all_css', sprintf('%s/resources/all.css', plugins_url('/endnote')));
}

function endnote_admin_menu()
{
    add_menu_page('EndNote', 'EndNote', 'manage_options', '/endnote', 'endnote_dashboard', '');
    add_submenu_page('/endnote', 'Dashboard', 'Dashboard', 'manage_options', '/endnote', 'endnote_dashboard');
    add_submenu_page('/endnote', 'F.A.Q', 'F.A.Q', 'manage_options', '/endnote/faq', 'endnote_faq');
}

function endnote_flashes()
{
    ?>
    <?php if (!empty($_SESSION['endnote']['flashes'])): ?>
        <?php foreach ($_SESSION['endnote']['flashes'] AS $flash): ?>
            <div class="<?php echo $flash[0]; ?>">
                <p><strong><?php echo $flash[1]; ?></strong></p>
            </div>
        <?php endforeach; ?>
        <?php $_SESSION['endnote']['flashes'] = array(); ?>
    <?php endif; ?>
    <?php
}

function endnote_dashboard()
{
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permissions to access this page.');
    }
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $_SESSION['endnote']['flashes'] = array();
        $xml = endnote_get_file(array($_FILES['file']['name']));
        if (!copy($_FILES['file']['tmp_name'],  $xml)) {
            $_SESSION['endnote']['flashes'][] = array('error', 'endnote_dashboard() - Invalid copy()');
            ?>
            <meta content="0;url=<?php echo admin_url('admin.php?page=endnote');?>" http-equiv="refresh">
            <?php
            die();
        }
        list($errors, $items) = endnote_get_items(file_get_contents($_FILES['file']['tmp_name']));
        if ($errors) {
            foreach ($errors AS $error) {
                $_SESSION['endnote']['flashes'][] = array('error', $error);
            }
            ?>
            <meta content="0;url=<?php echo admin_url('admin.php?page=endnote');?>" http-equiv="refresh">
            <?php
            die();
        }
        $csv = preg_replace('#\.xml$#', '.csv', $xml);
        $resource = @fopen($csv, 'w');
        if (!$resource) {
            $_SESSION['endnote']['flashes'][] = array('error', 'endnote_dashboard() - Invalid fopen()');
            ?>
            <meta content="0;url=<?php echo admin_url('admin.php?page=endnote');?>" http-equiv="refresh">
            <?php
            die();
        }
        foreach ($items as $item) {
            if (!fputcsv($resource, $item)) {
                $_SESSION['endnote']['flashes'][] = array('error', 'endnote_dashboard() - Invalid fputcsv()');
                ?>
                <meta content="0;url=<?php echo admin_url('admin.php?page=endnote');?>" http-equiv="refresh">
                <?php
                die();
            }
        }
        if (!@fclose($resource)) {
            $_SESSION['endnote']['flashes'][] = array('error', 'endnote_dashboard() - Invalid fclose()');
            ?>
            <meta content="0;url=<?php echo admin_url('admin.php?page=endnote');?>" http-equiv="refresh">
            <?php
            die();
        }
        $_SESSION['endnote']['flashes'][] = array('updated', 'Your file was processed successfully.');
        ?>
        <div class="endnote">
            <h2>Dashboard</h2>
            <?php endnote_flashes(); ?>
            <div class="welcome-panel">
            <p>
                <a href="<?php echo sprintf('%s/%s', plugins_url('endnote/files'), basename($csv)); ?>">
                    Click here
                </a>
                to download the processed file.
            </p>
        </div>
        <?php
        die();
    }
    ?>
    <div class="endnote">
        <h2>Dashboard</h2>
        <?php endnote_flashes(); ?>
        <div class="welcome-panel">
            <form
                action="<?php echo admin_url('admin.php?page=endnote'); ?>"
                enctype="multipart/form-data"
                method="post"
                >
                <p>
                    <input id="file" name="file" type="file">
                    Choose a valid EndNote XML file.
                </p>
                <hr>
                <p>
                    <input class="button-primary" type="submit" value="Submit">
                </p>
            </form>
        </div>
    </div>
    <?php
}

function endnote_faq()
{
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permissions to access this page.');
    }
    ?>
    <div class="endnote">
        <h2>F.A.Q.</h2>
        <div class="welcome-panel">
            <p class="center">...coming soon...</p>
        </div>
    </div>
    <?php
}

register_activation_hook(__FILE__, 'endnote_register_activation_hook');
register_deactivation_hook(__FILE__, 'endnote_register_deactivation_hook');

add_action('init', 'endnote_init');

add_action('admin_init', 'endnote_admin_init');
add_action('admin_menu', 'endnote_admin_menu');

add_shortcode('endnote', 'endnote_shortcode');
