<?php

function mlReferences_actions_license($file)
{
    mlReferences_license_update($file);
}

function mlReferences_actions_reset()
{
    $documents = mlReferences_models_documents_select_all();
    if (!empty($documents)) {
        foreach ($documents as $document) {
            mlReferences_models_documents_delete($document['id']);
        }
    }
}
