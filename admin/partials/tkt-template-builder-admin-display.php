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

<?php
$disabled = '';
$i = 0;

if ( ( isset( $template_options['global_footer'] )
		&& $post->ID === $template_options['global_footer']
	) || (
		isset( $template_options['global_header'] )
		&& $post->ID === $template_options['global_header']
		)
	) {
	$disabled = 'disabled';
}
?>
<fieldset form="post" class="tkt-fieldset">
	<legend class="tkt-legend"><?php esc_html_e( 'Template Usage', 'tkt-template-builder' ); ?></legend>
	<div class="tkt_fieldset_section">
		<label for="tkt_template_assigned_to"><?php esc_html_e( 'Use this Template for:', 'tkt-template-builder' ); ?></label>
		<select class="tkt_template_select_multi" id="tkt_template_assigned_to" multiple="multiple" name="tkt_template_assigned_to[]">
			<?php
			foreach ( $templates as $name => $label ) {
				if ( $name === $i ) {
					echo '<optgroup label="' . esc_attr( $templates[ $i ] ) . '">';
					$i++;
				} elseif ( 'optgroupstart' !== $name && 'optgroupend' !== $name ) {
					$selected = isset( $template_options[ $name ] ) && $template_options[ $name ] === $post->ID ? 'selected' : '';
					printf( '<option value="%s" ' . esc_attr( $selected ) . '>%s</option>', sanitize_key( $name ), esc_html( $label ) );
				} elseif ( 'optgroupend' === $name ) {
					echo '</optgroup>';
				}
			}
			?>
		</select>
	</div>
	<div class="tkt_fieldset_section">
		<label for="tkt_content_template_assigned_to"><?php esc_html_e( 'Replace the Post Body of:', 'tkt-template-builder' ); ?></label>
		<select class="tkt_template_select_multi" id="tkt_content_template_assigned_to" multiple="multiple" name="tkt_content_template_assigned_to[]">
			<?php
			foreach ( $post_types as $key => $object ) {
				$selected = isset( $ct_options[ $object->name ] ) && $ct_options[ $object->name ] === $post->ID ? 'selected' : '';
				printf( '<option value="%s" ' . esc_attr( $selected ) . '>%s</option>', sanitize_key( $object->name ), esc_html( $object->label ) );
			}
			?>
		</select>
	</div>
