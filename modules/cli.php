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
    * @subcommand test_end_note
    */
    public function testEndNote($args)
    {
        list($errors, $items, $txt) = mlReferences_end_note_get_items($args[0]);
        print_r($errors);
        print_r($txt);
    }

    /**
    * @subcommand test_spreadsheet
    */
    public function testSpreadsheet($args)
    {
        list($errors, $items) = mlReferences_spreadsheet_get_items($args[0]);
        print_r($errors);
    }

    /**
    * @subcommand test_zip
    */
    public function testZip($args)
    {
        $articles = mlReferences_zip_get_articles($args[0]);
        $articles = array_slice($articles, 0, 5);
        print_r($articles);
        $authors = mlReferences_zip_get_authors($args[0]);
        $authors = array_slice($authors, 0, 5);
        print_r($authors);
        $articles_authors = mlReferences_zip_get_articles_authors($args[0]);
        $articles_authors = array_slice($articles_authors, 0, 5);
        print_r($articles_authors);
    }
}

WP_CLI::add_command('mlReferences', 'mlReferences_cli');
