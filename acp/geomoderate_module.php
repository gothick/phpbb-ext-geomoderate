<?php
/**
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

	function main ($id, $mode)
	{
		global $db, $user, $auth, $template, $cache, $request, $phpbb_log;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;
		global $table_prefix;

		// Add our ACP language file.
		$user->add_lang_ext('gothick/geomoderate', 'geomoderate_acp');

		$this->tpl_name = 'geomoderate_body';
		$this->page_title = $user->lang('ACP_GEOMODERATE_TITLE');
		add_form_key('gothick/geomoderate');

		if ($request->is_set_post('submit'))
		{
			if (! check_form_key('gothick/geomoderate'))
			{
				trigger_error('FORM_INVALID');
			}

			$phpbb_log->add('admin', $user->data['user_id'], $user->ip,
					'GEOMODERATE_LOG_SETTING_CHANGED');

			trigger_error(
					$user->lang('ACP_GEOMODERATE_SETTING_SAVED') .
							adm_back_link($this->u_action));
		}

		$sql = 'SELECT * FROM ' . $table_prefix . 'gothick_geomoderate ORDER BY country_code';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('geomoderate', array(
				'COUNTRY_CODE' => $row['country_code'],
				'COUNTRY' => $row['country_name'],
				'MODERATE' => $row['moderate']
			));
		}
		$db->sql_freeresult($result);

		$template->assign_vars(
				array(
						'U_ACTION' => $this->u_action,
						'GOTHICK_GEOMODERATE_WHATEVER' => 'whatever'
				));
	}
}
