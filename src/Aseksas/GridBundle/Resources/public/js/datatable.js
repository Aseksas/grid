/***
 Wrapper/Helper Class for datagrid based on jQuery Datatable Plugin
 ***/
var Datatable = function() {

    var tableOptions; // main options
    var dataTable; // datatable object
    var table; // actual table jquery object
    var tableContainer; // actual table container object
    var tableWrapper; // actual table wrapper jquery object
    var tableInitialized = false;
    var ajaxParams = {}; // set filter mode
    var the;
    var defaultAjaxParams = {}; //

    return {

        init: function(options) {

            if (!$().dataTable) {
                return;
            }

            the = this;


            var langDefault = $.extend(true, {
                "lengthMenu": "Showing _MENU_",
                "info": "Total _TOTAL_ records",
                "infoEmpty": "No results",
                "emptyTable": "No data available in table",
                "zeroRecords": "No matching records found",
                "errorLoading": "Error loading data from \"_NAME_\" datatable",
                "massAction" : {
                    "canceled":  "Action was not confirmed",
                    "noAction" : "Please select action",
                    "noItem" : "Please select row or rows for action"
                },
                "paginate": {
                    "previous": "Previous",
                    "next": "Next",
                    "last": "Last",
                    "first": "First",
                    "page": "",
                    "pageOf": "of"
                }
            }, (typeof window.aseksasGridLanguage !== undefined) ? window.aseksasGridLanguage : {});

            options = $.extend(true, {
                src: "", // actual table
                filterApplyAction: "filter",
                filterCancelAction: "filter_cancel",
                resetGroupActionInputOnSuccess: true,
                loadingMessage: 'Loading...',
                dataTable: {
                    "dom": "<'row'<'col-md-12 col-sm-12'pli><'col-md-4 col-sm-12'<'table-group-actions pull-right'>>r><'table-scrollable't><'row'<'col-md-12 col-sm-12'pli><'col-md-4 col-sm-12'>>",
                    "pageLength": 10,
                    "language": langDefault,
                    "orderCellsTop": true,
                    "columnDefs": [{
                        'orderable': false,
                        'targets': [0]
                    }],
                    "pagingType": "numbers", // pagination type(bootstrap, bootstrap_full_number or bootstrap_extended)
                    "autoWidth": false, // disable fixed width and enable fluid table
                    "processing": false, // enable/disable display message box on record load
                    "serverSide": true, // enable/disable server side ajax loading

                    "ajax": { // define ajax settings
                        "url": "", // ajax URL
                        "type": "POST", // request type
                        "timeout": 20000,
                        "data": function(data) { // add request parameters before submit
                            $.each(ajaxParams, function(key, value) {
                                data[key] = value;
                            });


                        },
                        "dataSrc": function(res) {

                            if ($('.group-checkable', table).size() === 1) {
                                $('.group-checkable', table).attr("checked", false);
                            }

                            if(res.massAction !== undefined) {
                                var massAction = res.massAction;

                                if(typeof massAction.message !== undefined) {
                                    var status = (typeof massAction.status !== undefined) ? massAction.status : 'info';

                                    the.setMessage(status, massAction.message);

                                    if(typeof window[massAction.callback] !== 'undefined') {
                                        var array = $.map(massAction, function(value) {
                                            return [value];
                                        });
                                        window[massAction.callback].apply(this, array);
                                    }
                                }
                            }

                            return res.data;
                        },
                        "error": function(e) {
                            the.setMessage('error', tableOptions.dataTable.language.errorLoading.replace("_NAME_", options.gridName));
                        }
                    },

                    "drawCallback": function(oSettings) {
                        if (tableInitialized === false) {
                            tableInitialized = true;
                            table.show();
                        }

                        $.each(dataTable.ajax.json().highlight, function ( j, v ) {
                            $(dataTable.row(j).node()).addClass(v);
                        });

                        if (tableOptions.onDataLoad) {
                            tableOptions.onDataLoad.call(undefined, the);
                        }
                    }
                }
            }, options);

            this.setAjaxParam('gridName', options.gridName);

            tableOptions = options;
            table = $(options.src);
            tableContainer = table.parents(".table-container");

            var tmp = $.fn.dataTableExt.oStdClasses;

            $.fn.dataTableExt.oStdClasses.sWrapper = $.fn.dataTableExt.oStdClasses.sWrapper + " dataTables_extended_wrapper";
            $.fn.dataTableExt.oStdClasses.sFilterInput = "form-control input-small input-sm input-inline";
            $.fn.dataTableExt.oStdClasses.sLengthSelect = "form-control input-xsmall input-sm input-inline";
            dataTable = table.DataTable(options.dataTable);

            $.fn.dataTableExt.oStdClasses.sWrapper = tmp.sWrapper;
            $.fn.dataTableExt.oStdClasses.sFilterInput = tmp.sFilterInput;
            $.fn.dataTableExt.oStdClasses.sLengthSelect = tmp.sLengthSelect;

            tableWrapper = table.parents('.dataTables_wrapper');

            if ($('.table-actions-wrapper', tableContainer).size() === 1) {
                $('.table-group-actions', tableWrapper).html($('.table-actions-wrapper', tableContainer).html()); // place the panel inside the wrapper
                $('.table-actions-wrapper', tableContainer).remove(); // remove the template container
            }

            $('.group-checkable', table).change(function() {
                var checked = $(this).is(":checked");
                $('tbody > tr > td:nth-child(1) input[type="checkbox"]', table).each(function() {
                    $(this).attr("checked", checked);
                });
            });

            this.getTableWrapper().on('click', '.table-group-action-submit', function (e) {
                e.preventDefault();

                var action = $(".table-group-action-input");
                if (action.find('option').data('confirm') ) {
                    if(!confirm(action.find('option').data('confirm'))) {
                        the.setMessage('info', tableOptions.dataTable.language.massAction.canceled);
                        return false;
                    }
                }

                if (action.val() === "") {
                    the.setMessage('error', tableOptions.dataTable.language.massAction.noAction)
                } else if (!the.getSelectedRowsCount()) {
                    the.setMessage('error', tableOptions.dataTable.language.massAction.noItem)
                } else {
                    the.setAjaxParam("massActionName", action.val());
                    the.setAjaxParam("check", the.getSelectedRows());

                    the.reload();
                    the.clearAjaxParams();
                }
            });

            // table.on('keyup', '.filter input', function(e) {
            //
            //     window.clearTimeout(request);
            //     request = setTimeout(function(){
            //         the.submitFilter();
            //     }, 2000);
            //
            //     if(e.keyCode === 13) {
            //         the.submitFilter();
            //         window.clearTimeout(request);
            //     }
            //     e.preventDefault();
            //
            // });
            //
            // table.on('change', '.filter select', function(e) {
            //     e.preventDefault();
            //     the.submitFilter();
            // });
            //
            // // handle filter submit button click
            // table.on('click', '.filter-submit', function(e) {
            //     e.preventDefault();
            //     the.submitFilter();
            // });
            //
            // // handle filter cancel button click
            // table.on('click', '.filter-cancel', function(e) {
            //     e.preventDefault();
            //     the.resetFilter();
            // });
            //
            // table.on('change', '.filter input', function(e) {
            //     e.preventDefault();
            //     the.submitFilter();
            // });
        },

        submitFilter: function() {
            the.setAjaxParam("action", tableOptions.filterApplyAction);

            // get all typeable inputs
            $('textarea.form-filter, select.form-filter, input.form-filter:not([type="radio"],[type="checkbox"])', table).each(function() {
                the.setAjaxParam($(this).attr("name"), $(this).val());
            });

            // get all checkboxes
            $('input.form-filter[type="checkbox"]:checked', table).each(function() {
                the.addAjaxParam($(this).attr("name"), $(this).val());
            });

            // get all radio buttons
            $('input.form-filter[type="radio"]:checked', table).each(function() {
                the.setAjaxParam($(this).attr("name"), $(this).val());
            });

            the.reload();
        },

        resetFilter: function() {
            $('textarea.form-filter, select.form-filter, input.form-filter', table).each(function() {
                $(this).val("");
            });
            $('input.form-filter[type="checkbox"]', table).each(function() {
                $(this).attr("checked", false);
            });
            the.clearAjaxParams();
            the.addAjaxParam("action", tableOptions.filterCancelAction);
            the.reload();
        },

        getSelectedRows: function() {
            var rows = [];
            $('tbody > tr > td:nth-child(1) input[type="checkbox"]:checked', table).each(function() {
                rows.push($(this).val());
            });

            return rows;
        },

        getSelectedRowsCount: function() {
            return the.getSelectedRows().length;
        },


        setAjaxParam: function(name, value) {
            ajaxParams[name] = value;
        },

        addAjaxParam: function(name, value) {
            if (!ajaxParams[name]) {
                ajaxParams[name] = [];
            }

            skip = false;
            for (var i = 0; i < (ajaxParams[name]).length; i++) { // check for duplicates
                if (ajaxParams[name][i] === value) {
                    skip = true;
                }
            }

            if (skip === false) {
                ajaxParams[name].push(value);
            }
        },

        clearAjaxParams: function(name, value) {
            ajaxParams = Object.create(defaultAjaxParams);
        },

        getDataTable: function() {
            return dataTable;
        },

        getTableWrapper: function() {
            return tableWrapper;
        },

        gettableContainer: function() {
            return tableContainer;
        },

        getTable: function() {
            return table;
        },
        setMessage: function(type, message) {
            if(typeof window.aseksasGridMessageListener !== undefined) {
                window.aseksasGridMessageListener(type, message);
            }
        },
        reload: function() {
            the.getDataTable().ajax.reload();
        }

    };
};