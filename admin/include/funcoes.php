<?php

function getFiltrosPeriodos()
{
    global $wpdb, $srm_pagamentos_tbl_name;

    return $wpdb->get_results('SELECT MONTH(vencimento) as mes, YEAR(vencimento) as ano FROM ' . $srm_pagamentos_tbl_name . ' GROUP BY MONTH(vencimento), YEAR(vencimento)');
}