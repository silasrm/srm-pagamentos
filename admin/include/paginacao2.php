<nav aria-label="Page navigation">
    <ul class="pagination">
        <?php
        $quant_pg = ceil($quantreg / $numreg);
        $quant_pg++;

        $server = $_SERVER['SERVER_NAME'];
        $endereco = $_SERVER ['REQUEST_URI'];

        $page_id = "page=" . (isset($_GET['page']) ? $_GET['page'] : null);
        $pg = isset($_GET['pg']) ? $_GET['pg'] : 0;

        $i_pg2 = 0;
        $active = $i_pg2 == @$pg ? "class=\" active \"" : "";

        // Verifica se esta na primeira página, se nao estiver ele libera o link para anterior
        if ($pg > 0) {
            echo "<li><a href='" . $PHP_SELF . "?" . @$page_id . "&pg=" . ($pg - 1) . "'class=pg><b>&laquo; anterior</b></a></li>";
        } else {
            echo "<li class=\"active\"><a href='#'><b>&laquo; anterior</b></a></li>";
        }

        // Faz aparecer os numeros das página entre o ANTERIOR e PROXIMO
        for ($i_pg = 1; $i_pg < $quant_pg; $i_pg++) {
            // Verifica se a página que o navegante esta e retira o link do número para identificar visualmente
            if ($pg == ($i_pg - 1)) {
                echo "<li class=\"active\">&nbsp;<a href='#'><b>$i_pg</b></a>&nbsp;</li>";
            } else {
                $i_pg2 = $i_pg - 1;
                echo "<li>&nbsp;<a href=\" " . $PHP_SELF . "?" . @$page_id . "&pg=$i_pg2 \"><b>$i_pg</b></a>&nbsp;</li>";
            }
        }

        // Verifica se esta na ultima página, se nao estiver ele libera o link para próxima
        if (($pg + 2) < $quant_pg) {
            echo "<li><a href=" . $PHP_SELF . "?" . @$page_id . "&pg=" . ($pg + 1) . "><b>próximo &raquo;</b></a></li>";
        } else {
            echo "<li class=\"active\"><a href='#'><b>próximo &raquo;</b></a></li>";
        }
        ?>
    </ul>
</nav>    