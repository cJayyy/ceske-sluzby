<?php
class WC_Product_Tab_Ceske_Sluzby_Admin {

  public function __construct() {
    if ( is_admin() ) {
      add_filter( 'woocommerce_product_data_tabs', array( $this, 'ceske_sluzby_product_tab' ) );
      add_action( 'woocommerce_product_data_panels', array( $this, 'ceske_sluzby_product_tab_obsah' ) );
      add_action( 'woocommerce_process_product_meta', array( $this, 'ceske_sluzby_product_tab_ulozeni' ) );
    }
  }

  public function ceske_sluzby_product_tab( $product_tabs ) {
    $product_tabs['ceske_sluzby'] = array(
      'label' => 'České služby',
      'target' => 'ceske_sluzby_tab_data'
    );
    return $product_tabs;
  }

  public function ceske_sluzby_product_tab_obsah() {
    // Zobrazit aktuální hodnoty v podobě ukázky XML
    // http://www.remicorson.com/mastering-woocommerce-products-custom-fields/
    global $post;
    $xml_feed_heureka = get_option( 'wc_ceske_sluzby_xml_feed_heureka-aktivace' );
    $xml_feed_zbozi = get_option( 'wc_ceske_sluzby_xml_feed_zbozi-aktivace' );
    echo '<div id="ceske_sluzby_tab_data" class="panel woocommerce_options_panel">';
    echo '<div class="options_group">';
    echo '<div class="nadpis" style="margin-left: 12px; margin-top: 10px;"><strong>XML feedy</strong> (<a href="' . admin_url(). 'admin.php?page=wc-settings&tab=ceske-sluzby&section=xml-feed">hromadné nastavení</a>)</div>';

    $vynechane_kategorie = "";
    $product_categories = wp_get_post_terms( $post->ID, 'product_cat' );
    foreach ( $product_categories as $kategorie_produktu ) {
      $vynechano = get_woocommerce_term_meta( $kategorie_produktu->term_id, 'ceske-sluzby-xml-vynechano', true );
      if ( ! empty ( $vynechano ) ) {
        if ( ! empty ( $vynechane_kategorie ) ) {
          $vynechane_kategorie .= ", ";
        }
        $vynechane_kategorie .= '<a href="' . admin_url(). 'edit-tags.php?action=edit&taxonomy=product_cat&tag_ID=' . $kategorie_produktu->term_id . '">' . $kategorie_produktu->name . '</a>';
      }
    }
    if ( ! empty ( $vynechane_kategorie ) ) {
      echo '<p class="form-field"><label for="ceske_sluzby_xml_vynechano">Odebrat z XML</label>Není potřeba nic zadávat, protože jsou zcela ignorovány některé kategorie: ' . $vynechane_kategorie . '</p>';
    } else {
      woocommerce_wp_checkbox( 
        array( 
          'id' => 'ceske_sluzby_xml_vynechano', 
          'wrapper_class' => '', // show_if_simple - pouze u jednoduchých produktů
          'label' => 'Odebrat z XML', 
          'description' => 'Po zaškrtnutí nebude produkt zahrnut do žádného z generovaných XML feedů'
        ) 
      );
    }
    echo '</div>';

    if ( $xml_feed_heureka == "yes" ) {
      echo '<div class="options_group">'; // hide_if_grouped - skrýt u seskupených produktů
      echo '<div class="nadpis" style="margin-left: 12px; margin-top: 10px;"><strong>Heureka</strong> (<a href="http://sluzby.' . HEUREKA_URL . '/napoveda/xml-feed/" target="_blank">obecný manuál</a>)</div>';
      woocommerce_wp_text_input(
        array( 
          'id' => 'ceske_sluzby_xml_heureka_productname', 
          'label' => 'Přesný název (<a href="http://sluzby.' . HEUREKA_URL . '/napoveda/povinne-nazvy/" target="_blank">manuál</a>)', 
          'placeholder' => 'PRODUCTNAME',
          'desc_tip' => 'true',
          'description' => 'Zadejte přesný název produktu, pokud chcete aby byl odlišný od aktuálního názvu.' 
        )
      );
      
      $kategorie_heureka = "";
      foreach ( $product_categories as $kategorie_produktu ) {
        $kategorie = get_woocommerce_term_meta( $kategorie_produktu->term_id, 'ceske-sluzby-xml-heureka-kategorie', true );
        if ( ! empty ( $kategorie ) ) {
          if ( empty ( $kategorie_heureka ) ) {
            $kategorie_heureka = '<a href="' . admin_url(). 'edit-tags.php?action=edit&taxonomy=product_cat&tag_ID=' . $kategorie_produktu->term_id . '">' . $kategorie_produktu->name . '</a>';
            $nazev_kategorie = $kategorie;
          }
        }
      }
      if ( ! empty ( $kategorie_heureka ) ) {
        echo '<p class="form-field"><label for="ceske_sluzby_xml_vynechano">Upozornění (!)</label>Nastavená hodnota na úrovni kategorie ' . $kategorie_heureka . ': <code>' . $nazev_kategorie . '</code></p>';
      }
    
      woocommerce_wp_text_input(
        array( 
          'id' => 'ceske_sluzby_xml_heureka_kategorie', 
          'label' => 'Kategorie (<a href="http://www.' . HEUREKA_URL . '/direct/xml-export/shops/heureka-sekce.xml" target="_blank">přehled</a>)', 
          'placeholder' => 'CATEGORYTEXT',
          'desc_tip' => 'true',
          'description' => 'Příklad: Elektronika | Počítače a kancelář | Software | Multimediální software' 
        )
      );
      echo '</div>';
    }
    
    if ( $xml_feed_zbozi == "yes" ) {
      echo '<div class="options_group">';
      echo '<div class="nadpis" style="margin-left: 12px; margin-top: 10px;"><strong>Zbozi.cz</strong> (<a href="http://napoveda.seznam.cz/cz/zbozi/specifikace-xml-pro-obchody/specifikace-xml-feedu/" target="_blank">obecný manuál</a>)</div>';
      woocommerce_wp_text_input(
        array( 
          'id' => 'ceske_sluzby_xml_zbozi_productname', 
          'label' => 'Přesný název (<a href="http://napoveda.seznam.cz/cz/zbozi/specifikace-xml-pro-obchody/pravidla-pojmenovani-nabidek/" target="_blank">manuál</a>)', 
          'placeholder' => 'PRODUCTNAME',
          'desc_tip' => 'true',
          'description' => 'Zadejte přesný název produktu, pokud chcete aby byl odlišný od aktuálního názvu.' 
        )
      );
      echo '</div>';
    }
    echo '</div>';
  }

