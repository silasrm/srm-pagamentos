<?php

global $wpdb, $srm_pagamentos_tbl_name;

$proto = strtolower(preg_replace('/[^a-zA-Z]/', '', $_SERVER['SERVER_PROTOCOL'])); //pegando só o que for letra
$path = $location = $proto . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "&";

if (!empty($_POST) && isset($_POST['ids']) && !empty($_POST['ids'])) {
    foreach($_POST['ids'] as $pagamentoId) {
        $wpdb->update($srm_pagamentos_tbl_name, ['pagamento' => date('Y-m-d'), 'pago' => 1], ['id' => $pagamentoId]);
    }
    echo "<script>location.href='" . $path . "msg=ok'</script>";
} else {
    echo "<script>location.href='" . $path . "msg=erro'</script>";
}