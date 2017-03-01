<?php
#define('DISALLOW_FILE_EDIT', true );
// Acesso ao objeto global de gestão de bases de dados
global $wpdb, $srm_pagamentos_tbl_name;

// Vamos checar se a nova tabela existe
// A propriedade prefix é o prefixo de tabela escolhido na
// instalação do WordPress

// Se a tabela não existe vamos criá-la
if ($wpdb->get_var("SHOW TABLES LIKE '" . $srm_pagamentos_tbl_name . "'") != $srm_pagamentos_tbl_name) {
    $sql = "
			CREATE TABLE " . $srm_pagamentos_tbl_name . "(
				  id 			int(11) 		NOT NULL AUTO_INCREMENT,
				  user_id 		int(11) 		DEFAULT NULL,
				  email 		varchar(255) 	COLLATE latin1_bin NOT NULL,
				  vencimento    date NOT NULL,
				  valor         double(10,2) NOT NULL DEFAULT '0.0',
				  arquivo		varchar(255) 	COLLATE latin1_bin NOT NULL,
				  pagamento     date DEFAULT NULL,
				  pago   		int(11) 		DEFAULT 0,
				  PRIMARY KEY (id)
			);";

    // Para usarmos a função dbDelta() é necessário carregar este ficheiro
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    // Esta função cria a tabela na base de dados e executa as otimizações
    // necessárias.
    dbDelta($sql);
}

if (!is_dir(SRM_PAGAMENTO_UPLOAD_DIR)) {
    mkdir(SRM_PAGAMENTO_UPLOAD_DIR, 0777);
    mkdir(SRM_PAGAMENTO_UPLOAD_DIR . 'descompactadas', 0777);
    mkdir(SRM_PAGAMENTO_UPLOAD_DIR . 'pagamentos', 0777);
}