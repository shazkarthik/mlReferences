<?php

function mlReferences_utilities_delete($directory)
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

function mlReferences_utilities_filters_author($article)
{
    return $article['role'] === 'Author';
}

function mlReferences_utilities_filters_authors($article)
{
    if ($article === 'Ed') {
        return false;
    }
    if ($article === 'Eds') {
        return false;
    }
    if (substr($article, 0, 1) == '-') {
        return false;
    }
    if (preg_match('#[A-Za-z]\.$#', $article) !== 0) {
        return false;
    }
    if (strpos($article, '.') !== false) {
        return false;
    }

    return strlen($article) > 1;
}

function mlReferences_utilities_filters_editor($article)
{
    return $article['role'] === 'Editor';
}

function mlReferences_utilities_flashes()
{
    ?>
    <?php if (!empty($_SESSION['mlReferences']['flashes'])) : ?>
        <?php foreach ($_SESSION['mlReferences']['flashes'] as $key => $value) : ?>
            <div class="<?php echo $key; ?>">
                <p><strong><?php echo $value; ?></strong></p>
            </div>
        <?php endforeach; ?>
        <?php $_SESSION['mlReferences']['flashes'] = array(); ?>
    <?php endif; ?>
    <?php
}

function mlReferences_utilities_log($contents)
{
    if (defined('WP_DEBUG') && WP_DEBUG === true) {
        $file = '../mlReferences.log';
        file_put_contents($file, $contents, FILE_APPEND);
        file_put_contents($file, "\n", FILE_APPEND);
    }
}

function mlReferences_utilities_uasort($one, $two)
{
    preg_match('#[a-zA-Z]#', $one['string'], $match);
    $one = $match[0];
    preg_match('#[a-zA-Z]#', $two['string'], $match);
    $two = $match[0];
    if ($one === $two) {
        return 0;
    }
    return ($one < $two)? -1: 1;
}

function mlReferences_utilities_usort($one, $two)
{
    return strlen($two) - strlen($one);
}

function mlReferences_utilities_get_access_date($access_date)
{
    if (strpos($access_date, '/') !== false) {
        $access_date = str_replace('/', '.', $access_date);
        $access_date = mlReferences_utilities_get_access_date($access_date);
        return $access_date;
    }
    if (preg_match('#(\d{1,2})\.(\d{1,2})\.(\d{4})#', $access_date, $matches)) {
        $access_date = sprintf('%02d.%02d.%04d', $matches[1], $matches[2], $matches[3]);
        return $access_date;
    }
    if (preg_match('#(\d{4})\.(\d{1,2})\.(\d{1,2})#', $access_date, $matches)) {
        $access_date = sprintf('%02d.%02d.%04d', $matches[3], $matches[2], $matches[1]);
        return $access_date;
    }
    return '';
}

function mlReferences_utilities_get_admin_url($query)
{
    $admin_url = sprintf('admin.php?page=mlReferences&%s', $query);
    $admin_url = admin_url($admin_url);
    return $admin_url;
}

function mlReferences_utilities_get_author($name)
{
    $explode = array();
    $name = (string) $name->style;
    $name = trim($name);
    $preg_match = preg_match('#"(.*?)"$#', $name, $matches);
    if ($preg_match !== 0) {
        $explode[0] = $matches[1];
    } else {
        if (strpos($name, ',') !== false) {
            $explode = explode(',', $name, 2);
        } else {
            $explode = array_map('strrev', explode(' .', strrev($name), 2));
            if (@$explode[1]) {
                $explode[1] = sprintf('%s.', $explode[1]);
            }
        }
    }
    $explode = array_map('trim', $explode);
    if (!$explode[0]) {
        return false;
    }
    return $explode;
}

function mlReferences_utilities_get_authors($article)
{
    $authors = array();
    foreach ($article['authors'] as $author) {
        $authors[] = array(
            'name' => $author['name'],
            'first_name' => $author['first_name'],
        );
    }
    $authors = array_unique($authors, SORT_REGULAR);
    return $authors;
}

