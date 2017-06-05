<?php

class mlReferences_cli extends WP_CLI_Command
{
    /**
     * @subcommand reset
     */
    public function reset($args)
    {
        mlReferences_actions_reset();
    }

    /**
    * @subcommand end-note-test
    */
    public function testEndNote($args)
    {
        list($articles, $txt, $errors) = mlReferences_end_note_get_articles($args[0], $args[1]);
        $articles = array_slice($articles, 0, 5);
        print_r($articles);
        print_r($txt);
        print_r($errors);
    }

    /**
    * @subcommand spreadsheet-test
    */
    public function testSpreadsheet($args)
    {
        list($articles, $errors) = mlReferences_spreadsheet_get_articles($args[0]);
        $articles = array_slice($articles, 0, 5);
        print_r($articles);
        print_r($errors);
    }

    /**
    * @subcommand zip-test
    */
    public function testZip($args)
    {
        $articles = mlReferences_zip_upload_get_articles($args[0]);
        $articles = array_slice($articles, 0, 5);
        print_r($articles);
        $authors = mlReferences_zip_upload_get_authors($args[0]);
        $authors = array_slice($authors, 0, 5);
        print_r($authors);
        $articles_authors = mlReferences_zip_upload_get_articles_authors($args[0]);
        $articles_authors = array_slice($articles_authors, 0, 5);
        print_r($articles_authors);
    }

    /**
    * @subcommand end-note-upload
    */
    public function uploadEndNote($args)
    {
        list($articles, $txt, $errors) = mlReferences_end_note_get_articles($args[0], $args[1]);
        mlReferences_end_note_upload($args[0], basename($args[0]), $articles);
    }

    /**
    * @subcommand spreadsheet-upload
    */
    public function uploadSpreadsheet($args)
    {
        list($articles, $errors) = mlReferences_spreadsheet_get_articles($args[0]);
        mlReferences_spreadsheet_upload($args[0], basename($args[0]), $articles);
    }

    /**
    * @subcommand zip-upload
    */
    public function uploadZip($args)
    {
        $articles = mlReferences_zip_upload_get_articles($args[0]);
        $authors = mlReferences_zip_upload_get_authors($args[0]);
        $articles_authors = mlReferences_zip_upload_get_articles_authors($args[0]);
        mlReferences_zip_upload($articles, $authors, $articles_authors);
    }
}

WP_CLI::add_command('mlReferences', 'mlReferences_cli');
