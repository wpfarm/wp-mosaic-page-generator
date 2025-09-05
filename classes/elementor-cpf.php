<?php 
use Elementor\Core\DynamicTags\Tag;
use Elementor\Controls_Manager;
use Elementor\Repeater;
if ( ! defined( 'ABSPATH' ) ) exit; 

/**
 * Base class for Mosaic dynamic meta tags
 * Replaces dangerous eval() with secure class factory pattern
 */
class Mosaic_Dynamic_Meta_Tag extends Elementor\Core\DynamicTags\Tag {
    protected $meta_key;
    protected $field_title;

    public function __construct( $meta_key, $title ) {
        $this->meta_key = sanitize_key( $meta_key );
        $this->field_title = sanitize_text_field( $title );
        parent::__construct();
    }

    public function get_name() {
        return 'mosaic_dynamic_meta_' . $this->meta_key;
    }

    public function get_group() {
        return 'mosaic_custom_group';
    }

    public function get_title() {
        return $this->field_title;
    }

    public function get_categories() {
        return [ \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY ];
    }

    protected function render() {
        $post_id = get_the_ID();
        if ( ! $post_id ) {
            return;
        }
        
        $value = get_post_meta( $post_id, $this->meta_key, true );
        
        if ( ! empty( $value ) ) {
            echo wp_kses_post( $value );
        }
    }
}

function register_dynamic_meta_tags_mosaic( $dynamic_tags ) {
    wpmpgCommon::security_log("Registering Elementor dynamic tags", [
        'action' => 'elementor_tag_registration',
        'user_id' => get_current_user_id()
    ]);
    
    $custom_fields = getAllCustomFieldsMosaic();
    
    if ( ! is_array( $custom_fields ) ) {
        wpmpgCommon::debug_log("No custom fields found for Elementor registration", null, 'WARNING');
        return;
    }
    
    foreach ( $custom_fields as $field ) {   
        if ( ! isset( $field->cpf_field_name ) || ! isset( $field->cpf_field_label ) ) {
            wpmpgCommon::debug_log("Invalid field data in Elementor registration", $field, 'WARNING');
            continue;
        }
        
        $meta_key = sanitize_key( $field->cpf_field_name );
        $title = sanitize_text_field( $field->cpf_field_label );
        
        if ( empty( $meta_key ) || empty( $title ) ) {
            continue;
        }
        
        // Create secure tag instance instead of using eval()
        $tag_instance = new Mosaic_Dynamic_Meta_Tag( $meta_key, $title );
        $dynamic_tags->register_tag( $tag_instance );
        
        wpmpgCommon::debug_log("Registered Elementor dynamic tag", [
            'meta_key' => $meta_key,
            'title' => $title
        ]);
    }
}

add_action( 'elementor/dynamic_tags/register_tags', 'register_dynamic_meta_tags_mosaic' );
function mosaic_register_new_dynamic_tag_group( $dynamic_tags_manager ) {
	$dynamic_tags_manager->register_group(
		'mosaic_custom_group',
		[
			'title' => esc_html__( 'Mosaic Fields Group', 'wp-mosaic-page-generator' )
		]
	);
}
add_action( 'elementor/dynamic_tags/register', 'mosaic_register_new_dynamic_tag_group' );

function getAllCustomFieldsMosaic()   {
    global $wpdb;			
    $html = '';
    $sql =  'SELECT cpf.*, cpt.*  FROM ' . $wpdb->prefix . 'cpt_fields cpf  ' ;				
    $sql .= " RIGHT JOIN ". $wpdb->prefix."cpt  cpt  ON (cpt.cpt_id = cpf.cpf_cpt_id )";		
    $sql .= " WHERE cpt.cpt_id = cpf.cpf_cpt_id    ";	
    $sql .= " ORDER BY  cpf.cpf_field_sorting ";	
    $sql = $wpdb->prepare($sql);
    $cptRows = $wpdb->get_results($sql );
    return $cptRows ;
}