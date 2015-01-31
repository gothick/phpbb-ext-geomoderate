<?php
/**
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
	public $isoCode = 'GB';
}
/**
 * Simple mock GeoIp2 record
 */
class geoip2_mock_record {
	public $country;
	public function __construct() {
		$this->country = new geoip2_mock_country();
	}
}

/**
 * GeoIp2 mock that "detects" everything as being from the United Kingdom :)
 *
 */
class reader_always_united_kingdom implements \GeoIp2\ProviderInterface
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
	 */
	public function country($ipAddress)
	{
		return new geoip2_mock_record();
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