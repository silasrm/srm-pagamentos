(function ($) {
    $(document).ready(function () {
        $('#checkAll').click(function () {
            if ($('#checkAll').is(':checked')) {
                $('.check').attr('checked', true);
            } else {
                $('.check').attr('checked', false);
            }
        });
    });
}(jQuery));