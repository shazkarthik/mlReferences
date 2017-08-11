<?php

function mlReferences_dashboard_ris_upload_get()
{
    ?>
    <h1>mlReferences - Documents - Upload RIS</h1>
    <?php if (mlReferences_license_is_valid()): ?>
        <form
            action="<?php echo mlReferences_utilities_get_admin_url('action=ris-upload'); ?>"
            enctype="multipart/form-data"
            method="post"
            >
            <table class="bordered widefat wp-list-table">
                <tr>
                    <td class="label">
                        <label for="file">RIS File</label>
                    </td>
                    <td><input id="file" name="file" type="file"></td>
                </tr>
            </table>
            <p class="submit">
                <input class="button-primary" type="submit" value="Submit">
            </p>
        </form>
    <?php endif; ?>
    <?php
}

function mlReferences_dashboard_ris_upload_post()
{
    $file = $_FILES['file'];
    list($articles, $errors) = mlReferences_ris_get_articles($file['tmp_name']);
    if ($errors) {
        $_SESSION['mlReferences']['flashes'] = array(
            'error' => 'The document was not uploaded successfully. Please try again.',
        );
        ?>
        <meta
            content="0;url=<?php echo mlReferences_utilities_get_admin_url('action=ris-upload'); ?>"
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
            content="0;url=<?php echo mlReferences_utilities_get_admin_url('action=ris-upload'); ?>"
            http-equiv="refresh"
            >
        <?php
        return;
    }
    mlReferences_models_documents_insert($file['tmp_name'], $file['name'], 'RIS', $articles);
    $_SESSION['mlReferences']['flashes'] = array(
        'success' => 'The document was uploaded successfully.',
    );
    ?>
    <meta content="0;url=<?php echo mlReferences_utilities_get_admin_url('action='); ?>" http-equiv="refresh">
    <?php
    return;
}

function mlReferences_dashboard_ris_download()
{
    $id = $_REQUEST['id'];
    $id = intval($id);
    list($document, $contents) = mlReferences_ris_download($id);
    ob_clean();
    header(sprintf('Content-Disposition: attachment; filename="%s"', $document['name']));
    header(sprintf('Content-Length: %d', strlen($contents)));
    echo $contents;
}

function mlReferences_dashboard_mendeley_upload_get()
{
    ?>
    <h1>mlReferences - Documents - Upload Mendeley</h1>
    <?php if (mlReferences_license_is_valid()): ?>
        <form
            action="<?php echo mlReferences_utilities_get_admin_url('action=mendeley-upload'); ?>"
            enctype="multipart/form-data"
            method="post"
            >
            <table class="bordered widefat wp-list-table">
                <tr>
                    <td class="label">
                        <label for="file">XML File</label>
                    </td>
                    <td><input id="file" name="file" type="file"></td>
                </tr>
            </table>
            <p class="submit">
                <input class="button-primary" type="submit" value="Submit">
            </p>
        </form>
    <?php endif; ?>
    <?php
}

function mlReferences_dashboard_mendeley_upload_post()
{
    $file = $_FILES['file'];
    list($articles, $errors) = mlReferences_mendeley_get_articles($file['tmp_name']);
    if ($errors) {
        $_SESSION['mlReferences']['flashes'] = array(
            'error' => 'The document was not uploaded successfully. Please try again.',
        );
        ?>
        <meta
            content="0;url=<?php echo mlReferences_utilities_get_admin_url('action=mendeley-upload'); ?>"
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
            content="0;url=<?php echo mlReferences_utilities_get_admin_url('action=mendeley-upload'); ?>"
            http-equiv="refresh"
            >
        <?php
        return;
    }
    mlReferences_models_documents_insert($file['tmp_name'], $file['name'], 'Mendeley', $articles);
    $_SESSION['mlReferences']['flashes'] = array(
        'success' => 'The document was uploaded successfully.',
    );
    ?>
    <meta content="0;url=<?php echo mlReferences_utilities_get_admin_url('action='); ?>" http-equiv="refresh">
    <?php
    return;
}

function mlReferences_dashboard_mendeley_download()
{
    $id = $_REQUEST['id'];
    $id = intval($id);
    list($document, $contents) = mlReferences_mendeley_download($id);
    ob_clean();
    header(sprintf('Content-Disposition: attachment; filename="%s"', $document['name']));
    header(sprintf('Content-Length: %d', strlen($contents)));
    echo $contents;
}

