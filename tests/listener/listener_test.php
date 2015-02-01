<?php
/**
 *
 * @package phpBB Extension - Gothick Akismet
 * @copyright (c) 2015 Matt Gibson
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

class main_test extends \phpbb_test_case
{

	public function handle_data ()
	{
		return array(
				array(
						'jeff',
						'I’m a hideous spammer.',
						'59.58.118.122',
						true
				),
				array(
						'samantha',
						'I’m not a spammer. Hurrah!',
						'81.174.144.111',
						false
				),
				array(
						'matt',
						'Spammy as a spam fritter.',
						'222.247.36.187',
						true
				),
				array(
						'helen',
						'Would you like to buy some knock-off sports gear?',
						'222.247.36.187',
						true
				),
				array(
						'arthur',
						'No spam for me, please. That’s not my bag, baby. I’m too busy working on my PC in Virginia.',
						'54.174.106.196',
						false
				),
				array(
						'steve',
						'Make $$$ fast!',
						'93.182.36.82',
						true
				),
				array(
						'merlin',
						'I’m testing from localhost, which isn’t in your database, but which should let my message pass through.',
						'127.0.0.1',
						false
				)
		);
	}

	/**
	 * @dataProvider handle_data
	 */
	public function test_event_listener($username, $message, $ip, $should_be_moderated)
	{
		$phpbb_container = new \phpbb_mock_container_builder();
		$mock_reader = new \gothick\geomoderate\tests\mock\geoip2_reader_mock('');
		$phpbb_container->set('gothick.geomoderate.geoip2.reader', $mock_reader);

		$user = $this->getMockBuilder('\phpbb\user')
				->disableOriginalConstructor()
				->getMock();

		$user->ip = $ip;
		$user->data = array(
				'username' => $username,
				'session_ip' => $ip
		);

		$listener = new \gothick\geomoderate\event\main_listener(
				$user,
				new \phpbb\log\null(),
				$this->getMock('\phpbb\auth\auth'),
				$phpbb_container,
				new \gothick\geomoderate\tests\mock\country_rules_mock()
		);

		$data = array(
				'data' => array(
						// Not really necessary, but we might want to use them in future.
						'message' => $message,
						'topic_id' => 123
				)
		);
		$event = new \phpbb\event\data($data);
		$listener->check_submitted_post($event);

		if ($should_be_moderated)
		{
			$this->assertTrue(isset($event['data']['force_approved_state']));
			$this->assertEquals($event['data']['force_approved_state'], ITEM_UNAPPROVED);
		}
		else
		{
			$this->assertFalse(isset($event['data']['force_approved_state']));
		}
	}
	//TODO: Test if things are always allowed through from a moderator or admin.
}