function mlReferences_utilities_get_citations_first($authors, $year)
{
    $count = count($authors);
    if ($count === 0) {
        return '';
    }
    if ($count === 1) {
        return sprintf('%s et al., %s', @$authors[0]['name'], $year);
    }
    if ($count === 2) {
        return sprintf('%s & %s et al., %s', @$authors[0]['name'], @$authors[1]['name'], $year);
    }
    if ($count === 3 or $count === 4 or $count === 5) {
        $names = array();
        if (!empty($authors)) {
            foreach ($authors as $key => $value) {
                $separator = ',';
                if ($key + 1 === $count - 1) {
                    $separator = ' &';
                }
                if ($key + 1 === $count) {
                    $separator = '';
                }
                $names[] = sprintf('%s%s', $value['name'], $separator);
            }
        }
        return sprintf('%s et al., %s', implode(' ', $names), $year);
    }
    return sprintf('%s et al., %s', @$authors[0]['name'], $year);
}

function mlReferences_utilities_get_citations_parenthetical_first($authors, $year)
{
    $count = count($authors);
    if ($count === 0) {
        return '';
    }
    if ($count === 1) {
        return sprintf('(%s, %s)', @$authors[0]['name'], $year);
    }
    if ($count === 2) {
        return sprintf('(%s & %s, %s)', @$authors[0]['name'], @$authors[1]['name'], $year);
    }
    if ($count === 3 or $count === 4 or $count === 5) {
        $names = array();
        if (!empty($authors)) {
            foreach ($authors as $key => $value) {
                $separator = ',';
                if ($key + 1 === $count - 1) {
                    $separator = ' &';
                }
                if ($key + 1 === $count) {
                    $separator = '';
                }
                $names[] = sprintf('%s%s', $value['name'], $separator);
            }
        }
        return sprintf('(%s, %s)', implode(' ', $names), $year);
    }
    return sprintf('(%s et al., %s)', @$authors[0]['name'], $year);
}

function mlReferences_utilities_get_citations_parenthetical_subsequent($authors, $year)
{
    $count = count($authors);
    if ($count === 0) {
        return '';
    }
    if ($count === 1) {
        return sprintf('(%s, %s)', @$authors[0]['name'], $year);
    }
    if ($count === 2) {
        return sprintf('(%s & %s, %s)', @$authors[0]['name'], @$authors[1]['name'], $year);
    }
    return sprintf('(%s et al., %s)', @$authors[0]['name'], $year);
}

function mlReferences_utilities_get_citations_subsequent($authors, $year)
{
    $count = count($authors);
    if ($count === 0) {
        return '';
    }
    if ($count === 1) {
        return sprintf('%s (%s)', @$authors[0]['name'], $year);
    }
    if ($count === 2) {
        return sprintf('%s & %s (%s)', @$authors[0]['name'], @$authors[1]['name'], $year);
    }
    return sprintf('%s et al., (%s)', @$authors[0]['name'], $year);
}

function mlReferences_utilities_get_csv_dialect()
{
    return new Csv_Dialect(array(
        'delimiter' => ';',
        'escapechar' => "\"",
        'lineterminator' => "\n",
        'quotechar' => "\"",
        'quoting' => Csv_Dialect::QUOTE_ALL,
        'skipblanklines' => true,
    ));
}

function mlReferences_utilities_get_directory($items)
{
    $directory = mlReferences_utilities_get_file($items);
    if (!@is_dir($directory)) {
        @mkdir($directory, 0777, true);
    }
    return $directory;
}