function mlReferences_dashboard_zotero_upload_get()
{
    ?>
    <h1>mlReferences - Documents - Upload Zotero</h1>
    <?php if (mlReferences_license_is_valid()): ?>
        <form
            action="<?php echo mlReferences_utilities_get_admin_url('action=zotero-upload'); ?>"
            enctype="multipart/form-data"
            method="post"
            >
            <table class="bordered widefat wp-list-table">
                <tr>
                    <td class="label">
                        <label for="file">XML File</label>
                    </td>
                    <td><input id="file" name="file" type="file"></td>
                </tr>
            </table>
            <p class="submit">
                <input class="button-primary" type="submit" value="Submit">
            </p>
        </form>
    <?php endif; ?>
    <?php
}

function mlReferences_dashboard_zotero_upload_post()
{
    $file = $_FILES['file'];
    list($articles, $errors) = mlReferences_zotero_get_articles($file['tmp_name']);
    if ($errors) {
        $_SESSION['mlReferences']['flashes'] = array(
            'error' => 'The document was not uploaded successfully. Please try again.',
        );
        ?>
        <meta
            content="0;url=<?php echo mlReferences_utilities_get_admin_url('action=zotero-upload'); ?>"
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
            content="0;url=<?php echo mlReferences_utilities_get_admin_url('action=zotero-upload'); ?>"
            http-equiv="refresh"
            >
        <?php
        return;
    }
    mlReferences_models_documents_insert($file['tmp_name'], $file['name'], 'Zotero', $articles);
    $_SESSION['mlReferences']['flashes'] = array(
        'success' => 'The document was uploaded successfully.',
    );
    ?>
    <meta content="0;url=<?php echo mlReferences_utilities_get_admin_url('action='); ?>" http-equiv="refresh">
    <?php
    return;
}

function mlReferences_dashboard_zotero_download()
{
    $id = $_REQUEST['id'];
    $id = intval($id);
    list($document, $contents) = mlReferences_zotero_download($id);
    ob_clean();
    header(sprintf('Content-Disposition: attachment; filename="%s"', $document['name']));
    header(sprintf('Content-Length: %d', strlen($contents)));
    echo $contents;
}

function mlReferences_dashboard_end_note_upload_get()
{
    ?>
    <h1>mlReferences - Documents - Upload EndNote</h1>
    <?php if (mlReferences_license_is_valid()): ?>
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
    <?php endif; ?>
    <?php
}

