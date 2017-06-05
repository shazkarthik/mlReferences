<?php

function mlReferences_spreadsheet_upload($file, $name, $articles)
{
    mlReferences_models_documents_insert($file, $name, 'Spreadsheet', $articles);
}

function mlReferences_spreadsheet_download($id)
{
    $document = mlReferences_models_documents_select_one($id);
    $file = mlReferences_utilities_get_file(array($id, $document['name']));
    $type = PHPExcel_IOFactory::identify($file);
    $reader = PHPExcel_IOFactory::createReader($type);
    $load = $reader->load($file);
    $items = $load->getSheet(0)->toArray(null, true, true, true);
    if (!empty($items)) {
        foreach ($items as $key => $value) {
            if ($key === 1) {
                continue;
            }
            if (empty($value)) {
                continue;
            }
            $article = mlReferences_models_articles_select_one($document['id'], $value['A']);
            $load->getSheet(0)->setCellValue(sprintf('A%d', $key), $article['number']);
            $load->getSheet(0)->setCellValue(sprintf('B%d', $key), $article['type']);
            $load->getSheet(0)->setCellValue(sprintf('C%d', $key), $article['title_1']);
            $load->getSheet(0)->setCellValue(sprintf('D%d', $key), $article['title_2']);
            $load->getSheet(0)->setCellValue(sprintf('E%d', $key), $article['year']);
            $load->getSheet(0)->setCellValue(sprintf('F%d', $key), $article['volume']);
            $load->getSheet(0)->setCellValue(sprintf('G%d', $key), $article['issue']);
            $load->getSheet(0)->setCellValue(sprintf('H%d', $key), $article['page']);
            $load->getSheet(0)->setCellValue(sprintf('I%d', $key), $article['url']);
            $load->getSheet(0)->setCellValue(sprintf('J%d', $key), $article['doi']);
            $load->getSheet(0)->setCellValue(sprintf('K%d', $key), $article['issn']);
            $load->getSheet(0)->setCellValue(sprintf('L%d', $key), $article['original_publication']);
            $load->getSheet(0)->setCellValue(sprintf('M%d', $key), $article['isbn']);
            $load->getSheet(0)->setCellValue(sprintf('N%d', $key), $article['label']);
            $load->getSheet(0)->setCellValue(sprintf('O%d', $key), $article['publisher']);
            $load->getSheet(0)->setCellValue(sprintf('P%d', $key), $article['place_published']);
            $load->getSheet(0)->setCellValue(sprintf('Q%d', $key), $article['access_date']);
            $load->getSheet(0)->setCellValue(sprintf('R%d', $key), $article['attachment']);
            $authors = mlReferences_models_authors_select_many($article['id'], 'Author');
            if (!empty($authors)) {
                foreach ($authors as $key => $value) {
                    $authors[$key] = array($value['name'], $value['first_name']);
                    $authors[$key] = implode(', ', $authors[$key]);
                }
            }
            $authors = implode('|', $authors);
            $load->getSheet(0)->setCellValue(sprintf('S%d', $key), $authors);
            $editors = mlReferences_models_authors_select_many($article['id'], 'Editor');
            if (!empty($editors)) {
                foreach ($editors as $key => $value) {
                    $editors[$key] = array($value['name'], $value['first_name']);
                    $editors[$key] = implode(', ', $editors[$key]);
                }
            }
            $editors = implode('|', $editors);
            $load->getSheet(0)->setCellValue(sprintf('T%d', $key), $editors);
        }
    }
    $tempnam = tempnam(sys_get_temp_dir(), 'mlReferences');
    $load = new PHPExcel_Writer_Excel2007($load);
    $load->save($tempnam);
    return array($document, $tempnam);
}

function mlReferences_spreadsheet_get_article($value)
{
    $article = array();

    $article['number'] = $value['A'];

    $article['type'] = $value['B'];

    $article['title_1'] = $value['C'];

    $article['title_2'] = $value['D'];

    $article['year'] = $value['E'];

    $article['volume'] = $value['F'];

    $article['issue'] = $value['G'];

    $article['page'] = $value['H'];

    $article['url'] = $value['I'];

    $article['doi'] = $value['J'];

    $article['issn'] = $value['K'];

    $article['original_publication'] = $value['L'];

    $article['isbn'] = $value['M'];

    $article['label'] = $value['N'];

    $article['publisher'] = $value['O'];

    $article['place_published'] = $value['P'];

    $article['access_date'] = $value['Q'];

    $article['attachment'] = $value['R'];

    $article['authors'] = array();

    $authors = $value['S'];
    $authors = explode('|', $authors);
    if (!empty($authors)) {
        foreach ($authors as $author) {
            $author = trim($author);
            $author = explode(' ', $author, 2);
            $article['authors'][] = array(
                'name' => $author[0],
                'first_name' => !empty($author[1])? $author[1]: '',
                'role' => 'Author',
                'url' => '',
            );
        }
    }

    $editors = $value['T'];
    $editors = explode('|', $editors);
    if (!empty($editors)) {
        foreach ($editors as $editor) {
            $editor = trim($editor);
            $editor = explode(' ', $editor, 2);
            $article['authors'][] = array(
                'name' => $editor[0],
                'first_name' => !empty($editor[1])? $editor[1]: '',
                'role' => 'Editor',
                'url' => '',
            );
        }
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

    return $article;
}

function mlReferences_spreadsheet_get_articles($xlsx)
{
    $articles = array();
    $type = PHPExcel_IOFactory::identify($xlsx);
    $reader = PHPExcel_IOFactory::createReader($type);
    $load = $reader->load($xlsx);
    $items = $load->getSheet(0)->toArray(null, true, true, true);
    if (!empty($items)) {
        foreach ($items as $key => $value) {
            if ($key === 1) {
                continue;
            }
            if (empty($value)) {
                continue;
            }
            $article = mlReferences_spreadsheet_get_article($value);
            $articles[] = $article;
        }
    }
    return array($articles, '');
}