function mlReferences_utilities_get_endnote($article, &$txt)
{
    $article['authors'] = mlReferences_utilities_get_authors($article);
    mlReferences_utilities_log(str_repeat('-', 80));
    mlReferences_utilities_log('Article');
    mlReferences_utilities_log(sprintf('    Title          : %s', $article['title_1']));
    mlReferences_utilities_log(sprintf('    Year           : %s', $article['year']));
    mlReferences_utilities_log(sprintf('    Publisher      : %s', $article['publisher']));
    mlReferences_utilities_log(sprintf('    Place Published: %s', $article['place_published']));
    mlReferences_utilities_log('    Authors:');
    foreach ($article['authors'] as $author) {
        mlReferences_utilities_log(sprintf('        %s, %s', $author['name'], $author['first_name']));
    }
    foreach ($txt as $key => $value) {
        $statuses = array(
            'title' => false,
            'authors' => false,
        );
        mlReferences_utilities_log(sprintf('Checking if "%s" is a match...', $value));
        $value = mlReferences_utilities_get_value($value);
        $strlen = strlen($article['title_1']);
        $title_1 = substr($value[1], 0, $strlen);
        if ($article['title_1'] === $title_1) {
            if ((!empty($article['year']) and strpos($txt[$key], $article['year']) !== false) or
                (!empty($article['publisher']) and strpos($txt[$key], $article['publisher']) !== false) or
                (!empty($article['place_published']) and strpos($txt[$key], $article['place_published']) !== false)
            ) {
                $statuses['title'] = true;
                mlReferences_utilities_log('    Step 1: Title is a match');
            } else {
                mlReferences_utilities_log(
                    '    Step 1: Title is a match, but year/publisher/place published is not a match'
                );
            }
        } else {
            mlReferences_utilities_log('    Step 1: Title is not a match');
        }
        if ($statuses['authors'] === false) {
            $authors_1 = $value[0];
            $authors_1 = preg_split('/[^\p{L}a-zA-Z-\'’]/iu', $authors_1);
            $authors_1 = array_filter($authors_1, 'mlReferences_utilities_filters_authors');
            $count_1 = count($authors_1);
            $count_2 = 0;
            foreach ($article['authors'] as $author) {
                if (in_array($author['name'], $authors_1)) {
                    $count_2 += 1;
                    continue;
                }
                foreach ($authors_1 as $k => $v) {
                    if (strpos($author['name'], $v) !== false) {
                        $count_2 += 1;
                        break;
                    }
                }
            }
            if ($count_2 === $count_1 && $count_1 > 0 && $count_2 > 0) {
                $statuses['authors'] = true;
                mlReferences_utilities_log('    Step 2.1: Authors are a match');
            } else {
                mlReferences_utilities_log('    Step 2.1: Authors are not a match');
            }
        }
        if ($statuses['authors'] === false) {
            $authors_2 = $value[0];
            $authors_2 = preg_split('/, |& /iu', $authors_2);
            $authors_2 = array_filter($authors_2, 'mlReferences_utilities_filters_authors');
            $authors_2 = array_map('trim', $authors_2);
            $count_1 = count($authors_2);
            $count_2 = 0;
            foreach ($article['authors'] as $author) {
                if (in_array($author['name'], $authors_2)) {
                    $count_2 += 1;
                    continue;
                }
                foreach ($authors_2 as $k => $v) {
                    if (strpos($author['name'], $v) !== false) {
                        $count_2 += 1;
                        break;
                    }
                }
            }
            if ($count_2 === $count_1 && $count_1 > 0 && $count_2 > 0) {
                $statuses['authors'] = true;
                mlReferences_utilities_log('    Step 2.2: Authors are a match');
            } else {
                mlReferences_utilities_log('    Step 2.2: Authors are not a match');
            }
        }
        if ($statuses['authors'] === false) {
            $authors_3 = $value[0];
            $authors_3 = explode(',', $authors_3, 2);
            $authors_3 = array_filter($authors_3, 'mlReferences_utilities_filters_authors');
            $authors_3 = array_map('trim', $authors_3);
            $count_1 = count($authors_3);
            $count_2 = 0;
            foreach ($article['authors'] as $author) {
                if (in_array($author['name'], $authors_3)) {
                    $count_2 += 1;
                    continue;
                }
                foreach ($authors_3 as $k => $v) {
                    if (strpos($author['name'], $v) !== false) {
                        $count_2 += 1;
                        break;
                    }
                }
            }
            if ($count_2 === $count_1 && $count_1 > 0 && $count_2 > 0) {
                $statuses['authors'] = true;
                mlReferences_utilities_log('    Step 2.3: Authors are a match');
            } else {
                mlReferences_utilities_log('    Step 2.3: Authors are not a match');
            }
        }
        if ($statuses['authors'] === false) {
            $authors_4 = $value[0];
            $count_1 = count($authors_4);
            $count_2 = 0;
            foreach ($article['authors'] as $author) {
                if ($author['name'] === $authors_4) {
                    $count_2 += 1;
                }
            }
            if ($count_2 === $count_1 && $count_1 > 0 && $count_2 > 0) {
                $statuses['authors'] = true;
                mlReferences_utilities_log('    Step 2.4: Authors are a match');
            } else {
                mlReferences_utilities_log('    Step 2.4: Authors are not a match');
            }
        }
        if ($statuses['title'] === true and $statuses['authors'] === true) {
            mlReferences_utilities_log('    Success: We have a match!');
            mlReferences_utilities_log(str_repeat('-', 80));
            return $key;
        } else {
            mlReferences_utilities_log('    Failure: We do not have a match!');
            mlReferences_utilities_log('    Continuing onto the next line...');
        }
    }
    mlReferences_utilities_log('Failure: None of the lines in the TXT file were a match!');
    mlReferences_utilities_log(str_repeat('-', 80));
    return -1;
}

