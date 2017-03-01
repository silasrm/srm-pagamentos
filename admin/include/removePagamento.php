<?php

global $wpdb, $srm_pagamentos_tbl_name;

$proto = strtolower(preg_replace('/[^a-zA-Z]/', '', $_SERVER['SERVER_PROTOCOL'])); //pegando só o que for letra
$path = $location = $proto . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "&";

$wpdb->delete($srm_pagamentos_tbl_name, ['id' => strip_tags(addslashes($_GET['id']))]);

echo "<script>location.href='?page=srm-pagamentos/lista-pagamentos'</script>";