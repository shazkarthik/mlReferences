<?php

function mlReferences_end_note_upload($file, $name, $items)
{
    mlReferences_models_documents_insert($_FILES['file_1']['name'], 'EndNote', $items);
}

function mlReferences_end_note_download($id)
{
    $document = mlReferences_models_documents_select_one($id);
    $file = mlReferences_utilities_get_file(array($id, $document['name']));
    $contents = @file_get_contents($file);
    $xml = @simplexml_load_string($contents);
    $records = $xml->xpath('//xml/records/record');
    foreach ($records as $record) {
        $number = $value->xpath('rec-number');
        $number = array_pop($number);
        $number = (string) $number;
        $article = mlReferences_models_articles_select_one($document['id'], $number);
        if (!$article) {
            continue;
        }
        $types = $value->xpath('ref-type');
        foreach ($types as $type) {
            $dom = dom_import_simplexml($type);
            $dom->setAttribute('name', $article['type']);
        }
        $title_1s = $value->xpath('titles/title/style');
        foreach ($title_1s as $title_1) {
            $dom = dom_import_simplexml($title_1);
            $dom->nodeValue = $article['title_1'];
        }
        $title_2s = $value->xpath('titles/secondary-title/style');
        foreach ($title_2s as $title_2) {
            $dom = dom_import_simplexml($title_2);
            $dom->nodeValue = $article['title_2'];
        }
        $years = $value->xpath('dates/year/style');
        foreach ($years as $year) {
            $dom = dom_import_simplexml($year);
            $dom->nodeValue = $article['year'];
        }
        $volumes = $value->xpath('volume/style');
        foreach ($volumes as $volume) {
            $dom = dom_import_simplexml($volume);
            $dom->nodeValue = $article['volume'];
        }
        $issues = $value->xpath('number/style');
        foreach ($issues as $issue) {
            $dom = dom_import_simplexml($issue);
            $dom->nodeValue = $article['issue'];
        }
        $pages = $value->xpath('pages/style');
        foreach ($pages as $page) {
            $dom = dom_import_simplexml($page);
            $dom->nodeValue = $article['page'];
        }
        $urls = $value->xpath('urls/related-urls/url/style');
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
        $issns = $value->xpath('orig-pub/style');
        foreach ($issns as $issn) {
            $dom = dom_import_simplexml($issn);
            $dom->nodeValue = $article['issn'];
        }
        $original_publications = $value->xpath('orig-pub/style');
        foreach ($original_publications as $original_publication) {
            $dom = dom_import_simplexml($original_publication);
            $dom->nodeValue = $article['original_publication'];
        }
        $isbns = $value->xpath('isbn/style');
        foreach ($isbns as $isbn) {
            $dom = dom_import_simplexml($isbn);
            $dom->nodeValue = $article['isbn'];
        }
        $labels = $value->xpath('label/style');
        foreach ($labels as $label) {
            $dom = dom_import_simplexml($label);
            $dom->nodeValue = $article['label'];
        }
        $publishers = $value->xpath('publisher/style');
        foreach ($publishers as $publisher) {
            $dom = dom_import_simplexml($publisher);
            $dom->nodeValue = $article['publisher'];
        }
        $places_published = $value->xpath('pub-location/style');
        foreach ($places_published as $place_published) {
            $dom = dom_import_simplexml($place_published);
            $dom->nodeValue = $article['place_published'];
        }
        $access_dates = $value->xpath('access-date/style');
        foreach ($access_dates as $access_date) {
            $dom = dom_import_simplexml($access_date);
            $dom->nodeValue = $article['access_date'];
        }
        $attachments = $value->xpath('urls/pdf-urls/url');
        foreach ($attachments as $attachment) {
            $dom = dom_import_simplexml($attachment);
            $dom->nodeValue = sprintf('internal-pdf%s', $article['attachment']);
        }
        $authors = $value->xpath('contributors/authors/author/style');
        foreach ($authors as $k => $v) {
            $author = mlReferences_models_authors_select_one($article['id'], 'Author', $k);
            if ($author) {
                $dom = dom_import_simplexml($v);
                $dom->nodeValue = sprintf('%s, %s', $author['name'], $author['first_name']);
            }
        }
        $editors = $value->xpath('contributors/secondary-authors/author/style');
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

function mlReferences_end_note_get_item($record, $txt)
{
    $item = array();

    $item['number'] = $record->xpath('rec-number');
    $item['number'] = (string) array_pop($item['number']);
    $item['number'] = trim($item['number']);

    $item['type'] = $record->xpath('ref-type');
    $item['type'] = (string) array_pop($item['type'])->attributes()['name'];
    $item['type'] = trim($item['type']);

    $item['title_1'] = $record->xpath('titles/title/style');
    $item['title_1'] = (string) array_pop($item['title_1']);
    $item['title_1'] = trim($item['title_1']);

    $item['title_2'] = $record->xpath('titles/secondary-title/style');
    $item['title_2'] = (string) array_pop($item['title_2']);
    $item['title_2'] = trim($item['title_2']);

    $item['year'] = $record->xpath('dates/year/style');
    $item['year'] = (string) array_pop($item['year']);
    $item['year'] = trim($item['year']);

    $item['volume'] = $record->xpath('volume/style');
    $item['volume'] = (string) array_pop($item['volume']);
    $item['volume'] = trim($item['volume']);

    $item['issue'] = $record->xpath('number/style');
    $item['issue'] = (string) array_pop($item['issue']);
    $item['issue'] = trim($item['issue']);

    $item['page'] = $record->xpath('pages/style');
    $item['page'] = (string) array_pop($item['page']);
    $item['page'] = trim($item['page']);

    $urls = $record->xpath('urls/related-urls/url/style');

    $item['url'] = '';
    foreach ($urls as $url) {
        $url = (string) $url;
        $url = trim($url);
        if (stristr($url, 'doi') === false) {
            $item['url'] = $url;
            break;
        }
    }

    $item['doi'] = '';
    foreach ($urls as $url) {
        $url = (string) $url;
        $url = trim($url);
        if (stristr($url, 'doi') !== false) {
            $item['doi'] = $url;
            break;
        }
    }

    $item['issn'] = $record->xpath('orig-pub/style');
    $item['issn'] = (string) array_pop($item['issn']);
    $item['issn'] = trim($item['issn']);
    if ($item['issn'] === 'Contents') {
        $item['issn'] = '';
    }
    if ($item['issn'] === 'Original Publication') {
        $item['issn'] = '';
    }
    $item['issn'] = str_replace('ISSN: ', '', $item['issn']);

    $item['original_publication'] = $record->xpath('orig-pub/style');
    $item['original_publication'] = (string) array_pop($item['original_publication']);
    $item['original_publication'] = trim($item['original_publication']);

    $item['isbn'] = $record->xpath('isbn/style');
    $item['isbn'] = (string) array_pop($item['isbn']);
    $item['isbn'] = trim($item['isbn']);
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
    $item['isbn'] = preg_replace('/[^0-9A-Z ]/', '', $item['isbn']);
    $item['isbn'] = trim($item['isbn']);
    $item['isbn'] = explode(' ', $item['isbn']);
    usort($item['isbn'], 'mlReferences_utilities_usort');
    $item['isbn'] = $item['isbn'][0];
    $strlen = strlen($item['isbn']);
    if ($strlen === 8) {
        $item['isbn'] = '';
        $item['issn'] = sprintf('%s-%s', substr($item['isbn'], 0, 4), substr($item['isbn'], 4, 4));
    }
    if ($strlen === 10) {
        $item['isbn'] = sprintf(
            '%s-%s-%s-%s',
            substr($item['isbn'], 0, 1),
            substr($item['isbn'], 1, 3),
            substr($item['isbn'], 4, 5),
            substr($item['isbn'], 9, 1)
        );
    }
    if ($strlen === 13) {
        $item['isbn'] = sprintf(
            '%s-%s-%s-%s-%s',
            substr($item['isbn'], 0, 3),
            substr($item['isbn'], 3, 1),
            substr($item['isbn'], 4, 3),
            substr($item['isbn'], 7, 5),
            substr($item['isbn'], 12, 1)
        );
    }

    $item['label'] = $record->xpath('label/style');
    $item['label'] = (string) array_pop($item['label']);
    $item['label'] = trim($item['label']);

    $item['publisher'] = $record->xpath('publisher/style');
    $item['publisher'] = (string) array_pop($item['publisher']);
    $item['publisher'] = trim($item['publisher']);

    $item['place_published'] = $record->xpath('pub-location/style');
    $item['place_published'] = (string) array_pop($item['place_published']);
    $item['place_published'] = trim($item['place_published']);

    $item['access_date'] = $record->xpath('access-date/style');
    $item['access_date'] = (string) array_pop($item['access_date']);
    $item['access_date'] = trim($item['access_date']);
    $item['access_date'] = mlReferences_utilities_get_access_date($item['access_date']);

    $item['attachment'] = $record->xpath('urls/pdf-urls/url');
    $item['attachment'] = (string) array_pop($item['attachment']);
    $item['attachment'] = trim($item['attachment']);
    $item['attachment'] = str_replace('internal-pdf', '', $item['attachment']);

    $item['authors'] = array();
    foreach ($record->xpath('contributors/authors/author') as $name) {
        $author = mlReferences_utilities_get_author($name);
        if ($author === false) {
            continue;
        }
        $item['authors'][] = array(
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
        $item['authors'][] = array(
            'name' => $author[0],
            'first_name' => !empty($author[1])? $author[1]: '',
            'role' => 'Author',
            'url' => mlReferences_utilities_get_url(!empty($author[1])? $author[1]: '', $author[0]),
        );
    }
    $item['authors'] = array_unique($item['authors'], SORT_REGULAR);

    $item['citations_first'] = mlReferences_utilities_get_citations_first($item['authors'], $item['year']);

    $item['citations_subsequent'] = mlReferences_utilities_get_citations_subsequent($item['authors'], $item['year']);

    $item['citations_parenthetical_first'] = mlReferences_utilities_get_citations_parenthetical_first(
        $item['authors'],
        $item['year']
    );

    $item['citations_parenthetical_subsequent'] = mlReferences_utilities_get_citations_parenthetical_subsequent(
        $item['authors'],
        $item['year']
    );

    $item['references_authors'] = mlReferences_utilities_get_references_authors($item);

    $item['references_editors'] = mlReferences_utilities_get_references_editors($item);

    $item['references_all'] = mlReferences_utilities_get_references_all($item);

    $item['endnote'] = '';
    $endnote = mlReferences_utilities_get_endnote($item, $txt);
    if ($endnote !== -1) {
        $item['endnote'] = $txt[$endnote];
        unset($txt[$endnote]);
    }

    return $item;
}

function mlReferences_end_note_get_items($xml, $txt)
{
    $items = array();

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
        return array(array(), array());
    }
    foreach ($records as $record) {
        try {
            $item = mlReferences_end_note_get_item($record, $txt);
            $items[] = $item;
        } catch (Exception $exception) {
            return array(sprintf('mlReferences_end_note_get_items() - %s', $exception->getMessage()), array(), array());
        }
    }
    return array(array(), $items, $txt);
}
