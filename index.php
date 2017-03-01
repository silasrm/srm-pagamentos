<?php
/**
 * Plugin Name: SRM Pagamentos
 * Plugin URI: http://www.silasribas.com.br
 * Description: Gerenciamento de cobranças por boleto, anexado, aos usuários do sistema.
 * Version: 1.0
 * Author: Silas Ribas
 * Author URI: http://www.silasribas.com.br
 * License: MIT License
 *
 * The MIT License (MIT)
 *
 * Copyright (c) 2016 Silas Ribas
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

defined('ABSPATH') or die('No script kiddies please!');

// @see https://developer.wordpress.org/reference/functions/plugin_dir_path/
define('SRM_PAGAMENTO_PLUGIN_PATH', plugin_dir_path(__FILE__));
// @see https://developer.wordpress.org/reference/functions/plugins_url/
define('SRM_PAGAMENTO_PLUGIN_URL', plugins_url('', __FILE__));

$upload = wp_upload_dir();
define('SRM_PAGAMENTO_UPLOAD_DIR', $upload['basedir'] . '/srm_pagamentos/');

$srm_pagamentos_tbl_name = $wpdb->prefix . 'srm_pagamentos';

// Registramos a função para correr na ativação do plugin
register_activation_hook(__FILE__, 'srmp_install');

// Cria o banco de dados e a pasta onde vai ser salvo os arquivos
add_action('init', 'srmp_create_table');

#shortcode para listar os pagamentos do usuário
add_shortcode('srm_pagamentos_lista', 'srmp_pagamentos_lista_pagamentos_usuario');

#Cria um painel do plugin no administrativo do wordpress
add_action('admin_menu', 'srmp_configuracoes');

register_deactivation_hook(__FILE__, 'srmp_unistall');

function srmp_install()
{
    define('DISALLOW_FILE_EDIT', true);
    // Vamos testar a versão do PHP e do WordPress
    // caso as versões sejam antigas, desativamos
    // o nosso plugin.
    if (version_compare(PHP_VERSION, '5.4.0', '<')
        || version_compare(get_bloginfo('version'), '4.6', '<')
    ) {
        deactivate_plugins(basename(__FILE__));
    }
}

#Função que faz instalação do banco e cria a pasta
function srmp_create_table()
{
    include_once(SRM_PAGAMENTO_PLUGIN_PATH . 'install.php');
}

function srmp_configuracoes()
{
    #Cria um menu dentro do menu options
    add_menu_page('SRM Pagamentos', 'SRM Pagamentos', 'manage_options', 'srm-pagamentos', 'srm_pagamentos', '');

    add_submenu_page('srm-pagamentos', 'Informações', 'Informações', 'manage_options', 'srm-pagamentos', 'srm_pagamentos', '');

    #Submenu para fazer um novo cadastro
    add_submenu_page('srm-pagamentos', 'Nova Importação', 'Nova Importação', 'manage_options', 'srm-pagamentos/importacao', 'srmp_formulario_admin');

    #Submenu que exibe a lista de pagamentos cadastrados
    add_submenu_page('srm-pagamentos', 'Lista de pagamentos', 'Lista de pagamentos', 'manage_options', 'srm-pagamentos/lista-pagamentos', 'srmp_lista_pagamentos_admin');
}

function srm_pagamentos()
{
    include_once(SRM_PAGAMENTO_PLUGIN_PATH . 'admin/informativo.php');
}

function srmp_formulario_admin()
{
    include_once(SRM_PAGAMENTO_PLUGIN_PATH . 'admin/formulario.php');
}

function srmp_lista_pagamentos_admin()
{
    include_once(SRM_PAGAMENTO_PLUGIN_PATH . 'admin/lista_pagamentos.php');
}

function srmp_unistall()
{
    include_once(SRM_PAGAMENTO_PLUGIN_PATH . 'uninstall.php');
}

function srmp_pagamentos_lista_pagamentos_usuario()
{
    ob_start();

    include_once(SRM_PAGAMENTO_PLUGIN_PATH . 'lista_pagamentos.php');

    $lista = ob_get_contents();

    ob_end_clean();

    return $lista;
}


function srmp_pagamentos_assets()
{
    wp_enqueue_style('wpcva_bootstrap', SRM_PAGAMENTO_PLUGIN_URL . '/css/bootstrap.min.css');

    wp_enqueue_script('jquery');
    wp_enqueue_script('wpcva_bootstrapJS', SRM_PAGAMENTO_PLUGIN_URL . '/js/bootstrap.min.js');
    wp_enqueue_script('wpcva_script', SRM_PAGAMENTO_PLUGIN_URL . '/js/script.js');
}