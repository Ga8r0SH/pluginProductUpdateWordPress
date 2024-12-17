<?php
/**
 * Plugin Name: Product Update
 * Description: Массовое обновление пользовательского поля для продуктов WooCommerce.
 * Version: 1.5.2
 * Author: Stepan Gavriusenco
 * Text Domain: wc-bulk-product-update
 */

// Защита от прямого доступа
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

// Подключаем файлы
require_once plugin_dir_path( __FILE__ ) . 'includes/class-your-plugin.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-admin-interface.php';

// Инициализация плагина
function wc_bulk_product_update_init() {
    $plugin = new Your_Plugin();
    $plugin->init();
}

add_action( 'plugins_loaded', 'wc_bulk_product_update_init' );