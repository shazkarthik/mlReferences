<?php

function mlReferences_dashboard_end_note_upload_get()
{
    ?>
    <h1>Documents - Upload EndNote</h1>
    <form
        action="<?php echo mlReferences_utilities_get_admin_url('action=end-note-upload'); ?>"
        enctype="multipart/form-data"
        method="post"
        >
        <table class="bordered widefat wp-list-table">
            <tr>
                <td class="label">
                    <label for="file_1">XML File</label>
                </td>
                <td><input id="file" name="file_1" type="file"></td>
            </tr>
            <tr>
                <td class="label">
                    <label for="file_2">TXT File</label>
                </td>
                <td><input id="file" name="file_2" type="file"></td>
            </tr>
        </table>
        <p class="submit">
            <input class="button-primary" type="submit" value="Submit">
        </p>
    </form>
    <?php
}

function mlReferences_dashboard_end_note_upload_post()
{
    list($errors, $articles) = mlReferences_end_note_get_items($_FILES['file_1']['tmp_name']);
    if ($errors) {
        $_SESSION['mlReferences']['flashes'] = array(
            'error' => 'The document was not uploaded successfully. Please try again.',
        );
        ?>
        <meta
            content="0;url=<?php echo mlReferences_utilities_get_admin_url('action=end-note-upload'); ?>"
            http-equiv="refresh"
            >
        <?php
        return;
    }
    if (!$articles) {
        $_SESSION['mlReferences']['flashes'] = array(
            'error' => 'The document was not uploaded successfully. Please try again.',
        );
        ?>
        <meta
            content="0;url=<?php echo mlReferences_utilities_get_admin_url('action=end-note-upload'); ?>"
            http-equiv="refresh"
            >
        <?php
        return;
    }
    mlReferences_models_documents_insert(
        $_FILES['file_1']['tmp_name'],
        $_FILES['file_1']['name'],
        'EndNote',
        $articles
    );
    $_SESSION['mlReferences']['flashes'] = array(
        'updated' => 'The document was uploaded successfully.',
    );
    ?>
    <meta content="0;url=<?php echo mlReferences_utilities_get_admin_url('action='); ?>" http-equiv="refresh">
    <?php
    return;
}

function mlReferences_dashboard_end_note_download()
{
    $id = $_REQUEST['id'];
    $id = intval($id);
    list($document, $contents) = mlReferences_actions_download($id);
    ob_clean();
    header(sprintf('Content-Disposition: attachment; filename="%s"', $document['name']));
    header(sprintf('Content-Length: %d', strlen($contents)));
    echo $contents;
}

function mlReferences_dashboard_spreadsheet_upload_get()
{
}

function mlReferences_dashboard_spreadsheet_upload_post()
{
}

function mlReferences_dashboard_spreadsheet_download()
{
}

function mlReferences_dashboard_zip_upload_get()
{
    $admin_url = 'action=zip-upload&id=%d';
    $admin_url = sprintf($admin_url, $_REQUEST['id']);
    $admin_url = mlReferences_utilities_get_admin_url($admin_url);
    ?>
    <h1>Zip file - Upload</h1>
    <form action="<?php echo $admin_url; ?>" enctype="multipart/form-data" method="post">
        <table class="bordered widefat wp-list-table">
            <tr>
                <td class="label"><label for="file">File</label></td>
                <td><input id="file" name="file" type="file"></td>
            </tr>
        </table>
        <p class="submit"><input class="button-primary" type="submit" value="Submit"></p>
    </form>
    <?php
}

function mlReferences_dashboard_zip_upload_post()
{
    $articles = mlReferences_zip_get_articles($_FILES['file']['tmp_name']);
    mlReferences_zip_upload($articles);
    $_SESSION['mlReferences']['flashes'] = array(
        'updated' => 'The document was uploaded successfully.',
    );
    ?>
    <meta content="0;url=<?php echo mlReferences_utilities_get_admin_url('action='); ?>" http-equiv="refresh">
    <?php
}

function mlReferences_dashboard_zip_download()
{
    $id = $_REQUEST['id'];
    $id = intval($id);
    list($document, $tempnam) = mlReferences_zip_download($id);
    ob_clean();
    header('Content-Type: application/zip');
    header(sprintf('Content-Disposition: attachment; filename="%s.zip"', $document['name']));
    header(sprintf('Content-Length: %d', filesize($tempnam)));
    readfile($tempnam);
    unlink($tempnam);
}

