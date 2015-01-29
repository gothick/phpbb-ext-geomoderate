<?php
/**
 *
 * @package phpBB Extension - GeoModerate
 * @copyright (c) 2015 Matt Gibson gothick@gothick.org.uk
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */
namespace gothick\geomoderate\event;

/**
 *
 * @ignore
 *
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Event listener
 */
class main_listener implements EventSubscriberInterface
{

	static public function getSubscribedEvents ()
	{
		return array(
				'core.posting_modify_submit_post_before' => 'check_submitted_post'
		);
	}

	/* @var \phpbb\user */
	protected $user;

	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\log\log_interface */
	protected $log;

		/* @var \phpbb\auth\auth */
	protected $auth;

	/* @var \Symfony\Component\DependencyInjection\ContainerInterface */
	protected $phpbb_container;

	/**
	 * Constructor
	 *
	 * Lightweight initialisation of the API key and user ID.
	 * Heavy lifting is done only if the user actually tries
	 * to post a message.
	 *
	 * @param \phpbb\user $user
	 * @param \phpbb\config\config $config
	 * @param \phpbb\log\log_interface $log
	 * @param \phpbb\auth\auth $auth
	 * @param \Symfony\Component\DependencyInjection\ContainerInterface $phpbb_container
	 */
	public function __construct (
			\phpbb\user $user,
			\phpbb\config\config $config,
			\phpbb\log\log_interface $log,
			\phpbb\auth\auth $auth,
			\Symfony\Component\DependencyInjection\ContainerInterface $phpbb_container
		)
	{
		$this->user = $user;
		$this->config = $config;
		$this->log = $log;
		$this->auth = $auth;
		$this->phpbb_container = $phpbb_container;
	}

	/**
	 * The main event:
	 *
	 * Check if the user is a board admin or moderator. If so, let it through.
	 * If not, load GeoIP database, and look up user's country from their IP.
	 * If the country is in the configured list of countries to moderate,
	 * send the post to the moderation queue.
	 *
	 * If anything goes wrong, assume we should be approving the post, i.e.
	 * fail safe on exceptions, etc.
	 *
	 * @param unknown $event
	 */
	public function check_submitted_post ($event)
	{
		// Skip the check for anyone who's a moderator or an administrator. If your
		// admins and moderators are posting spam, you've got bigger problems...
		if (! ($this->auth->acl_getf_global('m_') ||
				$this->auth->acl_getf_global('a_')))
		{
			// Load GeoIP database
			// Look up $user->ip
			// Check list of countries
			// Approve or disapprove.
			$data = $event['data'];

			$moderate = false;
			$country_code = false;
			try
			{
				/* @var $reader \GeoIp2\Database\Reader */
				$reader = $this->phpbb_container->get(
					'gothick.geomoderate.geoip2.reader',
					ContainerInterface::NULL_ON_INVALID_REFERENCE
				);
				$record = $reader->country($this->user->ip);
				$country_code = $record->country->isoCode;
			} catch (\Exception $e)
			{
				// If anything went wrong, we just log the error in case
				// anyone wants to figure out why...
				$this->log->add('critical',
						$this->user->data['user_id'],
						$this->user->data['session_ip'],
						'GEOMODERATE_LOG_LOOKUP_FAILED', false,
						array(
								$e->getMessage()
						));
			}

			// TODO: Check configured list of $country_codes
			// http://dev.maxmind.com/geoip/legacy/codes/iso3166/
			$is_spam = false;

			if ($is_spam)
			{
				// Whatever the post status was before, this will override it
				// and mark it as unapproved.
				$data['force_approved_state'] = ITEM_UNAPPROVED;
				$event['data'] = $data;

				// Note our action in the moderation log
				if ($event['mode'] == 'post' || ($event['mode'] == 'edit' &&
						$data['topic_first_post_id'] == $data['post_id']))
				{
					$log_message = 'LOG_TOPIC_DISAPPROVED';
				}
				else
				{
					$log_message = 'LOG_POST_DISAPPROVED';
				}

				// We need the ACP langauge pack for the moderation message, as it's
				// destined for the ACP log page.
				$this->user->add_lang_ext('gothick/geomoderate', 'info_acp_geomoderate');

				$this->log->add('mod',
						$this->user->data['user_id'],
						$this->user->data['session_ip'],
						$log_message,
						false,
						array(
								$data['topic_title'],
								$this->user->lang('GEOMODERATE_DISAPPROVED'),
								$this->user->data['username']
						));
			}
		}
	}
}
