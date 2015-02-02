<?php
/**
 * Simple sanity checks of our third-party IP address geolocation component.
 *
 * Exercise the IP address geolocation in the same way as our extension, and
 * mae sure it behaves as we expect. This should help us get early warnings of
 * any breaking changes on updating the vendor software.
 *
 * The component uses a local database file for lookups, so this test doesn't
 * require any network connectivity.
 */

namespace gothick\geomoderate\tests\vendor;

require (__DIR__ . '/../../vendor/autoload.php');

class geolite_test extends \phpbb_test_case
{
	/**
	 * Test we get the kind of result we're expecting back from
	 * a simple, valid lookup.
	 */
	public function test_reader_basic_operation()
	{
		$reader = new \GeoIp2\Database\Reader(__DIR__ . '/../../data/GeoLite2-Country.mmdb');
		// GP IP address that's unlikely to change.
		$record = $reader->country('81.174.144.111');
		$this->assertInstanceOf('\GeoIp2\Model\Country', $record);
		$this->assertEquals($record->country->isoCode, 'GB');
	}

	/**
	 * Test we get an exception thrown on an invalid lookup.
	 * @expectedException \GeoIp2\Exception\AddressNotFoundException
	 */
	public function testException()
	{
		$reader = new \GeoIp2\Database\Reader(__DIR__ . '/../../data/GeoLite2-Country.mmdb');
		// The service can't know where 127.0.0.1 is...
		$record = $reader->country('127.0.0.1');
	}
}