function mlReferences_utilities_get_file($items)
{
    array_unshift($items, 'files');
    array_unshift($items, '..');
    array_unshift($items, rtrim(plugin_dir_path(__FILE__), '/'));
    return implode(DIRECTORY_SEPARATOR, $items);
}

function mlReferences_utilities_get_initials($name)
{
    $initials = array();
    $name = explode(' ', $name);
    if (!empty($name)) {
        foreach ($name as $key => $value) {
            $initials[] = sprintf('%s.', substr($value, 0, 1));
        }
    }
    return implode(' ', $initials);
}

function mlReferences_utilities_get_prefix()
{
    return sprintf('%smlReferences_', $GLOBALS['wpdb']->prefix);
}

function mlReferences_utilities_get_references_all($article)
{
    return sprintf(
        '%s. %s: %s. %s: %s',
        mlReferences_utilities_get_citations_first($article['authors'], $article['year']),
        $article['title_1'],
        $article['title_2'],
        $article['place_published'],
        $article['publisher']
    );
}

function mlReferences_utilities_get_references_authors($article)
{
    $article['authors'] = array_filter($article['authors'], 'mlReferences_utilities_filters_author');
    $count = count($article['authors']);
    if ($count === 0) {
        return '';
    }
    $names = array();
    if (!empty($article['authors'])) {
        foreach ($article['authors'] as $key => $value) {
            $separator = ',';
            if ($key + 1 === $count - 1) {
                $separator = ' &';
            }
            if ($key + 1 === $count) {
                $separator = '';
            }
            $names[] = sprintf('%s%s', $value['name'], $separator);
        }
    }
    return sprintf('%s, %s', implode(' ', $names), $article['year']);
}

function mlReferences_utilities_get_references_editors($article)
{
    $article['authors'] = array_filter($article['authors'], 'mlReferences_utilities_filters_editor');
    $count = count($article['authors']);
    if ($count === 0) {
        return '';
    }
    $names = array();
    if (!empty($article['authors'])) {
        foreach ($article['authors'] as $key => $value) {
            $separator = ',';
            if ($key + 1 === $count - 1) {
                $separator = ' &';
            }
            if ($key + 1 === $count) {
                $separator = '';
            }
            $names[] = sprintf('%s%s', $value['name'], $separator);
        }
    }
    return sprintf('%s, %s', implode(' ', $names), $article['year']);
}

