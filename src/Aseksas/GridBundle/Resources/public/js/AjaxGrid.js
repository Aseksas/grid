var AjaxGrid = function () {

    return {

        init: function (defaultParams) {

            var grid = new Datatable();
            grid.init({
                src: $('#'+defaultParams.name),
                gridName: defaultParams.name,
                loadingMessage: (typeof defaultParams.loadingMessage !== 'undefined') ? defaultParams.loadingMessage : 'Loading...',
                dataTable: {
                    columnDefs: [
                        { targets: 'no-sort', orderable: false }
                    ],

                    "lengthMenu": [10, 25, 50, 100],
                    "pageLength": (typeof defaultParams.pageLength !== 'undefined') ? defaultParams.pageLength : 50, // default record count per page
                    "ajax": {
                        "url": window.location.href,
                    },
                    "order": (typeof defaultParams.order !== 'undefined') ? defaultParams.order : [[1, "asc"]] // set first column as a default sort by asc
                }
            });
        }

    };

}();