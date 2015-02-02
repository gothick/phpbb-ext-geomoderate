<?php
/**
 *
* @package phpBB Extension - Acme Demo
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace gothick\geomoderate\tests\db;

require(__DIR__ . '/../../../../../../tests/test_framework/phpbb_session_test_case.php');

class country_rules_test extends \phpbb_session_test_case
{
	/**
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * @var \phpbb\db\tools
	 */
	protected $db_tools;

	protected $geomoderate_table;

	protected $backup_cache;

	protected $session;

	static protected function setup_extensions()
	{
		return array('gothick/geomoderate');
	}

	public function setUp()
	{
		global $table_prefix;

		parent::setUp();

		$this->db = $this->new_dbal();
		$this->db_tools = new \phpbb\db\tools($this->db);
		$this->geomoderate_table = $table_prefix . 'gothick_geomoderate';

		$this->session = $this->session_factory->get_session($this->db);
		global $cache, $config, $phpbb_root_path, $phpEx;
		$this->backup_cache = $cache;
		// Change the global cache object for this test because
		// the mock cache object does not hit the database as is needed
		// for this test.
		$cache = new \phpbb\cache\service(
				new \phpbb\cache\driver\file(),
				$config,
				$this->db,
				$phpbb_root_path,
				$phpEx
		);
	}

	public function tearDown()
	{
		parent::tearDown();
		// Set cache back to what it was before the test changed it
		global $cache;
		$cache = $this->backup_cache;
	}

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/country_rules.xml');
	}

	public function test_page_rules_all_results()
	{
		global $cache;
		$country_rules = new \gothick\geomoderate\rules\country_rules(
				$this->db,
				$cache->get_driver(),
				$this->geomoderate_table
			);
		$rules = $country_rules->page_rules(100);
		$this->assertEquals(6, sizeof($rules), 'Loaded all six rules from database.');

		// First entry
		$value = reset($rules);
		$this->assertEquals('AD', key($rules), 'First country in page should be Andorra.');
		$this->assertEquals(0, $value, 'Andorra should be approved.');

		// Last entry
		$value = end($rules);
		$this->assertEquals('MY', key($rules), 'Last country in page should be Malaysia.');
		$this->assertEquals(1, $value, "Malaysia should be moderated.");
	}

	public function test_page_rules_partial_results()
	{
		global $cache;
		$country_rules = new \gothick\geomoderate\rules\country_rules(
				$this->db,
				$cache->get_driver(),
				$this->geomoderate_table
		);
		$rules = $country_rules->page_rules(4, 1);
		$this->assertEquals(4, sizeof($rules), 'Loaded a page of four rules from database.');

		// First entry
		$value = reset($rules);
		$this->assertEquals('AF', key($rules), 'First country in page should be Afghanistan.');
		$this->assertEquals(0, $value, 'Afghanistan should be approved.');

		// Last entry
		$value = end($rules);
		$this->assertEquals('MX', key($rules), 'Last country in page should be Mexico.');
		$this->assertEquals(1, $value, "Mexico should be moderated.");
	}

	public function test_page_rules_count()
	{
		global $cache;
		$country_rules = new \gothick\geomoderate\rules\country_rules(
				$this->db,
				$cache->get_driver(),
				$this->geomoderate_table
		);
		$count = $country_rules->total_count();
		$this->assertEquals(6, $count, 'total_count found exactly six rules.');
	}

	public function test_page_rules_should_moderate()
	{
		global $cache;
		$country_rules = new \gothick\geomoderate\rules\country_rules(
				$this->db,
				$cache->get_driver(),
				$this->geomoderate_table
		);
		$this->assertFalse($country_rules->should_moderate('AG'), 'Antigua and Barbuda should be allowed.');
		$this->assertTrue($country_rules->should_moderate('MW'), 'Malawi should be moderated.');
		$this->assertFalse($country_rules->should_moderate('XX'), 'Unknnown country codes should be allowed.');
	}
	public function test_page_rules_bulk_update()
	{
		global $cache;
		$country_rules = new \gothick\geomoderate\rules\country_rules(
				$this->db,
				$cache->get_driver(),
				$this->geomoderate_table
		);

		$moderate_array = array(
			'AD' => 0,
			'AF' => 1,
			'AG' => 0,
			'MW' => 1,
			'MX' => 0,
			'MY' => 1
		);
		$country_rules->bulk_update($moderate_array);
		$expected = $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/country_rules_after_update.xml')->getTable($this->geomoderate_table);
		// NB: If you don't order by the same thing in the query as in the fixture, this will fail
		// on Postgres, as assertTablesEqual (in my opionion, rather crazily) relies on the ordering,
		// and on Postgres the records seem to come back ordered by the "moderate" column if there's
		// no ORDER BY... That only took me two hours and three virtual boxes to figure out.
		$query_table = $this->getConnection()->createQueryTable($this->geomoderate_table, 'SELECT * FROM ' . $this->geomoderate_table . ' ORDER BY country_code');
		$this->assertTablesEqual($expected, $query_table);
	}

	public function no_items_data ()
	{
		return array(
				array(
						// all should be set to 0
						array('AD' => 0, 'AF' => 0, 'AG' => 0, 'MW' => 0, 'MX' => 0, 'MY' => 0),
						dirname(__FILE__) . '/fixtures/country_rules_after_update_all_zero.xml'
				),
				array(
						// All should be set to 1
						array('AD' => 1, 'AF' => 1, 'AG' => 1, 'MW' => 1, 'MX' => 1, 'MY' => 1),
						dirname(__FILE__) . '/fixtures/country_rules_after_update_all_one.xml'
				),
				array(
						// No changes should be made
						array(),
						dirname(__FILE__) . '/fixtures/country_rules.xml'
				)
		);
	}

	/**
	 * Because the first time I pressed "submit" on a rules admin page with no checkboxes checked,
	 * I got a crash. This could happen with nothing sent to the bulk_update function, or with
	 * all ones or all zeros, as it means we end up with a "WHERE country_code IN ()"...
	 *
	 * @dataProvider no_items_data
	 */
	public function test_page_rules_bulk_update_no_items ($moderate_array, $expected_file)
	{
		global $cache;
		$country_rules = new \gothick\geomoderate\rules\country_rules(
				$this->db,
				$cache->get_driver(),
				$this->geomoderate_table
		);

		$country_rules->bulk_update($moderate_array);
		$query_table = $this->getConnection()->createQueryTable($this->geomoderate_table, 'SELECT * FROM ' . $this->geomoderate_table . ' ORDER BY country_code');

		$expected = $this->createXMLDataSet($expected_file)->getTable($this->geomoderate_table);

		$this->assertTablesEqual($expected, $query_table);
	}
}
