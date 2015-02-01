<?php
/**
 * GeoModerate Country Rules Mock object
 *
 * Returns a simple hardcoded set of answers.
 *
 * @package phpBB Extension - GeoModerate
 * @copyright (c) 2015 Matt Gibson Creative Ltd.
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */
namespace gothick\geomoderate\tests\mock;

class country_rules_mock extends \gothick\geomoderate\rules\country_rules
{
	public function __construct ()
	{
	}

	/**
	 * Should we moderate posts from a given country code?
	 * @param string $country_code ISO country code
	 * @return boolean
	 */
	public function should_moderate($country_code)
	{
		// Well, they are currently the two biggest spam senders according
		// to http://www.stopforumspam.com. But then they've got more people
		// than everyone else, too, so it's hardly a fair comparison :)
		return $country_code == 'CN' || $country_code == 'RU';
	}
}
