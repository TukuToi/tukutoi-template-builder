<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.tukutoi/
 * @since      1.1.0
 *
 * @package    Tkt_Template_Builder
 * @subpackage Tkt_Template_Builder/admin/partials
 */

?>

<h4>Layout usage</h4>
<select class="tkt_template_select" multiple="multiple" name="tkt_template_assigned_to[]">
	<?php
	printf( '<option value="">%s</option>', esc_html__( 'Unassigned Layout', 'tkt-template-builder' ) );
	foreach ( $templates as $name => $label ) {
		$selected = isset( $options[ $name ] ) && $options[ $name ] === $post->ID ? 'selected' : '';
		printf( '<option value="%s" ' . esc_attr( $selected ) . '>%s</option>', sanitize_key( $name ), esc_html( $label ) );
	}
	?>
</select>
<hr>
<h4>Header</h4>
<select class="tkt_template_select" name="tkt_template_header">
	<?php
	$theme = isset( $settings['header'] ) && 'theme_header' === $settings['header'] ? 'selected' : '';
	$none  = isset( $settings['header'] ) && 'no_header' === $settings['header'] ? 'selected' : '';
	printf( '<option value="theme_header" ' . esc_attr( $theme ) . '>%s</option>', esc_html__( 'Use Theme Header', 'tkt-template-builder' ) );
	printf( '<option value="no_header" ' . esc_attr( $none ) . '>%s</option>', esc_html__( 'Use No Header', 'tkt-template-builder' ) );
	foreach ( $layouts as $key => $layout ) {
		$selected = isset( $settings['header'] ) && absint( $settings['header'] ) === $layout->ID ? 'selected' : '';
		printf( '<option value="%s" ' . esc_attr( $selected ) . '>%s</option>', absint( $layout->ID ), esc_html( $layout->post_title ) );
	}
	?>
</select>
<h4>Footer</h4>
<select class="tkt_template_select" name="tkt_template_footer">
	<?php
	$theme = isset( $settings['footer'] ) && 'theme_footer' === $settings['footer'] ? 'selected' : '';
	$none  = isset( $settings['footer'] ) && 'no_footer' === $settings['footer'] ? 'selected' : '';
	printf( '<option value="theme_footer" ' . esc_attr( $theme ) . '>%s</option>', esc_html__( 'Use Theme Footer', 'tkt-template-builder' ) );
	printf( '<option value="no_footer" ' . esc_attr( $none ) . '>%s</option>', esc_html__( 'Use No Footer', 'tkt-template-builder' ) );
	foreach ( $layouts as $key => $layout ) {
		$selected = isset( $settings['footer'] ) && absint( $settings['footer'] ) === $layout->ID ? 'selected' : '';
		printf( '<option value="%s" ' . esc_attr( $selected ) . '>%s</option>', absint( $layout->ID ), esc_html( $layout->post_title ) );
	}
	?>
</select>
<?php
wp_nonce_field( 'save_tkt_template_settings', 'tkt_template_settings_nonce' );
