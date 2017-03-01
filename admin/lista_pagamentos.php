<?php

global $wpdb, $srm_pagamentos_tbl_name;

$pg = isset($_GET['pg']) ? $_GET['pg'] : 0;

$action = (isset($_GET['action']) && !empty($_GET['action'])) ? $_GET['action'] : null;

if ($action == 'remover'
    && isset($_GET['id'])
    && !empty($_GET['id'])) {
    include_once(SRM_PAGAMENTO_PLUGIN_PATH . 'admin/include/removePagamento.php');
}

$periodoFiltro = (isset($_GET['periodo']) && !empty($_GET['periodo'])) ? $_GET['periodo'] : null;

$msg = isset($_GET['msg']) ? $_GET['msg'] : null;

$where = "";

if ($periodoFiltro) {
    $dataInicio = \DateTime::createFromFormat('m/Y', $periodoFiltro);
    $dataInicio->modify('first day of this month');
    $dataFim = clone $dataInicio;
    $dataFim->modify('last day of this month');

    $where .= " and vencimento BETWEEN '" . $dataInicio->format('Y-m-d') . "' AND '" . $dataFim->format('Y-m-d') . "'";
}

include_once(SRM_PAGAMENTO_PLUGIN_PATH . 'admin/include/funcoes.php');

//########### Função para por como pago
if(isset($_POST['ids'])){
    include_once(SRM_PAGAMENTO_PLUGIN_PATH . 'admin/include/mudaStatusPagamentos.php');
}

//######### INICIO Paginação
$numreg = 20; // Quantos registros por página vai ser mostrado
if (!isset($pg)) {
	$pg = 0;
}
$inicial = $pg * $numreg;

//######### FIM dados Paginação

$sql = "SELECT a.*,
			   b.*
		FROM ".$srm_pagamentos_tbl_name." a
			left join ".$wpdb->prefix."users b
				on a.user_id = b.ID
		where 1=1 $where order by b.display_name asc LIMIT $inicial, $numreg ";
		
$query      = $wpdb->get_results( $sql );
$rowsCurr   = $wpdb->num_rows;

$sqlRow = "SELECT a.*,
			   b.*
		FROM ".$srm_pagamentos_tbl_name." a
			left join ".$wpdb->prefix."users b
				on a.user_id = b.ID
		   where 1=1 $where order by b.display_name asc, a.vencimento desc";

$queryRow = $wpdb->get_results($sqlRow);
$quantreg = $wpdb->num_rows; // Quantidade de registros pra paginação

srmp_pagamentos_assets();
?>
<div class="container-fluid">
    <h2>Listagem de pagamentos</h2>

    <div class="clearfix">
        <?php if (@$_GET['msg'] == 'ok') { ?>
            <div class="alert alert-success">Registro marcado como pago com sucesso!</div>
        <?php } elseif (@$_GET['msg'] == 'ok-desfeito') { ?>
            <div class="alert alert-success">Pagamento desfeito com sucesso!</div>
        <?php } ?>
    </div>

    <div class="clearfix">
        <?php
        $naoPagos = [];
        if ($rowsCurr > 0):
            $naoPagos = array_filter($query, function($item) {
                return empty($item->pagamento);
            });
            if (count($naoPagos) > 0):
            ?>
                <a href="javascript:registros.submit();" class="btn btn-success btn-xs">Marcar como pago</a>
            <?php endif; ?>

            <form action="" class="form-inline pull-right">
                <input type="hidden" name="page" value="srm-pagamentos/lista-pagamentos">
                <select name="periodo" id="periodo">
                    <option value="">- todos os meses -</option>
                    <?php
                    $periodos = getFiltrosPeriodos();
                    foreach($periodos as $periodo):
                        $data = \DateTime::createFromFormat('m/Y', implode('/', (array)$periodo));
                        if (!($data instanceof \DateTime)) {
                            continue;
                        }
                        $data = $data->format('m/Y');
                    ?>
                        <option value="<?php echo $data; ?>"
                            <?php echo (!empty($periodoFiltro) && $periodoFiltro == $data) ? ' selected="selected"' : null; ?>>
                            <?php echo $data; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-default btn-xs">Filtrar</button>
            </form>
        <?php endif; ?>
    </div>

    <br>

    <?php if ($rowsCurr > 0): ?>
        <p>
            <?php if ($rowsCurr > 1): ?>
                Existem <strong><?php echo $rowsCurr; ?></strong> pagamentos cadastrados.
            <?php else: ?>
                Existe <strong>1</strong> pagamento encontrado.
            <?php endif; ?>
        </p>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th width="30" class="text-center">
                        <?php if (count($naoPagos) > 0): ?>
                            <input type="checkbox" id="checkAll"/>
                        <?php endif; ?>
                    </th>
                    <th>Nome</th>
                    <th width="15%" class="text-center">E-mail</th>
                    <th width="100" class="text-center">Vencimento</th>
                    <th width="60" class="text-center">Valor</th>
                    <th width="100" class="text-center">Situação</th>
                    <th width="50" class="text-center">Boleto</th>
                    <th width="50" class="text-center"></th>
                </tr>
            </thead>
            <tbody>
                <form action="?page=srm-pagamentos%2Flista-pagamentos" name="registros" id="registros" method="post">
                    <?php
                    $x = 0;

                    foreach ($query as $k => $v) {
                        $vencimento = \DateTime::createFromFormat('Y-m-d', $v->vencimento);
                        $pagamento = \DateTime::createFromFormat('Y-m-d', $v->pagamento);
                    ?>
                        <input type="hidden" id="id_registro_<?php echo $x ?>" value="<?php echo $v->id ?>"/>
                        <tr>
                            <td class="text-center">
                                <?php if(!$pagamento): ?>
                                    <input type="checkbox" name="ids[]" value="<?php echo $v->id; ?>" class="check"/>
                                <?php else: ?>
                                    <i class="glyphicon glyphicon-ok text-success"></i>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $v->display_name; ?></td>
                            <td class="text-center"><?php echo $v->email; ?></td>
                            <td class="text-center"><?php echo $vencimento->format('d/m/Y'); ?></td>
                            <td class="text-center"><?php echo number_format($v->valor, 2, ',', '.'); ?></td>
                            <td class="text-center">
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
                            <td class="text-center">
                                <?php if ($v->arquivo != "") { ?>
                                    <a href="<?php echo content_url('uploads/srm_pagamentos/pagamentos/' . $v->arquivo); ?>"
                                       target="_blank">
                                        Baixar
                                    </a>
                                <?php } else { ?>
                                    -
                                <?php } ?>
                            </td>
                            <td class="text-center">
                                <a href="?page=srm-pagamentos/lista-pagamentos&action=remover&id=<?php echo $v->id; ?>"
                                    class="remover btn btn-danger btn-xs">
                                    Remover
                                </a>
                            </td>
                        </tr>

                        <?php $x++;
                    }

                    if ($quantreg == 0):
                    ?>
                        <tr>
                            <td colspan="7">
                                Nenhuma cobrança encontrada.
                            </td>
                        </tr>
                    <?php endif; ?>
                </form>
            </tbody>
        </table>
    </div>

    <?php include(SRM_PAGAMENTO_PLUGIN_PATH . 'admin/include/paginacao2.php' ); ?>
</div>