function mlReferences_dashboard_end_note_upload_post()
{
    $file_1 = $_FILES['file_1'];
    $file_2 = $_FILES['file_2'];
    list($articles, $txt, $errors) = mlReferences_end_note_get_articles($file_1['tmp_name'], $file_2['tmp_name']);
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
    mlReferences_models_documents_insert($file_1['tmp_name'], $file_1['name'], 'EndNote', $articles);
    $_SESSION['mlReferences']['flashes'] = array(
        'success' => 'The document was uploaded successfully.',
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
    list($document, $contents) = mlReferences_end_note_download($id);
    ob_clean();
    header(sprintf('Content-Disposition: attachment; filename="%s"', $document['name']));
    header(sprintf('Content-Length: %d', strlen($contents)));
    echo $contents;
}

function mlReferences_dashboard_spreadsheet_upload_get()
{
    $sample = sprintf('%s/mlReferences.xlsx', plugins_url('/mlReferences'));
    ?>
    <h1>mlReferences - Documents - Upload Spreadsheet</h1>
    <?php if (mlReferences_license_is_valid()): ?>
        <form
            action="<?php echo mlReferences_utilities_get_admin_url('action=spreadsheet-upload'); ?>"
            enctype="multipart/form-data"
            method="post"
            >
            <table class="bordered widefat wp-list-table">
                <tr>
                    <td class="label">
                        <label for="file">File (<a href="<?php echo $sample; ?>">Sample</a>)</label>
                    </td>
                    <td><input id="file" name="file" type="file"></td>
                </tr>
            </table>
            <p class="submit">
                <input class="button-primary" type="submit" value="Submit">
            </p>
        </form>
    <?php endif; ?>
    <?php
}

function mlReferences_dashboard_spreadsheet_upload_post()
{
    $file = $_FILES['file'];
    list($articles, $errors) = mlReferences_spreadsheet_get_articles($file['tmp_name']);
    if ($errors) {
        $_SESSION['mlReferences']['flashes'] = array(
            'error' => 'The document was not uploaded successfully. Please try again.',
        );
        ?>
        <meta
            content="0;url=<?php echo mlReferences_utilities_get_admin_url('action=spreadsheet-upload'); ?>"
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
            content="0;url=<?php echo mlReferences_utilities_get_admin_url('action=spreadsheet-upload'); ?>"
            http-equiv="refresh"
            >
        <?php
        return;
    }
    mlReferences_models_documents_insert($file['tmp_name'], $file['name'], 'Spreadsheet', $articles);
    $_SESSION['mlReferences']['flashes'] = array(
        'success' => 'The document was uploaded successfully.',
    );
    ?>
    <meta content="0;url=<?php echo mlReferences_utilities_get_admin_url('action='); ?>" http-equiv="refresh">
    <?php
    return;
}

function mlReferences_dashboard_spreadsheet_download()
{
    $id = $_REQUEST['id'];
    $id = intval($id);
    list($document, $tempnam) = mlReferences_spreadsheet_download($id);
    ob_clean();
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header(sprintf('Content-Disposition: attachment; filename="%s"', $document['name']));
    header(sprintf('Content-Length: %d', filesize($tempnam)));
    readfile($tempnam);
    unlink($tempnam);
}

function mlReferences_dashboard_zip_upload_get()
{
    $admin_url = 'action=zip-upload&id=%d';
    $admin_url = sprintf($admin_url, $_REQUEST['id']);
    $admin_url = mlReferences_utilities_get_admin_url($admin_url);
    ?>
    <h1>mlReferences - Zip file - Upload</h1>
    <?php if (mlReferences_license_is_valid()): ?>
        <form action="<?php echo $admin_url; ?>" enctype="multipart/form-data" method="post">
            <table class="bordered widefat wp-list-table">
                <tr>
                    <td class="label"><label for="file">File</label></td>
                    <td><input id="file" name="file" type="file"></td>
                </tr>
            </table>
            <p class="submit"><input class="button-primary" type="submit" value="Submit"></p>
        </form>
    <?php endif; ?>
    <?php
}

function mlReferences_dashboard_zip_upload_post()
{
    $articles = mlReferences_zip_upload_get_articles($_FILES['file']['tmp_name']);
    $authors = mlReferences_zip_upload_get_authors($_FILES['file']['tmp_name']);
    $articles_authors = mlReferences_zip_upload_get_articles_authors($_FILES['file']['tmp_name']);
    mlReferences_zip_upload($articles, $authors, $articles_authors);
    $_SESSION['mlReferences']['flashes'] = array(
        'success' => 'The document was uploaded successfully.',
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
    <h1>mlReferences - Documents - Delete</h1>
    <?php if (mlReferences_license_is_valid()): ?>
        <div class="notice notice-error">
            <p><strong>Are you sure you want to delete this document?</strong></p>
        </div>
        <form action="<?php echo $admin_url; ?>" method="post">
            <p class="submit">
                <input class="button-primary" type="submit" value="Yes">
                <a class="float-right" href="<?php echo mlReferences_utilities_get_admin_url('action='); ?>">No</a>
            </p>
        </form>
    <?php endif; ?>
    <?php
}

function mlReferences_dashboard_delete_post()
{
    $id = $_REQUEST['id'];
    $id = intval($id);
    mlReferences_models_documents_delete($id);
    $_SESSION['mlReferences']['flashes'] = array(
        'success' => 'The document was deleted successfully.',
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
        mlReferences - Documents
        <a
            class="page-title-action"
            href="<?php echo mlReferences_utilities_get_admin_url('action=end-note-upload'); ?>"
            >Upload EndNote</a>
        <a
            class="page-title-action"
            href="<?php echo mlReferences_utilities_get_admin_url('action=mendeley-upload'); ?>"
            >Upload Mendeley</a>
        <a
            class="page-title-action"
            href="<?php echo mlReferences_utilities_get_admin_url('action=zotero-upload'); ?>"
            >Upload Zotero</a>
        <a
            class="page-title-action"
            href="<?php echo mlReferences_utilities_get_admin_url('action=ris-upload'); ?>"
            >Upload RIS</a>
        <a
            class="page-title-action"
            href="<?php echo mlReferences_utilities_get_admin_url('action=spreadsheet-upload'); ?>"
            >Upload Spreadsheet</a>
    </h1>
    <?php if (mlReferences_license_is_valid()): ?>
        <?php mlReferences_utilities_flashes(); ?>
        <?php if ($documents) : ?>
            <table class="bordered widefat wp-list-table">
                <tr>
                    <th class="narrow right">Identifier</th>
                    <th>Name</th>
                    <th class="narrow center">ZIP</th>
                    <th class="narrow center">EndNote</th>
                    <th class="narrow center">Mendeley</th>
                    <th class="narrow center">Zotero</th>
                    <th class="narrow center">RIS</th>
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
                            <?php if ($document['type'] === 'Mendeley') : ?>
                                <?php
                                $admin_url = 'action=mendeley-download&id=%d';
                                $admin_url = sprintf($admin_url, $document['id']);
                                $admin_url = mlReferences_utilities_get_admin_url($admin_url);
                                ?>
                                <a href="<?php echo $admin_url; ?>">Download</a>
                            <?php endif; ?>
                        </td>
                        <td class="narrow center">
                            <?php if ($document['type'] === 'Zotero') : ?>
                                <?php
                                $admin_url = 'action=zotero-download&id=%d';
                                $admin_url = sprintf($admin_url, $document['id']);
                                $admin_url = mlReferences_utilities_get_admin_url($admin_url);
                                ?>
                                <a href="<?php echo $admin_url; ?>">Download</a>
                            <?php endif; ?>
                        </td>
                        <td class="narrow center">
                            <?php if ($document['type'] === 'RIS') : ?>
                                <?php
                                $admin_url = 'action=ris-download&id=%d';
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
            <div class="notice notice-error">
                <p><strong>There are no documents in the database.</strong></p>
            </div>
        <?php endif; ?>
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
            case 'mendeley-upload':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    mlReferences_dashboard_mendeley_upload_post();
                } else {
                    mlReferences_dashboard_mendeley_upload_get();
                }
                break;
            case 'mendeley-download':
                mlReferences_dashboard_mendeley_download();
                break;
            case 'zotero-upload':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    mlReferences_dashboard_zotero_upload_post();
                } else {
                    mlReferences_dashboard_zotero_upload_get();
                }
                break;
            case 'zotero-download':
                mlReferences_dashboard_zotero_download();
                break;
            case 'ris-upload':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    mlReferences_dashboard_ris_upload_post();
                } else {
                    mlReferences_dashboard_ris_upload_get();
                }
                break;
            case 'ris-download':
                mlReferences_dashboard_ris_download();
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
        <h1>mlReferences - Frequently Asked Questions</h1>
        <?php if (mlReferences_license_is_valid()): ?>
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
                <p><strong>Spreadsheets</strong></p>
                <hr>
                <ol>
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
                    <li>Authors (pipe separated values)</li>
                    <li>Editors (pipe separated values)</li>
                </ol>
            </div>
            <div class="welcome-panel">
                <p><strong>CSV Files</strong></p>
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
                            <li>Full Reference (EndNote)</li>
                            <li>Authors Publish Reference</li>
                            <li>Editors Publish Reference</li>
                            <li>Full Reference (Derived)</li>
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
        <?php endif; ?>
    </div>
    <?php
}

