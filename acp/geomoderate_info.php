<?php
/**
*
* @package phpBB Extension - Gothick GeoModerate
* @copyright (c) 2015 Matt Gibson
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace gothick\geomoderate\acp;

class geomoderate_info
{
	public function module()
	{
		return array(
			'filename'	=> '\gothick\geomoderate\acp\geomoderate_module',
			'title'		=> 'ACP_GEOMODERATE_TITLE',
			'version'	=> '1.0.1',
			'modes'		=> array(
				'settings'	=> array('title' => 'ACP_GEOMODERATE_SETTINGS',
						'auth' => 'ext_gothick/geomoderate && acl_a_board',
						'cat' => array('ACP_GEOMODERATE_TITLE')),
			),
		);
	}
}
