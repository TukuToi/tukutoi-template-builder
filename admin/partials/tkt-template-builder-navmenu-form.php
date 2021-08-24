<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.tukutoi.com/
 * @since      1.0.0
 *
 * @package    Tkt_Search_And_Filter
 * @subpackage Tkt_Search_And_Filter/admin/partials
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
	$this->select_fieldset( 'menu', 'Menu', '', array( $additional_options, 'allmenus_options' ) );
	$this->select_fieldset( 'theme_location', 'Theme Location', '', array( $additional_options, 'alllocations_options' ) );
	$this->text_fieldset( 'menu_class', 'Menu Class', '', 'CSS class to use for the ul element which forms the menu. Default \'menu\'' );
	$this->text_fieldset( 'menu_id', 'Menu ID', '', 'The ID that is applied to the ul element which forms the menu. Default is the menu slug, incremented' );
	$this->text_fieldset( 'container', 'Container', 'div', 'Whether to wrap the ul, and what to wrap it with. Default \'div\'' );
	$this->text_fieldset( 'container_class', 'Container Class', '', 'Class that is applied to the container. Default \'menu-{menu slug}-container\'' );
	$this->text_fieldset( 'container_id', 'Container ID', '', ' The ID that is applied to the container' );
	$this->text_fieldset( 'container_aria_label', 'Container Aria Label', '', 'The aria-label attribute that is applied to the container when it\'s a nav element' );
	$this->text_fieldset( 'fallback_cb', 'Fallback', '', 'If the menu doesn\'t exist, a callback function will fire. Default is \'wp_page_menu\'. Set to false for no fallback.' );
	$this->text_fieldset( 'before', 'Before Link Markup', '', ' Text before the link markup' );
	$this->text_fieldset( 'after', 'After Link Markup', '', 'Text after the link markup' );
	$this->text_fieldset( 'link_before', 'Before Link Text', '', 'Text before the link text' );
	$this->text_fieldset( 'link_after', 'After Link Text', '', 'Text after the link text' );
	$this->text_fieldset( 'depth', 'Depth', '', 'How many levels of the hierarchy are to be included. 0 means all' );
	$this->text_fieldset( 'walker', 'Custom Walker', '', 'The ClassName of your Custom Menu Walker. (No arguments or parentheses, just the ClassName)' );

	$this->text_fieldset( 'items_wrap', 'List Items Wrap', '', 'How the list items should be wrapped. Uses printf() format with numbered placeholders. Example \'<ul id="%1$s" class="%2$s">%3$s</ul>\'' );
	$this->select_fieldset( 'item_spacing', 'Items Spacing', '', array( $additional_options, 'allitemspacing_options' ) );

	?>
</form>
