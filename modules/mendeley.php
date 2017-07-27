<?php

function mlReferences_mendeley_upload($file, $name, $articles)
{
    mlReferences_models_documents_insert($file, $name, 'Mendeley', $articles);
}

function mlReferences_mendeley_download($id)
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
        $title_2s = '';
        $years = '';
        $volumes = '';
        $issues = '';
        $pages = '';
        $urls = $record->xpath('urls/pdf-urls/url');
        $issns = '';
        $original_publications = '';
        $isbns = '';
        $labels = '';
        $publishers = '';
        $places_published = '';
        $access_dates = '';
        $attachments = '';
        $authors = '';
        $editors = '';
    }
    $contents = $xml->asXML();
    return array($document, $contents);
}

function mlReferences_mendeley_get_article($record)
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

    $article['url'] = $record->xpath('urls/pdf-urls/url');
    $article['url'] = (string) array_pop($article['url']);
    $article['url'] = trim($article['url']);

    $article['title_2'] = '';

    $article['authors'] = array();

    $article['year'] = '';

    $article['volume'] = '';

    $article['issue'] = '';

    $article['page'] = '';

    $article['doi'] = '';

    $article['issn'] = '';

    $article['original_publication'] = '';

    $article['isbn'] = '';

    $article['label'] = '';

    $article['publisher'] = '';

    $article['place_published'] = '';

    $article['access_date'] = '';

    $article['attachment'] = '';

    $article['authors'] = '';

    $article['citations_first'] = '';

    $article['citations_subsequent'] = '';

    $article['citations_parenthetical_first'] = '';

    $article['citations_parenthetical_subsequent'] = '';

    $article['references_authors'] = '';

    $article['references_editors'] = '';

    $article['references_all'] = '';

    return $article;
}

function mlReferences_mendeley_get_articles($xml)
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
            $article = mlReferences_mendeley_get_article($record);
            $articles[] = $article;
        } catch (Exception $exception) {
            return array($articles, sprintf('mlReferences_mendeley_get_articles() - %s', $exception->getMessage()));
        }
    }

    return array($articles, '');
}
