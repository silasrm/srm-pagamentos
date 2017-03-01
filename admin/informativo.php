<?php

wp_enqueue_style('wpcva_bootstrap', plugins_url('../css/bootstrap.min.css', __FILE__));

wp_enqueue_script('jquery');	
wp_enqueue_script('wpcva_bootstrapJS', plugins_url('../js/bootstrap.min.js', __FILE__));

?>
<div class="container-fluid">
    <h2>Informações do plugin</h2>					
    <p>
        O <strong>SRM Pagamentos</strong> é um plugin que permite que subir arquivos de pagamento e controlar
        se foi pago ou não pelo administrador, aos usuários.
    </p>
    <p>Qualquer dúvida envie uma mensagem para o email <a href="mailto:silasrm@gmail.com">silasrm@gmail.com</a>.</p>

    <p>
        Para listar as cobranças do usuário logado, crie uma página e use o shortcode '[srm_pagamentos_lista]' e pronto.
    </p>
</div>