function mlReferences_license()
{
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permissions to access this page.');
    }
    ?>
    <div class="mlReferences wrap">
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            mlReferences_actions_license($_FILES['license']['tmp_name']);
            if (mlReferences_license_is_valid()) {
                $_SESSION['mlReferences']['flashes'] = array(
                    'success' => 'Your license was added successfully.',
                );
            } else {
                $_SESSION['mlReferences']['flashes'] = array(
                    'error' => 'Your license was not added successfully. Please try again',
                );
            }
            ?>
            <meta
                content="0;url=<?php echo admin_url('admin.php?page=mlReferences/license'); ?>"
                http-equiv="refresh"
                >
            <?php
            die();
        } else {
            ?>
            <h1>mlReferences - License</h1>
            <?php mlReferences_utilities_flashes(); ?>
            <?php if (mlReferences_license_is_valid()): ?>
                <?php
                $json = get_option('mlReferences_license_json', '{}');
                $json = json_decode($json, true);
                ?>
                <div class="welcome-panel">
                    <p>
                        <strong>Congratulations!</strong> Your license is active.
                    </p>
                    <table class="bordered widefat wp-list-table">
                        <tr>
                            <td>Type</td>
                            <td class="narrow right">
                                <?php echo $json['type']? $json['type']: 'Free'; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Expiration Date</td>
                            <td class="narrow right">
                                <?php echo $json['expiration_date']? $json['expiration_date']: 'N/A'; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Maximum number of references</td>
                            <td class="narrow right">
                                <?php echo number_format(intval($json['mlReferences_references']), 0); ?>
                            </td>
                        </tr>
                    </table>
                </div>
            <?php else: ?>
                <form action="<?php echo admin_url('admin.php?page=mlReferences/license'); ?>" method="post">
                </form>
                <form
                    action="<?php echo admin_url('admin.php?page=mlReferences/license'); ?>"
                    enctype="multipart/form-data"
                    method="post"
                    >
                    <table class="bordered widefat wp-list-table">
                        <tr>
                            <td class="label">
                                <label for="license">License</label>
                            </td>
                            <td><input id="license" name="license" type="file"></td>
                        </tr>
                    </table>
                    <p class="submit"><input class="button-primary" type="submit" value="Submit"></p>
                </form>
            <?php endif; ?>
            <?php
        }
        ?>
    </div>
    <?php
}
