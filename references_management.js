jQuery(function () {
    var references_management_3 = jQuery('.references_management_3');
    if (references_management_3.length) {
        var zebra = function (table) {
            table.find('tr').removeClass('even');
            table.find('tr').removeClass('odd');
            table.find('tr:even').addClass('even');
            table.find('tr:odd').addClass('odd');
        };
        references_management_3.on('click', '.add', function () {
            table.append(template({
                ontologies: ontologies,
                classes: classes,
                annotation: {
                    ontology: '',
                    class: '',
                    property: '',
                    value: ''
                }
            }));
            zebra(table);
        });
        references_management_3.on('click', '.delete', function () {
            jQuery(this).parents('tr').remove();
            zebra(table);
        });
        var table = references_management_3.find('table');
        var ontologies = jQuery.parseJSON(table.attr('data-ontologies'));
        var classes = jQuery.parseJSON(table.attr('data-classes'));
        var template = window._.template(references_management_3.find('.template').html());
        var annotations = jQuery.parseJSON(table.attr('data-annotations'));
        if (annotations.length) {
            jQuery.each(annotations, function (key, value) {
                table.append(template({
                    ontologies: ontologies,
                    classes: classes,
                    annotation: value
                }));
                zebra(table);
            });
        } else {
            references_management_3.find('.add').click();
        }
    }
    var references_management_4 = jQuery('.references_management_4');
    if (references_management_4.length) {
        references_management_4.on('click', '.add', function () {
            var $this = jQuery(this);
            var id = $this.parent().parent().attr('data-id');
            var style = $this.parent().attr('data-style');
            var shortcode = '[references_management id="' + id + '" style="' + style + '"]';
            if (jQuery('div#wp-content-wrap').length && jQuery('div#wp-content-wrap').hasClass('html-active')) {
                var text = jQuery('textarea#content').val();
                jQuery('textarea#content').val(text + shortcode);
            } else {
                if (typeof tinyMCE === 'object') {
                    tinyMCE.execCommand('mceInsertRawHTML', false, shortcode);
                }
            }
        });
        references_management_4.on('click', '.search', function () {
            var table_1 = jQuery(this).parents('table');
            var keywords = table_1.find('.keywords').val().toLowerCase();
            var table_2 = table_1.next();
            jQuery.each(table_2.find('tr.article'), function () {
                var $this = jQuery(this);
                if (keywords === '' || $this.find('td').eq(1).text().toLowerCase().indexOf(keywords) !== -1) {
                    $this.removeClass('hide');
                } else {
                    $this.addClass('hide');
                }
            });
            table_2.find('tr.article').removeClass('even');
            table_2.find('tr.article').removeClass('odd');
            table_2.find('tr.article:not(.hide):even').addClass('even');
            table_2.find('tr.article:not(.hide):odd').addClass('odd');
        });
        references_management_4.on('keydown', '.keywords', function (event) {
            if (event.keyCode === 13) {
                event.preventDefault();
                event.stopPropagation();
                references_management_4.find('.search').click();
            }
        });
    }
    if (window.location.hash.indexOf('references_management') !== -1) {
        jQuery('.references_management_highlight').removeClass('references_management_highlight');
        jQuery(window.location.hash).addClass('references_management_highlight');
    }
});
