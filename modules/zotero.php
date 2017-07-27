<?php

function mlReferences_zotero_upload($file, $name, $articles)
{
    mlReferences_models_documents_insert($file, $name, 'Zotero', $articles);
}

function mlReferences_zotero_download($id)
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
        $title_1s = $record->xpath('titles/title');
        foreach ($title_1s as $title_1) {
            $dom = dom_import_simplexml($title_1);
            $dom->nodeValue = $article['title_1'];
        }
        $title_2s = $record->xpath('titles/secondary-title');
        foreach ($title_2s as $title_2) {
            $dom = dom_import_simplexml($title_2);
            $dom->nodeValue = $article['title_2'];
        }
        $years = $record->xpath('dates/year');
        foreach ($years as $year) {
            $dom = dom_import_simplexml($year);
            $dom->nodeValue = $article['year'];
        }
        $volumes = $record->xpath('volume');
        foreach ($volumes as $volume) {
            $dom = dom_import_simplexml($volume);
            $dom->nodeValue = $article['volume'];
        }
        $issues = '';
        $pages = '';
        $urls = $record->xpath('urls/web-urls/url');
        foreach ($urls as $url) {
            $dom = dom_import_simplexml($url);
            $dom->nodeValue = $article['url'];
        }
        $issns = '';
        $original_publications = '';
        $isbns = $record->xpath('isbn');
        foreach ($isbns as $isbn) {
            $dom = dom_import_simplexml($isbn);
            $dom->nodeValue = $article['isbn'];
        }
        $labels = '';
        $publishers = $record->xpath('publisher');
        foreach ($publishers as $publisher) {
            $dom = dom_import_simplexml($publisher);
            $dom->nodeValue = $article['publisher'];
        }
        $places_published = $record->xpath('pub-location');
        foreach ($places_published as $place_published) {
            $dom = dom_import_simplexml($place_published);
            $dom->nodeValue = $article['place_published'];
        }
        $access_dates = '';
        $attachments = '';
        $authors = $record->xpath('contributors/authors/author');
        foreach ($authors as $k => $v) {
            $author = mlReferences_models_authors_select_one($article['id'], 'Author', $k);
            if ($author) {
                $dom = dom_import_simplexml($v);
                $dom->nodeValue = sprintf('%s, %s', $author['name'], $author['first_name']);
            }
        }
        $editors = '';
    }
    $contents = $xml->asXML();
    return array($document, $contents);
}

function mlReferences_zotero_get_article($record)
{
    $article = array();

    $article['number'] = $record->xpath('ref-type');
    $article['number'] = (string) array_pop($article['number']);
    $article['number'] = trim($article['number']);

    $article['type'] = $record->xpath('ref-type');
    $article['type'] = (string) array_pop($article['type'])->attributes()['name'];
    $article['type'] = trim($article['type']);

    $article['title_1'] = $record->xpath('titles/title');
    $article['title_1'] = (string) array_pop($article['title_1']);
    $article['title_1'] = trim($article['title_1']);

    $article['title_2'] = $record->xpath('titles/secondary-title');
    $article['title_2'] = (string) array_pop($article['title_2']);
    $article['title_2'] = trim($article['title_2']);

    $article['url'] = $record->xpath('urls/web-urls/url');
    $article['url'] = (string) array_pop($article['url']);
    $article['url'] = trim($article['url']);

    $article['authors'] = array();
    foreach ($record->xpath('contributors/authors/author') as $name) {
        if (strpos($name, ',') !== false) {
            $explode = explode(',', $name, 2);
        } else {
            $explode = array( (string) $name );
        }
        $article['authors'][] = array(
            'name' => $explode[0],
            'first_name' => !empty($explode[1])? $explode[1]: '',
            'role' => 'Author',
            'url' => mlReferences_utilities_get_url(!empty($explode[1])? $explode[1]: '', $explode[0]),
        );
    }
    $article['authors'] = array_unique($article['authors'], SORT_REGULAR);

    $article['year'] = $record->xpath('dates/year');
    $article['year'] = (string) array_pop($article['year']);
    $article['year'] = trim($article['year']);

    $article['volume'] = $record->xpath('volume');
    $article['volume'] = (string) array_pop($article['volume']);
    $article['volume'] = trim($article['volume']);

    $article['issue'] = '';

    $article['page'] = '';

    $article['doi'] = '';

    $article['issn'] = '';

    $article['original_publication'] = '';

    $article['isbn'] = $record->xpath('isbn');
    $article['isbn'] = (string) array_pop($article['isbn']);
    $article['isbn'] = trim($article['isbn']);

    $article['label'] = '';

    $article['publisher'] = $record->xpath('publisher');
    $article['publisher'] = (string) array_pop($article['publisher']);
    $article['publisher'] = trim($article['publisher']);

    $article['place_published'] = $record->xpath('pub-location');
    $article['place_published'] = (string) array_pop($article['place_published']);
    $article['place_published'] = trim($article['place_published']);

    $article['access_date'] = '';

    $article['attachment'] = '';

    $article['citations_first'] = '';

    $article['citations_subsequent'] = '';

    $article['citations_parenthetical_first'] = '';

    $article['citations_parenthetical_subsequent'] = '';

    $article['references_authors'] = '';

    $article['references_editors'] = '';

    $article['references_all'] = '';

    return $article;
}

function mlReferences_zotero_get_articles($xml)
{
    $articles = array();

    $xml = @file_get_contents($xml);
    $xml = @simplexml_load_string($xml);

    $records = $xml->xpath('//xml/records/record');

    if (empty($records)) {
        return array($articles);
    }

    foreach ($records as $record) {
        try {
            $article = mlReferences_zotero_get_article($record);
            $articles[] = $article;
        } catch (Exception $exception) {
            return array($articles, sprintf('mlReferences_zotero_get_articles() - %s', $exception->getMessage()));
        }
    }

    return array($articles, '');
}
