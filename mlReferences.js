jQuery(function () {
    var mlReferences_3 = jQuery('.mlReferences_3');
    if (mlReferences_3.length) {
        var zebra = function (table) {
            var tr = table.find('tr');
            tr.removeClass('even');
            tr.removeClass('odd');
            tr.filter(':even').addClass('even');
            tr.filter(':odd').addClass('odd');
        };
        mlReferences_3.on('click', '.add', function () {
            var options = {
                annotation: {
                    ontology: '',
                    class: '',
                    property: '',
                    value: ''
                },
                classes: classes,
                ontologies: ontologies
            };
            var tr = template(options);
            table.append(tr);
            zebra(table);
        });
        mlReferences_3.on('click', '.delete', function () {
            jQuery(this).parents('tr').remove();
            zebra(table);
        });
        var table = mlReferences_3.find('table');
        var template = window._.template(mlReferences_3.find('.template').html());
        var annotations = table.attr('data-annotations');
        annotations = jQuery.parseJSON(annotations);
        var ontologies = table.attr('data-ontologies');
        ontologies = jQuery.parseJSON(ontologies);
        var classes = table.attr('data-classes');
        classes = jQuery.parseJSON(classes);
        if (annotations.length) {
            jQuery.each(annotations, function (key, value) {
                var options = {
                    annotation: value,
                    classes: classes,
                    ontologies: ontologies
                };
                var tr = template(options);
                table.append(tr);
                zebra(table);
            });
        } else {
            mlReferences_3.find('.add').click();
        }
    }
    var mlReferences_4 = jQuery('.mlReferences_4');
    if (mlReferences_4.length) {
        mlReferences_4.on('click', '.add', function () {
            var $this = jQuery(this);
            var id = $this.parent().parent().attr('data-id');
            var style = $this.parent().attr('data-style');
            var shortcode = '[mlReferences id="' + id + '" style="' + style + '"]';
            if (jQuery('div#wp-content-wrap').length && jQuery('div#wp-content-wrap').hasClass('html-active')) {
                var text = jQuery('textarea#content').val();
                jQuery('textarea#content').val(text + shortcode);
            } else {
                if (typeof tinyMCE === 'object') {
                    tinyMCE.execCommand('mceInsertRawHTML', false, shortcode);
                }
            }
        });
        mlReferences_4.on('click', '.search', function () {
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
        mlReferences_4.on('keydown', '.keywords', function (event) {
            if (event.keyCode === 13) {
                event.preventDefault();
                event.stopPropagation();
                mlReferences_4.find('.search').click();
            }
        });
    }
    if (window.location.hash.indexOf('mlReferences') !== -1) {
        jQuery('.mlReferences_highlight').removeClass('mlReferences_highlight');
        jQuery(window.location.hash).addClass('mlReferences_highlight');
    }
});
