<?php

function mlReferences_ris_upload($file, $name, $articles)
{
    mlReferences_models_documents_insert($file, $name, 'RIS', $articles);
}

function mlReferences_ris_download($id)
{
    $document = mlReferences_models_documents_select_one($id);
    $file = mlReferences_utilities_get_file(array($id, $document['name']));
    $contents = @file_get_contents($file);
    $records = explode('ER  - ', $contents);
    foreach ($records as $record) {
        $data = array();
        preg_match('#ID  - ([^\n)]*)#', $record, $match);
        $query = 'SELECT * FROM `%sarticles` WHERE `document_id` = %%d AND `number` = %%s';
        $query = sprintf($query, mlReferences_utilities_get_prefix());
        $query = $GLOBALS['wpdb']->prepare($query, $document['id'], $match[1]);
        $article = $GLOBALS['wpdb']->get_row($query, ARRAY_A);
        $offset = 0;
        foreach (explode("\n", $record) as $key => $value) {
            $value = trim($value);
            $prefix = explode('-', $value);
            $tags = array(
                'TY' , 'ID' , 'T1' , 'A1' , 'PB' , 'DO' , 'IS' , 'VL'
            );
            switch (trim($prefix[0])) {
            case $tags[0]:
                $data = preg_replace(
                    sprintf('#%s  - .*?$#', $tags[0]),
                    sprintf('%s  - %s', $tags[0], $article['type']),
                    $value
                );
                $contents = str_replace($value, $data, $contents);
                break;
            case $tags[1]:
                $data = preg_replace(
                    sprintf('#%s  - .*?$#', $tags[1]),
                    sprintf('%s  - %s', $tags[1], $article['number']),
                    $value
                );
                $contents = str_replace($value, $data, $contents);
                break;
            case $tags[2]:
                $data = preg_replace(
                    sprintf('#%s  - .*?$#', $tags[2]),
                    sprintf('%s  - %s', $tags[2], $article['title_1']),
                    $value
                );
                $contents = str_replace($value, $data, $contents);
                break;
            case $tags[3]:
                $query_ = <<<EOD
SELECT `%sauthors`.`name`, `%sauthors`.`first_name`
FROM `%sauthors`
INNER JOIN `%sarticles_authors`
ON `%sarticles_authors`.`article_id` = %%d AND `%sarticles_authors`.`author_id` = `%sauthors`.`id`
WHERE `%sarticles_authors`.`role` = %%s
ORDER BY `%sarticles_authors`.`id` ASC
LIMIT 1
OFFSET %d
EOD;
                $query_ = sprintf(
                    $query_,
                    mlReferences_utilities_get_prefix(),
                    mlReferences_utilities_get_prefix(),
                    mlReferences_utilities_get_prefix(),
                    mlReferences_utilities_get_prefix(),
                    mlReferences_utilities_get_prefix(),
                    mlReferences_utilities_get_prefix(),
                    mlReferences_utilities_get_prefix(),
                    mlReferences_utilities_get_prefix(),
                    mlReferences_utilities_get_prefix(),
                    $offset
                );
                $query_ = $GLOBALS['wpdb']->prepare($query_, $article['id'], 'Author');
                $author = $GLOBALS['wpdb']->get_row($query_, ARRAY_A);
                $offset += 1;
                $data = preg_replace(
                    sprintf('#%s  - .*?$#', $tags[3]),
                    sprintf('%s  - %s, %s', $tags[3], $author['name'], $author['first_name']),
                    $value
                );
                $contents = str_replace($value, $data, $contents);
                break;
            case $tags[4]:
                $data = preg_replace(
                    sprintf('#%s  - .*?$#', $tags[4]),
                    sprintf('%s  - %s', $tags[4], $article['publisher']),
                    $value
                );
                $contents = str_replace($value, $data, $contents);
                break;
            case $tags[5]:
                $data = preg_replace(
                    sprintf('#%s  - .*?$#', $tags[5]),
                    sprintf('%s  - %s', $tags[5], $article['doi']),
                    $value
                );
                $contents = str_replace($value, $data, $contents);
                break;
            case $tags[6]:
                $data = preg_replace(
                    sprintf('#%s  - .*?$#', $tags[6]),
                    sprintf('%s  - %s', $tags[6], $article['issue']),
                    $value
                );
                $contents = str_replace($value, $data, $contents);
                break;
            case $tags[7]:
                $data = preg_replace(
                    sprintf('#%s  - .*?$#', $tags[7]),
                    sprintf('%s  - %s', $tags[7], $article['volume']),
                    $value
                );
                $contents = str_replace($value, $data, $contents);
                break;
            }
        }
    }

    return array($document, $contents);
}

function mlReferences_ris_get_article($record)
{
    $article = array();

    $authors = array();

    $article['authors'] = array();

    $article['number'] = '';

    $article['type'] = '';

    $article['title_1'] = '';

    $article['title_2'] = '';

    $article['url'] = '';

    $article['year'] = '';

    $article['volume'] = '';

    $article['issue'] = '';

    $article['page'] = '';

    $article['doi'] = '';

    $article['issn'] = '';

    $article['original_publication'] = '';

    $article['publisher'] = '';

    $article['isbn'] = '';

    $article['label'] = '';

    $article['place_published'] = '';

    $article['access_date'] = '';

    $article['attachment'] = '';

    $article['citations_first'] = '';

    $article['citations_subsequent'] = '';

    $article['citations_parenthetical_first'] = '';

    $article['citations_parenthetical_subsequent'] = '';

    $article['references_authors'] = '';

    $article['references_editors'] = '';

    $article['references_all'] = '';

    foreach (explode("\n", $record) as $key => $value) {
        $value = trim($value);
        $prefix = explode('-', $value);
        switch (trim($prefix[0])) {
            case 'TY':
                $article['type'] = trim($prefix[1]);
                break;
            case 'ID':
                $article['number'] = trim($prefix[1]);
                break;
            case 'T1':
                $article['title_1'] = trim($prefix[1]);
                break;
            case 'UR':
                $article['url'] = trim($prefix[1]);
                break;
            case 'A1':
                $authors[] = trim($prefix[1]);
                break;
            case 'VL':
                $article['volume'] = trim($prefix[1]);
                break;
            case 'IS':
                $article['issue'] = trim($prefix[1]);
                break;
            case 'DO':
                $article['doi'] = trim($prefix[1]);
                break;
            case 'PB':
                $article['publisher'] = trim($prefix[1]);
                break;
        }

    }
    foreach ($authors as $author) {
        if (strpos($author, ',') !== false) {
            $explode = explode(',', $author, 2);
        } else {
            $explode = array( (string) $author );
        }
        $article['authors'][] = array(
            'name' => $explode[0],
            'first_name' => !empty($explode[1])? $explode[1]: '',
            'role' => 'Author',
            'url' => mlReferences_utilities_get_url(!empty($explode[1])? $explode[1]: '', $explode[0]),
        );
    }

    return $article;
}

function mlReferences_ris_get_articles($ris)
{
    $articles = array();

    $ris = @file_get_contents($ris);
    $records = explode('ER  - ', $ris);
    foreach ($records as $record) {
        if (strpos($record, 'TY  -') === false) {
            continue;
        }
        try {
            preg_match('#ID  - ([^\n)]*)#', $record, $matches);
            if (!empty($matches)) {
                $article = mlReferences_ris_get_article($record);
                $articles[] = $article;
            }
        } catch (Exception $exception) {
            return array($articles, sprintf('mlReferences_ris_get_articles() - %s', $exception->getMessage()));
        }
    }

    return array($articles, '');
}