function mlReferences_dashboard_delete_get()
{
    $admin_url = 'action=delete&id=%d';
    $admin_url = sprintf($admin_url, $_REQUEST['id']);
    $admin_url = mlReferences_utilities_get_admin_url($admin_url);
    ?>
    <h1>Documents - Delete</h1>
    <div class="error">
        <p><strong>Are you sure you want to delete this document?</strong></p>
    </div>
    <form action="<?php echo $admin_url; ?>" method="post">
        <p class="submit">
            <input class="button-primary" type="submit" value="Yes">
            <a class="float-right" href="<?php echo mlReferences_utilities_get_admin_url('action='); ?>">No</a>
        </p>
    </form>
    <?php
}

function mlReferences_dashboard_delete_post()
{
    $id = $_REQUEST['id'];
    $id = intval($id);
    mlReferences_models_documents_delete($id);
    $_SESSION['mlReferences']['flashes'] = array(
        'updated' => 'The document was deleted successfully.',
    );
    ?>
    <meta
        content="0;url=<?php echo mlReferences_utilities_get_admin_url('action=&deleted=deleted'); ?>"
        http-equiv="refresh"
        >
    <?php
}

function mlReferences_dashboard_default()
{
    $documents = mlReferences_models_documents_select_all();
    ?>
    <h1>
        Documents
        <a
            class="page-title-action"
            href="<?php echo mlReferences_utilities_get_admin_url('action=end-note-upload'); ?>"
            >Upload EndNote</a>
        <a
            class="page-title-action"
            href="<?php echo mlReferences_utilities_get_admin_url('action=spreadsheet-upload'); ?>"
            >Upload Spreadsheet</a>
    </h1>
    <?php mlReferences_utilities_flashes(); ?>
    <?php if ($documents) : ?>
        <table class="bordered widefat wp-list-table">
            <tr>
                <th class="narrow right">Identifier</th>
                <th>Name</th>
                <th class="narrow center">ZIP</th>
                <th class="narrow center">EndNote</th>
                <th class="narrow center">Spreadsheet</th>
                <th class="narrow center">Actions</th>
            </tr>
            <?php foreach ($documents as $document) : ?>
                <tr>
                    <td class="narrow right"><?php echo $document['id']; ?></td>
                    <td>
                        <?php echo $document['name']; ?>
                        (<?php echo $document['type']; ?>)
                    </td>
                    <td class="narrow center">
                        <?php
                        $admin_url = 'action=zip-download&id=%d';
                        $admin_url = sprintf($admin_url, $document['id']);
                        $admin_url = mlReferences_utilities_get_admin_url($admin_url);
                        ?>
                        <a href="<?php echo $admin_url; ?>">Download</a>
                        -
                        <?php
                        $admin_url = 'action=zip-upload&id=%d';
                        $admin_url = sprintf($admin_url, $document['id']);
                        $admin_url = mlReferences_utilities_get_admin_url($admin_url);
                        ?>
                        <a href="<?php echo $admin_url; ?>">Upload</a>
                    </td>
                    <td class="narrow center">
                        <?php if ($document['type'] === 'EndNote') : ?>
                            <?php
                            $admin_url = 'action=end-note-download&id=%d';
                            $admin_url = sprintf($admin_url, $document['id']);
                            $admin_url = mlReferences_utilities_get_admin_url($admin_url);
                            ?>
                            <a href="<?php echo $admin_url; ?>">Download</a>
                        <?php endif; ?>
                    </td>
                    <td class="narrow center">
                        <?php if ($document['type'] === 'Spreadsheet') : ?>
                            <?php
                            $admin_url = 'action=spreadsheet-download&id=%d';
                            $admin_url = sprintf($admin_url, $document['id']);
                            $admin_url = mlReferences_utilities_get_admin_url($admin_url);
                            ?>
                            <a href="<?php echo $admin_url; ?>">Download</a>
                        <?php endif; ?>
                    </td>
                    <td class="narrow center">
                        <?php
                        $admin_url = 'action=delete&id=%d';
                        $admin_url = sprintf($admin_url, $document['id']);
                        $admin_url = mlReferences_utilities_get_admin_url($admin_url);
                        ?>
                        <a href="<?php echo $admin_url; ?>">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else : ?>
        <div class="error">
            <p><strong>There are no documents in the database.</strong></p>
        </div>
    <?php endif; ?>
    <?php
}

