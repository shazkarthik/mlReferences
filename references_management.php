<?php

/**
 * Plugin Name: References Management
 * Plugin URI: http://www.medialeg.ch
 * Description: ...coming soon...
 * Author: Reto Schneider
 * Version: 1.0
 * Author URI: http://www.medialeg.ch
 */

libxml_use_internal_errors(true);

$GLOBALS['references_management'] = array();

function references_management_filters_author($item)
{
    return $item['role'] === 'Author';
}

function references_management_filters_editor($item)
{
    return $item['role'] === 'Editor';
}

function references_management_get_file($items)
{
    array_unshift($items, 'files');
    array_unshift($items, rtrim(plugin_dir_path(__FILE__), '/'));
    return implode(DIRECTORY_SEPARATOR, $items);
}

function references_management_get_directory($items)
{
    $directory = references_management_get_file($items);
    if (!@is_dir($directory)) {
        @mkdir($directory, 0777, true);
    }
    return $directory;
}

function references_management_get_initials($name)
{
    $initials = array();
    $name = explode(' ', $name);
    if (!empty($name)) {
        foreach ($name AS $key => $value) {
            $initials[] = sprintf('%s.', substr($value, 0, 1));
        }
    }
    return implode(' ', $initials);
}

function references_management_get_citations_first($authors, $year)
{
    $count = count($authors);
    if ($count === 0) {
        return '';
    }
    if ($count === 1) {
        return sprintf(
            '%s, %s (%s)', $authors[0]['name'], references_management_get_initials($authors[0]['first_name']), $year
        );
    }
    if ($count === 2) {
        return sprintf(
            '%s, %s & %s, %s (%s)',
            $authors[0]['name'],
            references_management_get_initials($authors[0]['first_name']),
            $authors[1]['name'],
            references_management_get_initials($authors[1]['first_name']),
            $year
        );
    }
    if ($count === 3 or $count === 4 or $count === 5) {
        $names = array();
        if (!empty($authors)) {
            foreach ($authors AS $key => $value) {
                $separator = ',';
                if ($key + 1 === $count - 1) {
                    $separator = ', &';
                }
                if ($key + 1 === $count) {
                    $separator = '';
                }
                $names[] = sprintf(
                    '%s %s%s', $value['name'], references_management_get_initials($value['first_name']), $separator
                );
            }
        }
        return sprintf('%s (%s)', implode(' ', $names), $year);
    }
    return sprintf(
        '%s, %s et al. (%s)', $authors[0]['name'], references_management_get_initials($authors[0]['first_name']), $year
    );
}

function references_management_get_citations_subsequent($authors, $year)
{
    $count = count($authors);
    if ($count === 0) {
        return '';
    }
    if ($count === 1) {
        return sprintf(
            '%s, %s (%s)', $authors[0]['name'], references_management_get_initials($authors[0]['first_name']), $year
        );
    }
    if ($count === 2) {
        return sprintf(
            '%s, %s & %s, %s (%s)',
            $authors[0]['name'],
            references_management_get_initials($authors[0]['first_name']),
            $authors[1]['name'],
            references_management_get_initials($authors[1]['first_name']),
            $year
        );
    }
    return sprintf(
        '%s, %s et al. (%s)', $authors[0]['name'], references_management_get_initials($authors[0]['first_name']), $year
    );
}

function references_management_get_citations_parenthetical_first($authors, $year)
{
    $count = count($authors);
    if ($count === 0) {
        return '';
    }
    if ($count === 1) {
        return sprintf(
            '(%s, %s, %s)', $authors[0]['name'], references_management_get_initials($authors[0]['first_name']), $year
        );
    }
    if ($count === 2) {
        return sprintf(
            '(%s, %s & %s, %s, %s)',
            $authors[0]['name'],
            references_management_get_initials($authors[0]['first_name']),
            $authors[1]['name'],
            references_management_get_initials($authors[1]['first_name']),
            $year
        );
    }
    if ($count === 3 or $count === 4 or $count === 5) {
        $names = array();
        if (!empty($authors)) {
            foreach ($authors AS $key => $value) {
                $separator = ',';
                if ($key + 1 === $count - 1) {
                    $separator = ', &';
                }
                if ($key + 1 === $count) {
                    $separator = '';
                }
                $names[] = sprintf(
                    '%s %s%s', $value['name'], references_management_get_initials($value['first_name']), $separator
                );
            }
        }
        return sprintf('(%s, %s)', implode(' ', $names), $year);
    }
    return sprintf(
        '(%s, %s et al., %s)',
        $authors[0]['name'],
        references_management_get_initials($authors[0]['first_name']),
        $year
    );
}

function references_management_get_citations_parenthetical_subsequent($authors, $year)
{
    $count = count($authors);
    if ($count === 0) {
        return '';
    }
    if ($count === 1) {
        return sprintf(
            '(%s, %s, %s)', $authors[0]['name'], references_management_get_initials($authors[0]['first_name']), $year
        );
    }
    if ($count === 2) {
        return sprintf(
            '(%s, %s & %s, %s, %s)',
            $authors[0]['name'],
            references_management_get_initials($authors[0]['first_name']),
            $authors[1]['name'],
            references_management_get_initials($authors[1]['first_name']),
            $year
        );
    }
    return sprintf(
        '(%s, %s et al., %s)',
        $authors[0]['name'],
        references_management_get_initials($authors[0]['first_name']),
        $year
    );
}

function references_management_get_references_authors($authors)
{
    $authors = array_filter($authors, 'references_management_filters_author');
    $count = count($authors);
    $names = array();
    if (!empty($authors)) {
        foreach ($authors AS $key => $value) {
            $separator = ',';
            if ($key + 1 === $count - 1) {
                $separator = ', &';
            }
            if ($key + 1 === $count) {
                $separator = '';
            }
            $names[] = sprintf(
                '%s %s%s', $value['name'], references_management_get_initials($value['first_name']), $separator
            );
        }
    }
    return implode(' ', $names);
}

function references_management_get_references_editors($authors)
{
    $authors = array_filter($authors, 'references_management_filters_editor');
    $count = count($authors);
    $names = array();
    if (!empty($authors)) {
        foreach ($authors AS $key => $value) {
            $separator = ',';
            if ($key + 1 === $count - 1) {
                $separator = ', &';
            }
            if ($key + 1 === $count) {
                $separator = '';
            }
            $names[] = sprintf(
                '%s %s%s', $value['name'], references_management_get_initials($value['first_name']), $separator
            );
        }
    }
    return implode(' ', $names);
}

