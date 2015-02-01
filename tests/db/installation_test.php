<?php
/**
*
* @package phpBB Extension - Acme Demo
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace gothick\geomoderate\tests\db;

class simple_test extends \phpbb_database_test_case
{
	/**
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * @var \phpbb\db\tools\tools
	 */
	protected $db_tools;

	protected $geomoderate_table;

	static protected function setup_extensions()
	{
		return array('gothick/geomoderate');
	}

	public function setUp()
	{
		global $table_prefix;

		parent::setUp();

		$this->db = $this->new_dbal();
		$this->db_tools = new \phpbb\db\tools\tools($this->db);
		$this->geomoderate_table = $table_prefix . 'gothick_geomoderate';
	}

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/country_rules.xml');
		//return new \PHPUnit_Extensions_Database_DataSet_DefaultDataSet();
	}

	/**
	 * Does our migration successfully create our table?
	 */
	public function test_table_creation()
	{
		global $table_prefix;
		$this->assertTrue($this->db_tools->sql_table_exists($this->geomoderate_table), 'geomoderate table exists.');
		$this->assertTrue($this->db_tools->sql_column_exists($this->geomoderate_table, 'country_code'), 'Column "country code" exists.');
		$this->assertTrue($this->db_tools->sql_column_exists($this->geomoderate_table, 'moderate'), 'Column "moderate" exists.');
	}
	/**
	 * And does our fixture data show up successfully?
	 */
	public function test_data_insertion()
	{
		// There should be between two and three hundred countries in the world :D
		// We're on about 254 at the moment, so there's plenty of leeway in this test...
		$this->assertEquals(6, $this->db->get_row_count($this->geomoderate_table), "All fixtures loaded.");
		$sql = 'SELECT COUNT(*) AS moderate_count FROM ' . $this->geomoderate_table . ' WHERE moderate = 1';
		$result = $this->db->sql_query($sql);
		$this->assertEquals(3, (int) $this->db->sql_fetchfield('moderate_count'), 'Three fixture countries should be moderated.');
		$this->db->sql_freeresult($result);
	}
}
