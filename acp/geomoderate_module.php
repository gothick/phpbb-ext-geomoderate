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
		global $table_prefix, $phpbb_container;

		// Add our ACP language file.
		$user->add_lang_ext('gothick/geomoderate', 'geomoderate_acp');

		$this->tpl_name = 'geomoderate_body';
		$this->page_title = $user->lang('ACP_GEOMODERATE_TITLE');
		add_form_key('gothick/geomoderate');

		/* @var $pagination \phpbb\pagination */
		$pagination = $phpbb_container->get('pagination');

		// Pagination: where we are
		$start	= request_var('start', 0);

		// Pagination: entries to display
		$per_page = request_var('countries_per_page', 100);

		// Pagination: total
		$countries_count = $db->get_row_count($table_prefix . 'gothick_geomoderate');

		if ($request->is_set_post('submit') &&
				$request->is_set_post('moderate'))
		{
			if (! check_form_key('gothick/geomoderate'))
			{
				trigger_error('FORM_INVALID');
			}

			// Update our database table with the submitted values. Note that we use a hidden
			// form field alongside the checkbox to make sure we get all the rows back (as
			// a checked value that the user unchecked is still relevant) so we get back
			// an array like 'A1' => 0, 'A2' => 1, 'AD' => 0, etc.
			$moderate = $request->variable('moderate', array('' => 0));
			if (sizeof($moderate)) {
				$sql = 'UPDATE ' . $table_prefix . 'gothick_geomoderate SET moderate = 0 ' .
						' WHERE ' . $db->sql_in_set('country_code', array_keys($moderate, 0));
				$db->sql_query($sql);

				$sql = 'UPDATE ' . $table_prefix . 'gothick_geomoderate SET moderate = 1 ' .
						' WHERE ' . $db->sql_in_set('country_code', array_keys($moderate, 1));
				$db->sql_query($sql);
			}

			$phpbb_log->add('admin', $user->data['user_id'], $user->ip,
					'GEOMODERATE_LOG_SETTING_CHANGED');

			trigger_error(
					$user->lang('ACP_GEOMODERATE_SETTING_SAVED') .
							adm_back_link($this->u_action));
		}

		$sql = 'SELECT * FROM ' . $table_prefix . 'gothick_geomoderate ORDER BY country_code';
		$result = $db->sql_query_limit($sql, $per_page, $start);

		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('geomoderate', array(
				'COUNTRY_CODE' => $row['country_code'],
				'COUNTRY' => $row['country_name'],
				'MODERATE' => $row['moderate']
			));
		}
		$db->sql_freeresult($result);

		$base_url = $this->u_action . "&amp;countries_per_page=$per_page";
		$pagination->generate_template_pagination($base_url, 'pagination', 'start', $countries_count, $per_page, $start);

		$template->assign_vars(
				array(
						'U_ACTION' => $this->u_action . "&amp;countries_per_page=$per_page&amp;start=$start",
						'COUNTRIES_PER_PAGE' => $per_page
				));
	}
}
