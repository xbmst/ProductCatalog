(function($) {

    var scope = {
        options: {},
        headers: [],
        model: {},
        sortableColumns: null,
        sortOrder: null,
        allowedOrders: ["asc", "desc"],
        filterableColumns: null,
        filterPattern: null,
        filterLastId: null,
        paginationWrapper: null,
        activePage: null,
        rowsWrapper: null,
        gridHeader: null,
        grid: null,
        pagesAmount: 0,
        submitButton: null,
        context: null,
        state: {},
        methods: {
            setContext: function(context) {
                scope.context = context;
            },

            initSettings: function() {
                var sortBy = "<option value='none'>no sort</option>",
                    filterBy = "<option value='none'>no filter</option>",
                    header = "";
                for(var i = 0; i < scope.headers.length; i++) {
                    header = scope.headers[i].toLowerCase();
                    if(scope.options.sortableColumns.indexOf(header) >= 0) {
                        sortBy += "<option value='"+header+"'>"+header+"</option>";
                    }
                    if(scope.options.filterableColumns.indexOf(header) >= 0) {
                        filterBy += "<option value='"+header+"'>"+header+"</option>";
                    }
                }
                scope.sortableColumns.html(sortBy);
                scope.filterableColumns.html(filterBy);
                scope.methods.initSelects();
            },

            initSelects: function() {
                scope.selects = scope.context.find(".settings-select");
                scope.selects.each(function() {
                    $(this).material_select();
                });
            },

            initGrid: function() {
                scope.context.load("/products/get-template");
                this.get(scope.options.dataUrl, {}, function(response) {
                    scope.headers = response.headers;
                    scope.model = response.data;
                    scope.gridHeader = scope.context.children(".grid-header");
                    scope.grid = scope.context.children(".grid");
                    scope.sortableColumns = scope.context.find("#sort-by-value");
                    scope.sortOrder = scope.context.find("#sort-order-value");
                    scope.filterableColumns = scope.context.find("#filter-field-value");
                    scope.filterPattern = scope.context.find("#filter-pattern");
                    scope.submitButton = scope.context.find("#submit-button");
                    scope.paginationWrapper = scope.context.find(".pagination");
                    scope.activePage = scope.paginationWrapper.find("li.active");
                    scope.methods.getPages(scope.options["rowsPerPage"], function() {
                        $('.pagination').twbsPagination({
                            startPage: 1,
                            totalPages: scope.pagesAmount,
                            visiblePages: 7,
                            onPageClick: function (event, page) {
                                scope.state['page'] = page;
                                scope.methods.paginate(scope.state);
                            }
                        });
                    });
                    scope.methods.initSettings();
                    scope.methods.handleParamsChange();
                    scope.methods.updateView();
                });
            },

            handleParamsChange: function() {
                scope.methods.handleFiltering();
                scope.methods.handleSorting();
                scope.methods.handlePagination();
            },

            handleFiltering: function() {
                scope.filterableColumns.on("change", function() {
                    if(scope.filterPattern.val()) onFilter();
                });
                scope.filterPattern.on("keypress", function(e) {
                    if(e.keyCode == '13') onFilter();
                });

                function onFilter() {
                    if(!scope.filterPattern.val()) {
                        console.log("incorrect filtering format");
                    }
                    if(scope.filterableColumns.val() === 'none') {
                        delete scope.state["filter_pattern"];
                        delete scope.state["filter_field"];
                        delete scope.state["filter_last_id"];
                        scope.filterPattern.val('');
                        scope.filterableColumns.val('');
                    }
                    else {
                        scope.state["filter_pattern"] = scope.filterPattern.val();
                        scope.state["filter_field"] = scope.filterableColumns.val();

                    }
                    scope.methods.filter(scope.state);
                }
            },

            handleSorting: function() {
                scope.sortOrder.on("change", onSortParamsChange);
                scope.sortableColumns.on("change", onSortParamsChange);

                function onSortParamsChange() {
                    if(scope.allowedOrders.indexOf(scope.sortOrder.val()) < 0) {
                        console.log("incorrect sorting format");
                    }
                    else {
                        scope.state["sort_field"] = scope.sortableColumns.val();
                        scope.state["order_by"] = scope.sortOrder.val();
                        console.log(scope.state);
                        scope.methods.sort(scope.state);
                    }
                }
            },

            handlePagination: function() {
                scope.paginationWrapper.children("li[data-page]").on("click", function() {
                    scope.paginationWrapper.find("li.active").removeClass("active");
                    $(this).addClass("active");
                    scope.methods.paginate();
                });
            },

            sort: function(data) {
                this.process(scope.options.dataUrl, data);
            },

            filter: function(data) {
                this.process(scope.options.dataUrl, data);
            },

            paginate: function(data) {
                this.process(scope.options.dataUrl, data);
            },

            process: function(url, data, callback) {
                this.get(url, data, function(response) {
                    scope.model = response.data;
                    if(response.filter_last_id) {
                        scope.state['filter_last_id'] = response.filter_last_id;
                    }
                    scope.methods.updateView();
                });
            },

            get: function(url, data, callback) {
                $.ajax({
                    method: "GET",
                    url: url,
                    data: $.extend(data, {
                        'rows_per_page': scope.options['rowsPerPage']
                    }),
                    success: callback
                });
            },

            getPages: function(rows, callback) {
                $.get("/products/get-pages-amount", {
                    'rows_per_page': rows
                }, function(response) {
                    scope.pagesAmount = parseInt(response, 10);
                    callback();
                });
            },

            updateView: function() {
                var id, name, year, description, img,
                    resultDOM = "";
                for (var i = 0; i < scope.model.length; i++) {
                    var row = scope.model[i];
                    resultDOM += "<div class='product col s12 m4 l3 z-depth-2 rounded'>";
                    img = "<div class='prod-info image-wrapper'><img src='https://cdn1.iconfinder.com/data/icons/office-vol-5-2/128/office-13-128.png'></div>";
                    id = "<div class='prod-info name-id'><b>"+row["id"]+"</b> - "+row["name"]+"</div>";
                    description = "<div class='prod-info description'>"+row["description"]+"</div>";
                    year = "<div class='year'><b> Added "+row["year"]+"</b></div>";

                    resultDOM += img+id+description+year;
                    resultDOM += "</div>";
                }
                scope.grid.html(resultDOM);
            }
        }
    };

    $.fn.ajaxGrid = function(options) {
        options = options || {};
        $.extend(options, {
            dataUrl: "/products/get",
            sortableColumns: ["name"],
            filterableColumns: ["name", "description"],
        });

        scope.options = options;
        scope.methods.setContext(this);
        scope.methods.initGrid();

        return this;
    }

})(jQuery);
