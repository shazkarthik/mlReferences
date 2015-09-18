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
    array_unshift($items, 'files');
    array_unshift($items, rtrim(plugin_dir_path(__FILE__), '/'));
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

function endnote_get_url($name)
{
    return 'http://...';
}

function endnote_get_items($xml)
{
    $items = array();
    foreach (@simplexml_load_string($xml)->xpath('//xml/records/record') AS $key => $value) {
        try {
            $item = array();
            $item['number'] = (string) array_pop($value->xpath('rec-number'));
            $item['type'] = (string) array_pop($value->xpath('ref-type'))->attributes()['name'];
            $item['title'] = (string) array_pop($value->xpath('titles/title/style'));
            $item['year'] = (string) array_pop($value->xpath('dates/year/style'));
            $item['book_title'] = (string) array_pop($value->xpath('titles/secondary-title/style'));
            $item['journal'] = (string) array_pop($value->xpath('titles/secondary-title/style'));
            $item['volume'] = (string) array_pop($value->xpath('volume/style'));
            $item['issue'] = (string) array_pop($value->xpath('number/style'));
            $item['pages'] = (string) array_pop($value->xpath('pages/style'));
            $item['url'] = (string) array_pop($value->xpath('urls/related-urls/url/style'));
            $item['doi'] = (string) array_pop($value->xpath('urls/related-urls/url/style'));
            $item['issn'] = (string) array_pop($value->xpath('orig-pub/style'));
            $item['isbn'] = (string) array_pop($value->xpath('isbn/style'));
            $item['publisher'] = (string) array_pop($value->xpath('pages/style'));
            $item['place_published'] = (string) array_pop($value->xpath('pub-location/style'));
            $item['access_date'] = (string) array_pop($value->xpath('access-date/style'));
            $item['authors'] = array();
            foreach ($value->xpath('contributors/authors/author') AS $name) {
                $name = (string) $name->style;
                $item['authors'][] = array(
                    'name' => $name,
                    'first_name' => trim(explode(',', $name, 2)[1]),
                    'role' => 'Author',
                    'url' => endnote_get_url($name),
                );
            }
            foreach ($value->xpath('contributors/secondary-authors/author') AS $name) {
                $name = (string) $name->style;
                $item['authors'][] = array(
                    'name' => $name,
                    'first_name' => trim(explode(',', $name, 2)[1]),
                    'role' => 'Editor',
                    'url' => endnote_get_url($name),
                );
            }
            $items[] = $item;
        } catch (Exception $exception) {
            return array(sprintf('endnote_get_items() - %s', $exception->getMessage()), array());
        }
    }
    return array(array(), $items);
}