  public function ceske_sluzby_product_tab_ulozeni( $post_id ) {
    if ( isset( $_POST['ceske_sluzby_xml_heureka_productname'] ) ) {
      $heureka_productname = $_POST['ceske_sluzby_xml_heureka_productname'];
      if( ! empty( $heureka_productname ) ) {
        update_post_meta( $post_id, 'ceske_sluzby_xml_heureka_productname', esc_attr( $heureka_productname ) );
      }
    }
    
    if ( isset( $_POST['ceske_sluzby_xml_heureka_kategorie'] ) ) {
      $heureka_kategorie = $_POST['ceske_sluzby_xml_heureka_kategorie'];
      if( ! empty( $heureka_kategorie ) ) {
        update_post_meta( $post_id, 'ceske_sluzby_xml_heureka_kategorie', esc_attr( $heureka_kategorie ) );
      }
    }
    
    if ( isset( $_POST['ceske_sluzby_xml_zbozi_productname'] ) ) {
      $zbozi_productname = $_POST['ceske_sluzby_xml_zbozi_productname'];
      if( ! empty( $zbozi_productname ) ) {
        update_post_meta( $post_id, 'ceske_sluzby_xml_zbozi_productname', esc_attr( $zbozi_productname ) );
      }
    }

    $xml_vynechano_ulozeno = get_post_meta( $post_id, 'ceske_sluzby_xml_vynechano', true );
    if ( isset( $_POST['ceske_sluzby_xml_vynechano'] ) ) {
      $xml_vynechano = $_POST['ceske_sluzby_xml_vynechano'];
      if ( ! empty( $xml_vynechano ) ) {
        update_post_meta( $post_id, 'ceske_sluzby_xml_vynechano', $xml_vynechano );  
      }
    } elseif ( ! empty( $xml_vynechano_ulozeno ) ) {
        delete_post_meta( $post_id, 'ceske_sluzby_xml_vynechano' );  
    }
  }
}
// Variace: http://www.remicorson.com/woocommerce-custom-fields-for-variations/