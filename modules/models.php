<?php

function mlReferences_models_documents_select_all()
{
    $query = 'SELECT * FROM `%sdocuments` ORDER BY `id` DESC';
    $query = sprintf($query, mlReferences_utilities_get_prefix());
    $documents = $GLOBALS['wpdb']->get_results($query, ARRAY_A);
    return $documents;
}

function mlReferences_models_documents_select_one($id)
{
    $query = 'SELECT * FROM `%sdocuments` WHERE `id` = %%d';
    $query = sprintf($query, mlReferences_utilities_get_prefix());
    $query = $GLOBALS['wpdb']->prepare($query, $id);
    $document = $GLOBALS['wpdb']->get_row($query, ARRAY_A);
    return $document;
}

function mlReferences_models_documents_insert($file, $name, $type, $articles)
{
    $array = array(
        'name' => $name,
        'type' => $type,
    );
    $GLOBALS['wpdb']->insert(sprintf('%sdocuments', mlReferences_utilities_get_prefix()), $array);
    $id = $GLOBALS['wpdb']->insert_id;
    mlReferences_utilities_get_directory(array($id));
    copy($file, mlReferences_utilities_get_file(array($id, $name)));
    if (empty($articles)) {
        return;
    }
    foreach ($articles as $article) {
        $article['document_id'] = $id;
        $article['id'] = mlReferences_models_articles_insert($article);
        foreach ($article['authors'] as $author) {
            $author['id'] = mlReferences_models_authors_insert($author);
            mlReferences_models_articles_authors_insert($article, $author);
        }
    }
}

function mlReferences_models_documents_delete($id)
{
    $directory = mlReferences_utilities_get_directory(array($id));
    mlReferences_utilities_delete($directory);
    rmdir($directory);
    $GLOBALS['wpdb']->delete(
        sprintf('%sdocuments', mlReferences_utilities_get_prefix()),
        array(
            'id' => $id,
        ),
        null,
        null
    );
}

function mlReferences_models_articles_select_all($document_id)
{
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
    references_all,
    references_authors,
    references_editors
FROM `%sarticles`
WHERE `document_id` = %%d
ORDER BY `type` ASC, `id` ASC
EOD;
    $query = sprintf($query, mlReferences_utilities_get_prefix());
    $query = $GLOBALS['wpdb']->prepare($query, $document_id);
    $articles = $GLOBALS['wpdb']->get_results($query, ARRAY_A);
    return $articles;
}

function mlReferences_models_articles_select_one($document_id, $number)
{
    $query = 'SELECT * FROM `%sarticles` WHERE `document_id` = %%d AND `number` = %%s';
    $query = sprintf($query, mlReferences_utilities_get_prefix());
    $query = $GLOBALS['wpdb']->prepare($query, $document_id, $number);
    $article = $GLOBALS['wpdb']->get_row($query, ARRAY_A);
    return $article;
}

function mlReferences_models_articles_insert($article)
{
    $article = array(
        'document_id' => $article['document_id'],
        'number' => $article['number'],
        'type' => $article['type'],
        'title_1' => $article['title_1'],
        'title_2' => $article['title_2'],
        'year' => $article['year'],
        'volume' => $article['volume'],
        'issue' => $article['issue'],
        'page' => $article['page'],
        'url' => $article['url'],
        'doi' => $article['doi'],
        'issn' => $article['issn'],
        'original_publication' => $article['original_publication'],
        'isbn' => $article['isbn'],
        'label' => $article['label'],
        'publisher' => $article['publisher'],
        'place_published' => $article['place_published'],
        'access_date' => $article['access_date'],
        'attachment' => $article['attachment'],
        'citations_first' => $article['citations_first'],
        'citations_subsequent' => $article['citations_subsequent'],
        'citations_parenthetical_first' => $article['citations_parenthetical_first'],
        'citations_parenthetical_subsequent' => $article['citations_parenthetical_subsequent'],
        'references_authors' => $article['references_authors'],
        'references_editors' => $article['references_editors'],
        'references_all' => $article['references_all'],
    );
    $GLOBALS['wpdb']->insert(sprintf('%sarticles', mlReferences_utilities_get_prefix()), $article);
    $article_id = $GLOBALS['wpdb']->insert_id;
    return $article_id;
}

function mlReferences_models_articles_update($article)
{
    $array = array(
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
        'references_all' => $article[23],
        'references_authors' => $article[24],
        'references_editors' => $article[25],
    );
    $GLOBALS['wpdb']->update(
        sprintf('%sarticles', mlReferences_utilities_get_prefix()),
        $array,
        array(
            'id' => $article[0],
        )
    );
}

function mlReferences_models_authors_select_all()
{
    $query = 'SELECT id, name, first_name, url FROM `%sauthors` ORDER BY `id` ASC';
    $query = sprintf($query, mlReferences_utilities_get_prefix());
    $authors = $GLOBALS['wpdb']->get_results($query, ARRAY_A);
    return $authors;
}