</fieldset>
<fieldset form="post" class="tkt-fieldset">
	<legend class="tkt-legend"><?php esc_html_e( 'Template Hierarchy', 'tkt-template-builder' ); ?></legend>
	<div class="tkt_fieldset_section">
		<label for="tkt_template_parent"><?php esc_html_e( 'Use this Parent Template:', 'tkt-template-builder' ); ?></label>
		<select class="tkt_template_select" name="tkt_template_parent" id="tkt_template_parent">
			<?php
			printf( '<option value="no_parent">%s</option>', esc_html__( 'No Parent Template', 'tkt-template-builder' ) );
			foreach ( $tkt_templates as $key => $tkt_template ) {
				$selected = isset( $settings['parent'] ) && absint( $settings['parent'] ) === $tkt_template->ID ? 'selected' : '';
				printf( '<option value="%s" ' . esc_attr( $selected ) . '>%s</option>', absint( $tkt_template->ID ), esc_html( $tkt_template->post_title ) );
			}
			?>
		</select>
	</div>
	<div class="tkt_fieldset_section">
		<label for="tkt_template_header"><?php esc_html_e( 'Use this Header:', 'tkt-template-builder' ); ?></label>
		<select class="tkt_template_select" name="tkt_template_header" id="tkt_template_header" <?php echo esc_attr( $disabled ); ?>>
			<?php
			// If Theme header is set for this template.
			$theme_header = isset( $settings['header'] ) && 'theme_header' === $settings['header'] ? 'selected' : '';
			// If No Header is set for this template.
			$no_header  = ( isset( $settings['header'] ) && 'no_header' === $settings['header'] ) || ( isset( $template_options['global_header'] ) && $template_options['global_header'] === $post->ID ) || ( isset( $template_options['global_footer'] ) && $template_options['global_footer'] === $post->ID ) ? 'selected' : '';
			// If Global Header or no header setting at all is set.
			$global_header  = isset( $template_options['global_header'] ) && $template_options['global_header'] === $settings['header'] && $template_options['global_header'] !== $post->ID || ( isset( $template_options['global_header'] ) && empty( $theme_header ) && empty( $no_header ) ) ? 'selected' : '';
			// Print the options.
			// Revise this.
			$global_head = isset( $template_options['global_header'] ) ? $template_options['global_header'] : '';
			printf( '<option value="theme_header" ' . esc_attr( $theme_header ) . '>%s</option>', esc_html__( 'Theme Header', 'tkt-template-builder' ) );
			printf( '<option value="no_header" ' . esc_attr( $no_header ) . '>%s</option>', esc_html__( 'No Header', 'tkt-template-builder' ) );
			printf( '<option value="%s" ' . esc_attr( $global_header ) . '>%s</option>', esc_attr( $global_head ), esc_html__( 'Global Header', 'tkt-template-builder' ) );
			?>
		</select>
	</div>
	<div class="tkt_fieldset_section">
		<label for="tkt_template_footer"><?php esc_html_e( 'Use this Footer:', 'tkt-template-builder' ); ?></label>
		<select class="tkt_template_select" name="tkt_template_footer" id="tkt_template_footer" <?php echo esc_attr( $disabled ); ?>>
			<?php
			$theme_footer = isset( $settings['footer'] ) && 'theme_footer' === $settings['footer'] ? 'selected' : '';
			$no_footer  = ( isset( $settings['footer'] ) && 'no_footer' === $settings['footer'] ) || ( isset( $template_options['global_header'] ) && $template_options['global_header'] === $post->ID ) || ( isset( $template_options['global_footer'] ) && $template_options['global_footer'] === $post->ID ) ? 'selected' : '';
			$global_footer  = ( isset( $template_options['global_footer'] ) && $template_options['global_footer'] === $settings['footer'] ) && ( isset( $template_options['global_header'] ) && $template_options['global_header'] !== $post->ID ) || ( isset( $template_options['global_footer'] ) && empty( $theme_footer ) && empty( $no_footer ) ) ? 'selected' : '';
			// revise this.
			$global_foot = isset( $template_options['global_footer'] ) ? $template_options['global_footer'] : '';
			printf( '<option value="theme_footer" ' . esc_attr( $theme_footer ) . '>%s</option>', esc_html__( 'Theme Footer', 'tkt-template-builder' ) );
			printf( '<option value="no_footer" ' . esc_attr( $no_footer ) . '>%s</option>', esc_html__( 'No Footer', 'tkt-template-builder' ) );
			printf( '<option value="%s" ' . esc_attr( $global_footer ) . '>%s</option>', esc_attr( $global_foot ), esc_html__( 'Global Footer', 'tkt-template-builder' ) );
			?>
		</select>
	<?php
	if ( ! empty( $disabled ) ) {
		?>
		<div class="tkt_fieldset_section">
			<details>
				  <summary><?php esc_html_e( 'Why is this disabled?', 'tkt-template-builder' ); ?></summary>
				  <p><small></small><em><?php esc_html_e( 'Since this is a Global Header or Footer Template, you cannot assign another Header or Footer Template. You could use a Parent Template instead, if you really need a hierarchy applied to your Global Template.', 'tkt-template-builder' ); ?></em></small></p>
			</details>
		</div>
		<?php
	}
	?>
	</div>
</fieldset>
<fieldset form="post" class="tkt-fieldset">
	<legend class="tkt-legend"><?php esc_html_e( 'ShortCode', 'tkt-template-builder' ); ?></legend>
	<div class="tkt_fieldset_section tkt_template_shortcode_copy">
		<code id="tkt_template_shortcode">[tkt_scs_template id="<?php echo absint( $post->ID ); ?>"]</code>
		<button id="tkt_copy_template_shortcode" class="ui-button ui-corner-all" title="Copy to Clipboard">
			<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clipboard" viewBox="0 0 16 16"><path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/><path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/></svg>
		</button>
	</div>
</fieldset>
<?php
wp_nonce_field( 'save_tkt_template_settings', 'tkt_template_settings_nonce' );
