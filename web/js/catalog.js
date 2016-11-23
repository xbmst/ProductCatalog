$(document).ready(function () {
    $("#products").ajaxGrid({
        dataUrl: "/products/get",
        sortableColumns: ["name"],
        filterableColumns: ["name", "description"],
        rowsPerPage: 15
    });
});