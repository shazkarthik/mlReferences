<?php

function mlReferences_end_note_upload($file, $name, $articles)
{
    mlReferences_models_documents_insert($file, $name, 'EndNote', $articles);
}

function mlReferences_end_note_download($id)
{
    $document = mlReferences_models_documents_select_one($id);
    $file = mlReferences_utilities_get_file(array($id, $document['name']));
    $contents = @file_get_contents($file);
    $xml = @simplexml_load_string($contents);
    $records = $xml->xpath('//xml/records/record');
    foreach ($records as $record) {
        $number = $record->xpath('rec-number');
        $number = array_pop($number);
        $number = (string) $number;
        $article = mlReferences_models_articles_select_one($document['id'], $number);
        if (!$article) {
            continue;
        }
        $types = $record->xpath('ref-type');
        foreach ($types as $type) {
            $dom = dom_import_simplexml($type);
            $dom->setAttribute('name', $article['type']);
        }
        $title_1s = $record->xpath('titles/title/style');
        foreach ($title_1s as $title_1) {
            $dom = dom_import_simplexml($title_1);
            $dom->nodeValue = $article['title_1'];
        }
        $title_2s = $record->xpath('titles/secondary-title/style');
        foreach ($title_2s as $title_2) {
            $dom = dom_import_simplexml($title_2);
            $dom->nodeValue = $article['title_2'];
        }
        $years = $record->xpath('dates/year/style');
        foreach ($years as $year) {
            $dom = dom_import_simplexml($year);
            $dom->nodeValue = $article['year'];
        }
        $volumes = $record->xpath('volume/style');
        foreach ($volumes as $volume) {
            $dom = dom_import_simplexml($volume);
            $dom->nodeValue = $article['volume'];
        }
        $issues = $record->xpath('number/style');
        foreach ($issues as $issue) {
            $dom = dom_import_simplexml($issue);
            $dom->nodeValue = $article['issue'];
        }
        $pages = $record->xpath('pages/style');
        foreach ($pages as $page) {
            $dom = dom_import_simplexml($page);
            $dom->nodeValue = $article['page'];
        }
        $urls = $record->xpath('urls/related-urls/url/style');
        foreach ($urls as $url) {
            $dom = dom_import_simplexml($url);
            if (stristr($url, 'doi') === false) {
                $dom->nodeValue = $article['url'];
                break;
            }
        }
        foreach ($urls as $url) {
            $dom = dom_import_simplexml($url);
            if (stristr($url, 'doi') !== false) {
                $dom->nodeValue = $article['doi'];
                break;
            }
        }
        $issns = $record->xpath('orig-pub/style');
        foreach ($issns as $issn) {
            $dom = dom_import_simplexml($issn);
            $dom->nodeValue = $article['issn'];
        }
        $original_publications = $record->xpath('orig-pub/style');
        foreach ($original_publications as $original_publication) {
            $dom = dom_import_simplexml($original_publication);
            $dom->nodeValue = $article['original_publication'];
        }
        $isbns = $record->xpath('isbn/style');
        foreach ($isbns as $isbn) {
            $dom = dom_import_simplexml($isbn);
            $dom->nodeValue = $article['isbn'];
        }
        $labels = $record->xpath('label/style');
        foreach ($labels as $label) {
            $dom = dom_import_simplexml($label);
            $dom->nodeValue = $article['label'];
        }
        $publishers = $record->xpath('publisher/style');
        foreach ($publishers as $publisher) {
            $dom = dom_import_simplexml($publisher);
            $dom->nodeValue = $article['publisher'];
        }
        $places_published = $record->xpath('pub-location/style');
        foreach ($places_published as $place_published) {
            $dom = dom_import_simplexml($place_published);
            $dom->nodeValue = $article['place_published'];
        }
        $access_dates = $record->xpath('access-date/style');
        foreach ($access_dates as $access_date) {
            $dom = dom_import_simplexml($access_date);
            $dom->nodeValue = $article['access_date'];
        }
        $attachments = $record->xpath('urls/pdf-urls/url');
        foreach ($attachments as $attachment) {
            $dom = dom_import_simplexml($attachment);
            $dom->nodeValue = sprintf('internal-pdf%s', $article['attachment']);
        }
        $authors = $record->xpath('contributors/authors/author/style');
        foreach ($authors as $k => $v) {
            $author = mlReferences_models_authors_select_one($article['id'], 'Author', $k);
            if ($author) {
                $dom = dom_import_simplexml($v);
                $dom->nodeValue = sprintf('%s, %s', $author['name'], $author['first_name']);
            }
        }
        $editors = $record->xpath('contributors/secondary-authors/author/style');
        foreach ($editors as $k => $v) {
            $author = mlReferences_models_authors_select_one($article['id'], 'Editor', $k);
            if ($author) {
                $dom = dom_import_simplexml($v);
                $dom->nodeValue = sprintf('%s, %s', $author['name'], $author['first_name']);
            }
        }
    }
    $contents = $xml->asXML();
    return array($document, $contents);
}

