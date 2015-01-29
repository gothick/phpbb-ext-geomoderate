<?php
/**
 *
 * @package phpBB Extension - GeoModerate
 * @copyright (c) 2015 Matt Gibson Creative Ltd.
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */
namespace gothick\geomoderate\migrations;

class release_1_0_0 extends \phpbb\db\migration\migration
{
	static public function depends_on ()
	{
		return array(
					'\phpbb\db\migration\data\v310\alpha2'
		);
	}

	public function update_schema()
	{
		return array(
				'add_tables'		=> array(
						$this->table_prefix . 'gothick_geomoderate'	=> array(
								'COLUMNS'		=> array(
										'country_code'			=> array('CHAR:2', ''),
										'country_name'			=> array('VCHAR:255', ''),
										'moderate'				=> array('BOOL', 0)
								),
								'PRIMARY_KEY'	=> 'country_code',
						),
				)
		);
	}

	public function revert_schema()
	{
		return array(
				'drop_tables'    => array(
						$this->table_prefix . 'gothick_geomoderate'
				),
		);
	}

	/**
	 * We use a custom migration to load country codes from a CSV file.
	 * @see \phpbb\db\migration\migration::update_data()
	 */
	public function update_data()
	{
		return array(
				array('custom', array(array($this, 'load_countries'))),
		);
	}

	/**
	 * Load our list of countries and codes from the MaxMind CSV file.
	 */
	public function load_countries()
	{
		$countries_file = $this->phpbb_root_path . 'ext/gothick/geomoderate/data/countries.csv';
		if (($handle = fopen("$countries_file", "r")) !== FALSE)
		{
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
			{
				$sql = 'INSERT INTO ' . $this->table_prefix . 'gothick_geomoderate' . ' ' .
					$this->db->sql_build_array('INSERT', array(
						'country_code'	=> (string) $data[0],
						'country_name'	=> (string) $data[1]
					)
				);
				$this->sql_query($sql);
			}
			fclose($handle);
		}
	}
}
