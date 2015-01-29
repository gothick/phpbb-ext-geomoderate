<?php
/**
 *
 * @package phpBB Extension - Gothick Geomoderate
 * @copyright (c) 2013 phpBB Group
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
				// ACP modules
				'ACP_GEOMODERATE_TITLE' => 'GeoModerate',
				'ACP_GEOMODERATE_SETTINGS' => 'GeoModerate Settings',
				'ACP_GEOMODERATE_SETTINGS_SAVED' => 'Settings have been saved successfully!',

				// Log operations
				'GEOMODERATE_LOG_LOOKUP_FAILED' => '<strong>GeoModerate country lookup failed</strong><br />» GeoIP2 Reader returned: "%1$s"',
		));