function mlReferences_end_note_get_article($record, &$txt)
{
    $article = array();

    $article['number'] = $record->xpath('rec-number');
    $article['number'] = (string) array_pop($article['number']);
    $article['number'] = trim($article['number']);

    $article['type'] = $record->xpath('ref-type');
    $article['type'] = (string) array_pop($article['type'])->attributes()['name'];
    $article['type'] = trim($article['type']);

    $article['title_1'] = $record->xpath('titles/title/style');
    $article['title_1'] = (string) array_pop($article['title_1']);
    $article['title_1'] = trim($article['title_1']);

    $article['title_2'] = $record->xpath('titles/secondary-title/style');
    $article['title_2'] = (string) array_pop($article['title_2']);
    $article['title_2'] = trim($article['title_2']);

    $article['year'] = $record->xpath('dates/year/style');
    $article['year'] = (string) array_pop($article['year']);
    $article['year'] = trim($article['year']);

    $article['volume'] = $record->xpath('volume/style');
    $article['volume'] = (string) array_pop($article['volume']);
    $article['volume'] = trim($article['volume']);

    $article['issue'] = $record->xpath('number/style');
    $article['issue'] = (string) array_pop($article['issue']);
    $article['issue'] = trim($article['issue']);

    $article['page'] = $record->xpath('pages/style');
    $article['page'] = (string) array_pop($article['page']);
    $article['page'] = trim($article['page']);

    $urls = $record->xpath('urls/related-urls/url/style');

    $article['url'] = '';
    foreach ($urls as $url) {
        $url = (string) $url;
        $url = trim($url);
        if (stristr($url, 'doi') === false) {
            $article['url'] = $url;
            break;
        }
    }

    $article['doi'] = '';
    foreach ($urls as $url) {
        $url = (string) $url;
        $url = trim($url);
        if (stristr($url, 'doi') !== false) {
            $article['doi'] = $url;
            break;
        }
    }

    $article['issn'] = $record->xpath('orig-pub/style');
    $article['issn'] = (string) array_pop($article['issn']);
    $article['issn'] = trim($article['issn']);
    if ($article['issn'] === 'Contents') {
        $article['issn'] = '';
    }
    if ($article['issn'] === 'Original Publication') {
        $article['issn'] = '';
    }
    $article['issn'] = str_replace('ISSN: ', '', $article['issn']);

    $article['original_publication'] = $record->xpath('orig-pub/style');
    $article['original_publication'] = (string) array_pop($article['original_publication']);
    $article['original_publication'] = trim($article['original_publication']);

    $article['isbn'] = $record->xpath('isbn/style');
    $article['isbn'] = (string) array_pop($article['isbn']);
    $article['isbn'] = trim($article['isbn']);
    if ($article['isbn'] === 'ISBN') {
        $article['isbn'] = '';
    }
    if ($article['isbn'] === 'ISSN') {
        $article['isbn'] = '';
    }
    if ($article['isbn'] === 'Report Number') {
        $article['isbn'] = '';
    }
    $article['isbn'] = str_replace('ISSN: ', '', $article['isbn']);
    $article['isbn'] = str_replace("\n", ' ', $article['isbn']);
    $article['isbn'] = str_replace("\r", ' ', $article['isbn']);
    $article['isbn'] = str_replace("\t", ' ', $article['isbn']);
    $article['isbn'] = preg_replace('/[^0-9A-Z ]/', '', $article['isbn']);
    $article['isbn'] = trim($article['isbn']);
    $article['isbn'] = explode(' ', $article['isbn']);
    usort($article['isbn'], 'mlReferences_utilities_usort');
    $article['isbn'] = $article['isbn'][0];
    $strlen = strlen($article['isbn']);
    if ($strlen === 8) {
        $article['issn'] = sprintf('%s-%s', substr($article['isbn'], 0, 4), substr($article['isbn'], 4, 4));
        $article['isbn'] = '';
    }
    if ($strlen === 10) {
        $article['isbn'] = sprintf(
            '%s-%s-%s-%s',
            substr($article['isbn'], 0, 1),
            substr($article['isbn'], 1, 3),
            substr($article['isbn'], 4, 5),
            substr($article['isbn'], 9, 1)
        );
    }
    if ($strlen === 13) {
        $article['isbn'] = sprintf(
            '%s-%s-%s-%s-%s',
            substr($article['isbn'], 0, 3),
            substr($article['isbn'], 3, 1),
            substr($article['isbn'], 4, 3),
            substr($article['isbn'], 7, 5),
            substr($article['isbn'], 12, 1)
        );
    }

    $article['label'] = $record->xpath('label/style');
    $article['label'] = (string) array_pop($article['label']);
    $article['label'] = trim($article['label']);

    $article['publisher'] = $record->xpath('publisher/style');
    $article['publisher'] = (string) array_pop($article['publisher']);
    $article['publisher'] = trim($article['publisher']);

    $article['place_published'] = $record->xpath('pub-location/style');
    $article['place_published'] = (string) array_pop($article['place_published']);
    $article['place_published'] = trim($article['place_published']);

    $article['access_date'] = $record->xpath('access-date/style');
    $article['access_date'] = (string) array_pop($article['access_date']);
    $article['access_date'] = trim($article['access_date']);
    $article['access_date'] = mlReferences_utilities_get_access_date($article['access_date']);

    $article['attachment'] = $record->xpath('urls/pdf-urls/url');
    $article['attachment'] = (string) array_pop($article['attachment']);
    $article['attachment'] = trim($article['attachment']);
    $article['attachment'] = str_replace('internal-pdf', '', $article['attachment']);

    $article['authors'] = array();
    foreach ($record->xpath('contributors/authors/author') as $name) {
        $author = mlReferences_utilities_get_author($name);
        if ($author === false) {
            continue;
        }
        $article['authors'][] = array(
            'name' => $author[0],
            'first_name' => !empty($author[1])? $author[1]: '',
            'role' => 'Author',
            'url' => mlReferences_utilities_get_url(!empty($author[1])? $author[1]: '', $author[0]),
        );
    }
    foreach ($record->xpath('contributors/secondary-authors/author') as $name) {
        $author = mlReferences_utilities_get_author($name);
        if ($author === false) {
            continue;
        }
        $article['authors'][] = array(
            'name' => $author[0],
            'first_name' => !empty($author[1])? $author[1]: '',
            'role' => 'Editor',
            'url' => mlReferences_utilities_get_url(!empty($author[1])? $author[1]: '', $author[0]),
        );
    }
    $article['authors'] = array_unique($article['authors'], SORT_REGULAR);

    $article['citations_first'] = mlReferences_utilities_get_citations_first($article['authors'], $article['year']);

    $article['citations_subsequent'] = mlReferences_utilities_get_citations_subsequent(
        $article['authors'],
        $article['year']
    );

    $article['citations_parenthetical_first'] = mlReferences_utilities_get_citations_parenthetical_first(
        $article['authors'],
        $article['year']
    );

    $article['citations_parenthetical_subsequent'] = mlReferences_utilities_get_citations_parenthetical_subsequent(
        $article['authors'],
        $article['year']
    );

    $article['references_authors'] = mlReferences_utilities_get_references_authors($article);

    $article['references_editors'] = mlReferences_utilities_get_references_editors($article);

    $article['references_all'] = mlReferences_utilities_get_references_all($article);

    $article['endnote'] = '';
    $endnote = mlReferences_utilities_get_endnote($article, $txt);
    if ($endnote !== -1) {
        $article['endnote'] = $txt[$endnote];
        unset($txt[$endnote]);
    }

    return $article;
}

function mlReferences_end_note_get_articles($xml, $txt)
{
    $articles = array();

    $xml = @file_get_contents($xml);
    $xml = @simplexml_load_string($xml);

    $txt = @file_get_contents($txt);
    if (empty($txt)) {
        $txt = '';
    }
    $txt = mlReferences_utilities_get_txt($txt);
    $txt = explode("\n", $txt);
    $txt = array_map('trim', $txt);

    $records = $xml->xpath('//xml/records/record');

    if (empty($records)) {
        return array($articles, $txt, '');
    }

    foreach ($records as $record) {
        try {
            $article = mlReferences_end_note_get_article($record, $txt);
            $articles[] = $article;
        } catch (Exception $exception) {
            return array($articles, $txt, sprintf('mlReferences_end_note_get_articles() - %s', $exception->getMessage()));
        }
    }

    return array($articles, $txt, '');
}
