var CustomConfirm = function () {

    var initConfirm = function () {
        $('#confirm-delete').on('show.bs.modal', function(e) {
            $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
        });
    }
    return {

        //main function to initiate the module
        init: function () {
            initConfirm();
        }
    };
}();

