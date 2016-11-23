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
        paginationWrapper: null,
        activePage: null,
        rowsWrapper: null,
        gridHeader: null,
        grid: null,
        submitButton: null,
        context: null,
        state: {
            sort: {},
            filter: {}
        },
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
            },

            initGrid: function() {
                scope.context.load("templates/grid-template.html");
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
                scope.submitButton.on("click", function() {
                    if(!scope.filterPattern.val() || scope.filterableColumns.val() === "none") {
                        console.log("incorrect filtering format");
                    }
                    else {
                        scope.state.filter["filter_pattern"] = scope.filterPattern.val();
                        scope.state.filter["filter_column"] = scope.filterableColumns.val();
                        scope.methods.filter(scope.state);
                    }
                });
            },

            handleSorting: function() {
                scope.sortOrder.on("change", onSortParamsChange);
                scope.sortableColumns.on("change", onSortParamsChange);

                function onSortParamsChange() {
                    if(scope.allowedOrders.indexOf(scope.sortOrder.val()) < 0 || scope.sortableColumns.val() === "none") {
                        console.log("incorrect sorting format");
                    }
                    else {
                        scope.state.sort["sort_column"] = scope.sortableColumns.val();
                        scope.state.filter["sort_order"] = scope.sortOrder.val();
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
                this.process(scope.options.dataUrl, data.sort);
            },

            filter: function(data) {
                this.process(scope.options.dataUrl, data.filter);
            },

            paginate: function(data) {
                this.process(scope.options.dataUrl, data);
            },

            process: function(url, data, callback) {
                this.get(url, data, function(response) {
                    scope.model = response.data;
                    scope.methods.updateView();
                });
            },

            get: function(url, data, callback) {
                $.ajax({
                    method: "GET",
                    url: url,
                    data: data,
                    success: callback || function(response) {
                        scope.model = response.data;
                        scope.methods.updateView();
                    }
                });
            },

            updateView: function() {
                var id, name, year, description, img,
                    resultDOM = "";
                for (var i = 0; i < scope.options.rowsPerPage; i++) {
                    var row = scope.model[i];
                    resultDOM += "<div class='product col s12 m4 l3 z-depth-2 rounded'>";
                    img = "<div class='prod-info image-wrapper'><img src='img/product.png'></div>";
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
            rowsPerPage: 10
        });

        scope.options = options;
        scope.methods.setContext(this);
        scope.methods.initGrid();

        return this;
    }

})(jQuery);
