$(function() {
    $.widget("fks.searchTable", $.ui.autocomplete, {
// default options
        options: {
            metaSuffix: '__meta'
        },
        _create: function() {
            var elTable = this.element;

            var elSearchInput = $(elTable.data('st-search-input'));
            var elCount = $(elTable.data('st-count'));

            var index = new Array();
            var fullCount = 0;

            elTable.find('tbody tr').each(function() {
                var row = $(this);
                var content = null;
                if (row.data('st-data')) {
                    content = row.data('st-data');
                } else {
                    content = row.text();
                }
                index.push({
                    text: content,
                    row: row
                });
                fullCount += 1;
            });

            elCount.text(fullCount);
            function searchHandler() {
                var term = elSearchInput.val();
                var re = new RegExp('.*' + term + '.*', 'im');
                var count = 0;
                for (var i in index) {
                    if (re.test(index[i].text)) {
                        index[i].row.show();
                        count += 1;
                    } else {
                        index[i].row.hide();
                    }
                }
                elCount.text(count);
            }

            elSearchInput.keyup(searchHandler);
        }
    });

    $('table[data-st]').searchTable();

});