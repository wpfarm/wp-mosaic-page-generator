<?php 
use Elementor\Core\DynamicTags\Tag;
use Elementor\Controls_Manager;
use Elementor\Repeater;
if ( ! defined( 'ABSPATH' ) ) exit; 

function register_dynamic_meta_tags_mosaic( $dynamic_tags ) {
    global $wpdb;	
    $custom_fields = getAllCustomFieldsMosaic();
    foreach ( $custom_fields as $field  ) {   
        $meta_key = $field->cpf_field_name;
        $title = $field->cpf_field_label;
        $tag_class_name = 'Dynamic_Meta_Tag_' . sanitize_key( $meta_key );
        if ( ! class_exists( $tag_class_name ) ) {
            // Dynamically create a unique class for each meta field
            eval( "
                class $tag_class_name extends \Elementor\Core\DynamicTags\Tag {
                    protected \$meta_key = '$meta_key';
                    protected \$title = '$title';

                    public function get_name() {
                        return 'mosaic_dynamic_meta_' . sanitize_key( \$this->meta_key );
                    }

                     public function get_group() {
                        return 'mosaic_custom_group';
                    }

                    public function get_title() {
                        return \$this->title;
                    }

                    public function get_categories() {
                        return [ \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY ];
                    }

                    protected function render() {
                        \$post_id = get_the_ID();
                        \$value = get_post_meta( \$post_id, \$this->meta_key, true );

                        if ( ! empty( \$value ) ) {
                            echo  \$value;
                        } else {
                            //echo __( 'No meta value found', 'wp-mosaic-page-generator' );
                        }
                    }
                }
            " );
        }
        // Register the dynamically created tag class
        $dynamic_tags->register_tag( $tag_class_name );
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