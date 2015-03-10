<?php
/*
Plugin Name: Testimonials by Mightybytes
Description: Testimonials by Mightybytes
Author: Davo Hynds
Author URI: http://mightybytes.com
Version: 0.1
Text Domain: mb-testimonials
*/

class MB_Testimonials {

	protected $ns = 'mb-testimonials';

	function __construct() {
		add_action( 'init', array($this,'testimonials_init') );
		add_action( 'add_meta_boxes', array($this,'attribution') );
		add_action( 'save_post', array($this,'attribution_save_meta_box_data') );
	}

	function testimonials_init() {
		register_post_type(
			'testimonial',
			array(
				'labels' => array(
					'name' => __( 'Testimonials', $this->ns ),
					'singular_name' => __( 'Testimonial', $this->ns )
				),
				'public' => true,
				'rewrite' => false,
				'searchable' => false,
				'supports' => array('title','editor','thumbnail','excerpt','revisions'),
				'menu_icon' => 'dashicons-format-quote',
			)
		);

		register_taxonomy(
			'testimonials-category',
			array('testimonial'),
			array(
				'labels' => array(
					'name' => __( 'Categories' , 'mb-testimonials'),
					'singular' => __( 'Category' , $this->ns ),
				),
				'hierarchical' => true,
			)
		);
		register_taxonomy(
			'testimonials-tag',
			array('testimonial'),
			array(
				'labels' => array(
					'name' => __( 'Tags' , $this->ns ),
					'singular' => __( 'Tag' , $this->ns ),
				),
				'hierarchical' => false,
			)
		);

	}

	function attribution() {	
		add_meta_box( 'testimonial-attribution', 'Attribution', array($this,'attribution_callback'), 'testimonial', 'normal', 'high' );
	}

	function attribution_callback($post) {
		wp_nonce_field( 'attribution_meta_box', 'attribution_meta_box_nonce' );
		$value = get_post_meta( $post->ID, 'testimonial_attribution', true );

		echo '<label class="screen-reader-text" for="testimonial_attribution">'.__('Attribution','mb-testimonials').'</label>';
		echo '<input type="text" id="attribution" name="testimonial_attribution" value="' . esc_attr( $value ) . '" size="40" />';
	}

	function attribution_save_meta_box_data( $post_id ) {

		/*
		 * We need to verify this came from our screen and with proper authorization,
		 * because the save_post action can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['attribution_meta_box_nonce'] ) ) {
			return;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['attribution_meta_box_nonce'], 'attribution_meta_box' ) ) {
			return;
		}

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check the user's permissions.
		if ( isset( $_POST['post_type'] ) && 'testimonial' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return;
			}

		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}
		}

		/* OK, it's safe for us to save the data now. */

		// Make sure that it is set.
		if ( ! isset( $_POST['testimonial_attribution'] ) ) {
			return;
		}

		// Sanitize user input.
		$my_data = sanitize_text_field( $_POST['testimonial_attribution'] );

		// Update the meta field in the database.
		update_post_meta( $post_id, 'testimonial_attribution', $my_data );
	}

}

$testimonials = new MB_Testimonials;



