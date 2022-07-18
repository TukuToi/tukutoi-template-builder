<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * We are not using an uninstall hook because WordPress perfoms bad when using it.
 * Even if below issue is "fixed", it did not resolve the perfomance issue.
 *
 * @see https://core.trac.wordpress.org/ticket/31792
 *
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - Check if the $_REQUEST['plugin'] content actually is tkt-template-builder/tkt-template-builder.php
 * - Check if the $_REQUEST['action'] content actually is delete-plugin
 * - Run a check_ajax_referer check to make sure it goes through authentication
 * - Run a current_user_can check to make sure current user can delete a plugin
 *
 * @todo Consider multisite. Once for a single site in the network, once sitewide.
 *
 * @link       https://www.tukutoi/
 * @since      0.0.1
 * @package    Plugins\TemplateBuilder
 * @author     Beda Schmid <beda@tukutoi.com>
 */

/**
 * Perform Uninstall Actions.
 *
 * If uninstall not called from WordPress,
 * If no uninstall action,
 * If not this plugin,
 * If no caps,
 * then exit.
 *
 * @since 0.0.1
 */
function plugin_name_uninstall() {

	if ( ! defined( 'WP_UNINSTALL_PLUGIN' )
		|| empty( $_REQUEST )
		|| ! isset( $_REQUEST['plugin'] )
		|| ! isset( $_REQUEST['action'] )
		|| 'tukutoi-template-builder/tkt-template-builder.php' !== $_REQUEST['plugin']
		|| 'delete-plugin' !== $_REQUEST['action']
		|| ! check_ajax_referer( 'updates', '_ajax_nonce' )
		|| ! current_user_can( 'activate_plugins' )
	) {

		exit;

	}

	/**
	 * It is now safe to perform your uninstall actions here.
	 *
	 * @see https://developer.wordpress.org/plugins/plugin-basics/uninstall-methods/#method-2-uninstall-php
	 */

}

plugin_name_uninstall();
