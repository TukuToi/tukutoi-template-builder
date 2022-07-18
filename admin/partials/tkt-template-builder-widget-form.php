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
	$this->select_fieldset( 'widget', 'Widget', '', array( $additional_options, 'allwidgets_options' ) );

	$this->text_fieldset( 'title', 'Title', '', 'The title of the Widget' );
	$this->text_fieldset( 'classname', 'Class Name', '', 'The class name of the Widget' );
	$this->text_fieldset( 'before_widget', 'Before Widget', '', 'HTML content that will be prepended to the widget\'s HTML output. Default \'<div class="widget %s">\', where %s is the widget\'s class name.' );
	$this->text_fieldset( 'after_widget', 'After Widget', '', 'HTML content that will be appended to the widget\'s HTML output. Default </div>.' );
	$this->text_fieldset( 'before_title', 'Before Title', '', ' HTML content that will be prepended to the widget\'s title when displayed. Default <h2 class="widgettitle">' );
	$this->text_fieldset( 'after_title', 'After Widget', '', 'HTML content that will be appended to the widget\'s title when displayed. Default </h2>' );

	?>
	<div class="tkt-conditional-gui-section WP_Widget_Archives WP_Widget_Categories">
		<?php
		$this->text_fieldset( 'count', 'Count', '', 'Wether to show the Count or not (value can be 0 or 1). Available for Categories and Archive Widget' );
		$this->text_fieldset( 'dropdown', 'Dropdown', '', 'Display as drop-down list (1). Default: 0 (an unordered list). Available for Categories and Archive Widget' );
		?>
	</div>
		<div class="tkt-conditional-gui-section WP_Widget_Categories">
		<?php
		$this->text_fieldset( 'hierarchical', 'Hierarchical', '', 'Display sub-categories as nested items inside the parent category (1). Default: 0 (in-line). Available for Categories widget' );
		?>
	</div>
		<div class="tkt-conditional-gui-section WP_Widget_Links">
		<?php
		$this->text_fieldset( 'category', 'Category', '', 'Link category IDs , separated by commas, to display. The category parameter of wp_list_bookmarks. Default: false (display all of link categories). Available for Links Widget' );
		$this->text_fieldset( 'description', 'Description', '', 'Display description of link (1 – true). The show_description parameter. Default: false (hide). Available for Links Widget' );
		$this->text_fieldset( 'rating', 'Rating', '', 'Display rating of link (1- true). The show_rating parameter. Default: false (hide). Available for Links Widget' );
		$this->text_fieldset( 'images', 'Images', '', 'Display image of link (1 – true). The show_images parameter. Default: true (show). Available for Links Widget' );
		$this->text_fieldset( 'name', 'Name', '', 'If display link image, output link name to the alt attribute. The show_name parameter. Default: false. Available for Links Widget' );
		?>
	</div>
		<div class="tkt-conditional-gui-section WP_Widget_Pages">
		<?php
		$this->text_fieldset( 'sortby', 'Sort By', '', 'The sort_column parameter of wp_list_pages. Default: menu_order. Available for Pages Widget' );
		$this->text_fieldset( 'exclude', 'Exclude', '', 'Page IDs, separated by commas, to be excluded from the list. Default: null (show all of Pages). Available for Pages Widget' );
		?>
	</div>
		<div class="tkt-conditional-gui-section WP_Widget_Recent_Comments WP_Widget_Recent_Posts">
		<?php
		$this->text_fieldset( 'number', 'Number', '', 'Number of comments or posts to show (at most 15). Default: 5' );
		?>
	</div>
		<div class="tkt-conditional-gui-section WP_Widget_RSS">
		<?php
		$this->text_fieldset( 'url', 'URL', '', 'RSS or Atom feed URL to include. Available for RSS Widget' );
		$this->text_fieldset( 'items', 'Items', '', 'the number of RSS or Atom items to display. Available for RSS Widget' );
		$this->text_fieldset( 'show_summary', 'Show Summary', '', '?. Available for RSS Widget' );
		$this->text_fieldset( 'show_author', 'Show Author', '', '?. Available for RSS Widget' );
		$this->text_fieldset( 'show_date', 'Show Date', '', '?. Available for RSS Widget' );
		?>
	</div>
		<div class="tkt-conditional-gui-section WP_Widget_Tag_Cloud">
		<?php
		$this->text_fieldset( 'taxonomy', 'Taxonomy', '', 'The taxonomy the cloud will draw tags from. default: post_tag. Available for Cloud Widget' );
		?>
	</div>
		<div class="tkt-conditional-gui-section WP_Widget_Text">
		<?php
		$this->text_fieldset( 'filter', 'Filter', '', '?. Available for Text Widget' );
		?>
	</div>
		<div class="tkt-conditional-gui-section WP_Nav_Menu_Widget">
		<?php
		$this->select_fieldset( 'nav_menu', 'Navigation Menu', '', array( $additional_options, 'allmenus_options' ) );
		?>
	</div>
</form>
