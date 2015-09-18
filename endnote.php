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

function endnote_get_items($xml)
{
    $items = array();
    foreach (@simplexml_load_string($xml)->xpath('//xml/records/record') AS $key => $value) {
        try {
            $id = (string) array_pop($value->xpath('rec-number'));
            $type = (string) array_pop($value->xpath('ref-type'))->attributes()['name'];
            $title = (string) array_pop($value->xpath('titles/title/style'));
            $year = (string) array_pop($value->xpath('dates/year/style'));
            $book_title = (string) array_pop($value->xpath('titles/secondary-title/style'));
            $journal = (string) array_pop($value->xpath('titles/secondary-title/style'));
            $volume = (string) array_pop($value->xpath('volume/style'));
            $issue = (string) array_pop($value->xpath('number/style'));
            $pages = (string) array_pop($value->xpath('pages/style'));
            $url = (string) array_pop($value->xpath('urls/related-urls/url/style'));
            $doi = (string) array_pop($value->xpath('urls/related-urls/url/style'));
            $issn = (string) array_pop($value->xpath('orig-pub/style'));
            $isbn = (string) array_pop($value->xpath('isbn/style'));
            $publisher = (string) array_pop($value->xpath('pages/style'));
            $place_published = (string) array_pop($value->xpath('pub-location/style'));
            $access_date = (string) array_pop($value->xpath('access-date/style'));
            $authors_primary = array();
            foreach ($value->xpath('contributors/authors/author') as $name) {
                $authors_primary[] = (string) $name->style;
            }
            $authors_secondary = array();
            foreach ($value->xpath('contributors/secondary-authors/author') as $name) {
                $authors_secondary[] = (string) $sec_name->style;
            }
            $items[] = array(
                $id,
                $type,
                $title,
                $year,
                $book_title,
                $journal,
                $volume,
                $issue,
                $pages,
                $url,
                $doi,
                $issn,
                $isbn,
                $publisher,
                $place_published,
                $access_date,
                $authors_primary,
                $authors_secondary,
            );
        } catch (Exception $exception) {
            return array(sprintf('endnote_get_items() - %s', $exception->getMessage()), array());
        }
    }
    return array(array(), $items);
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
    ob_start();
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
                    $GLOBALS['wpdb']->insert(
                        sprintf('%sdocuments', endnote_get_prefix()),
                        array(
                            'name' => $_FILES['file']['name'],
                        )
                    );
                    endnote_get_directory(array($GLOBALS['wpdb']->insert_id));
                    $file = endnote_get_file(array($GLOBALS['wpdb']->insert_id, $_FILES['file']['name']));
                    copy($_FILES['file']['tmp_name'], $file);
                    list($errors, $items) = endnote_get_items(@file_get_contents($file));
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
                    $document_id = $GLOBALS['wpdb']->insert_id;
                    foreach ($items as $item) {
                        $GLOBALS['wpdb']->insert(
                            sprintf('%sarticles', endnote_get_prefix()),
                            array(
                                'id' => $item[0],
                                'document_id' => $document_id,
                                'type' => $item[1],
                                'title' => $item[2],
                                'year' => $item[3],
                                'book_title' => $item[4],
                                'journal' => $item[5],
                                'volume' => $item[6],
                                'issue' => $item[7],
                                'page' => $item[8],
                                'url' => $item[9],
                                'doi' => $item[10],
                                'issn' => $item[11],
                                'isbn' => $item[12],
                                'publisher' => $item[13],
                                'place_published' => $item[14],
                                'access_date' => $item[15],
                            )
                        );
                        $article_id = $GLOBALS['wpdb']->insert_id;
                        if ($item[16]) {
                            foreach ($item[16] as $name) {
                                $GLOBALS['wpdb']->insert(
                                    sprintf('%sauthors', endnote_get_prefix()),
                                    array(
                                        'document_id' => $document_id,
                                        'name' => $name,
                                        'first_names' => explode(',', $name, 2)[1],
                                    )
                                );
                                $GLOBALS['wpdb']->insert(
                                    sprintf('%sarticles_authors', endnote_get_prefix()),
                                    array(
                                        'article_id' => $article_id,
                                        'author_id' => $GLOBALS['wpdb']->insert_id,
                                        'role' => 'Author',
                                    )
                                );
                            }
                        }
                        if ($item[17]) {
                            foreach ($item[17] as $name) {
                                $GLOBALS['wpdb']->insert(
                                    sprintf('%sauthors', endnote_get_prefix()),
                                    array(
                                        'document_id' => $document_id,
                                        'name' => $name,
                                        'first_names' => explode(',', $name, 2)[1],
                                    )
                                );
                                $GLOBALS['wpdb']->insert(
                                    sprintf('%sarticles_authors', endnote_get_prefix()),
                                    array(
                                        'article_id' => $article_id,
                                        'author_id' => $GLOBALS['wpdb']->insert_id,
                                        'role' => 'Editor',
                                    )
                                );
                            }
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
                $doc = new DomDocument();
                $file_name = $GLOBALS['wpdb']->get_var(sprintf(
                    "SELECT name FROM %sdocuments WHERE id=%s",endnote_get_prefix(), $_REQUEST['id']
                ));
                $tmp_xml_file = tempnam(sys_get_temp_dir(), '');
                $xml = simplexml_load_file(
                    endnote_get_file(array($_REQUEST['id'], $file_name))
                );
                $articles_results = $GLOBALS['wpdb']->get_results(sprintf(
                    "SELECT
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
                    FROM %sarticles
                    WHERE document_id=%s",
                    endnote_get_prefix(),
                    $_REQUEST['id']
                ), ARRAY_A);
                $authors_results = $GLOBALS['wpdb']->get_results(sprintf(
                    "SELECT wp_endnote_authors.name
                    FROM wp_endnote_authors JOIN wp_endnote_articles_authors
                    ON wp_endnote_authors.id = wp_endnote_articles_authors.author_id
                    WHERE wp_endnote_articles_authors.role='Author'",
                    endnote_get_prefix(), endnote_get_prefix()
                ), ARRAY_A);
                $editors_results = $GLOBALS['wpdb']->get_results(sprintf(
                    "SELECT wp_endnote_authors.name
                    FROM wp_endnote_authors JOIN wp_endnote_articles_authors
                    ON wp_endnote_authors.id = wp_endnote_articles_authors.author_id
                    WHERE wp_endnote_articles_authors.role='Editor'",
                    endnote_get_prefix(), endnote_get_prefix()
                ), ARRAY_A);
                foreach ($xml->xpath('//xml/records/record') as $value) {
                    foreach ($articles_results as $articles_result) {
                        $types = $value->xpath('ref-type');
                        foreach ($types as $type) {
                            $dom=dom_import_simplexml($type);
                            $dom->setAttribute('name', $articles_result['type']);
                        }
                        $titles = $value->xpath('titles/title/style');
                        foreach ($titles as $title) {
                            $dom=dom_import_simplexml($title);
                            $dom->nodeValue = $articles_result['title'];
                        }
                        $years = $value->xpath('dates/year/style');
                        foreach ($years as $year) {
                            $dom=dom_import_simplexml($year);
                            $dom->nodeValue = $articles_result['year'];
                        }
                        $book_titles = $value->xpath('titles/secondary-title/style');
                        foreach ($book_titles as $book_title) {
                            $dom=dom_import_simplexml($book_title);
                            $dom->nodeValue = $articles_result['book_title'];
                        }
                        $journals = $value->xpath('titles/secondary-title/style');
                        foreach ($journals as $journal) {
                            $dom=dom_import_simplexml($journal);
                            $dom->nodeValue = $articles_result['journal'];
                        }
                        $volumes = $value->xpath('volume/style');
                        foreach ($volumes as $volume) {
                            $dom=dom_import_simplexml($volume);
                            $dom->nodeValue = $articles_result['volume'];
                        }
                        $issues = $value->xpath('number/style');
                        foreach ($issues as $issue) {
                            $dom=dom_import_simplexml($issue);
                            $dom->nodeValue = $articles_result['issue'];
                        }
                        $pages = $value->xpath('pages/style');
                        foreach ($pages as $page) {
                            $dom=dom_import_simplexml($page);
                            $dom->nodeValue = $articles_result['page'];
                        }
                        $urls = $value->xpath('urls/related-urls/url/style');
                        foreach ($urls as $url) {
                            $dom=dom_import_simplexml($url);
                            $dom->nodeValue = $articles_result['url'];
                        }
                        $dois = $value->xpath('urls/related-urls/url/style');
                        foreach ($dois as $doi) {
                            $dom=dom_import_simplexml($doi);
                            $dom->nodeValue = $articles_result['doi'];
                        }
                        $issns = $value->xpath('orig-pub/style');
                        foreach ($issns as $issn) {
                            $dom=dom_import_simplexml($issn);
                            $dom->nodeValue = $articles_result['issn'];
                        }
                        $isbns = $value->xpath('isbn/style');
                        foreach ($isbns as $isbn) {
                            $dom=dom_import_simplexml($isbn);
                            $dom->nodeValue = $articles_result['isbn'];
                        }
                        $publishers = $value->xpath('pages/style');
                        foreach ($publishers as $publisher) {
                            $dom=dom_import_simplexml($publisher);
                            $dom->nodeValue = $articles_result['publisher'];
                        }
                        $places_published = $value->xpath('pub-location/style');
                        foreach ($places_published as $place_published) {
                            $dom=dom_import_simplexml($place_published);
                            $dom->nodeValue = $articles_result['place_published'];
                        }
                        $access_dates = $value->xpath('access-date/style');
                        foreach ($access_dates as $access_date) {
                            $dom=dom_import_simplexml($access_date);
                            $dom->nodeValue = $articles_result['access_date'];
                        }
                        array_shift ($articles_results);
                        break;
                    }
                    $authors = $value->xpath('contributors/authors/author/style');
                    foreach ($authors as $author) {
                        foreach ($authors_results as $authors_result) {
                            $dom=dom_import_simplexml($author);
                            $dom->nodeValue = $authors_result['name'];
                            break;
                        }
                        array_shift ($authors_results);
                    }
                    $editors = $value->xpath('contributors/secondary-authors/author/style');
                    foreach ($editors as $editor) {
                        foreach ($editors_results as $editors_result) {
                            $dom=dom_import_simplexml($editor);
                            $dom->nodeValue = $editors_result['name'];
                            break;
                        }
                        array_shift ($editors_results);
                    }
                }
                file_put_contents($tmp_xml_file, $xml->asXML());

                ob_clean();

                header(sprintf('Content-disposition: attachment; filename="%s"', $file_name));
                header(sprintf('Content-Length: %d', filesize($tmp_xml_file)));
                readfile($tmp_xml_file);

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
                $document = $GLOBALS['wpdb']->get_results(
                    sprintf('SELECT * FROM `%sdocuments` ORDER BY `id` DESC', endnote_get_prefix()), ARRAY_A
                );
                $articles = @fopen('php://temp/maxmemory:999999999', 'w');
                $results = $GLOBALS['wpdb']->get_results(sprintf(
                    "SELECT
                        id,
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
                    FROM %sarticles
                    WHERE document_id=%s",
                    endnote_get_prefix(), $_REQUEST['id']
                ), ARRAY_A);
                $headers = array(
                    'id' => 'ID',
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
                );
                @fputcsv($articles, $headers);
                foreach ($results as $result) {
                    @fputcsv($articles, $result);
                }
                @rewind($articles);
                $articles_csv = stream_get_contents($articles);
                @fclose($articles);

                $authors = @fopen('php://temp/maxmemory:999999999', 'w');
                $results = $GLOBALS['wpdb']->get_results(sprintf(
                    "SELECT id,name,first_names FROM %sauthors WHERE document_id=%s",
                    endnote_get_prefix(), $_REQUEST['id']
                ), ARRAY_A);
                $headers = array(
                    'id' => 'ID',
                    'name' => 'Name',
                    'first_names' => 'First Name',
                );
                @fputcsv($authors, $headers);
                foreach ($results as $result) {
                    @fputcsv($authors, $result);
                }
                @rewind($authors);
                $authors_csv = stream_get_contents($authors);
                @fclose($authors);

                $articles_authors = @fopen('php://temp/maxmemory:999999999', 'w');
                $results = $GLOBALS['wpdb']->get_results(sprintf(
                    "SELECT * FROM %sarticles_authors
                    WHERE
                        article_id IN (
                            SELECT id FROM %sarticles WHERE document_id='%s'
                        )
                        AND
                        author_id IN (
                            SELECT id FROM %sauthors WHERE document_id='%s'
                        )",
                    endnote_get_prefix(), endnote_get_prefix(), $_REQUEST['id'], endnote_get_prefix(), $_REQUEST['id']
                ), ARRAY_A);
                $headers = array(
                    'id' => 'ID',
                    'article_id' => 'Article_id',
                    'author_id' => 'Author_id',
                    'role' => 'Role',
                );
                @fputcsv($articles_authors, $headers);
                foreach ($results as $result) {
                    @fputcsv($articles_authors, $result);
                }
                @rewind($articles_authors);
                $articles_authors_csv = stream_get_contents($articles_authors);
                @fclose($articles_authors);

                $file = tempnam(sys_get_temp_dir(), 'endnote');

                $zip = new ZipArchive();
                $zip->open($file, ZipArchive::CREATE);
                $zip->addFromString('articles.csv', $articles_csv);
                $zip->addFromString('authors.csv', $authors_csv);
                $zip->addFromString('articles_authors.csv', $articles_authors_csv);
                $zip->close();

                ob_clean();

                header('Content-Type: application/zip');
                header(sprintf('Content-disposition: attachment; filename="%s.zip"', $_REQUEST['id']));
                header(sprintf('Content-Length: %d', filesize($file)));
                readfile($file);

                unlink($file);

                break;
            case 'upload_zip':
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $articles_csv = file_get_contents(sprintf(
                        'zip://%s#%s', $_FILES['file']['tmp_name'],
                        'articles.csv'
                    ));
                    $articles = str_getcsv($articles_csv, "\n");
                    foreach ($articles as $article) {
                        $articles = str_getcsv($article, ',');
                        $GLOBALS['wpdb']->query(sprintf(
                            "UPDATE %sarticles
                            SET
                                type='%s',
                                title='%s',
                                year='%s',
                                book_title='%s',
                                journal='%s',
                                volume='%s',
                                issue='%s',
                                page='%s',
                                url='%s',
                                doi='%s',
                                issn='%s',
                                isbn='%s',
                                publisher='%s',
                                place_published='%s',
                                access_date='%s'
                            WHERE id='%d'",
                            endnote_get_prefix(),
                            $articles[1],
                            $articles[2],
                            $articles[3],
                            $articles[4],
                            $articles[5],
                            $articles[6],
                            $articles[7],
                            $articles[8],
                            $articles[9],
                            $articles[10],
                            $articles[11],
                            $articles[12],
                            $articles[13],
                            $articles[14],
                            $articles[15],
                            $articles[0]
                        ));
                    }
                    $authors_csv = file_get_contents(sprintf(
                        'zip://%s#%s', $_FILES['file']['tmp_name'],
                        'authors.csv'
                    ));
                    $authors = str_getcsv($authors_csv, "\n");
                    foreach ($authors as $author) {
                        $authors = str_getcsv($author, ',');
                        $GLOBALS['wpdb']->query(sprintf(
                            "UPDATE %sauthors
                            SET
                                name='%s',
                                first_names='%s',
                                urls='%s'
                            WHERE id='%d'",
                            endnote_get_prefix(),
                            $authors[2],
                            $authors[3],
                            $authors[4],
                            $authors[0]
                        ));
                    }

                    $articles_authors_csv = file_get_contents(sprintf(
                        'zip://%s#%s', $_FILES['file']['tmp_name'],
                        'articles_authors.csv'
                    ));
                    $articles_authors = str_getcsv($articles_authors_csv, "\n");
                    foreach ($articles_authors as $articles_author) {
                        $articles_authors = str_getcsv($articles_author, ',');
                        $GLOBALS['wpdb']->query(sprintf(
                            "UPDATE %sarticles_authors
                            SET
                                article_id='%s',
                                author_id='%s',role='%s'
                            WHERE id='%d'",
                            endnote_get_prefix(),
                            $articles_authors[1],
                            $articles_authors[2],
                            $articles_authors[3],
                            $articles_authors[0]
                        ));
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
                        action="<?php echo admin_url(sprintf(
                            'admin.php?action=upload_zip&id=%d&page=endnote', $_REQUEST['id']
                        )); ?>"
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
