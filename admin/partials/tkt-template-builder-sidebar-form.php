<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.tukutoi.com/
 * @since      1.0.0
 * @package    Plugins\TemplateBuilder\Admin\Partials
 * @author     Beda Schmid <beda@tukutoi.com>
 */

?>
<?php
/**
 * We need to add some data to the existing TukuToi ShortCode GUI Selector Options.
 *
 * @since 2.0.0
 */
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'class-tkt-template-builder-gui.php';
$additional_options = new Tkt_Template_Builder_Gui( new Tkt_Template_Builder_Declarations() );
?>
<form class="tkt-shortcode-form">
	<?php
	$this->select_fieldset( 'sidebar', 'Sidebar', '', array( $additional_options, 'allsidebars_options' ) );
	$this->text_fieldset( 'error', 'Error Message', '', 'Message to display if the sidebar was not found.' );
	?>
</form>
