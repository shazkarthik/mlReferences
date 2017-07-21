<?php

function mlReferences_zip_upload($articles, $authors, $articles_authors)
{
    if (!empty($articles)) {
        foreach ($articles as $article) {
            mlReferences_models_articles_update($article);
        }
    }
    if (!empty($authors)) {
        foreach ($authors as $author) {
            mlReferences_models_authors_update($article);
        }
    }
    if (!empty($articles_authors)) {
        foreach ($articles_authors as $article_author) {
            mlReferences_models_articles_authors_update($article_author);
        }
    }
}

function mlReferences_zip_upload_get_articles($file)
{
    $articles = array();
    $contents = @file_get_contents(sprintf('zip://%s#%s', $file, 'articles.csv'));
    if ($contents !== false) {
        $articles = str_getcsv($contents, "\n");
        if (!empty($articles)) {
            foreach ($articles as $key => $value) {
                $articles[$key] = str_getcsv($value, ';');
            }
        }
        return $articles;
    }
    $contents = @file_get_contents(sprintf('zip://%s#%s', $file, 'articles.xls'));
    if ($contents !== false) {
        $articles = mlReferences_zip_upload_get_items($contents);
        return $articles;
    }
    $contents = @file_get_contents(sprintf('zip://%s#%s', $file, 'articles.xlsx'));
    if ($contents !== false) {
        $articles = mlReferences_zip_upload_get_items($contents);
        return $articles;
    }
    return $articles;
}

function mlReferences_zip_upload_get_authors($file)
{
    $authors = array();
    $contents = @file_get_contents(sprintf('zip://%s#%s', $file, 'authors.csv'));
    if ($contents !== false) {
        $authors = str_getcsv($contents, "\n");
        if (!empty($authors)) {
            foreach ($authors as $key => $value) {
                $authors[$key] = str_getcsv($value, ';');
            }
        }
        return $authors;
    }
    $contents = @file_get_contents(sprintf('zip://%s#%s', $file, 'authors.xls'));
    if ($contents !== false) {
        $authors = mlReferences_zip_upload_get_items($contents);
        return $authors;
    }
    $contents = @file_get_contents(sprintf('zip://%s#%s', $file, 'authors.xlsx'));
    if ($contents !== false) {
        $authors = mlReferences_zip_upload_get_items($contents);
        return $authors;
    }
    return $authors;
}

function mlReferences_zip_upload_get_articles_authors($file)
{
    $articles_authors = array();
    $contents = @file_get_contents(sprintf('zip://%s#%s', $file, 'articles_authors.csv'));
    if ($contents !== false) {
        $articles_authors = str_getcsv($contents, "\n");
        if (!empty($articles_authors)) {
            foreach ($articles_authors as $key => $value) {
                $articles_authors[$key] = str_getcsv($value, ';');
            }
        }
        return $articles_authors;
    }
    $contents = @file_get_contents(sprintf('zip://%s#%s', $file, 'articles_authors.xls'));
    if ($contents !== false) {
        $articles_authors = mlReferences_zip_upload_get_items($contents);
        return $articles_authors;
    }
    $contents = @file_get_contents(sprintf('zip://%s#%s', $file, 'articles_authors.xlsx'));
    if ($contents !== false) {
        $articles_authors = mlReferences_zip_upload_get_items($contents);
        return $articles_authors;
    }
    return $articles_authors;
}

function mlReferences_zip_upload_get_items($contents)
{
    $items = array();
    $tempnam = tempnam(sys_get_temp_dir(), 'mlReferences_');
    $fopen = fopen($tempnam, 'w');
    fwrite($fopen, $contents);
    fclose($fopen);
    $type = PHPExcel_IOFactory::identify($tempnam);
    $reader = PHPExcel_IOFactory::createReader($type);
    $load = $reader->load($tempnam);
    $items = $load->getActiveSheet()->toArray(null, true, true, true);
    if (!empty($items)) {
        foreach ($items as $key => $value) {
            $item = array();
            if (!empty($value)) {
                foreach ($value as $k => $v) {
                    $k = PHPExcel_Cell::columnIndexFromString($k) - 1;
                    $item[$k] = $v;
                }
            }
            $items[$key] = $item;
        }
    }
    unlink($tempnam);
    return $items;
}

function mlReferences_zip_download($id)
{
    $document = mlReferences_models_documents_select_one($id);
    $tempnam = tempnam(sys_get_temp_dir(), 'mlReferences');
    $zip = new ZipArchive();
    $zip->open($tempnam, ZipArchive::CREATE);
    $articles = mlReferences_zip_download_get_articles($document);
    $zip->addFromString('articles.csv', $articles);
    $authors = mlReferences_zip_download_get_authors();
    $zip->addFromString('authors.csv', $authors);
    $articles_authors = mlReferences_zip_download_get_articles_authors($document);
    $zip->addFromString('articles_authors.csv', $articles_authors);
    $zip->close();
    return array($document, $tempnam);
}

function mlReferences_zip_download_get_articles($document)
{
    $articles = mlReferences_models_articles_select_all($document['id']);
    $resource = @fopen('php://temp/maxmemory:999999999', 'w');
    $dialect = mlReferences_utilities_get_csv_dialect();
    $writer = new Csv_Writer($resource, $dialect);
    $row = array(
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
        'endnote' => 'Full Reference (EndNote)',
        'references_authors' => 'Authors Publish Reference',
        'references_editors' => 'Editors Publish Reference',
        'references_all' => 'Full Reference (Derived)',
        'references_authors_' => 'Authors Publish Reference',
        'references_editors_' => 'Editors Publish Reference',
    );
    $writer->writeRow($row, ';');
    foreach ($articles as $article) {
        $article['references_authors_'] = $article['references_authors'];
        $article['references_editors_'] = $article['references_editors'];
        $writer->writeRow($article, ';');
    }
    @rewind($resource);
    $articles = stream_get_contents($resource);
    @fclose($resource);
    return $articles;
}

function mlReferences_zip_download_get_authors()
{
    $authors = mlReferences_models_authors_select_all();
    $resource = @fopen('php://temp/maxmemory:999999999', 'w');
    $dialect = mlReferences_utilities_get_csv_dialect();
    $writer = new Csv_Writer($resource, $dialect);
    $row = array(
        'id' => 'Identifier',
        'name' => 'Name',
        'first_name' => 'First Name',
        'url' => 'URL',
    );
    $writer->writeRow($row, ';');
    foreach ($authors as $author) {
        $writer->writeRow($author, ';');
    }
    @rewind($resource);
    $authors = stream_get_contents($resource);
    @fclose($resource);
    return $authors;
}

function mlReferences_zip_download_get_articles_authors($document)
{
    $articles_authors = mlReferences_models_articles_authors_select_all($document['id']);
    $resource = @fopen('php://temp/maxmemory:999999999', 'w');
    $dialect = mlReferences_utilities_get_csv_dialect();
    $writer = new Csv_Writer($resource, $dialect);
    $row = array(
        'id' => 'Identifier',
        'article_id' => 'Article Identifier',
        'author_id' => 'Author Identifier',
        'role' => 'Role',
    );
    $writer->writeRow($row, ';');
    foreach ($articles_authors as $article_author) {
        $writer->writeRow($article_author, ';');
    }
    @rewind($resource);
    $articles_authors = stream_get_contents($resource);
    @fclose($resource);
    return $articles_authors;
}
