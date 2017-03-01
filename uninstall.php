<?php

// Vamos garantir que é o WordPress que chama este ficheiro
// e que realmente está a desistalar o plugin.
#if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
 	#die();

// Acesso ao objeto global de gestão de bases de dados
global $wpdb, $srm_pagamentos_tbl_name;

//$sql = "DROP TABLE ".$srm_pagamentos_tbl_name;
//$wpdb->query($sql);
//
//$upload = wp_upload_dir();
//$upload_dir = $upload['basedir'];
//$upload_dir = $upload_dir . '/curriculos';
//
//@rmdir($upload_dir);