function endnote_delete($directory)
{
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($files as $file) {
        if ($file->isDir()) {
            rmdir($file->getRealPath());
        } else {
            unlink($file->getRealPath());
        }
    }
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
    `number` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `type` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
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
    `place_published` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `access_date` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    PRIMARY KEY (`id`),
    KEY `number` (`number`),
    KEY `type` (`type`),
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
    KEY `place_published` (`place_published`),
    KEY `access_date` (`access_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0;
EOD;
    $GLOBALS['wpdb']->query(sprintf($query, endnote_get_prefix()));

    $query = <<<EOD
CREATE TABLE IF NOT EXISTS `%sauthors` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `document_id` INT(11) UNSIGNED NOT NULL,
    `name` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `first_name` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `url` TEXT COLLATE utf8_unicode_ci NOT NULL,
    PRIMARY KEY (`id`),
    KEY `name` (`name`),
    KEY `first_name` (`first_name`)
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

    endnote_delete(endnote_get_directory(array()));

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
    ob_start();
    if (get_magic_quotes_gpc()) {
        $temporary = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
        while (list($key, $value) = each($temporary)) {
            foreach ($value AS $k => $v) {
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
                    list($errors, $items) = endnote_get_items(@file_get_contents($_FILES['file']['tmp_name']));
                    if ($errors) {
                        $_SESSION['endnote']['flashes'] = array(
                            'error' => 'The document was not uploaded successfully. Please try again.',
                        );
                        ?>
                        <meta
                            content="0;url=<?php echo admin_url('admin.php?action=upload&page=endnote'); ?>"
                            http-equiv="refresh"
                            >
                        <?php
                        die();
                    }
                    if (!$items) {
                        $_SESSION['endnote']['flashes'] = array(
                            'error' => 'The document was not uploaded successfully. Please try again.',
                        );
                        ?>
                        <meta
                            content="0;url=<?php echo admin_url('admin.php?action=upload&page=endnote'); ?>"
                            http-equiv="refresh"
                            >
                        <?php
                        die();
                    }
                    $GLOBALS['wpdb']->insert(
                        sprintf('%sdocuments', endnote_get_prefix()),
                        array(
                            'name' => $_FILES['file']['name'],
                        )
                    );
                    $document_id = $GLOBALS['wpdb']->insert_id;
                    endnote_get_directory(array($document_id));
                    copy(
                        $_FILES['file']['tmp_name'],
                        endnote_get_file(array($document_id, $_FILES['file']['name']))
                    );
                    foreach ($items AS $item) {
                        $GLOBALS['wpdb']->insert(
                            sprintf('%sarticles', endnote_get_prefix()),
                            array(
                                'document_id' => $document_id,
                                'number' => $item['number'],
                                'type' => $item['type'],
                                'title' => $item['title'],
                                'year' => $item['year'],
                                'book_title' => $item['book_title'],
                                'journal' => $item['journal'],
                                'volume' => $item['volume'],
                                'issue' => $item['issue'],
                                'page' => $item['page'],
                                'url' => $item['url'],
                                'doi' => $item['doi'],
                                'issn' => $item['issn'],
                                'isbn' => $item['isbn'],
                                'publisher' => $item['publisher'],
                                'place_published' => $item['place_published'],
                                'access_date' => $item['access_date'],
                            )
                        );
                        $article_id = $GLOBALS['wpdb']->insert_id;
                        foreach ($item['authors'] AS $author) {
                            $GLOBALS['wpdb']->insert(
                                sprintf('%sauthors', endnote_get_prefix()),
                                array(
                                    'document_id' => $document_id,
                                    'name' => $author['name'],
                                    'first_name' => $author['first_name'],
                                    'url' => $author['url'],
                                )
                            );
                            $GLOBALS['wpdb']->insert(
                                sprintf('%sarticles_authors', endnote_get_prefix()),
                                array(
                                    'article_id' => $article_id,
                                    'author_id' => $GLOBALS['wpdb']->insert_id,
                                    'role' => $author['role'],
                                )
                            );
                        }
                    }
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
                $document = $GLOBALS['wpdb']->get_row(
                    sprintf(
                        'SELECT * FROM `%sdocuments` WHERE `id` = %s',
                        endnote_get_prefix(),
                        intval($_REQUEST['id'])
                    ),
                    ARRAY_A
                );
                $query = <<<EOD
SELECT `%sauthors`.`name`
FROM `%sauthors`
INNER JOIN `%sarticles_authors` ON
    `%sarticles_authors`.`article_id` = %s AND `%sarticles_authors`.`author_id` = `%sauthors`.`id`
WHERE `%sarticles_authors`.`role` = '%s'
LIMIT 1
OFFSET %s
EOD;
                $xml = simplexml_load_file(endnote_get_file(array($_REQUEST['id'], $document['name'])));
                foreach ($xml->xpath('//xml/records/record') AS $key => $value) {
                    $number = (string) array_pop($value->xpath('rec-number'));
                    $article = $GLOBALS['wpdb']->get_row(
                        sprintf(
                            "SELECT * FROM `%sarticles` WHERE `document_id` = %s AND `number` = '%s'",
                            endnote_get_prefix(),
                            $document['id'],
                            $number
                        ),
                        ARRAY_A
                    );
                    if (!$article) {
                        continue;
                    }
                    $types = $value->xpath('ref-type');
                    foreach ($types AS $type) {
                        $dom = dom_import_simplexml($type);
                        $dom->setAttribute('name', $article['type']);
                    }
                    $titles = $value->xpath('titles/title/style');
                    foreach ($titles AS $title) {
                        $dom = dom_import_simplexml($title);
                        $dom->nodeValue = $article['title'];
                    }
                    $years = $value->xpath('dates/year/style');
                    foreach ($years AS $year) {
                        $dom = dom_import_simplexml($year);
                        $dom->nodeValue = $article['year'];
                    }
                    $book_titles = $value->xpath('titles/secondary-title/style');
                    foreach ($book_titles AS $book_title) {
                        $dom = dom_import_simplexml($book_title);
                        $dom->nodeValue = $article['book_title'];
                    }
                    $journals = $value->xpath('titles/secondary-title/style');
                    foreach ($journals AS $journal) {
                        $dom = dom_import_simplexml($journal);
                        $dom->nodeValue = $article['journal'];
                    }
                    $volumes = $value->xpath('volume/style');
                    foreach ($volumes AS $volume) {
                        $dom = dom_import_simplexml($volume);
                        $dom->nodeValue = $article['volume'];
                    }
                    $issues = $value->xpath('number/style');
                    foreach ($issues AS $issue) {
                        $dom = dom_import_simplexml($issue);
                        $dom->nodeValue = $article['issue'];
                    }
                    $pages = $value->xpath('pages/style');
                    foreach ($pages AS $page) {
                        $dom = dom_import_simplexml($page);
                        $dom->nodeValue = $article['page'];
                    }
                    $urls = $value->xpath('urls/related-urls/url/style');
                    foreach ($urls AS $url) {
                        $dom = dom_import_simplexml($url);
                        $dom->nodeValue = $article['url'];
                    }
                    $dois = $value->xpath('urls/related-urls/url/style');
                    foreach ($dois AS $doi) {
                        $dom = dom_import_simplexml($doi);
                        $dom->nodeValue = $article['doi'];
                    }
                    $issns = $value->xpath('orig-pub/style');
                    foreach ($issns AS $issn) {
                        $dom = dom_import_simplexml($issn);
                        $dom->nodeValue = $article['issn'];
                    }
                    $isbns = $value->xpath('isbn/style');
                    foreach ($isbns AS $isbn) {
                        $dom = dom_import_simplexml($isbn);
                        $dom->nodeValue = $article['isbn'];
                    }
                    $publishers = $value->xpath('pages/style');
                    foreach ($publishers AS $publisher) {
                        $dom = dom_import_simplexml($publisher);
                        $dom->nodeValue = $article['publisher'];
                    }
                    $places_published = $value->xpath('pub-location/style');
                    foreach ($places_published AS $place_published) {
                        $dom = dom_import_simplexml($place_published);
                        $dom->nodeValue = $article['place_published'];
                    }
                    $access_dates = $value->xpath('access-date/style');
                    foreach ($access_dates AS $access_date) {
                        $dom = dom_import_simplexml($access_date);
                        $dom->nodeValue = $article['access_date'];
                    }
                    $authors = $value->xpath('contributors/authors/author/style');
                    foreach ($authors AS $k => $v) {
                        $author = $GLOBALS['wpdb']->get_row(
                            sprintf(
                                $query,
                                endnote_get_prefix(),
                                endnote_get_prefix(),
                                endnote_get_prefix(),
                                endnote_get_prefix(),
                                $article['id'],
                                endnote_get_prefix(),
                                endnote_get_prefix(),
                                endnote_get_prefix(),
                                'Author',
                                $k
                            ),
                            ARRAY_A
                        );
                        if ($author) {
                            $dom = dom_import_simplexml($v);
                            $dom->nodeValue = $author['name'];
                        }
                    }
                    $editors = $value->xpath('contributors/secondary-authors/author/style');
                    foreach ($editors AS $k => $v) {
                        $editor = $GLOBALS['wpdb']->get_row(
                            sprintf(
                                $query,
                                endnote_get_prefix(),
                                endnote_get_prefix(),
                                endnote_get_prefix(),
                                endnote_get_prefix(),
                                $article['id'],
                                endnote_get_prefix(),
                                endnote_get_prefix(),
                                endnote_get_prefix(),
                                'Editor',
                                $k
                            ),
                            ARRAY_A
                        );
                        if ($editor) {
                            $dom = dom_import_simplexml($v);
                            $dom->nodeValue = $editor['name'];
                        }
                    }
                }
                $contents = $xml->asXML();
                ob_clean();
                header(sprintf('Content-Disposition: attachment; filename="%s"', $document['name']));
                header(sprintf('Content-Length: %d', strlen($contents)));
                echo $contents;
                break;
            case 'delete':
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $directory = endnote_get_directory(array($_REQUEST['id']));
                    endnote_delete($directory);
                    rmdir($directory);
                    $GLOBALS['wpdb']->delete(
                        sprintf('%sdocuments', endnote_get_prefix()),
                        array(
                            'id' => $_REQUEST['id'],
                        ),
                        null,
                        null
                    );
                    $_SESSION['endnote']['flashes'] = array(
                        'updated' => 'The document was deleted successfully.',
                    );
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
                        action="<?php echo admin_url(
                            sprintf('admin.php?action=delete&id=%d&page=endnote', $_REQUEST['id'])
                        ); ?>"
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
                $document = $GLOBALS['wpdb']->get_row(
                    sprintf(
                        'SELECT * FROM `%sdocuments` WHERE `id` = %s', endnote_get_prefix(), intval($_REQUEST['id'])
                    ),
                    ARRAY_A
                );
                $query = <<<EOD
SELECT
    id,
    number,
    type,
    title,
    year,
    book_title,
    journal,
    volume,
    issue,
    page,
    url,
    doi,
    issn,
    isbn,
    publisher,
    place_published,
    access_date
FROM `%sarticles`
WHERE `document_id` = %s
EOD;
                $articles = $GLOBALS['wpdb']->get_results(
                    sprintf($query, endnote_get_prefix(), $document['id']), ARRAY_A
                );
                $resource = @fopen('php://temp/maxmemory:999999999', 'w');
                @fputcsv(
                    $resource,
                    array(
                        'id' => 'ID',
                        'number' => 'Number',
                        'type' => 'Type',
                        'title' => 'Title',
                        'year' => 'Year',
                        'book_title' => 'Book Title',
                        'journal' => 'Journal',
                        'volume' => 'Volume',
                        'issue' => 'Issue',
                        'page' => 'Page',
                        'url' => 'URL',
                        'doi' => 'DOI',
                        'issn' => 'ISSN',
                        'isbn' => 'ISBN',
                        'publisher' => 'Publisher',
                        'place_published' => 'Place Published',
                        'access_date' => 'Access Date',
                    )
                );
                foreach ($articles AS $article) {
                    @fputcsv($resource, $article);
                }
                @rewind($resource);
                $articles = stream_get_contents($resource);
                @fclose($resource);
                $authors = $GLOBALS['wpdb']->get_results(
                    sprintf(
                        'SELECT id, name, first_name, url FROM `%sauthors` WHERE `document_id` = %s',
                        endnote_get_prefix(),
                        $document['id']
                    ),
                    ARRAY_A
                );
                $resource = @fopen('php://temp/maxmemory:999999999', 'w');
                @fputcsv(
                    $resource,
                    array(
                        'id' => 'ID',
                        'name' => 'Name',
                        'first_name' => 'First Name',
                        'url' => 'URL',
                    )
                );
                foreach ($authors AS $author) {
                    @fputcsv($resource, $author);
                }
                @rewind($resource);
                $authors = stream_get_contents($resource);
                @fclose($resource);
                $query = <<<EOD
SELECT id, article_id, author_id, role
FROM `%sarticles_authors`
WHERE
    `article_id` IN (
        SELECT `id` FROM `%sarticles` WHERE `document_id` = %s
    )
    AND
    `author_id` IN (
        SELECT `id` FROM `%sauthors` WHERE `document_id` = %s
    )
EOD;
                $articles_authors = $GLOBALS['wpdb']->get_results(
                    sprintf(
                        $query,
                        endnote_get_prefix(),
                        endnote_get_prefix(),
                        $document['id'],
                        endnote_get_prefix(),
                        $document['id']
                    ),
                    ARRAY_A
                );
                $resource = @fopen('php://temp/maxmemory:999999999', 'w');
                @fputcsv(
                    $resource,
                    array(
                        'id' => 'ID',
                        'article_id' => 'Article ID',
                        'author_id' => 'Author ID',
                        'role' => 'Role',
                    )
                );
                foreach ($articles_authors AS $article_author) {
                    @fputcsv($resource, $article_author);
                }
                @rewind($resource);
                $articles_authors = stream_get_contents($resource);
                @fclose($resource);

                $tempnam = tempnam(sys_get_temp_dir(), 'endnote');
                $zip = new ZipArchive();
                $zip->open($tempnam, ZipArchive::CREATE);
                $zip->addFromString('articles.csv', $articles);
                $zip->addFromString('authors.csv', $authors);
                $zip->addFromString('articles_authors.csv', $articles_authors);
                $zip->close();
                ob_clean();
                header('Content-Type: application/zip');
                header(sprintf('Content-Disposition: attachment; filename="%s.zip"', $document['name']));
                header(sprintf('Content-Length: %d', filesize($tempnam)));
                readfile($tempnam);
                unlink($tempnam);
                break;
            case 'upload_zip':
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $articles = str_getcsv(
                        @file_get_contents(sprintf('zip://%s#%s', $_FILES['file']['tmp_name'], 'articles.csv')),
                        "\n"
                    );
                    foreach ($articles AS $article) {
                        $article = str_getcsv($article, ',');
                        $GLOBALS['wpdb']->update(
                            sprintf('%sarticles', endnote_get_prefix()),
                            array(
                                'type' => $article[2],
                                'title' => $article[3],
                                'year' => $article[4],
                                'book_title' => $article[5],
                                'journal' => $article[6],
                                'volume' => $article[7],
                                'issue' => $article[8],
                                'page' => $article[9],
                                'url' => $article[10],
                                'doi' => $article[11],
                                'issn' => $article[12],
                                'isbn' => $article[13],
                                'publisher' => $article[14],
                                'place_published' => $article[15],
                                'access_date' => $article[16],
                            ),
                            array(
                                'id' => $article[0]
                            )
                        );
                    }
                    $authors = str_getcsv(
                        @file_get_contents(sprintf('zip://%s#%s', $_FILES['file']['tmp_name'], 'authors.csv')),
                        "\n"
                    );
                    foreach ($authors AS $author) {
                        $author = str_getcsv($author, ',');
                        $GLOBALS['wpdb']->update(
                            sprintf('%sauthors', endnote_get_prefix()),
                            array(
                                'name' => $author[1],
                                'first_name' => $author[2],
                                'url' => $author[3],
                            ),
                            array(
                                'id' => $author[0]
                            )
                        );
                    }
                    $articles_authors = str_getcsv(
                        @file_get_contents(
                            sprintf('zip://%s#%s', $_FILES['file']['tmp_name'], 'articles_authors.csv')
                        ),
                        "\n"
                    );
                    foreach ($articles_authors AS $article_author) {
                        $article_author = str_getcsv($article_author, ',');
                        $GLOBALS['wpdb']->update(
                            sprintf('%sarticles_authors', endnote_get_prefix()),
                            array(
                                'article_id' => $article_author[1],
                                'author_id' => $article_author[2],
                                'role' => $article_author[3],
                            ),
                            array(
                                'id' => $article_author[0]
                            )
                        );
                    }
                    $_SESSION['endnote']['flashes'] = array(
                        'updated' => 'The document was uploaded successfully.',
                    );
                    ?>
                    <meta
                        content="0;url=<?php echo admin_url('admin.php?action=&page=endnote'); ?>"
                        http-equiv="refresh"
                        >
                    <?php
                } else {
                    ?>
                    <h1>Zip file - Upload</h1>
                    <form
                        action="<?php echo admin_url(
                            sprintf('admin.php?action=upload_zip&id=%d&page=endnote', $_REQUEST['id'])
                        ); ?>"
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
                        <?php foreach ($documents AS $document): ?>
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
