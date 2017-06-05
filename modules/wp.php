<?php

function mlReferences_register_activation_hook()
{
    mlReferences_register_deactivation_hook();

    $query = <<<EOD
CREATE TABLE IF NOT EXISTS `%sdocuments` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `type` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    PRIMARY KEY (`id`),
    KEY `name` (`name`),
    KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0;
EOD;
    $GLOBALS['wpdb']->query(sprintf($query, mlReferences_utilities_get_prefix()));

    $query = <<<EOD
CREATE TABLE IF NOT EXISTS `%sarticles` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `document_id` INT(11) UNSIGNED NOT NULL,
    `number` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `type` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `title_1` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `title_2` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `year` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `volume` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `issue` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `page` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `url` TEXT COLLATE utf8_unicode_ci NOT NULL,
    `doi` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `issn` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `original_publication` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `isbn` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `label` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `publisher` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `place_published` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `access_date` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `attachment` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `citations_first` TEXT COLLATE utf8_unicode_ci NOT NULL,
    `citations_subsequent` TEXT COLLATE utf8_unicode_ci NOT NULL,
    `citations_parenthetical_first` TEXT COLLATE utf8_unicode_ci NOT NULL,
    `citations_parenthetical_subsequent` TEXT COLLATE utf8_unicode_ci NOT NULL,
    `references_authors` TEXT COLLATE utf8_unicode_ci NOT NULL,
    `references_editors` TEXT COLLATE utf8_unicode_ci NOT NULL,
    `references_all` TEXT COLLATE utf8_unicode_ci NOT NULL,
    `endnote` TEXT COLLATE utf8_unicode_ci,
    PRIMARY KEY (`id`),
    KEY `number` (`number`),
    KEY `type` (`type`),
    KEY `title_1` (`title_1`),
    KEY `title_2` (`title_2`),
    KEY `year` (`year`),
    KEY `volume` (`volume`),
    KEY `issue` (`issue`),
    KEY `page` (`page`),
    KEY `doi` (`doi`),
    KEY `issn` (`issn`),
    KEY `original_publication` (`original_publication`),
    KEY `isbn` (`isbn`),
    KEY `label` (`label`),
    KEY `publisher` (`publisher`),
    KEY `place_published` (`place_published`),
    KEY `access_date` (`access_date`),
    KEY `attachment` (`attachment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0;
EOD;
    $GLOBALS['wpdb']->query(sprintf($query, mlReferences_utilities_get_prefix()));

    $query = <<<EOD
CREATE TABLE IF NOT EXISTS `%sauthors` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `first_name` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `url` TEXT COLLATE utf8_unicode_ci NOT NULL,
    PRIMARY KEY (`id`),
    KEY `name` (`name`),
    KEY `first_name` (`first_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0;
EOD;
    $GLOBALS['wpdb']->query(sprintf($query, mlReferences_utilities_get_prefix()));

    $query = <<<EOD
CREATE TABLE IF NOT EXISTS `%sarticles_authors` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `article_id` INT(11) UNSIGNED NOT NULL,
    `author_id` INT(11) UNSIGNED NOT NULL,
    `role` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    PRIMARY KEY (`id`),
    KEY `role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0;
EOD;
    $GLOBALS['wpdb']->query(sprintf($query, mlReferences_utilities_get_prefix()));

    $query = <<<EOD
ALTER TABLE `%sarticles`
    ADD CONSTRAINT `%sarticles_document_id`
    FOREIGN KEY (`document_id`)
    REFERENCES `%sdocuments` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;
EOD;
    $GLOBALS['wpdb']->query(
        sprintf(
            $query,
            mlReferences_utilities_get_prefix(),
            mlReferences_utilities_get_prefix(),
            mlReferences_utilities_get_prefix()
        )
    );

    $query = <<<EOD
ALTER TABLE `%sarticles_authors`
    ADD CONSTRAINT `%sarticles_authors_article_id`
    FOREIGN KEY (`article_id`)
    REFERENCES `%sarticles` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;
EOD;
    $GLOBALS['wpdb']->query(
        sprintf(
            $query,
            mlReferences_utilities_get_prefix(),
            mlReferences_utilities_get_prefix(),
            mlReferences_utilities_get_prefix()
        )
    );

    $query = <<<EOD
ALTER TABLE `%sarticles_authors`
    ADD CONSTRAINT `%sarticles_authors_author_id`
    FOREIGN KEY (`author_id`)
    REFERENCES `%sauthors` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;
EOD;
    $GLOBALS['wpdb']->query(
        sprintf(
            $query,
            mlReferences_utilities_get_prefix(),
            mlReferences_utilities_get_prefix(),
            mlReferences_utilities_get_prefix()
        )
    );

    mlReferences_utilities_get_directory(array());
}

function mlReferences_register_deactivation_hook()
{
    mlReferences_utilities_delete(mlReferences_utilities_get_directory(array()));

    $GLOBALS['wpdb']->query(sprintf('DROP TABLE IF EXISTS `%sarticles_authors`', mlReferences_utilities_get_prefix()));
    $GLOBALS['wpdb']->query(sprintf('DROP TABLE IF EXISTS `%sauthors`', mlReferences_utilities_get_prefix()));
    $GLOBALS['wpdb']->query(sprintf('DROP TABLE IF EXISTS `%sarticles`', mlReferences_utilities_get_prefix()));
    $GLOBALS['wpdb']->query(sprintf('DROP TABLE IF EXISTS `%sdocuments`', mlReferences_utilities_get_prefix()));
}

function mlReferences_init()
{
    if (!session_id()) {
        session_start();
    }
    ob_start();
    if (get_magic_quotes_gpc()) {
        $temporary = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
        while (list($key, $value) = each($temporary)) {
            foreach ($value as $k => $v) {
                unset($temporary[$key][$k]);
                if (is_array($v)) {
                    $temporary[$key][stripslashes($k)] = $v;
                    $temporary[] = &$temporary[$key][stripslashes($k)];
                } else {
                    $temporary[$key][stripslashes($k)] = stripslashes($v);
                }
            }
        }
        unset($temporary);
    }
    add_action('wp_enqueue_scripts', 'mlReferences_scripts');
    add_action('wp_enqueue_scripts', 'mlReferences_styles');
}

function mlReferences_admin_init()
{
    add_action('admin_print_scripts', 'mlReferences_scripts');
    add_action('admin_print_styles', 'mlReferences_styles');
}

function mlReferences_scripts()
{
    wp_enqueue_script('mlReferences_js', sprintf('%s/mlReferences.js', plugins_url('/mlReferences')), array('jquery'));
}

function mlReferences_styles()
{
    wp_enqueue_style('mlReferences_css', sprintf('%s/mlReferences.css', plugins_url('/mlReferences')));
}

function mlReferences_admin_menu()
{
    add_menu_page('mlReferences', 'mlReferences', 'manage_options', '/mlReferences', 'mlReferences_dashboard', '');
    add_submenu_page('/mlReferences', 'F.A.Q', 'F.A.Q', 'manage_options', '/mlReferences/faq', 'mlReferences_faq');
}

function mlReferences_add_meta_boxes()
{
    add_meta_box('mlReferences_1', 'mlReferences - Page Detail', 'mlReferences_add_meta_boxes_1', 'page');
    add_meta_box('mlReferences_2', 'mlReferences - Translations', 'mlReferences_add_meta_boxes_2', 'page');
    add_meta_box('mlReferences_3', 'mlReferences - Semantic Annotations', 'mlReferences_add_meta_boxes_3', 'page');
    add_meta_box('mlReferences_4', 'mlReferences - References', 'mlReferences_add_meta_boxes_4', 'page');
}

function mlReferences_add_meta_boxes_1($page)
{
    wp_nonce_field('mlReferences_add_meta_boxes_1', 'mlReferences_add_meta_boxes_1');
    $multipage_report = get_post_meta($page->ID, 'mlReferences_1_multipage_report', true);
    $root = intval(get_post_meta($page->ID, 'mlReferences_1_root', true));
    $pages = get_pages(array(
        'authors' => '',
        'child_of' => 0,
        'exclude' => $page->ID,
        'exclude_tree' => '',
        'hierarchical' => 0,
        'include' => '',
        'meta_key' => '',
        'meta_value' => '',
        'number' => '',
        'offset' => 0,
        'parent' => -1,
        'post_status' => 'publish',
        'post_type' => 'page',
        'sort_column' => 'post_title',
        'sort_order' => 'asc',
    ));
    ?>
    <div class="mlReferences_1">
        <table class="mlReferences_widget">
            <tr class="even">
                <td class="label">
                    <label for="mlReferences_1_multipage_report">Multipage Report</label>
                </td>
                <td>
                    <select id="mlReferences_1_multipage_report" name="mlReferences_1_multipage_report">
                        <option <?php echo $multipage_report === "No"? 'selected="selected"': ''; ?> value="No">
                            No
                        </option>
                        <option <?php echo $multipage_report === "Yes"? 'selected="selected"': ''; ?> value="Yes">
                            Yes
                        </option>
                    </select>
                </td>
            </tr>
            <tr class="even">
                <td class="label"><label for="mlReferences_1_root">Root</label></td>
                <td>
                    <select id="mlReferences_1_root" name="mlReferences_1_root">
                        <option <?php echo $root === 0? 'selected="selected"': ''; ?> value="0">None</option>
                        <?php foreach ($pages as $page) : ?>
                            <option
                                <?php echo $root === $page->ID? 'selected="selected"': ''; ?>
                                value="<?php echo $page->ID; ?>"
                                >
                                <?php echo $page->post_title; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>
    </div>
    <?php
}

function mlReferences_add_meta_boxes_2($page)
{
    $table_of_contents = get_post_meta($page->ID, 'mlReferences_2_table_of_contents', true);
    $references = get_post_meta($page->ID, 'mlReferences_2_references', true);
    ?>
    <div class="mlReferences_2">
        <table class="mlReferences_widget">
            <tr class="even">
                <td class="label">
                    <label for="mlReferences_2_table_of_contents">Table of Contents</label>
                </td>
                <td>
                    <input
                        id="mlReferences_2_table_of_contents"
                        name="mlReferences_2_table_of_contents"
                        type="text"
                        value="<?php echo $table_of_contents? $table_of_contents: 'Table of Contents'; ?>"
                        >
                </td>
            </tr>
            <tr class="even">
                <td class="label"><label for="mlReferences_2_references">References</label></td>
                <td>
                    <input
                        id="mlReferences_2_references"
                        name="mlReferences_2_references"
                        type="text"
                        value="<?php echo $references? $references: 'References'; ?>"
                        >
                </td>
            </tr>
        </table>
    </div>
    <?php
}

function mlReferences_add_meta_boxes_3($page)
{
    $annotations = json_decode(get_post_meta($page->ID, 'mlReferences_3', true), true);
    if (empty($annotations)) {
        $annotations = array();
        $annotations[] = array(
            'ontology' => 'DoCO',
            'class' => 'Chapter',
            'property' => $_POST['mlReferences_3_properties'][$key],
            'value' => $_POST['mlReferences_3_values'][$key],
        );
        $annotations[] = array(
            'ontology' => 'DoCO',
            'class' => 'ChapterTitle',
            'property' => $_POST['mlReferences_3_properties'][$key],
            'value' => $_POST['mlReferences_3_values'][$key],
        );
        $annotations[] = array(
            'ontology' => 'dc',
            'class' => 'subject',
            'property' => $_POST['mlReferences_3_properties'][$key],
            'value' => $_POST['mlReferences_3_values'][$key],
        );
    }
    $ontologies = array(
        'dc' => 'dc',
        'DoCO' => 'DoCO',
    );
    $classes = array(
        'dc' => array(
            'subject' => 'subject',
        ),
        'DoCO' => array(
            'Appendix' => 'Appendix',
            'Chapter' => 'Chapter',
            'ChapterTitle' => 'ChapterTitle',
            'FrontMatter' => 'FrontMatter',
            'Glossary' => 'Glossary',
            'ListOfAuthors' => 'ListOfAuthors',
            'ListOfFigures' => 'ListOfFigures',
            'ListOfOrganizations' => 'ListOfOrganizations',
            'ListOfTables' => 'ListOfTables',
            'Preface' => 'Preface',
            'TableOfContents' => 'TableOfContents',
        ),
    );
    ?>
    <div class="mlReferences_3">
        <script class="template" type="text/template">
            <tr>
                <td>
                    <select class="wide" name="mlReferences_3_ontologies[]">
                        <% _.forEach(ontologies, function (value, key) { %>
                            <option
                                <% if (key === annotation.ontology) { %>selected="selected"<% } %>
                                value="<%= key %>"
                                ><%= value %></option>
                        <% }); %>
                    </select>
                </td>
                <td>
                    <select class="wide" name="mlReferences_3_classes[]">
                        <% _.forEach(classes, function (value, key) { %>
                            <optgroup label="<%= key %>">
                                <% _.forEach(value, function (v, k) { %>
                                    <option
                                        <% if (k === annotation.class) { %>selected="selected"<% } %>
                                        value="<%= k %>"
                                        ><%= v %></option>
                                <% }); %>
                            </optgroup>
                        <% }); %>
                    </select>
                </td>
                <td>
                    <input
                        class="wide"
                        name="mlReferences_3_properties[]"
                        type="text"
                        value="<%= annotation.property %>"
                        >
                </td>
                <td>
                    <a class="dashicons dashicons-no-alt delete" title="Delete"></a>
                    <input
                        class="wide"
                        name="mlReferences_3_values[]"
                        type="text"
                        value="<%= annotation.value %>"
                        >
                </td>
            </tr>
        </script>
        <table
            class="mlReferences_widget wide"
            data-annotations="<?php echo htmlspecialchars(json_encode($annotations), ENT_QUOTES, 'UTF-8'); ?>"
            data-classes="<?php echo htmlspecialchars(json_encode($classes), ENT_QUOTES, 'UTF-8'); ?>"
            data-ontologies="<?php echo htmlspecialchars(json_encode($ontologies), ENT_QUOTES, 'UTF-8'); ?>"
            >
            <tr>
                <th>
                    <a class="dashicons dashicons-plus add float-right" title="Add"></a>
                    Ontology
                </th>
                <th>Class</th>
                <th>Property</th>
                <th>Value</th>
            </tr>
        </table>
    </div>
    <?php
}

function mlReferences_add_meta_boxes_4($page)
{
    $query = <<<EOD
SELECT
    `id`,
    `title_1`,
    `year`,
    `citations_first`,
    `citations_subsequent`,
    `citations_parenthetical_first`,
    `citations_parenthetical_subsequent`
FROM `%sarticles`
ORDER BY `title_1` ASC
EOD;
    $query = sprintf($query, mlReferences_utilities_get_prefix());
    $articles = $GLOBALS['wpdb']->get_results($query, ARRAY_A);
    ?>
    <div class="mlReferences_4">
        <table class="mlReferences_widget">
            <tr class="even">
                <td>
                    <input
                        class="keywords"
                        id="mlReferences_4_keywords"
                        name="mlReferences_4_keywords"
                        type="text"
                        >
                </td>
                <td class="label"><input class="button-primary search" type="button" value="Search"></td>
            </tr>
        </table>
        <table class="mlReferences_widget wide">
            <tr>
                <th class="narrow right">ID</th>
                <th>Title</th>
                <th class="narrow">Style 1</th>
                <th class="narrow">Style 2</th>
                <th class="narrow">Style 3</th>
                <th class="narrow">Style 4</th>
            </tr>
            <?php foreach ($articles as $key => $value) : ?>
                <tr
                    class="article <?php echo ($key % 2 === 0)? 'even': 'odd'; ?>"
                    data-id="<?php echo $value['id']; ?>"
                    >
                    <td class="narrow right"><?php echo $value['id']; ?></td>
                    <td title="<?php echo $value['title_1']; ?>">
                        <?php echo substr($value['title_1'], 0, 15); ?>...
                    </td>
                    <td
                        class="narrow"
                        data-style="citations_first"
                        title="<?php echo $value['citations_first']; ?>"
                        >
                        <a class="dashicons dashicons-plus add float-right" title="Add"></a>
                        <?php echo substr($value['citations_first'], 0, 10); ?>...
                    </td>
                    <td
                        class="narrow"
                        data-style="citations_subsequent"
                        title="<?php echo $value['citations_subsequent']; ?>"
                        >
                        <a class="dashicons dashicons-plus add float-right" title="Add"></a>
                        <?php echo substr($value['citations_subsequent'], 0, 10); ?>...
                    </td>
                    <td
                        class="narrow"
                        data-style="citations_parenthetical_first"
                        title="<?php echo $value['citations_parenthetical_first']; ?>"
                        >
                        <a class="dashicons dashicons-plus add float-right" title="Add"></a>
                        <?php echo substr($value['citations_parenthetical_first'], 0, 10); ?>...
                    </td>
                    <td
                        class="narrow"
                        data-style="citations_parenthetical_subsequent"
                        title="<?php echo $value['citations_parenthetical_subsequent']; ?>"
                        >
                        <a class="dashicons dashicons-plus add float-right" title="Add"></a>
                        <?php echo substr($value['citations_parenthetical_subsequent'], 0, 10); ?>...
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php
}

function mlReferences_save_post($page_id)
{
    if (!isset($_POST['mlReferences_add_meta_boxes_1'])) {
        return $page_id;
    }
    if (!wp_verify_nonce($_POST['mlReferences_add_meta_boxes_1'], 'mlReferences_add_meta_boxes_1')) {
        return $page_id;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $page_id;
    }
    if ('page' === $_POST['post_type']) {
        if (!current_user_can('edit_page', $page_id)) {
            return $page_id;
        }
    } else {
        if (!current_user_can('edit_post', $page_id)) {
            return $page_id;
        }
    }
    $annotations = array();
    foreach ($_POST['mlReferences_3_ontologies'] as $key => $_) {
        if (!empty($_POST['mlReferences_3_ontologies'][$key]) and
            !empty($_POST['mlReferences_3_classes'][$key]) and
            !empty($_POST['mlReferences_3_properties'][$key]) and
            !empty($_POST['mlReferences_3_values'][$key])
        ) {
            $annotations[] = array(
                'ontology' => $_POST['mlReferences_3_ontologies'][$key],
                'class' => $_POST['mlReferences_3_classes'][$key],
                'property' => $_POST['mlReferences_3_properties'][$key],
                'value' => $_POST['mlReferences_3_values'][$key],
            );
        }
    }
    update_post_meta($page_id, 'mlReferences_1_multipage_report', $_POST['mlReferences_1_multipage_report']);
    update_post_meta($page_id, 'mlReferences_1_root', $_POST['mlReferences_1_root']);
    update_post_meta($page_id, 'mlReferences_2_table_of_contents', $_POST['mlReferences_2_table_of_contents']);
    update_post_meta($page_id, 'mlReferences_2_references', $_POST['mlReferences_2_references']);
    update_post_meta($page_id, 'mlReferences_3', json_encode($annotations));
}

function mlReferences_wp_head()
{
    $page = get_post(get_queried_object_id());
    if (!empty(mlReferences_utilities_get_shortcodes($page->post_content))) {
        $user = get_userdata($page->post_author);
        echo sprintf('<meta content="References for %s" name="Biro.BibliographicCollection">', $page->post_title);
        echo sprintf('<meta content="%s" name="DC.creator">', $user->display_name);
        echo sprintf('<meta content="%s" name="DC.date">', $page->post_date);
        echo sprintf('<meta content="%s" name="DC.description">', $page->post_title);
        echo sprintf('<meta content="%s" name="DC.title">', $page->post_title);
        echo sprintf('<meta content="%s" name="Doco.BackMatter">', $page->post_title);
        echo sprintf('<meta content="%s" name="Doco.Bibliography">', $page->post_title);
    }
}

function mlReferences_the_content($contents)
{
    $id = get_the_ID();
    $mlReferences_1_multipage_report = get_post_meta($id, 'mlReferences_1_multipage_report', true);
    $table_of_contents = get_post_meta($id, 'mlReferences_2_table_of_contents', true);
    $table_of_contents = $table_of_contents? $table_of_contents: 'Table of Contents';
    $references = get_post_meta($id, 'mlReferences_2_references', true);
    $references = $references? $references: 'References';
    $pages = get_pages(array(
        'authors' => '',
        'child_of' => 0,
        'exclude' => $id,
        'exclude_tree' => '',
        'hierarchical' => 0,
        'include' => '',
        'meta_key' => 'mlReferences_1_root',
        'meta_value' => $id,
        'number' => '',
        'offset' => 0,
        'parent' => -1,
        'post_status' => 'publish',
        'post_type' => 'page',
        'sort_column' => 'post_title',
        'sort_order' => 'asc',
    ));
    if (!empty($pages)) {
        $contents = array();
        $contents[] = sprintf('<p><strong>%s:</strong></p>', $table_of_contents);
        $items = array();
        foreach ($pages as $page) {
            $shortcodes = mlReferences_utilities_get_shortcodes($page->post_content);
            if (!empty($shortcodes)) {
                $items[] = sprintf('<li><a href="%s">%s</a></li>', get_permalink($page->ID), get_the_title($page->ID));
            }
        }
        if (!empty($items)) {
            $contents[] = '<ul>';
            $contents[] = implode('', $items);
            $contents[] = '</ul>';
        }
        $contents[] = sprintf('<p><strong>%s:</strong></p>', $references);
        $items = array();
        foreach ($pages as $page) {
            $shortcodes = mlReferences_utilities_get_shortcodes($page->post_content);
            if (!empty($shortcodes)) {
                uasort($shortcodes, 'mlReferences_utilities_uasort');
                foreach ($shortcodes as $key => $value) {
                    if (empty($items[$value['string']])) {
                        $items[$value['string']] = array(
                            'id' => $value['id'],
                            'style' => $value['style'],
                            'string' => $value['string'],
                            'numbers' => array(),
                        );
                    }
                    if (!empty($value['numbers'])) {
                        foreach ($value['numbers'] as $number) {
                            $items[$value['string']]['numbers'][] = sprintf('%d.%d', $page->ID, $number);
                        }
                    }
                }
            }
        }
        $items = array_values($items);
        uasort($items, 'mlReferences_utilities_uasort');
        if (!empty($items)) {
            $contents[] = '<ul role="doc-bibliography">';
            foreach ($items as $item) {
                $numbers = array();
                if (!empty($item['numbers'])) {
                    foreach ($item['numbers'] as $number) {
                        $numbers[] = sprintf('[%s]', $number);
                    }
                }
                $numbers = implode(' ', $numbers);
                $contents[] = sprintf(
                    '<li id="mlReferences_%s_%s" role="doc-biblioentry">%s %s</li>',
                    $item['id'],
                    $item['style'],
                    $item['string'],
                    $numbers
                );
            }
            $contents[] = '</ul>';
        }
        $contents = implode('', $contents);
    } else {
        $shortcodes = mlReferences_utilities_get_shortcodes($contents);
        if (!empty($shortcodes)) {
            $index = 0;
            foreach ($shortcodes as $key => $value) {
                $index++;
                $contents = str_replace(
                    $key,
                    sprintf('[<a href="#mlReferences_%s_%s">%s</a>]', $value['id'], $value['style'], $index),
                    $contents
                );
            };
            uasort($shortcodes, 'mlReferences_utilities_uasort');
            $items = array();
            $items[] = sprintf('<p><strong>%s:</strong></p>', $references);
            $items[] = '<ul role="doc-bibliography">';
            foreach ($shortcodes as $key => $value) {
                $numbers = array();
                if (!empty($value['numbers'])) {
                    foreach ($value['numbers'] as $number) {
                        $numbers[] = sprintf('[%d]', $number);
                    }
                }
                $numbers = implode(' ', $numbers);
                $items[] = sprintf(
                    '<li id="mlReferences_%s_%s" role="doc-biblioentry">%s %s</li>',
                    $value['id'],
                    $value['style'],
                    $value['string'],
                    $numbers
                );
            }
            $items[] = '</ul>';
            $items = implode('', $items);
            $contents .= $items;
        }
    }
    return $contents;
}
