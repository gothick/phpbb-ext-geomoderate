<?php
/**
 * GeoModerate Country Rules
 *
 * Basically an interface to our database table. Handles paging lists of rules
 * for the admin interface, checking rules for our event listener, updating
 * rules, etc.
 *
 * @package phpBB Extension - GeoModerate
 * @copyright (c) 2015 Matt Gibson Creative Ltd.
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */
namespace gothick\geomoderate\rules;

class country_rules
{
	/**
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * @var \phpbb\cache\driver\driver_interface
	 */
	protected $cache;

	/**
	 * @var string GeoModerate table
	 */
	protected $geomoderate_table;

	public function __construct (
			\phpbb\db\driver\driver_interface $db,
			\phpbb\cache\driver\driver_interface $cache,
			$geomoderate_table)
	{
		$this->db = $db;
		$this->cache = $cache;
		$this->geomoderate_table = $geomoderate_table;
	}

	public function page_rules ($total, $offset = 0)
	{
		$rules = array();

		$sql = 'SELECT country_code, moderate FROM ' . $this->geomoderate_table . ' ORDER BY country_code';
		$result = $this->db->sql_query_limit($sql, $total, $offset);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$rules[$row['country_code']] = $row['moderate'];
		}
		$this->db->sql_freeresult($result);
		return $rules;
	}

	/**
	 * Total number of rows in the rules table (number of countries in the
	 * geoip2 database, basically.)
	 *
	 * @return number
	 */
	public function total_count ()
	{
		// I have no idea why the Dbal's get_row_count() returns a string,
		// but I'm going to cast it...
		return intval($this->db->get_row_count($this->geomoderate_table));
	}

	/**
	 * Pass an array mapping country codes to 0 (don't moderate) or 1 (moderate). The
	 * rules will be bulk-updated accordingly.
	 *
	 * @param array $moderate_array
	 */
	public function bulk_update($moderate_array)
	{
		if (sizeof($moderate_array))
		{
			$sql = 'UPDATE ' . $this->geomoderate_table . ' SET moderate = 0 ' .
					' WHERE ' . $this->db->sql_in_set('country_code', array_keys($moderate_array, 0));
			$this->db->sql_query($sql);

			$sql = 'UPDATE ' . $this->geomoderate_table . ' SET moderate = 1 ' .
					' WHERE ' . $this->db->sql_in_set('country_code', array_keys($moderate_array, 1));
			$this->db->sql_query($sql);

			$this->cache->destroy('sql', $this->geomoderate_table);
		}
	}

	/**
	 * Should we moderate posts from a given country code?
	 * @param unknown $country_code
	 * @return boolean
	 */
	public function should_moderate($country_code)
	{
		$sql_ary = array('country_code' => $country_code);
		$sql = 'SELECT COUNT(*) AS moderate FROM ' . $this->geomoderate_table . ' WHERE ' .
				$this->db->sql_build_array('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql, 3600); // We clear the cache in our update methods.
		return (bool) $this->db->sql_fetchfield('moderate');
	}
}


