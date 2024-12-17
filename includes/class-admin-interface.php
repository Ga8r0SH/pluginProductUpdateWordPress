<?php
class Admin_Interface {
    public function render() {
        ?>
        <div class="wrap">
            <h1>Bulk Product Update</h1>
            <form id="bulk-update-form">
                <!-- Фильтрация по категории -->
                <label for="filter_category">Filter by Category:</label>
                <select id="filter_category" name="filter_category">
                    <option value="">Select Category</option>
                    <?php
                    $all_categories = get_terms( array( 'taxonomy' => 'product_cat', 'orderby' => 'name', 'hide_empty' => false ) );
                    foreach ( $all_categories as $category ) {
                        echo '<option value="' . $category->term_id . '">' . $category->name . '</option>';
                    }
                    ?>
                </select>
    
                <!-- Фильтрация по цене -->
                <label for="filter_price_min">Price Min:</label>
                <input type="number" id="filter_price_min" name="filter_price_min" step="0.01" placeholder="Min Price">
    
                <label for="filter_price_max">Price Max:</label>
                <input type="number" id="filter_price_max" name="filter_price_max" step="0.01" placeholder="Max Price">
    
                <!-- Фильтрация по названию товара -->
                <label for="filter_name">Product Name:</label>
                <input type="text" id="filter_name" name="filter_name" placeholder="Product Name">
    
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Select</th>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Category</th>
                            <th>Discount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $products = wc_get_products( array( 'limit' => -1 ) );
                        foreach ( $products as $product ) {
                            echo '<tr>';
                            echo '<td><input type="checkbox" name="product_ids[]" value="' . $product->get_id() . '"></td>';
                            echo '<td>' . $product->get_name() . '</td>';
                            echo '<td><input type="number" name="prices[' . $product->get_id() . ']" value="' . $product->get_price() . '" step="0.01"></td>';
    
                            // Выбираем категории
                            $categories = wp_get_post_terms( $product->get_id(), 'product_cat' );
                            echo '<td>';
                            echo '<select name="categories[' . $product->get_id() . ']">';
                            echo '<option value="">Select Category</option>';
                            $all_categories = get_terms( array( 'taxonomy' => 'product_cat', 'orderby' => 'name', 'hide_empty' => false ) );
                            foreach ( $all_categories as $category ) {
                                $selected = in_array( $category->term_id, wp_list_pluck( $categories, 'term_id' ) ) ? 'selected' : '';
                                echo '<option value="' . $category->term_id . '" ' . $selected . '>' . $category->name . '</option>';
                            }
                            echo '</select>';
                            echo '</td>';
    
                            // Поле для скидки
                            $sale_price = $product->get_sale_price();
                            echo '<td><input type="number" name="discounts[' . $product->get_id() . ']" value="' . $sale_price . '" step="0.01"></td>';
    
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
    
                <?php wp_nonce_field( 'wc_bulk_update_nonce', 'nonce' ); ?>
    
                <button type="button" id="update-selected" class="button button-primary">Update Selected</button>
                <button type="button" id="update-all" class="button button-primary">Update All Products</button>
            </form>
    
            <script>
                jQuery(document).ready(function($) {
                    // Обновление всех товаров
                    $('#update-all').on('click', function() {
                        var allProducts = [];
                        $('input[name="product_ids[]"]').each(function() {
                            allProducts.push($(this).val());
                        });
    
                        if (allProducts.length === 0) {
                            alert('No products available to update.');
                            return;
                        }
    
                        var prices = {};
                        $('input[name^="prices"]').each(function() {
                            prices[$(this).attr('name').match(/\[(.*?)\]/)[1]] = $(this).val();
                        });
    
                        var categories = {};
                        $('select[name^="categories"]').each(function() {
                            categories[$(this).attr('name').match(/\[(.*?)\]/)[1]] = $(this).val();
                        });
    
                        var discounts = {};
                        $('input[name^="discounts"]').each(function() {
                            discounts[$(this).attr('name').match(/\[(.*?)\]/)[1]] = $(this).val();
                        });
    
                        var nonce = $('input[name="nonce"]').val();
    
                        $.post(ajaxurl, {
                            action: 'wc_bulk_update_products',
                            nonce: nonce,
                            product_ids: allProducts,
                            prices: prices,
                            categories: categories,
                            discounts: discounts
                        }, function(response) {
                            if (response.success) {
                                alert(response.data.message);
                            } else {
                                alert(response.data.message);
                            }
                        });
                    });
    
                    // Обновление выбранных товаров
                    $('#update-selected').on('click', function() {
                        var selectedProducts = [];
                        $('input[name="product_ids[]"]:checked').each(function() {
                            selectedProducts.push($(this).val());
                        });
    
                        if (selectedProducts.length === 0) {
                            alert('Please select products.');
                            return;
                        }
    
                        var prices = {};
                        $('input[name^="prices"]').each(function() {
                            prices[$(this).attr('name').match(/\[(.*?)\]/)[1]] = $(this).val();
                        });
    
                        var categories = {};
                        $('select[name^="categories"]').each(function() {
                            categories[$(this).attr('name').match(/\[(.*?)\]/)[1]] = $(this).val();
                        });
    
                        var discounts = {};
                        $('input[name^="discounts"]').each(function() {
                            discounts[$(this).attr('name').match(/\[(.*?)\]/)[1]] = $(this).val();
                        });
    
                        var nonce = $('input[name="nonce"]').val();
    
                        $.post(ajaxurl, {
                            action: 'wc_bulk_update_products',
                            nonce: nonce,
                            product_ids: selectedProducts,
                            prices: prices,
                            categories: categories,
                            discounts: discounts
                        }, function(response) {
                            if (response.success) {
                                alert(response.data.message);
                            } else {
                                alert(response.data.message);
                            }
                        });
                    });
    
                    // Фильтрация по категории, цене и названию
                    $('select[name="filter_category"], input[name="filter_price_min"], input[name="filter_price_max"], input[name="filter_name"]').on('change', function() {
                        var selectedCategory = $('#filter_category').val();
                        var minPrice = parseFloat($('#filter_price_min').val()) || 0;
                        var maxPrice = parseFloat($('#filter_price_max').val()) || Infinity;
                        var productName = $('#filter_name').val().toLowerCase();
                        var rows = $('tbody tr');
    
                        rows.each(function() {
                            var rowCategory = $(this).find('select[name^="categories"]').val();
                            var rowPrice = parseFloat($(this).find('input[name^="prices"]').val()) || 0;
                            var rowName = $(this).find('td:eq(1)').text().toLowerCase();
    
                            var showRow = true;
                            if (selectedCategory && rowCategory != selectedCategory) {
                                showRow = false;
                            }
                            if (rowPrice < minPrice || rowPrice > maxPrice) {
                                showRow = false;
                            }
                            if (productName && !rowName.includes(productName)) {
                                showRow = false;
                            }
    
                            if (showRow) {
                                $(this).show();
                            } else {
                                $(this).hide();
                            }
                        });
                    });
                });
            </script>
        </div>
        <?php
    }

}