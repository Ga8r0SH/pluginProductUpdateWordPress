<?php
class Your_Plugin {
    public function init() {
        // Регистрируем хук для активации
        register_activation_hook( __FILE__, array( $this, 'activate' ) );

        // Регистрируем хук для деактивации
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        // Регистрируем действия
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'wp_ajax_wc_bulk_update_products', array( $this, 'bulk_update_products' ) );

        // Проверка на каждом запросе в админке, активен ли WooCommerce
        add_action( 'admin_init', array( $this, 'check_woocommerce_status' ) );
    }

    public function activate() {
        // Проверка на активацию WooCommerce
        if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            wp_die( 'Для работы плагина требуется активированный WooCommerce.' );
        }
    }

    public function deactivate() {
        // Код для деактивации плагина
    }

    // Проверяем статус WooCommerce
    public function check_woocommerce_status() {
        if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            // Если WooCommerce не активен, деактивируем наш плагин
            deactivate_plugins( plugin_basename( __FILE__ ) );
            add_action( 'admin_notices', array( $this, 'woocommerce_inactive_notice' ) );
        }
    }

    // Уведомление, если WooCommerce не активен
    public function woocommerce_inactive_notice() {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p><?php _e( 'WooCommerce не активен! Плагин не может работать без активного WooCommerce.', 'your-plugin-textdomain' ); ?></p>
        </div>
        <?php
    }

    public function add_admin_menu() {
        add_menu_page(
            'Bulk Product Update', // Заголовок страницы
            'Bulk Product Update', // Название в меню
            'manage_options', // Права доступа
            'bulk-product-update', // Уникальный идентификатор меню
            array( $this, 'render_admin_page' ) // Функция отображения страницы
        );
    }

    public function render_admin_page() {
        require_once plugin_dir_path( __FILE__ ) . 'class-admin-interface.php';
        $admin_interface = new Admin_Interface();
        $admin_interface->render();
    }

    public function bulk_update_products() {
        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
        if ( ! wp_verify_nonce( $nonce, 'wc_bulk_update_nonce' ) ) {
            wp_send_json_error( array( 'message' => 'Nonce verification failed' ) );
        }

        $product_ids = isset( $_POST['product_ids'] ) ? array_map( 'intval', $_POST['product_ids'] ) : array();
        $prices = isset( $_POST['prices'] ) ? array_map( 'floatval', $_POST['prices'] ) : array();
        $categories = isset( $_POST['categories'] ) ? array_map( 'intval', $_POST['categories'] ) : array();
        $discounts = isset( $_POST['discounts'] ) ? $_POST['discounts'] : array();

        if ( empty( $product_ids ) ) {
            wp_send_json_error( array( 'message' => 'Invalid input' ) );
        }

        foreach ( $product_ids as $product_id ) {
            $product = wc_get_product( $product_id );
            if ( ! $product ) {
                continue;
            }

            $regular_price = $product->get_regular_price();

            if ( isset( $discounts[$product_id] ) && $discounts[$product_id] !== '' ) {
                $sale_price = floatval( $discounts[$product_id] );
                $product->set_sale_price( $sale_price );
                $product->set_price( $sale_price );
            } else {
                $product->set_sale_price( '' );
                $product->set_price( $regular_price );
            }

            if ( isset( $categories[$product_id] ) && ! empty( $categories[$product_id] ) ) {
                $category_id = intval( $categories[$product_id] );
                wp_set_object_terms( $product_id, $category_id, 'product_cat' );
            }

            $product->save();
        }

        wp_send_json_success( array( 'message' => 'Products updated successfully.' ) );
    }
}