function references_management_get_references_all($item)
{
    return sprintf(
        '%s. %s: %s. %s: %s',
        references_management_get_citations_first($item['authors'], $item['year']),
        $item['title_1'],
        $item['title_2'],
        $item['place_published'],
        $item['publisher']
    );
}

function references_management_get_prefix()
{
    return sprintf('%sreferences_management_', $GLOBALS['wpdb']->prefix);
}

function references_management_get_url($first_name, $last_name)
{
    if (@$_SERVER['SERVER_NAME'] === '0.0.0.0') {
        return '';
    }
    $name = sprintf('%s %s', $first_name, $last_name);
    $xml = @file_get_contents(
        sprintf(
            'http://lookup.dbpedia.org/api/search/KeywordSearch?QueryClass=person&QueryString=%s', urlencode($name)
        )
    );
    if (!$xml) {
        return '';
    }
    $xml = @simplexml_load_string($xml);
    if (!$xml) {
        return '';
    }
    $xml->registerXPathNamespace('xpns', 'http://lookup.dbpedia.org/');
    foreach ($xml->xpath('//xpns:Result') AS $key => $value) {
        if ((string) $value->Label === $name) {
            return (string) $value->URI;
        }
    }
    return '';
}

function references_management_get_items($xml)
{
    $items = array();
    foreach (@simplexml_load_string($xml)->xpath('//xml/records/record') AS $key => $value) {
        try {
            $item = array();
            $item['number'] = (string) array_pop($value->xpath('rec-number'));
            $item['type'] = (string) array_pop($value->xpath('ref-type'))->attributes()['name'];
            $item['title_1'] = (string) array_pop($value->xpath('titles/title/style'));
            $item['title_2'] = (string) array_pop($value->xpath('titles/secondary-title/style'));
            $item['year'] = (string) array_pop($value->xpath('dates/year/style'));
            $item['volume'] = (string) array_pop($value->xpath('volume/style'));
            $item['issue'] = (string) array_pop($value->xpath('number/style'));
            $item['page'] = (string) array_pop($value->xpath('pages/style'));
            $urls = $value->xpath('urls/related-urls/url/style');
            $item['url'] = '';
            foreach ($urls AS $url) {
                $url = (string) $url;
                if (stristr($url, 'doi') === false) {
                    $item['url'] = $url;
                    break;
                }
            }
            $item['doi'] = '';
            foreach ($urls AS $url) {
                $url = (string) $url;
                if (stristr($url, 'doi') !== false) {
                    $item['doi'] = $url;
                    break;
                }
            }
            $item['issn'] = (string) array_pop($value->xpath('orig-pub/style'));
            if ($item['issn'] === 'Contents') {
                $item['issn'] = '';
            }
            if ($item['issn'] === 'Original Publication') {
                $item['issn'] = '';
            }
            $item['issn'] = str_replace('ISSN: ', '', $item['issn']);
            $item['original_publication'] = (string) array_pop($value->xpath('orig-pub/style'));
            $item['isbn'] = (string) array_pop($value->xpath('isbn/style'));
            if ($item['isbn'] === 'ISBN') {
                $item['isbn'] = '';
            }
            if ($item['isbn'] === 'ISSN') {
                $item['isbn'] = '';
            }
            if ($item['isbn'] === 'Report Number') {
                $item['isbn'] = '';
            }
            $item['isbn'] = str_replace('ISSN: ', '', $item['isbn']);
            $item['isbn'] = str_replace("\n", ' ', $item['isbn']);
            $item['isbn'] = str_replace("\r", ' ', $item['isbn']);
            $item['isbn'] = str_replace("\t", ' ', $item['isbn']);
            $item['isbn'] = preg_replace('/[^0-9A-Z]/', '', $item['isbn']);
            $item['isbn'] = explode(' ', $item['isbn']);
            $item['isbn'] = $item['isbn'][0];
            if (strlen($item['isbn']) === 8) {
                $item['issn'] = sprintf('%s-%s', substr($item['isbn'], 0, 4), substr($item['isbn'], 4, 4));
                $item['isbn'] = '';
            } else {
                if (strlen($item['isbn']) >= 10) {
                    $item['isbn'] = substr($item['isbn'], 0, 10);
                } else {
                    $item['isbn'] = '';
                }
            }
            $item['label'] = (string) array_pop($value->xpath('label/style'));
            $item['publisher'] = (string) array_pop($value->xpath('publisher/style'));
            $item['place_published'] = (string) array_pop($value->xpath('pub-location/style'));
            $item['access_date'] = (string) array_pop($value->xpath('access-date/style'));
            $item['attachment'] = (string) array_pop($value->xpath('urls/pdf-urls/url'));
            $item['attachment'] = str_replace('internal-pdf', '', $item['attachment']);
            $item['authors'] = array();
            foreach ($value->xpath('contributors/authors/author') AS $name) {
                $name = (string) $name->style;
                $explode = explode(',', $name, 2);
                $explode = array_map('trim', $explode);
                if (!$explode[0] or !$explode[1]) {
                    continue;
                }
                $item['authors'][] = array(
                    'name' => $explode[0],
                    'first_name' => $explode[1],
                    'role' => 'Author',
                    'url' => references_management_get_url($explode[1], $explode[0]),
                );
            }
            foreach ($value->xpath('contributors/secondary-authors/author') AS $name) {
                $name = (string) $name->style;
                $explode = explode(',', $name, 2);
                $explode = array_map('trim', $explode);
                if (!$explode[0] or !$explode[1]) {
                    continue;
                }
                $item['authors'][] = array(
                    'name' => $explode[0],
                    'first_name' => $explode[1],
                    'role' => 'Editor',
                    'url' => references_management_get_url($explode[1], $explode[0]),
                );
            }
            $item['citations_first'] = references_management_get_citations_first($item['authors'], $item['year']);
            $item['citations_subsequent'] = references_management_get_citations_subsequent(
                $item['authors'], $item['year']
            );
            $item['citations_parenthetical_first'] = references_management_get_citations_parenthetical_first(
                $item['authors'], $item['year']
            );
            $item['citations_parenthetical_subsequent'] = references_management_get_citations_parenthetical_subsequent(
                $item['authors'], $item['year']
            );
            $item['references_authors'] = references_management_get_references_authors($item['authors']);
            $item['references_editors'] = references_management_get_references_editors($item['authors']);
            $item['references_all'] = references_management_get_references_all($item);
            $items[] = $item;
        } catch (Exception $exception) {
            return array(sprintf('references_management_get_items() - %s', $exception->getMessage()), array());
        }
    }
    return array(array(), $items);
}

