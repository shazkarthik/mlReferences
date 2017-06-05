<?php

function mlReferences_actions_reset()
{
    $documents = mlReferences_models_documents_select_all();
    if (empty($documents)) {
        return;
    }
    foreach ($documents as $document) {
        mlReferences_models_documents_delete($document->id);
    }
}
