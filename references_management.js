jQuery(function () {
    jQuery('.references_management_widget').on('keydown', '.keywords', function (event) {
        if (event.keyCode == 13) {
            event.preventDefault();
            event.stopPropagation();
            jQuery('.references_management_widget .search').click();
        }
    });

    jQuery('.references_management_widget').on('click', '.search', function () {
        var table_1 = jQuery(this).parents('table');
        var keywords = table_1.find('.keywords').val().toLowerCase();
        var table_2 = table_1.next().find('table');
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
});
