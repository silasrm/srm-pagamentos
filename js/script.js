(function ($) {
    $(document).ready(function(){
        $(document).on('click', 'a.remover', function(e) {
            if (confirm('Deseja realmente excluir este item?')) {
                return true;
            }

            return false;
        });
    });
}(jQuery));