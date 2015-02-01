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

/**
* Admin controller
*/
class admin_controller
{
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

	/** @var \gothick\geomoderate\rules\country_rules */
	protected $country_rules;

	/** @var string Custom form action */
	protected $u_action;

	const FORM_KEY = 'gothick/geomoderate';

	/**
	* Constructor
	*
	* @param \phpbb\request\request $request Request object
	* @param \phpbb\template\template $template Template object
	* @param \phpbb\user $user User object
	* @param \phpbb\log\log_interface $log Log object
	* @param \phpbb\pagination $pagination Pagination object
	* @param \gothick\geomoderate\rules\country_rules $country_rules
	*/
	public function __construct(
			\phpbb\request\request $request,
			\phpbb\template\template $template,
			\phpbb\user $user,
			\phpbb\log\log_interface $log,
			\phpbb\pagination $pagination,
			\gothick\geomoderate\rules\country_rules $country_rules
		)
	{
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->log = $log;
		$this->pagination = $pagination;
		$this->country_rules = $country_rules;
	}

	/**
	* GeoModerate settings
	*
	*/
	public function display_settings()
	{
		add_form_key(self::FORM_KEY);

		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key(self::FORM_KEY))
			{
				trigger_error('FORM_INVALID');
			}

			$this->save_settings();

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip,
					'GEOMODERATE_LOG_SETTING_CHANGED');

			trigger_error(
					$this->user->lang('ACP_GEOMODERATE_SETTING_SAVED') .
							adm_back_link($this->u_action));
		}

		// Pagination
		$start = $this->request->variable('start', 0);
		$per_page = $this->request->variable('countries_per_page', 100);
		$countries_count = $this->country_rules->total_count();

		// Grab a pageful of rules
		$rules = $this->country_rules->page_rules($per_page, $start);
		// Block assign template variable for our checkboxy list
		foreach ($rules as $country_code => $moderate)
		{
			$this->template->assign_block_vars('geomoderate', array(
					'COUNTRY_CODE' => $country_code,
					'COUNTRY_NAME' => $this->user->lang(array('ACP_GEOMODERATE_COUNTRIES', $country_code)),
					'MODERATE' => $moderate
			));
		}

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
		// Update our database table with the submitted values. Note that we use a hidden
		// form field alongside the checkbox to make sure we get all the rows back (as
		// a checked value that the user unchecked is still relevant) so we get back
		// an array like 'A1' => 0, 'A2' => 1, 'AD' => 0, etc.
		$moderate = $this->request->variable('moderate', array('' => 0));
		if (sizeof($moderate))
		{
			$this->country_rules->bulk_update($moderate);
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
