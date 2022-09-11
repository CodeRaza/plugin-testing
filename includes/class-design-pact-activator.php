<?php

/**
 * Fired during plugin activation
 *
 * @link       https://farazahmad.net
 * @since      1.0.0
 *
 * @package    Design_Pact
 * @subpackage Design_Pact/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Design_Pact
 * @subpackage Design_Pact/includes
 * @author     Faraz Ahmad <farazahmad759@gmail.com>
 */
class Design_Pact_Activator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate()
	{
		$self = new self();
		$self->set_options();
		$self->create_pages();
		$self->create_directories();
	}

	function create_pages()
	{
		// Variations Management Page
		$page = get_page_by_path("variations-management", OBJECT);
		if (!isset($page)) {
			$my_post = array(
				'post_type'     => 'page',
				'post_title'    => 'Variations Management',
				'post_content'  => '',
				'post_status'   => 'publish',
				'post_author'   => 1
			);
			wp_insert_post($my_post);
		}
	}

	function create_directories()
	{
		$directories = array(
			'../scripts',
			'../scripts/printful',
			'../scripts/printful/temp',
			'../scripts/printful/cached-variations'
		);
		foreach ($directories as $directory) {
			if (!file_exists($directory)) {
				mkdir($directory, 0777, true);
			}
		}
	}

	function set_options()
	{
		if (empty(get_option('dpdv_info_email'))) {
			update_option('dpdv_info_email', 'info@' . $_SERVER['HTTP_HOST']);
		}
		if (empty(get_option('dpdv_support_email'))) {
			update_option('dpdv_support_email', 'support@' . $_SERVER['HTTP_HOST']);
		}
	}
}
