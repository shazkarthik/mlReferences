<?php

function mlReferences_actions_reset()
{
    $documents = mlReferences_models_documents_select_all();
    if (!empty($documents)) {
        foreach ($documents as $document) {
            mlReferences_models_documents_delete($document['id']);
        }
    }
}