function mlReferences_models_authors_select_many($article_id, $role)
{
    $query = <<<EOD
SELECT `%sauthors`.`name`, `%sauthors`.`first_name`
FROM `%sauthors`
INNER JOIN `%sarticles_authors`
ON `%sarticles_authors`.`article_id` = %%d AND `%sarticles_authors`.`author_id` = `%sauthors`.`id`
WHERE `%sarticles_authors`.`role` = %%s
ORDER BY `%sarticles_authors`.`id` ASC
EOD;
    $query = sprintf(
        $query,
        mlReferences_utilities_get_prefix(),
        mlReferences_utilities_get_prefix(),
        mlReferences_utilities_get_prefix(),
        mlReferences_utilities_get_prefix(),
        mlReferences_utilities_get_prefix(),
        mlReferences_utilities_get_prefix(),
        mlReferences_utilities_get_prefix(),
        mlReferences_utilities_get_prefix(),
        mlReferences_utilities_get_prefix()
    );
    $query = $GLOBALS['wpdb']->prepare($query, $article_id, $role);
    $authors = $GLOBALS['wpdb']->get_results($query, ARRAY_A);
    return $authors;
}

function mlReferences_models_authors_select_one($article_id, $role, $offset)
{
    $query = <<<EOD
SELECT `%sauthors`.`name`, `%sauthors`.`first_name`
FROM `%sauthors`
INNER JOIN `%sarticles_authors`
ON `%sarticles_authors`.`article_id` = %%d AND `%sarticles_authors`.`author_id` = `%sauthors`.`id`
WHERE `%sarticles_authors`.`role` = %%s
ORDER BY `%sarticles_authors`.`id` ASC
LIMIT 1
OFFSET %d
EOD;
    $query = sprintf(
        $query,
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
    $query = $GLOBALS['wpdb']->prepare($query, $article_id, $role);
    $author = $GLOBALS['wpdb']->get_row($query, ARRAY_A);
    return $author;
}

function mlReferences_models_authors_insert($author)
{
    $query = 'SELECT * FROM `%sauthors` WHERE `name` = %%s AND `first_name` = %%s';
    $query = sprintf($query, mlReferences_utilities_get_prefix());
    $query = $GLOBALS['wpdb']->prepare($query, $author['name'], $author['first_name']);
    $row = $GLOBALS['wpdb']->get_row($query, ARRAY_A);
    if ($row) {
        return $row['id'];
    }
    $author = array(
        'name' => $author['name'],
        'first_name' => $author['first_name'],
        'url' => $author['url'],
    );
    $GLOBALS['wpdb']->insert(sprintf('%sauthors', mlReferences_utilities_get_prefix()), $author);
    $id = $GLOBALS['wpdb']->insert_id;
    return $id;
}

function mlReferences_models_authors_update($author)
{
    $array = array(
        'name' => $author[1],
        'first_name' => $author[2],
        'url' => $author[3],
    );
    $GLOBALS['wpdb']->update(
        sprintf('%sauthors', mlReferences_utilities_get_prefix()),
        $array,
        array(
            'id' => $author[0]
        )
    );
}

function mlReferences_models_articles_authors_select_all($document_id)
{
    $query = <<<EOD
SELECT id, article_id, author_id, role
FROM `%sarticles_authors`
WHERE
    `article_id` IN ( SELECT `id` FROM `%sarticles` WHERE `document_id` = %%d )
    AND
    `author_id` IN ( SELECT `id` FROM `%sauthors` )
ORDER BY `id` ASC
EOD;
    $query = sprintf(
        $query,
        mlReferences_utilities_get_prefix(),
        mlReferences_utilities_get_prefix(),
        mlReferences_utilities_get_prefix()
    );
    $query = $GLOBALS['wpdb']->prepare($query, $document['id']);
    $articles_authors = $GLOBALS['wpdb']->get_results($query, ARRAY_A);
    return $articles_authors;
}

function mlReferences_models_articles_authors_insert($article, $author)
{
    $article_author = array(
        'article_id' => $article['id'],
        'author_id' => $author['id'],
        'role' => $author['role'],
    );
    $GLOBALS['wpdb']->insert(sprintf('%sarticles_authors', mlReferences_utilities_get_prefix()), $article_author);
}

function mlReferences_models_articles_authors_update($article_author)
{
    $array = array(
        'article_id' => $article_author[1],
        'author_id' => $article_author[2],
        'role' => $article_author[3],
    );
    $GLOBALS['wpdb']->update(
        sprintf('%sarticles_authors', mlReferences_utilities_get_prefix()),
        $array,
        array(
            'id' => $article_author[0]
        )
    );
}
