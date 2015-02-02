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
	 * Basic test of event listener: does it mark things from certain countries
	 * for moderation?
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

	/**
	 * Do moderation events get logged correctly?
	 */
	public function test_event_listener_log_moderation()
	{
		$phpbb_container = new \phpbb_mock_container_builder();
		$mock_reader = new \gothick\geomoderate\tests\mock\geoip2_reader_mock('');
		$phpbb_container->set('gothick.geomoderate.geoip2.reader', $mock_reader);

		$user = $this->getMockBuilder('\phpbb\user')
				->disableOriginalConstructor()
				->getMock();

		// This IP address is known to our mock reader as Russian,
		// and Russian posts are configured to be moderated in our
		// mock rules. No exception should be thrown, and the post
		// should be moderated.
		$user->ip = '93.182.36.82';
		$user->data = array(
				'user_id' => '123',
				'username' => 'Yuri',
				'session_ip' => '93.182.36.82'
		);

		$log = $this->getMockBuilder('\phpbb\log\log')
				->disableOriginalConstructor()
				->setMethods(array('add'))
				->getMock();

		$log->expects($this->once())
				->method('add')
				->with(
					$this->equalTo('mod'),
					$this->equalTo(123),
					$this->equalTo('93.182.36.82'),
					$this->stringContains('DISAPPROVED'),
					$this->equalTo(false),
					$this->anything()
			);

		$listener = new \gothick\geomoderate\event\main_listener(
				$user,
				$log,
				$this->getMock('\phpbb\auth\auth'),
				$phpbb_container,
				new \gothick\geomoderate\tests\mock\country_rules_mock()
		);

		$data = array('data' => array('message' => 'Test'));
		$event = new \phpbb\event\data($data);

		$listener->check_submitted_post($event);
		// Message should be moderated
		$this->assertTrue(isset($event['data']['force_approved_state']), "force_approved_state should be set for known moderation IP.");
		$this->assertEquals($event['data']['force_approved_state'], ITEM_UNAPPROVED, "known moderation IP should be unapproved.");
	}

	/**
	 * Do unexpected exceptions get logged quietly, with the post being
	 * marked as approved?
	 */
	public function test_event_listener_log_exceptions()
	{
		$phpbb_container = new \phpbb_mock_container_builder();
		$mock_reader = new \gothick\geomoderate\tests\mock\geoip2_reader_mock('');
		$phpbb_container->set('gothick.geomoderate.geoip2.reader', $mock_reader);

		$user = $this->getMockBuilder('\phpbb\user')
		->disableOriginalConstructor()
		->getMock();

		// The GeoIp2 service can't handle 127.0.0.1, and throws an exception,
		// as does our mock service.
		$user->ip = '127.0.0.1';
		$user->data = array(
				'user_id' => '123',
				'username' => 'Yuri',
				'session_ip' => '127.0.0.1'
		);

		$log = $this->getMockBuilder('\phpbb\log\log')
				->disableOriginalConstructor()
				->setMethods(array('add'))
				->getMock();

		$log->expects($this->once())
		->method('add')
		->with(
				$this->equalTo('critical'),
				$this->equalTo(123),
				$this->equalTo('127.0.0.1'),
				$this->stringContains('LOOKUP_FAILED'),
				$this->equalTo(false),
				$this->anything()
		);

		$listener = new \gothick\geomoderate\event\main_listener(
				$user,
				$log,
				$this->getMock('\phpbb\auth\auth'),
				$phpbb_container,
				new \gothick\geomoderate\tests\mock\country_rules_mock()
		);

		$data = array('data' => array('message' => 'Test'));
		$event = new \phpbb\event\data($data);

		$listener->check_submitted_post($event);
		// Message should not be moderated
		$this->assertFalse(isset($event['data']['force_approved_state']), 'Post from IP address that causes exception should be quietly approved.');
	}
	/**
	 * Do unexpected exceptions get logged quietly, with the post being
	 * marked as approved?
	 */
	public function test_event_listener_no_logging_normal_operation()
	{
		$phpbb_container = new \phpbb_mock_container_builder();
		$mock_reader = new \gothick\geomoderate\tests\mock\geoip2_reader_mock('');
		$phpbb_container->set('gothick.geomoderate.geoip2.reader', $mock_reader);

		$user = $this->getMockBuilder('\phpbb\user')
		->disableOriginalConstructor()
		->getMock();

		// Known GB IP address; no moderation, no exceptions thrown.
		$user->ip = '81.174.144.111';
		$user->data = array(
				'user_id' => '345',
				'username' => 'Matt',
				'session_ip' => '81.174.144.111'
		);

		$log = $this->getMockBuilder('\phpbb\log\log')
				->disableOriginalConstructor()
				->setMethods(array('add'))
				->getMock();

		// This is the main test here: nothing should be logged in normal
		// operation.
		$log->expects($this->exactly(0))
				->method('add');

		$listener = new \gothick\geomoderate\event\main_listener(
				$user,
				$log,
				$this->getMock('\phpbb\auth\auth'),
				$phpbb_container,
				new \gothick\geomoderate\tests\mock\country_rules_mock()
		);

		$data = array('data' => array('message' => 'Test'));
		$event = new \phpbb\event\data($data);

		$listener->check_submitted_post($event);
		// Message should not be moderated
		$this->assertFalse(isset($event['data']['force_approved_state']), 'Post from GP IP should be approved.');
	}
}