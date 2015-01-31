<?php
/**
 * GeoModerate Admin Controller
 *
 * @package phpBB Extension - GeoModerate
 * @copyright (c) 2015 Matt Gibson Creative Ltd.
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace gothick\geomoderate\controller;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
* Admin controller
*/
class admin_controller
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\log\log_interface */
	protected $log;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var ContainerInterface */
	protected $container;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string phpEx */
	protected $php_ext;

	/** @var string Custom form action */
	protected $u_action;

	/** @var string GeoModerate table */
	protected $geomoderate_table;

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface    $db                Database object
	* @param \phpbb\request\request               $request           Request object
	* @param \phpbb\template\template             $template          Template object
	* @param \phpbb\user                          $user              User object
	* @param \phpbb\log\log_interface             $log               Log object
	* @param \phpbb\pagination                    $pagination        Pagination object
	* @param ContainerInterface                   $container         Service container interface
	* @param string                               $root_path         phpBB root path
	* @param string                               $php_ext           phpEx
	* @param string                               $geomoderate_table GeoModerate config table
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db,
			\phpbb\request\request $request,
			\phpbb\template\template $template,
			\phpbb\user $user,
			\phpbb\log\log_interface $log,
			\phpbb\pagination $pagination,
			ContainerInterface $container,
			$root_path,
			$php_ext,
			$geomoderate_table)
	{
		$this->db = $db;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->log = $log;
		$this->pagination = $pagination;
		$this->container = $container;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
		$this->geomoderate_table = $geomoderate_table;
	}

	/**
	* GeoModerate settings
	*
	*/
	public function display_settings()
	{
		add_form_key('gothick/gemoderate');

		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key('gothick/geomoderate'))
			{
				trigger_error('FORM_INVALID');
			}

			$this->save_settings();

			$this->log->add('admin', $user->data['user_id'], $user->ip,
					'GEOMODERATE_LOG_SETTING_CHANGED');

			trigger_error(
					$this->user->lang('ACP_GEOMODERATE_SETTING_SAVED') .
							adm_back_link($this->u_action));
		}

		// TODO: Replace this direct DB access with something more abstracted?

		// Pagination
		$start = $this->request->variable('start', 0);
		$per_page = $this->request->variable('countries_per_page', 100);
		$countries_count = $this->db->get_row_count($this->geomoderate_table);

		// Get our page of countries and current moderation settings
		$sql = 'SELECT * FROM ' . $this->geomoderate_table . ' ORDER BY country_code';
		$result = $this->db->sql_query_limit($sql, $per_page, $start);

		// Block assign template variable for our checkboxy list
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('geomoderate', array(
					'COUNTRY_CODE' => $row['country_code'],
					'COUNTRY_NAME' => $this->user->lang($row['country_name']),
					'MODERATE' => $row['moderate']
			));
		}
		$this->db->sql_freeresult($result);

		$base_url = $this->u_action . "&amp;countries_per_page=$per_page";
		$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $countries_count, $per_page, $start);

		// Assign standard template vars as well
		$this->template->assign_vars(
				array(
						'U_ACTION' => $this->u_action . "&amp;countries_per_page=$per_page&amp;start=$start",
						'COUNTRIES_PER_PAGE' => $per_page
				));
	}

	/**
	* Save settings back to the DB
	*/
	protected function save_settings()
	{
		// TODO: Refactor this DB access into a GeoModerate settings object/service?

		// Update our database table with the submitted values. Note that we use a hidden
		// form field alongside the checkbox to make sure we get all the rows back (as
		// a checked value that the user unchecked is still relevant) so we get back
		// an array like 'A1' => 0, 'A2' => 1, 'AD' => 0, etc.
		$moderate = $this->request->variable('moderate', array('' => 0));
		if (sizeof($moderate))
		{
			$sql = 'UPDATE ' . $this->geomoderate_table . ' SET moderate = 0 ' .
					' WHERE ' . $this->db->sql_in_set('country_code', array_keys($moderate, 0));
			$this->db->sql_query($sql);

			$sql = 'UPDATE ' . $this->geomoderate_table . ' SET moderate = 1 ' .
					' WHERE ' . $this->$db->sql_in_set('country_code', array_keys($moderate, 1));
			$this->db->sql_query($sql);

		}
	}

	/**
	* Set action
	*
	* @param string $u_action Action
	*/
	public function set_action($u_action)
	{
		$this->u_action = $u_action;
	}
}