function references_management_delete($directory)
{
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($files AS $file) {
        if ($file->isDir()) {
            rmdir($file->getRealPath());
        } else {
            unlink($file->getRealPath());
        }
    }
}

function references_management_register_activation_hook()
{
    references_management_register_deactivation_hook();

    $query = <<<EOD
CREATE TABLE IF NOT EXISTS `%sdocuments` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    PRIMARY KEY (`id`),
    KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0;
EOD;
    $GLOBALS['wpdb']->query(sprintf($query, references_management_get_prefix()));

    $query = <<<EOD
CREATE TABLE IF NOT EXISTS `%sarticles` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `document_id` INT(11) UNSIGNED NOT NULL,
    `number` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `type` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `title_1` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `title_2` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `year` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `volume` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `issue` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `page` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `url` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `doi` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `issn` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `original_publication` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `isbn` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `label` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `publisher` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `place_published` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `access_date` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `attachment` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `citations_first` TEXT COLLATE utf8_unicode_ci NOT NULL,
    `citations_subsequent` TEXT COLLATE utf8_unicode_ci NOT NULL,
    `citations_parenthetical_first` TEXT COLLATE utf8_unicode_ci NOT NULL,
    `citations_parenthetical_subsequent` TEXT COLLATE utf8_unicode_ci NOT NULL,
    `references_authors` TEXT COLLATE utf8_unicode_ci NOT NULL,
    `references_editors` TEXT COLLATE utf8_unicode_ci NOT NULL,
    `references_all` TEXT COLLATE utf8_unicode_ci NOT NULL,
    PRIMARY KEY (`id`),
    KEY `number` (`number`),
    KEY `type` (`type`),
    KEY `title_1` (`title_1`),
    KEY `title_2` (`title_2`),
    KEY `year` (`year`),
    KEY `volume` (`volume`),
    KEY `issue` (`issue`),
    KEY `page` (`page`),
    KEY `url` (`url`),
    KEY `doi` (`doi`),
    KEY `issn` (`issn`),
    KEY `original_publication` (`original_publication`),
    KEY `isbn` (`isbn`),
    KEY `label` (`label`),
    KEY `publisher` (`publisher`),
    KEY `place_published` (`place_published`),
    KEY `access_date` (`access_date`),
    KEY `attachment` (`attachment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0;
EOD;
    $GLOBALS['wpdb']->query(sprintf($query, references_management_get_prefix()));

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
    $GLOBALS['wpdb']->query(sprintf($query, references_management_get_prefix()));

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
    $GLOBALS['wpdb']->query(sprintf($query, references_management_get_prefix()));

    $query = <<<EOD
ALTER TABLE `%sarticles`
    ADD CONSTRAINT `%sarticles_document_id`
    FOREIGN KEY (`document_id`)
    REFERENCES `%sdocuments` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;
EOD;
    $GLOBALS['wpdb']->query(sprintf($query, references_management_get_prefix(), references_management_get_prefix(), references_management_get_prefix()));

    $query = <<<EOD
ALTER TABLE `%sauthors`
    ADD CONSTRAINT `%sauthors_document_id`
    FOREIGN KEY (`document_id`)
    REFERENCES `%sdocuments` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;
EOD;
    $GLOBALS['wpdb']->query(sprintf($query, references_management_get_prefix(), references_management_get_prefix(), references_management_get_prefix()));

    $query = <<<EOD
ALTER TABLE `%sarticles_authors`
    ADD CONSTRAINT `%sarticles_authors_article_id`
    FOREIGN KEY (`article_id`)
    REFERENCES `%sarticles` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;
EOD;
    $GLOBALS['wpdb']->query(sprintf($query, references_management_get_prefix(), references_management_get_prefix(), references_management_get_prefix()));

    $query = <<<EOD
ALTER TABLE `%sarticles_authors`
    ADD CONSTRAINT `%sarticles_authors_author_id`
    FOREIGN KEY (`author_id`)
    REFERENCES `%sauthors` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;
EOD;
    $GLOBALS['wpdb']->query(sprintf($query, references_management_get_prefix(), references_management_get_prefix(), references_management_get_prefix()));

    references_management_get_directory(array());
}

function references_management_register_deactivation_hook()
{

    references_management_delete(references_management_get_directory(array()));

    $GLOBALS['wpdb']->query(sprintf('DROP TABLE IF EXISTS `%sarticles_authors`', references_management_get_prefix()));
    $GLOBALS['wpdb']->query(sprintf('DROP TABLE IF EXISTS `%sauthors`', references_management_get_prefix()));
    $GLOBALS['wpdb']->query(sprintf('DROP TABLE IF EXISTS `%sarticles`', references_management_get_prefix()));
    $GLOBALS['wpdb']->query(sprintf('DROP TABLE IF EXISTS `%sdocuments`', references_management_get_prefix()));
}

function references_management_init()
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
    add_action('wp_enqueue_scripts', 'references_management_scripts');
    add_action('wp_enqueue_scripts', 'references_management_styles');
}

function references_management_admin_init()
{
    add_action('admin_print_scripts', 'references_management_scripts');
    add_action('admin_print_styles', 'references_management_styles');
}

function references_management_scripts()
{
    wp_enqueue_script(
        'all_js', sprintf('%s/references_management.js', plugins_url('/references_management')), array('jquery')
    );
}

function references_management_styles()
{
    wp_enqueue_style(
        'all_css', sprintf('%s/references_management.css', plugins_url('/references_management'))
    );
}

function references_management_admin_menu()
{
    add_menu_page(
        'RM',
        'RM',
        'manage_options',
        '/references_management',
        'references_management_dashboard',
        ''
    );
    add_submenu_page(
        '/references_management',
        'F.A.Q',
        'F.A.Q',
        'manage_options',
        '/references_management/faq',
        'references_management_faq'
    );
}

function references_management_flashes()
{
    ?>
    <?php if (!empty($_SESSION['references_management']['flashes'])) : ?>
        <?php foreach ($_SESSION['references_management']['flashes'] AS $key => $value) : ?>
            <div class="<?php echo $key; ?>">
                <p><strong><?php echo $value; ?></strong></p>
            </div>
        <?php endforeach; ?>
        <?php $_SESSION['references_management']['flashes'] = array(); ?>
    <?php endif; ?>
    <?php
}

function references_management_dashboard()
{
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permissions to access this page.');
    }
    $action = $_REQUEST['action']? $_REQUEST['action']: '';
    ?>
    <div class="references_management wrap">
        <?php
        switch ($action) {
        case 'upload':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                list($errors, $items) = references_management_get_items(
                    file_get_contents($_FILES['file']['tmp_name'])
                );
                if ($errors) {
                    $_SESSION['references_management']['flashes'] = array(
                        'error' => 'The document was not uploaded successfully. Please try again.',
                    );
                    ?>
                    <meta
                        content="0;url=<?php echo admin_url(
                            'admin.php?action=upload&page=references_management'
                        ); ?>"
                        http-equiv="refresh"
                        >
                    <?php
                    die();
                }
                if (!$items) {
                    $_SESSION['references_management']['flashes'] = array(
                        'error' => 'The document was not uploaded successfully. Please try again.',
                    );
                    ?>
                    <meta
                        content="0;url=<?php echo admin_url(
                            'admin.php?action=upload&page=references_management'
                        ); ?>"
                        http-equiv="refresh"
                        >
                    <?php
                    die();
                }
                $GLOBALS['wpdb']->insert(
                    sprintf('%sdocuments', references_management_get_prefix()),
                    array(
                        'name' => $_FILES['file']['name'],
                    )
                );
                $document_id = $GLOBALS['wpdb']->insert_id;
                references_management_get_directory(array($document_id));
                copy(
                    $_FILES['file']['tmp_name'],
                    references_management_get_file(array($document_id, $_FILES['file']['name']))
                );
                foreach ($items AS $item) {
                    $GLOBALS['wpdb']->insert(
                        sprintf('%sarticles', references_management_get_prefix()),
                        array(
                            'document_id' => $document_id,
                            'number' => $item['number'],
                            'type' => $item['type'],
                            'title_1' => $item['title_1'],
                            'title_2' => $item['title_2'],
                            'year' => $item['year'],
                            'volume' => $item['volume'],
                            'issue' => $item['issue'],
                            'page' => $item['page'],
                            'url' => $item['url'],
                            'doi' => $item['doi'],
                            'issn' => $item['issn'],
                            'original_publication' => $item['original_publication'],
                            'isbn' => $item['isbn'],
                            'label' => $item['label'],
                            'publisher' => $item['publisher'],
                            'place_published' => $item['place_published'],
                            'access_date' => $item['access_date'],
                            'attachment' => $item['attachment'],
                            'citations_first' => $item['citations_first'],
                            'citations_subsequent' => $item['citations_subsequent'],
                            'citations_parenthetical_first' => $item['citations_parenthetical_first'],
                            'citations_parenthetical_subsequent' => $item['citations_parenthetical_subsequent'],
                            'references_authors' => $item['references_authors'],
                            'references_editors' => $item['references_editors'],
                            'references_all' => $item['references_all'],
                        )
                    );
                    $article_id = $GLOBALS['wpdb']->insert_id;
                    foreach ($item['authors'] AS $author) {
                        $query = <<<EOD
SELECT *
FROM `%sauthors`
WHERE `document_id` = %%d AND `name` = %%s AND `first_name` = %%s
EOD;
                        $row = $GLOBALS['wpdb']->get_row(
                            $GLOBALS['wpdb']->prepare(
                                sprintf($query, references_management_get_prefix()),
                                $document_id,
                                $author['name'],
                                $author['first_name']
                            ),
                            ARRAY_A
                        );
                        if ($row) {
                            $author_id = $row['id'];
                        } else {
                            $GLOBALS['wpdb']->insert(
                                sprintf('%sauthors', references_management_get_prefix()),
                                array(
                                    'document_id' => $document_id,
                                    'name' => $author['name'],
                                    'first_name' => $author['first_name'],
                                    'url' => $author['url'],
                                )
                            );
                            $author_id = $GLOBALS['wpdb']->insert_id;
                        }
                        $GLOBALS['wpdb']->insert(
                            sprintf('%sarticles_authors', references_management_get_prefix()),
                            array(
                                'article_id' => $article_id,
                                'author_id' => $author_id,
                                'role' => $author['role'],
                            )
                        );
                    }
                }
                $_SESSION['references_management']['flashes'] = array(
                    'updated' => 'The document was uploaded successfully.',
                );
                ?>
                <meta
                    content="0;url=<?php echo admin_url('admin.php?action=&page=references_management'); ?>"
                    http-equiv="refresh"
                    >
                <?php
                die();
            } else {
                ?>
                <h1>Documents - Upload</h1>
                <form
                    action="<?php echo admin_url('admin.php?action=upload&page=references_management'); ?>"
                    enctype="multipart/form-data"
                    method="post"
                    >
                    <table class="bordered widefat wp-list-table">
                        <tr>
                            <td class="label"><label for="file">File</label></td>
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
                $GLOBALS['wpdb']->prepare(
                    sprintf('SELECT * FROM `%sdocuments` WHERE `id` = %%d', references_management_get_prefix()),
                    intval($_REQUEST['id'])
                ),
                ARRAY_A
            );
            $query = <<<EOD
SELECT `%sauthors`.`name`
FROM `%sauthors`
INNER JOIN `%sarticles_authors` ON
`%sarticles_authors`.`article_id` = %%d AND `%sarticles_authors`.`author_id` = `%sauthors`.`id`
WHERE `%sarticles_authors`.`role` = %%s
LIMIT 1
OFFSET %d
EOD;
            $xml = simplexml_load_file(references_management_get_file(array($_REQUEST['id'], $document['name'])));
            foreach ($xml->xpath('//xml/records/record') AS $key => $value) {
                $number = (string) array_pop($value->xpath('rec-number'));
                $article = $GLOBALS['wpdb']->get_row(
                    $GLOBALS['wpdb']->prepare(
                        sprintf(
                            'SELECT * FROM `%sarticles` WHERE `document_id` = %%d AND `number` = %%s',
                            references_management_get_prefix()
                        ),
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
                $title_1s = $value->xpath('titles/title/style');
                foreach ($title_1s AS $title_1) {
                    $dom = dom_import_simplexml($title_1);
                    $dom->nodeValue = $article['title_1'];
                }
                $title_2s = $value->xpath('titles/secondary-title/style');
                foreach ($title_2s AS $title_2) {
                    $dom = dom_import_simplexml($title_2);
                    $dom->nodeValue = $article['title_2'];
                }
                $years = $value->xpath('dates/year/style');
                foreach ($years AS $year) {
                    $dom = dom_import_simplexml($year);
                    $dom->nodeValue = $article['year'];
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
                    if (stristr($url, 'doi') === false) {
                        $dom->nodeValue = $article['url'];
                        break;
                    }
                }
                foreach ($urls AS $url) {
                    $dom = dom_import_simplexml($url);
                    if (stristr($url, 'doi') !== false) {
                        $dom->nodeValue = $article['doi'];
                        break;
                    }
                }
                $issns = $value->xpath('orig-pub/style');
                foreach ($issns AS $issn) {
                    $dom = dom_import_simplexml($issn);
                    $dom->nodeValue = $article['issn'];
                }
                $original_publications = $value->xpath('orig-pub/style');
                foreach ($original_publications AS $original_publication) {
                    $dom = dom_import_simplexml($original_publication);
                    $dom->nodeValue = $article['original_publication'];
                }
                $isbns = $value->xpath('isbn/style');
                foreach ($isbns AS $isbn) {
                    $dom = dom_import_simplexml($isbn);
                    $dom->nodeValue = $article['isbn'];
                }
                $labels = $value->xpath('label/style');
                foreach ($labels AS $label) {
                    $dom = dom_import_simplexml($label);
                    $dom->nodeValue = $article['label'];
                }
                $publishers = $value->xpath('publisher/style');
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
                $attachments = $value->xpath('urls/pdf-urls/url');
                foreach ($attachments AS $attachment) {
                    $dom = dom_import_simplexml($attachment);
                    $dom->nodeValue = sprintf('internal-pdf%s', $article['attachment']);
                }
                $authors = $value->xpath('contributors/authors/author/style');
                foreach ($authors AS $k => $v) {
                    $author = $GLOBALS['wpdb']->get_row(
                        $GLOBALS['wpdb']->prepare(
                            sprintf(
                                $query,
                                references_management_get_prefix(),
                                references_management_get_prefix(),
                                references_management_get_prefix(),
                                references_management_get_prefix(),
                                references_management_get_prefix(),
                                references_management_get_prefix(),
                                references_management_get_prefix(),
                                $k
                            ),
                            $article['id'],
                            'Author'
                        ),
                        ARRAY_A
                    );
                    if ($author) {
                        $dom = dom_import_simplexml($v);
                        $dom->nodeValue = sprintf('%s, %s', $author['name'], $author['first_name']);
                    }
                }
                $editors = $value->xpath('contributors/secondary-authors/author/style');
                foreach ($editors AS $k => $v) {
                    $editor = $GLOBALS['wpdb']->get_row(
                        $GLOBALS['wpdb']->prepare(
                            sprintf(
                                $query,
                                references_management_get_prefix(),
                                references_management_get_prefix(),
                                references_management_get_prefix(),
                                references_management_get_prefix(),
                                references_management_get_prefix(),
                                references_management_get_prefix(),
                                references_management_get_prefix(),
                                $k
                            ),
                            $article['id'],
                            'Editor'
                        ),
                        ARRAY_A
                    );
                    if ($editor) {
                        $dom = dom_import_simplexml($v);
                        $dom->nodeValue = sprintf('%s, %s', $author['name'], $author['first_name']);
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
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $directory = references_management_get_directory(array($_REQUEST['id']));
                references_management_delete($directory);
                rmdir($directory);
                $GLOBALS['wpdb']->delete(
                    sprintf('%sdocuments', references_management_get_prefix()),
                    array(
                        'id' => $_REQUEST['id'],
                    ),
                    null,
                    null
                );
                $_SESSION['references_management']['flashes'] = array(
                    'updated' => 'The document was deleted successfully.',
                );
                ?>
                <meta
                    content="0;url=<?php echo admin_url(
                        'admin.php?action=&deleted=deleted&page=references_management'
                    ); ?>"
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
                        sprintf('admin.php?action=delete&id=%d&page=references_management', $_REQUEST['id'])
                    ); ?>"
                    method="post"
                    >
                    <p class="submit">
                        <input class="button-primary" type="submit" value="Yes">
                        <a
                            class="float-right"
                            href="<?php echo admin_url('admin.php?action=&page=references_management'); ?>"
                            >
                            No
                        </a>
                    </p>
                </form>
                <?php
            }
            break;
        case 'download_zip':
            $document = $GLOBALS['wpdb']->get_row(
                $GLOBALS['wpdb']->prepare(
                    sprintf('SELECT * FROM `%sdocuments` WHERE `id` = %%d', references_management_get_prefix()),
                    intval($_REQUEST['id'])
                ),
                ARRAY_A
            );
            $query = <<<EOD
SELECT
id,
number,
type,
title_1,
title_2,
year,
volume,
issue,
page,
url,
doi,
issn,
original_publication,
isbn,
label,
publisher,
place_published,
access_date,
attachment,
citations_first,
citations_subsequent,
citations_parenthetical_first,
citations_parenthetical_subsequent,
references_authors,
references_editors,
references_all
FROM `%sarticles`
WHERE `document_id` = %%d
ORDER BY `type` ASC, `id` ASC
EOD;
            $articles = $GLOBALS['wpdb']->get_results(
                $GLOBALS['wpdb']->prepare(sprintf($query, references_management_get_prefix()), $document['id']),
                ARRAY_A
            );
            $resource = @fopen('php://temp/maxmemory:999999999', 'w');
            @fputcsv(
                $resource,
                array(
                    'id' => 'Identifier',
                    'number' => 'Number',
                    'type' => 'Type',
                    'title_1' => 'Title',
                    'title_2' => 'Title2',
                    'year' => 'Year',
                    'volume' => 'Volume',
                    'issue' => 'Issue',
                    'page' => 'Page',
                    'url' => 'URL',
                    'doi' => 'DOI',
                    'issn' => 'ISSN',
                    'original_publication' => 'Original Publication',
                    'isbn' => 'ISBN',
                    'label' => 'Label',
                    'publisher' => 'Publisher',
                    'place_published' => 'Place Published',
                    'access_date' => 'Access Date',
                    'attachment' => 'Attachment',
                    'citations_first' => 'Authors Publish Text First',
                    'citations_subsequent' => 'Authors Publish Text Subsequent',
                    'citations_parenthetical_first' => 'Authors Publish Text First Parenthetical',
                    'citations_parenthetical_subsequent' => 'Authors Publish Text Subsequent Parenthetical',
                    'references_authors' => 'Authors Publish Reference',
                    'references_editors' => 'Editors Publish Reference',
                    'references_all' => 'Reference Entry',
                ),
                ';'
            );
            foreach ($articles AS $article) {
                @fputcsv($resource, $article, ';');
            }
            @rewind($resource);
            $articles = stream_get_contents($resource);
            @fclose($resource);
            $query = <<<EOD
SELECT id, name, first_name, url
FROM `%sauthors`
WHERE `document_id` = %%d
ORDER BY `id` ASC
EOD;
            $authors = $GLOBALS['wpdb']->get_results(
                $GLOBALS['wpdb']->prepare(sprintf($query, references_management_get_prefix()), $document['id']),
                ARRAY_A
            );
            $resource = @fopen('php://temp/maxmemory:999999999', 'w');
            @fputcsv(
                $resource,
                array(
                    'id' => 'Identifier',
                    'name' => 'Name',
                    'first_name' => 'First Name',
                    'url' => 'URL',
                ),
                ';'
            );
            foreach ($authors AS $author) {
                @fputcsv($resource, $author, ';');
            }
            @rewind($resource);
            $authors = stream_get_contents($resource);
            @fclose($resource);
            $query = <<<EOD
SELECT id, article_id, author_id, role
FROM `%sarticles_authors`
WHERE
`article_id` IN (
    SELECT `id` FROM `%sarticles` WHERE `document_id` = %%d
)
AND
`author_id` IN (
    SELECT `id` FROM `%sauthors` WHERE `document_id` = %%d
)
ORDER BY `id` ASC
EOD;
            $articles_authors = $GLOBALS['wpdb']->get_results(
                $GLOBALS['wpdb']->prepare(
                    sprintf($query, references_management_get_prefix(), references_management_get_prefix(), references_management_get_prefix()),
                    $document['id'],
                    $document['id']
                ),
                ARRAY_A
            );
            $resource = @fopen('php://temp/maxmemory:999999999', 'w');
            @fputcsv(
                $resource,
                array(
                    'id' => 'Identifier',
                    'article_id' => 'Article Identifier',
                    'author_id' => 'Author Identifier',
                    'role' => 'Role',
                ),
                ';'
            );
            foreach ($articles_authors AS $article_author) {
                @fputcsv($resource, $article_author, ';');
            }
            @rewind($resource);
            $articles_authors = stream_get_contents($resource);
            @fclose($resource);

            $tempnam = tempnam(sys_get_temp_dir(), 'references_management');
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
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $articles = str_getcsv(
                    @file_get_contents(sprintf('zip://%s#%s', $_FILES['file']['tmp_name'], 'articles.csv')),
                    "\n"
                );
                foreach ($articles AS $article) {
                    $article = str_getcsv($article, ';');
                    $GLOBALS['wpdb']->update(
                        sprintf('%sarticles', references_management_get_prefix()),
                        array(
                            'type' => $article[2],
                            'title_1' => $article[3],
                            'title_2' => $article[4],
                            'year' => $article[5],
                            'volume' => $article[6],
                            'issue' => $article[7],
                            'page' => $article[8],
                            'url' => $article[9],
                            'doi' => $article[10],
                            'issn' => $article[11],
                            'original_publication' => $article[12],
                            'isbn' => $article[13],
                            'label' => $article[14],
                            'publisher' => $article[15],
                            'place_published' => $article[16],
                            'access_date' => $article[17],
                            'attachment' => $article[18],
                            'citations_first' => $article[19],
                            'citations_subsequent' => $article[20],
                            'citations_parenthetical_first' => $article[21],
                            'citations_parenthetical_subsequent' => $article[22],
                            'references_authors' => $article[23],
                            'references_editors' => $article[24],
                            'references_all' => $article[25],
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
                    $author = str_getcsv($author, ';');
                    $GLOBALS['wpdb']->update(
                        sprintf('%sauthors', references_management_get_prefix()),
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
                    $article_author = str_getcsv($article_author, ';');
                    $GLOBALS['wpdb']->update(
                        sprintf('%sarticles_authors', references_management_get_prefix()),
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
                $_SESSION['references_management']['flashes'] = array(
                    'updated' => 'The document was uploaded successfully.',
                );
                ?>
                <meta
                    content="0;url=<?php echo admin_url('admin.php?action=&page=references_management'); ?>"
                    http-equiv="refresh"
                    >
                <?php
            } else {
                ?>
                <h1>Zip file - Upload</h1>
                <form
                    action="<?php echo admin_url(
                        sprintf('admin.php?action=upload_zip&id=%d&page=references_management', $_REQUEST['id'])
                    ); ?>"
                    enctype="multipart/form-data"
                    method="post"
                    >
                    <table class="bordered widefat wp-list-table">
                        <tr>
                            <td class="label"><label for="file">File</label></td>
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
                sprintf('SELECT * FROM `%sdocuments` ORDER BY `id` DESC', references_management_get_prefix()),
                ARRAY_A
            );
            ?>
            <h1>
                Documents
                <a
                    class="page-title-action"
                    href="<?php echo admin_url('admin.php?action=upload&page=references_management'); ?>"
                    >Upload</a>
            </h1>
            <?php references_management_flashes(); ?>
            <?php if ($documents) : ?>
                <table class="bordered widefat wp-list-table">
                    <tr>
                        <th class="narrow right">Identifier</th>
                        <th>Name</th>
                        <th class="narrow center">ZIP</th>
                        <th class="narrow center">XML</th>
                        <th class="narrow center">Actions</th>
                    </tr>
                    <?php foreach ($documents AS $document) : ?>
                        <tr>
                            <td class="narrow right"><?php echo $document['id']; ?></td>
                            <td><?php echo $document['name']; ?></td>
                            <td class="narrow center">
                                <a href="<?php
                                echo admin_url(
                                    sprintf(
                                        'admin.php?action=download_zip&id=%d&page=references_management',
                                        $document['id']
                                    )
                                );
                                ?>">Download</a>
                                -
                                <a href="<?php
                                echo admin_url(
                                    sprintf(
                                        'admin.php?action=upload_zip&id=%d&page=references_management',
                                        $document['id']
                                    )
                                );
                                ?>">Upload</a>
                            </td>
                            <td class="narrow center">
                                <a href="<?php
                                echo admin_url(
                                    sprintf(
                                        'admin.php?action=download&id=%d&page=references_management',
                                        $document['id']
                                    )
                                );
                                ?>">Download</a>
                            </td>
                            <td class="narrow center">
                                <a href="<?php
                                echo admin_url(
                                    sprintf(
                                        'admin.php?action=delete&id=%d&page=references_management',
                                        $document['id']
                                    )
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

function references_management_faq()
{
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permissions to access this page.');
    }
    ?>
    <div class="references_management wrap">
        <h1>Frequently Asked Questions</h1>
        <div class="welcome-panel">
            <p><strong>Steps</strong></p>
            <hr>
            <ol>
                <li>
                    Upload a new EndNote XML file using the <strong>Upload</strong> link next to the page header
                </li>
                <li>
                    Download a ZIP file using the <strong>Download</strong> link in the <strong>ZIP</strong> column
                </li>
                <li>
                    Extract the downloaded ZIP file
                </li>
                <li>
                    Edit the CSV files inside the extracted ZIP file as required
                </li>
                <li>
                    Re-create the ZIP file and populate it with the edited CSV files
                </li>
                <li>
                    Upload the re-created ZIP file using the <strong>Upload</strong> link in the <strong>ZIP</strong>
                    column
                </li>
                <li>
                    Downloaded the updated EndNote XML file using the <strong>Download</strong> link in the
                    <strong>XML</strong> column
                </li>
            </ol>
        </div>
        <div class="welcome-panel">
            <p><strong>Columns</strong></p>
            <hr>
            <ol>
                <li>
                    <strong>articles.csv</strong>
                    <ul>
                        <li>Identifier</li>
                        <li>Number</li>
                        <li>Type</li>
                        <li>Title</li>
                        <li>Title2</li>
                        <li>Year</li>
                        <li>Volume</li>
                        <li>Issue</li>
                        <li>Page</li>
                        <li>URL</li>
                        <li>DOI</li>
                        <li>ISSN</li>
                        <li>ISBN</li>
                        <li>Label</li>
                        <li>Publisher</li>
                        <li>Place Published</li>
                        <li>Access Date</li>
                        <li>Authors Publish Text First</li>
                        <li>Authors Publish Text Subsequent</li>
                        <li>Authors Publish Text First Parenthetical</li>
                        <li>Authors Publish Text Subsequent Parenthetical</li>
                        <li>Authors Publish Reference</li>
                        <li>Editors Publish Reference</li>
                        <li>Reference Entry</li>
                    </ul>
                </li>
                <li>
                    <strong>authors.csv</strong>
                    <ul>
                        <li>Identifier</li>
                        <li>Name</li>
                        <li>First Name</li>
                        <li>URL</li>
                    </ul>
                </li>
                <li>
                    <strong>articles_authors.csv</strong>
                    <ul>
                        <li>Identifier</li>
                        <li>Article Identifier</li>
                        <li>Author Identifier</li>
                        <li>Role</li>
                    </ul>
                </li>
            </ol>
        </div>
    </div>
    <?php
}

function references_management_add_meta_boxes()
{
    add_meta_box(
        'references_management_1',
        'References Management - Page Detail',
        'references_management_add_meta_boxes_1',
        'page'
    );
    add_meta_box(
        'references_management_2',
        'References Management - Translations',
        'references_management_add_meta_boxes_2',
        'page'
    );
    add_meta_box(
        'references_management_references',
        'References Management - References',
        'references_management_add_meta_boxes_3',
        'page'
    );
    add_meta_box(
        'references_management_semantic_4',
        'References Management - Semantic Annotations',
        'references_management_add_meta_boxes_4',
        'page'
    );
}

function references_management_add_meta_boxes_1($post)
{
    wp_nonce_field('references_management_add_meta_boxes_1', 'references_management_add_meta_boxes_1');
    $multipage_report = get_post_meta($post->ID, 'references_management_1_multipage_report', true);
    $root = intval(get_post_meta($post->ID, 'references_management_1_root', true));
    $pages = get_pages(array(
        'authors' => '',
        'child_of' => 0,
        'exclude' => '',
        'exclude_tree' => '',
        'hierarchical' => 0,
        'include' => '',
        'meta_key' => '',
        'meta_value' => '',
        'number' => '',
        'offset' => 0,
        'parent' => -1,
        'post_status' => 'publish',
        'post_type' => 'page',
        'sort_column' => 'post_title',
        'sort_order' => 'asc',
    ));
    ?>
    <table class="references_management_widget">
        <tr class="even">
            <td class="label">
                <label for="references_management_1_multipage_report">Multipage Report</label>
            </td>
            <td>
                <select id="references_management_1_multipage_report" name="references_management_1_multipage_report">
                    <option <?php echo $multipage_report === "Yes"? 'selected="selected"': ''; ?> value="Yes">
                        Yes
                    </option>
                    <option <?php echo $multipage_report === "No"? 'selected="selected"': ''; ?> value="No">
                        No
                    </option>
                </select>
            </td>
        </tr>
        <tr class="even">
            <td class="label"><label for="references_management_1_root">Root</label></td>
            <td>
                <select id="references_management_1_root" name="references_management_1_root">
                    <option <?php echo $root === 0? 'selected="selected"': ''; ?> value="0">None</option>
                    <?php foreach ($pages AS $page) : ?>
                        <option
                            <?php echo $root === $page->ID? 'selected="selected"': ''; ?>
                            value="<?php echo $page->ID; ?>"
                            >
                            <?php echo $page->post_title; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
    </table>
    <?php
}

function references_management_add_meta_boxes_2($post)
{
    $references = get_post_meta($post->ID, 'references_management_2_references', true);
    $table_of_contents = get_post_meta($post->ID, 'references_management_2_table_of_contents', true);
    ?>
    <table class="references_management_widget">
        <tr class="even">
            <td class="label"><label for="references_management_2_references">References</label></td>
            <td>
                <input
                    id="references_management_2_references"
                    name="references_management_2_references"
                    type="text"
                    value="<?php echo $references? $references: 'References'; ?>"
                    >
            </td>
        </tr>
        <tr class="even">
            <td class="label">
                <label for="references_management_2_table_of_contents">Table of Contents</label>
            </td>
            <td>
                <input
                    id="references_management_2_table_of_contents"
                    name="references_management_2_table_of_contents"
                    type="text"
                    value="<?php echo $table_of_contents? $table_of_contents: 'Table of Contents'; ?>"
                    >
            </td>
        </tr>
    </table>
    <?php
}

function references_management_add_meta_boxes_3($post)
{
    ?>
    <table class="references_management_widget wide">
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Style 1</th>
            <th>Style 2</th>
            <th>Style 3</th>
            <th>Style 4</th>
        </tr>
        <?php for ($index = 1; $index <= 1; $index = $index + 1) : ?>
            <tr class="<?php echo ($index % 2 === 0)? 'even': 'odd'; ?>">
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
        <?php endfor; ?>
    </table>
    <?php
}

function references_management_add_meta_boxes_4($post)
{
    $annotations = json_decode(get_post_meta($post->ID, 'references_management_semantic_4', true), true);
    ?>
    <table class="references_management_widget wide">
        <tr>
            <th>Ontology</th>
            <th>Class</th>
            <th>Property</th>
            <th>Value</th>
        </tr>
        <?php if (!empty($annotations)) : ?>
            <?php foreach ($annotations AS $key => $value) : ?>
                <tr class="<?php echo ($key % 2 === 0)? 'even': 'odd'; ?>">
                    <td>
                        <select class="wide" name="references_management_semantic_4_ontologies[]">
                            <option
                                <?php echo ($annotations[$key]['ontologies'] === ""? 'selected="selected"': ''); ?>
                                value=""
                                >Select...</option>
                            <option
                                <?php echo ($annotations[$key]['ontologies'] === "DoCO"? 'selected="selected"': ''); ?>
                                value="DoCO"
                                >DoCO</option>
                        </select>
                    </td>
                    <td>
                        <select class="wide" name="references_management_semantic_4_classes[]">
                            <option
                                <?php echo (
                                    $annotations[$key]['classes'] === ""? 'selected="selected"': ''
                                ); ?>
                                value=""
                                >Select...</option>
                            <option
                                <?php echo (
                                    $annotations[$key]['classes'] === "Appendix"? 'selected="selected"': ''
                                ); ?>
                                value="Appendix"
                                >Appendix</option>
                            <option
                                <?php echo (
                                    $annotations[$key]['classes'] === "FrontMatter"? 'selected="selected"': ''
                                ); ?>
                                value="FrontMatter"
                                >FrontMatter</option>
                            <option
                                <?php echo (
                                    $annotations[$key]['classes'] === "Glossary"? 'selected="selected"': ''
                                ); ?>
                                value="Glossary"
                                >Glossary</option>
                            <option
                                <?php echo (
                                    $annotations[$key]['classes'] === "ListOfAuthors"? 'selected="selected"': ''
                                ); ?>
                                value="ListOfAuthors"
                                >ListOfAuthors</option>
                            <option
                                <?php echo (
                                    $annotations[$key]['classes'] === "ListOfFigures"? 'selected="selected"': ''
                                ); ?>
                                value="ListOfFigures"
                                >ListOfFigures</option>
                            <option
                                <?php echo (
                                    $annotations[$key]['classes'] === "ListOfOrganizations"? 'selected="selected"': ''
                                ); ?>
                                value="ListOfOrganizations"
                                >ListOfOrganizations</option>
                            <option
                                <?php echo (
                                    $annotations[$key]['classes'] === "ListOfTables"? 'selected="selected"': ''
                                ); ?>
                                value="ListOfTables"
                                >ListOfTables</option>
                            <option
                                <?php echo (
                                    $annotations[$key]['classes'] === "Preface"? 'selected="selected"': ''
                                ); ?>
                                value="Preface"
                                >Preface</option>
                            <option
                                <?php echo (
                                    $annotations[$key]['classes'] === "TableOfContents"? 'selected="selected"': ''
                                ); ?>
                                value="TableOfContents"
                                >TableOfContents</option>
                        </select>
                    </td>
                    <td>
                        <input
                            class="wide"
                            name="references_management_semantic_4_properties[]"
                            type="text"
                            value="<?php echo $annotations[$key]['properties']; ?>"
                            >
                    </td>
                    <td>
                        <input
                            class="wide"
                            name="references_management_semantic_4_values[]"
                            type="text"
                            value="<?php echo $annotations[$key]['values']; ?>"
                            >
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php for ($index = count($annotations? $annotations: array()) + 1; $index <= 6; $index = $index + 1) : ?>
            <tr class="<?php echo ($index % 2 === 0)? 'even': 'odd'; ?>">
                <td>
                    <select class="wide" name="references_management_semantic_4_ontologies[]">
                        <option value="">Select...</option>
                        <option value="DoCO">DoCO</option>
                    </select>
                </td>
                <td>
                    <select class="wide" name="references_management_semantic_4_classes[]">
                        <option value="">Select...</option>
                        <option value="Appendix">Appendix</option>
                        <option value="FrontMatter">FrontMatter</option>
                        <option value="Glossary">Glossary</option>
                        <option value="ListOfAuthors">ListOfAuthors</option>
                        <option value="ListOfFigures">ListOfFigures</option>
                        <option value="ListOfOrganizations">ListOfOrganizations</option>
                        <option value="ListOfTables">ListOfTables</option>
                        <option value="Preface">Preface</option>
                        <option value="TableOfContents">TableOfContents</option>
                    </select>
                </td>
                <td>
                    <input class="wide" name="references_management_semantic_4_properties[]" type="text" value="">
                </td>
                <td>
                    <input class="wide" name="references_management_semantic_4_values[]" type="text" value="">
                </td>
            </tr>
        <?php endfor; ?>
    </table>
    <?php
}

function references_management_save_post($post_id)
{
    if (!isset($_POST['references_management_add_meta_boxes_1'])) {
        return $post_id;
    }
    if (!wp_verify_nonce($_POST['references_management_add_meta_boxes_1'], 'references_management_add_meta_boxes_1')) {
        return $post_id;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }
    if ('page' === $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id)) {
            return $post_id;
        }
    } else {
        if (!current_user_can('edit_post', $post_id)) {
            return $post_id;
        }
    }
    $annotations = array();
    foreach ($_POST['references_management_semantic_4_ontologies'] AS $key => $_) {
        if (
            !empty($_POST['references_management_semantic_4_ontologies'][$key])
            and
            !empty($_POST['references_management_semantic_4_classes'][$key])
            and
            !empty($_POST['references_management_semantic_4_properties'][$key])
            and
            !empty($_POST['references_management_semantic_4_values'][$key])
        ) {
            $annotations[] = array(
                'ontologies' => $_POST['references_management_semantic_4_ontologies'][$key],
                'classes' => $_POST['references_management_semantic_4_classes'][$key],
                'properties' => $_POST['references_management_semantic_4_properties'][$key],
                'values' => $_POST['references_management_semantic_4_values'][$key],
            );
        }
    }
    update_post_meta(
        $post_id, 'references_management_1_multipage_report', $_POST['references_management_1_multipage_report']
    );
    update_post_meta(
        $post_id, 'references_management_1_root', $_POST['references_management_1_root']
    );
    update_post_meta(
        $post_id, 'references_management_2_references', $_POST['references_management_2_references']
    );
    update_post_meta(
        $post_id, 'references_management_2_table_of_contents', $_POST['references_management_2_table_of_contents']
    );
    update_post_meta(
        $post_id, 'references_management_semantic_4', json_encode($annotations)
    );
}

register_activation_hook(__FILE__, 'references_management_register_activation_hook');
register_deactivation_hook(__FILE__, 'references_management_register_deactivation_hook');

add_action('init', 'references_management_init');

add_action('admin_init', 'references_management_admin_init');
add_action('admin_menu', 'references_management_admin_menu');
add_action('add_meta_boxes', 'references_management_add_meta_boxes');
add_action('save_post', 'references_management_save_post');

add_shortcode('references_management', 'references_management_shortcode');
