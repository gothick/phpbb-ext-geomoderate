<?php
/**
 *
* @package phpBB Extension - GeoModerate
* @copyright (c) 2015 Matt Gibson Creative Ltd.
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
if (! defined('IN_PHPBB'))
{
	exit();
}

if (empty($lang) || ! is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang,
		array(
				'ACP_GEOMODERATE_TITLE' => 'GeoModerate Settings',
				'ACP_GEOMODERATE_EXPLAIN' => 'GeoModerate will send all posts from chosen countries to the moderation queue. (Posts from administrators and moderators will not be affected.) Tick your countries to moderate here.',
				'ACP_GEOMODERATE_MAXMIND_LINK' => 'The country of origin is determined by the IP address of the sender. This extension includes GeoLite data created by MaxMind, available from <a href="http://www.maxmind.com">http://www.maxmind.com</a>.',
				'ACP_GEOMODERATE_NO_COUNTRIES_LOADED' => 'No countries loaded.',
				'ACP_GEOMODERATE_COUNTRY_CODE' => 'Country Code',
				'ACP_GEOMODERATE_COUNTRY_NAME' => 'Country Name',
				'ACP_GEOMODERATE_MODERATE' => 'Moderate?',
				'ACP_GEOMODERATE_SETTING_SAVED' => 'GeoModerate settings successfully saved!',
		));
