<?php
/**
 * GeoModerate ACP Module
 *
 * @package phpBB Extension - GeoModerate
 * @copyright (c) 2015 Matt Gibson Creative Ltd.
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */
namespace gothick\geomoderate\acp;

/**
 * ACP page for configuring Gothick GeoModerate: allows the user to specify
 * which country codes we should always moderate posts from, based on
 * geolocated IP.
 *
 * @author matt
 *
 */
class geomoderate_module
{

	var $u_action;

	public function main ($id, $mode)
	{
		// The example extension documentation seems to suggest using
		// these as globals rather then fetching them out of the container.
		// Not sure why.
		global $phpbb_container, $user, $language;

		// TODO: Are we using this new Language object correctly?
		// Add ACP language strings
		$user->add_lang('geomoderate_acp', 'gothick/geomoderate');
		// And all our country names
		$user->add_lang('geomoderate_acp_countries', 'gothick/geomoderate');

		/* @var $admin_controller \gothick\geomoderate\controller\admin_controller */
		$admin_controller = $phpbb_container->get('gothick.geomoderate.admin.controller');
		$admin_controller->set_action($this->u_action);

		$this->tpl_name = 'geomoderate_body';
		$this->page_title = $user->lang('ACP_GEOMODERATE_TITLE');
		$admin_controller->display_settings();
	}
}
