<?php

require_once dirname(__FILE__) . "/../../vendor/autoload.php";
require_once dirname(__FILE__) . "/ArquivoImportaService.php";

global $wpdb, $srm_pagamentos_tbl_name;

$proto = strtolower(preg_replace('/[^a-zA-Z]/', '', $_SERVER['SERVER_PROTOCOL'])); //pegando só o que for letra
$path = $location = $proto . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "&";

if (!empty($_POST)) {
    $service = new ArquivoImportaService();
    $result = $service->save();

    foreach($result as $arquivo) {
        if ($arquivo['success'] && !$arquivo['updated']) {
            echo sprintf(
                '<p>Arquivo <strong>%s</strong> importado com sucesso para <strong>%s</strong></p>',
                $arquivo['data'][4],
                $arquivo['data'][0]
            );
        } elseif ($arquivo['success'] && $arquivo['updated']) {
            echo sprintf(
                '<p>Arquivo <strong>%s</strong> atualizado com sucesso para <strong>%s</strong></p>',
                $arquivo['data'][4],
                $arquivo['data'][0]
            );
        } else {
            echo sprintf(
                '<p class="text-danger">Erro ao importar <strong>%s</strong> de <strong>%s</strong>: %s</p>',
                $arquivo['data'][4],
                $arquivo['data'][0],
                $arquivo['error']
            );
        }
    }
} else {
    echo "<script>location.href='" . $path . "msg=erro'</script>";
}