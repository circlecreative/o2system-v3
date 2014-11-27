$(document).ready(function() {
    $(".fixed-table").each(function() {
        var Id = $(this).get(0).id;
        var maintbheight = 1051;
        var maintbwidth = 1000;

        $("#" + Id + " .FixedTables").fixedTable({
            width: maintbwidth,
            height: maintbheight,
            fixedColumns: 3,
            classHeader: "fixedHead",
            classFooter: "fixedFoot",
            classColumn: "fixedColumn",
            fixedColumnWidth: 150,
            outerId: Id,
            Contentbackcolor: "#FFFFFF",
            Contenthovercolor: "#99CCFF",
            fixedColumnbackcolor:"#187BAF",
            fixedColumnhovercolor:"#99CCFF"
        });
    });
});