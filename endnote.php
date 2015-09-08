<?php

/**
 * Plugin Name: EndNote
 * Plugin URI: http://www.medialeg.ch
 * Description: ...coming soon...
 * Author: Reto Schneider
 * Version: 1.0
 * Author URI: http://www.medialeg.ch
 */

libxml_use_internal_errors(true);

$GLOBALS['endnote'] = array();

function endnote_get_file($items)
{
    array_unshift($items, 'files', rtrim(plugin_dir_path(__FILE__), '/'));
    return implode(DIRECTORY_SEPARATOR, $items);
}

function endnote_get_directory($items)
{
    $directory = endnote_get_file($items);
    if (!@is_dir($directory)) {
        @mkdir($directory, 0777, true);
    }
    return $directory;
}

function endnote_get_prefix()
{
    return sprintf('%sendnote_', $GLOBALS['wpdb']->prefix);
}

function endnote_register_activation_hook()
{
    endnote_register_deactivation_hook();

    $query = <<<EOD
CREATE TABLE IF NOT EXISTS `%sdocuments` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    PRIMARY KEY (`id`),
    KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0;
EOD;
    $GLOBALS['wpdb']->query(sprintf($query, endnote_get_prefix()));

    $query = <<<EOD
CREATE TABLE IF NOT EXISTS `%sarticles` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `document_id` INT(11) UNSIGNED NOT NULL,
    `document_type` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `title` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `year` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `book_title` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `journal` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `volume` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `issue` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `page` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `url` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `doi` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `issn` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `isbn` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `publisher` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `place` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `published` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `access_date` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    PRIMARY KEY (`id`),
    KEY `document_type` (`document_type`),
    KEY `title` (`title`),
    KEY `year` (`year`),
    KEY `book_title` (`book_title`),
    KEY `journal` (`journal`),
    KEY `volume` (`volume`),
    KEY `issue` (`issue`),
    KEY `page` (`page`),
    KEY `url` (`url`),
    KEY `doi` (`doi`),
    KEY `issn` (`issn`),
    KEY `isbn` (`isbn`),
    KEY `publisher` (`publisher`),
    KEY `place` (`place`),
    KEY `published` (`published`),
    KEY `access_date` (`access_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0;
EOD;
    $GLOBALS['wpdb']->query(sprintf($query, endnote_get_prefix()));

    $query = <<<EOD
CREATE TABLE IF NOT EXISTS `%sauthors` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `document_id` INT(11) UNSIGNED NOT NULL,
    `name` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `first_names` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `urls` TEXT COLLATE utf8_unicode_ci NOT NULL,
    PRIMARY KEY (`id`),
    KEY `name` (`name`),
    KEY `first_names` (`first_names`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0;
EOD;
    $GLOBALS['wpdb']->query(sprintf($query, endnote_get_prefix()));

    $query = <<<EOD
CREATE TABLE IF NOT EXISTS `%sarticles_authors` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `article_id` INT(11) UNSIGNED NOT NULL,
    `author_id` INT(11) UNSIGNED NOT NULL,
    `role` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    PRIMARY KEY (`id`),
    KEY `role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0;
EOD;
    $GLOBALS['wpdb']->query(sprintf($query, endnote_get_prefix()));

    $query = <<<EOD
ALTER TABLE `%sarticles`
    ADD CONSTRAINT `%sarticles_document_id`
    FOREIGN KEY (`document_id`)
    REFERENCES `%sdocuments` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;
EOD;
    $GLOBALS['wpdb']->query(sprintf($query, endnote_get_prefix(), endnote_get_prefix(), endnote_get_prefix()));

    $query = <<<EOD
ALTER TABLE `%sauthors`
    ADD CONSTRAINT `%sauthors_document_id`
    FOREIGN KEY (`document_id`)
    REFERENCES `%sdocuments` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;
EOD;
    $GLOBALS['wpdb']->query(sprintf($query, endnote_get_prefix(), endnote_get_prefix(), endnote_get_prefix()));

    $query = <<<EOD
ALTER TABLE `%sarticles_authors`
    ADD CONSTRAINT `%sarticles_authors_article_id`
    FOREIGN KEY (`article_id`)
    REFERENCES `%sarticles` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;
EOD;
    $GLOBALS['wpdb']->query(sprintf($query, endnote_get_prefix(), endnote_get_prefix(), endnote_get_prefix()));

    $query = <<<EOD
ALTER TABLE `%sarticles_authors`
    ADD CONSTRAINT `%sarticles_authors_author_id`
    FOREIGN KEY (`author_id`)
    REFERENCES `%sauthors` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;
EOD;
    $GLOBALS['wpdb']->query(sprintf($query, endnote_get_prefix(), endnote_get_prefix(), endnote_get_prefix()));

    endnote_get_directory(array());
}

function endnote_register_deactivation_hook()
{
    rmdir(endnote_get_directory(array()));

    $GLOBALS['wpdb']->query(sprintf('DROP TABLE IF EXISTS `%sarticles_authors`', endnote_get_prefix()));
    $GLOBALS['wpdb']->query(sprintf('DROP TABLE IF EXISTS `%sauthors`', endnote_get_prefix()));
    $GLOBALS['wpdb']->query(sprintf('DROP TABLE IF EXISTS `%sarticles`', endnote_get_prefix()));
    $GLOBALS['wpdb']->query(sprintf('DROP TABLE IF EXISTS `%sdocuments`', endnote_get_prefix()));
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
    wp_enqueue_script('all_js', sprintf('%s/endnote.js', plugins_url('/endnote')), array('jquery'));
}

function endnote_styles()
{
    wp_enqueue_style('all_css', sprintf('%s/endnote.css', plugins_url('/endnote')));
}

function endnote_admin_menu()
{
    add_menu_page('EndNote', 'EndNote', 'manage_options', '/endnote', 'endnote_dashboard', '');
    add_submenu_page('/endnote', 'F.A.Q', 'F.A.Q', 'manage_options', '/endnote/faq', 'endnote_faq');
}

function endnote_flashes()
{
    ?>
    <?php if (!empty($_SESSION['endnote']['flashes'])): ?>
        <?php foreach ($_SESSION['endnote']['flashes'] AS $key => $value): ?>
            <div class="<?php echo $key; ?>">
                <p><strong><?php echo $value; ?></strong></p>
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
    $action = $_REQUEST['action']? $_REQUEST['action']: '';
    ?>
    <div class="endnote wrap">
        <?php
        switch ($action) {
            case 'upload':
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $file = endnote_get_file(array($GLOBALS['wpdb']->insert_id, $_FILES['file']['name']));
                    if (copy($_FILES['file']['name'], $file)) {
                        $GLOBALS['wpdb']->insert(
                            sprintf('%sdocuments', endnote_get_prefix()),
                            array(
                                'name' => $_FILES['file']['name'],
                            )
                        );
                        /**
                         * 1. insert corresponding records into `articles` (using `$GLOBALS['wpdb']->insert()`)
                         * 2. insert corresponding records into `authors` (using `$GLOBALS['wpdb']->insert()`)
                         * 3. insert corresponding records into `articles_authors` (using `$GLOBALS['wpdb']->insert()`)
                         * Reference: https://bitbucket.org/kalkura/endnote/wiki/UI (see "Upload XML" section)
                         * i.e.: identical to what you do in 1.php but for all 3 tables instead of just one
                         */
                        $_SESSION['endnote']['flashes'] = array(
                            'updated' => 'The document was uploaded successfully.',
                        );
                        ?>
                        <meta
                            content="0;url=<?php echo admin_url('admin.php?action=&page=endnote'); ?>"
                            http-equiv="refresh"
                            >
                        <?php
                        die();
                    }
                    $_SESSION['endnote']['flashes'] = array(
                        'error' => 'The document was not uploaded successfully. Please try again.',
                    );
                    ?>
                    <meta
                        content="0;url=<?php echo admin_url('admin.php?action=&page=endnote'); ?>" http-equiv="refresh"
                        >
                    <?php
                    die();
                } else {
                    ?>
                    <h1>Documents - Upload</h1>
                    <form
                        action="<?php echo admin_url('admin.php?action=upload&page=endnote'); ?>"
                        enctype="multipart/form-data"
                        method="post"
                        >
                        <table class="bordered widefat wp-list-table">
                            <tr>
                                <td class="narrow" class="top"><label for="file">File</label></td>
                                <td><input id="file" name="file" type="file"></td>
                            </tr>
                        </table>
                        <p class="submit"><input class="button-primary" type="submit" value="Submit"></p>
                    </form>
                    <?php
                }
                break;
            case 'download':
                /**
                 * Reference: https://bitbucket.org/kalkura/endnote/wiki/UI (see "Download XML" section)
                 */
                break;
            case 'delete':
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $GLOBALS['wpdb']->delete(
                        sprintf('%sdocuments', endnote_get_prefix()),
                        array(
                            'id' => $_REQUEST['id'],
                        ),
                        null,
                        null
                    );
                    $_SESSION['endnote']['flashes'] = array('updated' => 'The document was deleted successfully.');
                    ?>
                    <meta
                        content="0;url=<?php echo admin_url('admin.php?action=&deleted=deleted&page=endnote'); ?>"
                        http-equiv="refresh"
                        >
                    <?php
                    die();
                } else {
                    ?>
                    <h1>Documents - Delete</h1>
                    <div class="error">
                        <p><strong>Are you sure you want to delete this document?</strong></p>
                    </div>
                    <form
                        action="<?php
                        echo admin_url(sprintf('admin.php?action=delete&id=%d&page=endnote', $_REQUEST['id']));
                        ?>"
                        method="post"
                        >
                        <p class="submit">
                            <input class="button-primary" type="submit" value="Yes">
                            <a class="float-right" href="<?php echo admin_url('admin.php?action=&page=endnote'); ?>">
                                No
                            </a>
                        </p>
                    </form>
                    <?php
                }
                break;
            case 'download_zip':
                /**
                 * Reference: https://bitbucket.org/kalkura/endnote/wiki/UI (see "Download ZIP" section)
                 */
                break;
            case 'upload_zip':
                /**
                 * Reference: https://bitbucket.org/kalkura/endnote/wiki/UI (see "Upload ZIP" section)
                 */
                break;
            default:
                $documents = $GLOBALS['wpdb']->get_results(
                    sprintf('SELECT * FROM `%sdocuments` ORDER BY `id` DESC', endnote_get_prefix()), ARRAY_A
                );
                ?>
                <h1>
                    Documents
                    <a
                        class="page-title-action"
                        href="<?php echo admin_url('admin.php?action=upload&page=endnote'); ?>"
                        >Upload</a>
                </h1>
                <?php endnote_flashes(); ?>
                <?php if ($documents): ?>
                    <table class="bordered widefat wp-list-table">
                        <tr>
                            <th class="narrow center">ID</th>
                            <th>Name</th>
                            <th class="narrow center">ZIP</th>
                            <th class="narrow center">XML</th>
                            <th class="narrow center">Actions</th>
                        </tr>
                        <?php foreach ($documents as $document): ?>
                            <tr>
                                <td class="narrow center"><?php echo $document['id']; ?></td>
                                <td><?php echo $document['name']; ?></td>
                                <td class="narrow center">
                                    <a href="<?php
                                    echo admin_url(
                                        sprintf('admin.php?action=download_zip&id=%d&page=endnote', $document['id'])
                                    );
                                    ?>">Download</a>
                                    -
                                    <a href="<?php
                                    echo admin_url(
                                        sprintf('admin.php?action=upload_zip&id=%d&page=endnote', $document['id'])
                                    );
                                    ?>">Upload</a>
                                </td>
                                <td class="narrow center">
                                    <a href="<?php
                                    echo admin_url(
                                        sprintf('admin.php?action=download&id=%d&page=endnote', $document['id'])
                                    );
                                    ?>">Download</a>
                                </td>
                                <td class="narrow center">
                                    <a href="<?php
                                    echo admin_url(
                                        sprintf('admin.php?action=delete&id=%d&page=endnote', $document['id'])
                                    );
                                    ?>">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php else: ?>
                    <div class="error">
                        <p><strong>There are no documents in the database.</strong></p>
                    </div>
                <?php endif; ?>
                <?php
                break;
        }
        ?>
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
    <div class="endnote wrap">
        <h1>F.A.Q.</h1>
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