function mlReferences_utilities_get_shortcodes($contents)
{
    $items = array();
    preg_match_all(
        '/\[mlReferences id=&#\d+;(.*?)&#\d+; style=&#\d+;(.*?)&#\d+;\]/',
        $contents,
        $matches,
        PREG_SET_ORDER
    );
    if (!empty($matches)) {
        $number = 0;
        foreach ($matches as $match) {
            $number++;
            $article = $GLOBALS['wpdb']->get_row(
                $GLOBALS['wpdb']->prepare(
                    sprintf("SELECT * FROM `%sarticles` WHERE `id` = %%d", mlReferences_utilities_get_prefix()),
                    intval($match[1])
                ),
                ARRAY_A
            );
            if (empty($items[$match[0]])) {
                $items[$match[0]] = array(
                    'id' => $match[1],
                    'style' => $match[2],
                    'numbers' => array(),
                    'string' => $article[$match[2]],
                );
            }
            $items[$match[0]]['numbers'][] = $number;
        }
    }
    return $items;
}

function mlReferences_utilities_get_txt($txt)
{
    $bom = pack('H*', 'EFBBBF');
    $txt = preg_replace("/^{$bom}/", '', $txt);
    return $txt;
}

function mlReferences_utilities_get_url($first_name, $last_name)
{
    if (@$_SERVER['SERVER_NAME'] === '0.0.0.0') {
        return '';
    }
    if (defined('WP_CLI') && WP_CLI) {
        return '';
    }
    $name = sprintf('%s %s', $first_name, $last_name);
    $xml = @file_get_contents(
        sprintf(
            'http://lookup.dbpedia.org/api/search/KeywordSearch?QueryClass=person&QueryString=%s',
            urlencode($name)
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
    foreach ($xml->xpath('//xpns:Result') as $key => $value) {
        if ((string) $value->Label === $name) {
            return (string) $value->URI;
        }
    }
    return '';
}

function mlReferences_utilities_get_value($value_1)
{
    $value_2 = preg_split('#\((\d\d\d\d|n\.d\.)\)\s*\.#', $value_1);
    if (count($value_2) < 2) {
        $value_2 = preg_split('#\((\d\d\d\d, \d\d\d\d|n\.d\.)\)\s*\.#', $value_1);
    }
    if (count($value_2) < 2) {
        $value_2 = preg_split('#\((\d\d\d\d, [A-Za-z]{3,}\.? \d{1,},? \d\d\d\d|n\.d\.)\)\s*\.#', $value_1);
    }
    if (count($value_2) < 2) {
        $value_2 = preg_split('#\((\d\d\d\d, \d{1,2}-\d{1,2} [A-Za-z]{3,}\.? \d\d\d\d|n\.d\.)\)\s*\.#', $value_1);
    }
    if (count($value_2) < 2) {
        $value_2 = preg_split(
            '#\((\d\d\d\d, \d{1,}\.\d{1,}\.\d\d\d\d|n\.d\.|n\.d\., \d{1,}\.\d{1,}\.\d\d\d\d)\)\s*\.#',
            $value_1
        );
    }
    if (count($value_2) < 2) {
        $value_2 = preg_split(
            '#\((\d\d\d\d, [A-Za-z]{3,} \d{1,} \d\d\d\d\-[a-zA-Z]{3,} \d{1,} \d\d\d\d|n\.d\.)\)\s*\.#',
            $value_1
        );
    }
    $value_2[0] = str_replace('"', '', @$value_2[0]);
    $value_2[0] = trim(@$value_2[0]);
    $value_2[0] = trim(@$value_2[0], '.');
    $value_2[1] = trim(@$value_2[1]);
    return $value_2;
}
