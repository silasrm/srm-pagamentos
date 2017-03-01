<?php
global $wpdb, $srm_pagamentos_tbl_name;

srmp_pagamentos_assets();
?>

<div class="container-fluid">
    <h2>Nova Importação</h2>

    <form id="formCadastro" name="formCadastro" method="post" enctype="multipart/form-data">
        <div class="container-fluid">
            <div class="form-group">
                <label class="control-label">Arquivo de importação:</label>

                <div class="controls">
                    <input type="file" name="arquivo" id="arquivo" class="input-medium input-block-level"> <br/>
                    <span id="msgFile">
                        Somente arquivo <strong>.zip</strong>.
                    </span>
                </div>
            </div>
        </div>
        <button type="submit" name="importar" id="importar" class="btn btn-primary">Importar</button>
    </form>
    <?php
    if (isset($_POST['importar'])) {
        include_once(SRM_PAGAMENTO_PLUGIN_PATH . 'admin/include/importaPagamentos.php');
    }
    ?>
</div>