function mlReferences_dashboard()
{
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permissions to access this page.');
    }
    $action = @$_REQUEST['action']? $_REQUEST['action']: '';
    ?>
    <div class="mlReferences wrap">
        <?php
        switch ($action) {
            case 'end-note-upload':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    mlReferences_dashboard_end_note_upload_post();
                } else {
                    mlReferences_dashboard_end_note_upload_get();
                }
                break;
            case 'end-note-download':
                mlReferences_dashboard_end_note_download();
                break;
            case 'spreadsheet-upload':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    mlReferences_dashboard_spreadsheet_upload_post();
                } else {
                    mlReferences_dashboard_spreadsheet_upload_get();
                }
                break;
            case 'spreadsheet-download':
                mlReferences_dashboard_spreadsheet_download();
                break;
            case 'zip-upload':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    mlReferences_dashboard_zip_upload_post();
                } else {
                    mlReferences_dashboard_zip_upload_get();
                }
                break;
            case 'zip-download':
                mlReferences_dashboard_zip_download();
                break;
            case 'delete':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    mlReferences_dashboard_delete_post();
                } else {
                    mlReferences_dashboard_delete_get();
                }
                break;
            default:
                mlReferences_dashboard_default();
                break;
        }
        ?>
        </div>
    </div>
    <?php
}

function mlReferences_faq()
{
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permissions to access this page.');
    }
    ?>
    <div class="mlReferences wrap">
        <h1>Frequently Asked Questions</h1>
        <div class="welcome-panel">
            <p><strong>Steps, if using EndNote:</strong></p>
            <hr>
            <ol>
                <li>
                    Upload a new EndNote file using the <strong>Upload EndNote</strong> link next to the page header
                </li>
                <li>
                    Download a ZIP file using the <strong>Download</strong> link in the <strong>ZIP</strong> column
                </li>
                <li>
                    Extract the downloaded ZIP file
                </li>
                <li>
                    Edit the CSV files inside the extracted ZIP file as required
                </li>
                <li>
                    Re-create the ZIP file and populate it with the edited CSV files
                </li>
                <li>
                    Upload the re-created ZIP file using the <strong>Upload</strong> link in the <strong>ZIP</strong>
                    column
                </li>
                <li>
                    Downloaded the updated EndNote XML file using the <strong>Download</strong> link in the
                    <strong>EndNote</strong> column
                </li>
            </ol>
        </div>
        <div class="welcome-panel">
            <p><strong>Steps, if using spreadsheet:</strong></p>
            <hr>
            <ol>
                <li>
                    Upload a new spreadsheet using the <strong>Upload Spreadsheet</strong> link next to the page header
                </li>
                <li>
                    Download a ZIP file using the <strong>Download</strong> link in the <strong>ZIP</strong> column
                </li>
                <li>
                    Extract the downloaded ZIP file
                </li>
                <li>
                    Edit the CSV files inside the extracted ZIP file as required
                </li>
                <li>
                    Re-create the ZIP file and populate it with the edited CSV files
                </li>
                <li>
                    Upload the re-created ZIP file using the <strong>Upload</strong> link in the <strong>ZIP</strong>
                    column
                </li>
                <li>
                    Downloaded the updated spreadsheet using the <strong>Download</strong> link in the
                    <strong>Spreadsheet</strong> column
                </li>
            </ol>
        </div>
        <div class="welcome-panel">
            <p><strong>Columns</strong></p>
            <hr>
            <ol>
                <li>
                    <strong>Articles</strong>
                    <ul>
                        <li>Identifier</li>
                        <li>Number</li>
                        <li>Type</li>
                        <li>Title</li>
                        <li>Title2</li>
                        <li>Year</li>
                        <li>Volume</li>
                        <li>Issue</li>
                        <li>Page</li>
                        <li>URL</li>
                        <li>DOI</li>
                        <li>ISSN</li>
                        <li>Original Publication</li>
                        <li>ISBN</li>
                        <li>Label</li>
                        <li>Publisher</li>
                        <li>Place Published</li>
                        <li>Access Date</li>
                        <li>Attachment</li>
                        <li>Authors Publish Text First</li>
                        <li>Authors Publish Text Subsequent</li>
                        <li>Authors Publish Text First Parenthetical</li>
                        <li>Authors Publish Text Subsequent Parenthetical</li>
                        <li>Full Reference</li>
                        <li>Authors Publish Reference</li>
                        <li>Editors Publish Reference</li>
                    </ul>
                </li>
                <li>
                    <strong>Authors</strong>
                    <ul>
                        <li>Identifier</li>
                        <li>Name</li>
                        <li>First Name</li>
                        <li>URL</li>
                    </ul>
                </li>
                <li>
                    <strong>Articles &amp; Authors</strong>
                    <ul>
                        <li>Identifier</li>
                        <li>Article Identifier</li>
                        <li>Author Identifier</li>
                        <li>Role</li>
                    </ul>
                </li>
            </ol>
        </div>
    </div>
    <?php
}
