<?php
/**
 * Mock GeoIp2 reader with a few hardcoded addresses and the same
 * exception thrown on record not found as the real thing.
 *
 * @package phpBB Extension - Gothick Geomoderate
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace gothick\geomoderate\tests\mock;

require (__DIR__ . '/../../vendor/autoload.php');

/**
 * Simple mock GeoIp2 country
 */
class geoip2_mock_country {
	public $isoCode;
	public function __construct($isoCode)
	{
		$this->isoCode = $isoCode;
	}
}
/**
 * Simple mock GeoIp2 record
 */
class geoip2_mock_record {
	public $country;
	public function __construct($isoCode) {
		$this->country = new geoip2_mock_country($isoCode);
	}
}

/**
 * Reader with a few hardcoded values
 *
 */
class geoip2_reader_mock implements \GeoIp2\ProviderInterface
{
	// This is the minimum we need to cope with being thrown at us:
	//$reader = $this->phpbb_container->get('gothick.geomoderate.geoip2.reader');
	//$record = $reader->country($this->user->ip);
	//$country_code = $record->country->isoCode;
	public function __construct($filename, $locales = array('en'))
	{
	}
	/**
	 * @param ipAddress
	 *            IPv4 or IPv6 address to lookup.
	 * @return \GeoIp2\Model\Country A Country model for the requested IP address.
	 * @throws \GeoIp2\Exception\AddressNotFoundException if the address is not in the database.
	 *
	 */
	public function country($ipAddress)
	{
		$iso_code = '';
		switch ($ipAddress)
		{
			case '59.58.118.122':
			case '222.247.36.187':
			case '222.247.36.187':
				$iso_code = 'CN';
				break;
			case '93.182.36.82':
				$iso_code = 'RU';
				break;
			case '81.174.144.111':
				$iso_code = 'GB';
				break;
			case '54.174.106.196':
				$iso_code = 'US';
				break;
			default:
				break;
		}
		if ($iso_code != '')
		{
			return new geoip2_mock_record($iso_code);
		}
		else
		{
			throw new \GeoIp2\Exception\AddressNotFoundException("The address $ipAddress is not in the database.");
		}
	}
	/**
	 * @param ipAddress
	 *            IPv4 or IPv6 address to lookup.
	 * @return \GeoIp2\Model\City A City model for the requested IP address.
	*/
	public function city($ipAddress)
	{
		// Might need this later. But for now we're only using the country.
	}
}