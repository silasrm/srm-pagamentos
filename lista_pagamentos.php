<?php

global $wpdb, $srm_pagamentos_tbl_name;

$pg = isset($_GET['pg']) ? $_GET['pg'] : 0;

$buscar = isset($_POST['buscar']) ? $_POST['buscar'] : null;

$msg = isset($_GET['msg']) ? $_GET['msg'] : null;

foreach ($_POST as $key => $value) {
    ${$key} = $value;
}

$where = " and a.user_id = " . get_current_user_id();

//if($buscar != ''){
//	$where .= " and ( LOWER(a.nome) LIKE  '%".strtolower($buscar)."%' or LOWER(a.descricao) LIKE '%".strtolower($buscar)."%' or LOWER(b.area) LIKE '%".strtolower($buscar)."%')";
//}

######### INICIO Paginação
$numreg = 20; // Quantos registros por página vai ser mostrado
if (!isset($pg)) {
    $pg = 0;
}

$inicial = $pg * $numreg;

######### FIM dados Paginação

$sql = "SELECT a.*
		FROM " . $srm_pagamentos_tbl_name . " a
		where 1=1 $where order by a.vencimento asc LIMIT $inicial, $numreg ";

$query = $wpdb->get_results($sql);

$sqlRow = "SELECT a.*
		FROM " . $srm_pagamentos_tbl_name . " a
		where 1=1 $where order by a.vencimento asc";
$queryRow = $wpdb->get_results($sqlRow);
$quantreg = $wpdb->num_rows; // Quantidade de registros pra paginação
?>
<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Vencimento</th>
                <th>Valor</th>
                <th>Situação</th>
                <th width="50" style="text-align:center;">Boleto</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $x = 1;
        foreach ($query as $k => $v) {
            $vencimento = \DateTime::createFromFormat('Y-m-d', $v->vencimento);
            $pagamento = \DateTime::createFromFormat('Y-m-d', $v->pagamento);
        ?>
            <input type="hidden" id="id_registro_<?php echo $x ?>" value="<?php echo $v->id ?>"/>
            <tr>
                <td><?php echo $vencimento->format('d/m/Y'); ?></td>
                <td><?php echo number_format($v->valor, 2, ',', '.'); ?></td>
                <td>
                    <?php
                    if ($pagamento) {
                        echo 'Pago';
                    } else {
                        $agora = new \DateTime();
                        if ($vencimento > $agora) {
                            echo 'Pendente';
                        } else {
                            echo 'Vencido';
                        }
                    }
                    ?>
                </td>
                <td style="text-align:center;">
                    <?php if ($v->arquivo != "") { ?>
                        <a href="<?php echo content_url('uploads/srm_pagamentos/pagamentos/' . $v->arquivo); ?>"
                           target="_blank">
                            Baixar
                        </a>
                    <?php } else { ?>
                        -
                    <?php } ?>
                </td>
            </tr>
        <?php
            $x++;
        }

        if ($quantreg == 0):
        ?>
            <tr>
                <td colspan="4">
                    Nenhuma cobrança encontrada.
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
    if($quantreg > $numreg){
        // Chama o arquivo que monta a paginação. ex: << anterior 1 2 3 4 5 próximo >>
        include(SRM_PAGAMENTO_PLUGIN_PATH . 'admin/include/paginacao2.php' );
    }
    wp_enqueue_script('scriptJSa', plugins_url('js/script.js', __FILE__));
?>