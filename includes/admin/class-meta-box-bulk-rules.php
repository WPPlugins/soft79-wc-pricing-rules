<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class SOFT79_Meta_Box_Bulk_Rules {

    /**
     * Output the metabox
     */
    public static function output( $post ) {
        wp_nonce_field( 'soft79_save_data', 'soft79_meta_nonce' );

        $rule = new SOFT79_Bulk_Rule( $post );

        echo '<div id="j79_pricing_rules_meta" class="panel woocommerce_options_panel">';

        SOFT79_WC_Pricing_Rules_Plugin()->admin->render_admin_bulk_rules( $rule->bulk_rules );
            
        // Quantity scope
        woocommerce_wp_select( array( 
            'id' => '_j79_quantity_scope', 
            'label' => __( 'Quantity scope', 'soft79-wc-pricing-rules' ), 
            'options' => array( 
                    "product" => "Single product (default)",
                    "global" => "Acumulative"
        ) ) );                
                
        //=============================
        // Product ids
        ?>
        <p class="form-field"><label><?php _e( 'Products', 'woocommerce' ); ?></label>
        <input type="hidden" class="wc-product-search" data-multiple="true" style="width: 50%;" name="product_ids" data-placeholder="<?php 
            esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); 
        ?>" data-action="woocommerce_json_search_products_and_variations" data-selected="<?php
            $json_ids    = array();

            foreach ( $rule->product_ids as $product_id ) {
                $product = wc_get_product( $product_id );
                if ( is_object( $product ) ) {
                    $json_ids[ $product_id ] = wp_kses_post( $product->get_formatted_name() );
                }
            }

            echo esc_attr( json_encode( $json_ids ) );
        ?>" value="<?php echo implode( ',', array_keys( $json_ids ) ); ?>" /> <img class="help_tip" data-tip='<?php _e( 'Products which need to be in the cart to use this pricing rule.', 'soft79-wc-pricing-rules' ); ?>' src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" /></p>
        <?php

        //=============================
        // Exclude Product ids
        ?>
        <p class="form-field"><label><?php _e( 'Exclude products', 'woocommerce' ); ?></label>
        <input type="hidden" class="wc-product-search" data-multiple="true" style="width: 50%;" name="exclude_product_ids" data-placeholder="<?php 
            esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); 
        ?>" data-action="woocommerce_json_search_products_and_variations" data-selected="<?php
            $json_ids    = array();

            foreach ( $rule->exclude_product_ids as $product_id ) {
                $product = wc_get_product( $product_id );
                if ( is_object( $product ) ) {
                    $json_ids[ $product_id ] = wp_kses_post( $product->get_formatted_name() );
                }
            }

            echo esc_attr( json_encode( $json_ids ) );
        ?>" value="<?php echo implode( ',', array_keys( $json_ids ) ); ?>" /> <img class="help_tip" data-tip='<?php 
            _e( 'Products which must not be in the cart to use this pricing rule.', 'soft79-wc-pricing-rules' ); 
        ?>' src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" /></p>
        <?php


        //=============================
        // Categories
        ?>
        <p class="form-field"><label for="product_categories"><?php _e( 'Product categories', 'woocommerce' ); ?></label>
        <select id="product_categories" name="product_categories[]" style="width: 50%;"  class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Any category', 'woocommerce' ); ?>">
            <?php
                $categories   = get_terms( 'product_cat', 'orderby=name&hide_empty=0' );

                if ( $categories ) foreach ( $categories as $cat ) {
                    echo '<option value="' . esc_attr( $cat->term_id ) . '"' . selected( in_array( $cat->term_id, $rule->category_ids ), true, false ) . '>' . esc_html( $cat->name ) . '</option>';
                }
            ?>
        </select> <img class="help_tip" data-tip='<?php 
            _e( 'A product must be in this category for the pricing rule to remain valid.', 'soft79-wc-pricing-rules' ); 
        ?>' src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" /></p>
        <?php

        //=============================
        // Exclude Categories
        ?>
        <p class="form-field"><label for="_j79_exclude_product_categories"><?php _e( 'Exclude categories', 'woocommerce' ); ?></label>
        <select id="_j79_exclude_product_categories" name="_j79_exclude_product_categories[]" style="width: 50%;"  class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'No categories', 'woocommerce' ); ?>">
            <?php
                $categories   = get_terms( 'product_cat', 'orderby=name&hide_empty=0' );

                if ( $categories ) foreach ( $categories as $cat ) {
                    echo '<option value="' . esc_attr( $cat->term_id ) . '"' . selected( in_array( $cat->term_id, $rule->exclude_category_ids ), true, false ) . '>' . esc_html( $cat->name ) . '</option>';
                }
            ?>
        </select> <img class="help_tip" data-tip='<?php 
            _e( 'Product must not be in this category for the pricing rule to remain valid.', 'soft79-wc-pricing-rules' ) 
        ?>' src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" /></p>
        <?php

        //=============================
        // User roles
        ?>
        <p class="form-field"><label for="_j79_user_roles"><?php _e( 'Allowed User Roles', 'soft79-wc-pricing-rules' ); ?></label>
        <select id="_j79_user_roles" name="_j79_user_roles[]" style="width: 50%;"  class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php _e( 'Any role', 'soft79-wc-pricing-rules' ); ?>">
            <?php            

                $available_customer_roles = array_reverse( get_editable_roles() );
                foreach ( $available_customer_roles as $role_id => $role ) {
                    $role_name = translate_user_role($role['name'] );
    
                    echo '<option value="' . esc_attr( $role_id ) . '"'
                    . selected( in_array( $role_id, $rule->user_roles ), true, false ) . '>'
                    . esc_html( $role_name ) . '</option>';
                }
            ?>
        </select> <img class="help_tip" data-tip='<?php 
            _e( 'The pricing rule only applies to these User Roles.', 'soft79-wc-pricing-rules' ); 
        ?>' src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" /></p>
        <?php    

        //=============================
        // Excluded user roles
        ?>
        <p class="form-field"><label for="_j79_exclude_user_roles"><?php _e( 'Disallowed User Roles', 'soft79-wc-pricing-rules' ); ?></label>
        <select id="_j79_exclude_user_roles" name="_j79_exclude_user_roles[]" style="width: 50%;"  class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php _e( 'Any role', 'soft79-wc-pricing-rules' ); ?>">
            <?php            

                foreach ( $available_customer_roles as $role_id => $role ) {
                    $role_name = translate_user_role($role['name'] );
    
                    echo '<option value="' . esc_attr( $role_id ) . '"'
                    . selected( in_array( $role_id, $rule->exclude_user_roles ), true, false ) . '>'
                    . esc_html( $role_name ) . '</option>';
                }
            ?>
        </select> <img class="help_tip" data-tip='<?php 
            _e( 'These User Roles will be specifically excluded from this pricing rule.', 'soft79-wc-pricing-rules' ); 
        ?>' src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" /></p>
        <?php
        
        // Quantity scope
        woocommerce_wp_select( array( 
            'id' => '_j79_display_on_prod_page', 
            'label' => __( 'Display on product page', 'soft79-wc-pricing-rules' ), 
            'value' => empty( $rule->display_on_prod_page ) ? "description,table" : $rule->display_on_prod_page, 
            'options' => array( 
                    "description,table" => "Description and table",
                    "description" => "Description",
                    "table" => "Table",
                    "nothing" => "Nothing"
        ) ) );
        
                
        echo '</div>';


    }
    
    private static function html_select( $name, $values, $selected = null, $attribs = array() ) {
        echo "<select name='" . $name . "'";
        foreach($attribs as $key => $desc ) {
            printf (" %s='%s'", $key, $desc );
        }
        echo ">";
        
        foreach ( $values as $key => $desc ) {
            echo '<option value="' . esc_attr( $key ) . '"' . selected( $key == $selected ) . '>' . esc_html( $desc ) . '</option>';
        }
        echo "</select>";
    }
    
    private static function html_help_icon( $text ) {
        $img_url = esc_url( WC()->plugin_url() ) . "/assets/images/help.png";
        echo "<img class='help_tip' data-tip='" . esc_attr( $text ) . "' src='" . $img_url . "' height='16' width='16' />";        
    }    
    


    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        SOFT79_WC_Pricing_Rules_Plugin()->admin->save_admin_bulk_rules( $post_id, $post );
        
        //Extended fields
        $user_roles            = isset( $_POST['_j79_user_roles'] ) ? $_POST['_j79_user_roles'] : '';
        $exclude_user_roles            = isset( $_POST['_j79_exclude_user_roles'] ) ? $_POST['_j79_exclude_user_roles'] : '';
        
        $product_ids            = implode( ',', array_filter( array_map( 'intval', explode( ',', $_POST['product_ids'] ) ) ) );
        $exclude_product_ids    = implode( ',', array_filter( array_map( 'intval', explode( ',', $_POST['exclude_product_ids'] ) ) ) );
        $product_categories         = isset( $_POST['product_categories'] ) ? array_map( 'intval', $_POST['product_categories'] ) : array();
        $exclude_product_categories = isset( $_POST['exclude_product_categories'] ) ? array_map( 'intval', $_POST['exclude_product_categories'] ) : array();
        $exclude_sale_items     = isset( $_POST['exclude_sale_items'] ) ? 'yes' : 'no';
    
    
        update_post_meta( $post_id, '_j79_user_roles', $user_roles );
        update_post_meta( $post_id, '_j79_exclude_user_roles', $exclude_user_roles );
        update_post_meta( $post_id, '_j79_product_ids', $product_ids );
        update_post_meta( $post_id, '_j79_exclude_product_ids', $exclude_product_ids );
        update_post_meta( $post_id, '_j79_product_categories', $product_categories );
        update_post_meta( $post_id, '_j79_exclude_product_categories', $exclude_product_categories );
        update_post_meta( $post_id, '_j79_exclude_sale_items', $exclude_sale_items );
    }
}
