<?php namespace JFusion\Core;
/**
 * @package     Joomla.Libraries
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use JFusion\User\Userinfo;

use Joomla\Event\Event;
use Joomla\Input\Input;

/**
 * Joomla! CMS Application class
 *
 * @package     Joomla.Libraries
 * @subpackage  Application
 * @since       3.2
 */
class Application
{
	/**
	 * @var    Application  The application instance.
	 * @since  11.3
	 */
	protected static $instance;
	/**
	 * @var boolean $messageStatus
	 * @since  11.3
	 */
	private $messageStatus = true;

	/**
	 * @var Input $input
	 */
	public $input = null;

	/**
	 * Class constructor.
	 *
	 * @param   mixed  $input   An optional argument to provide dependency injection for the application's
	 *                          input object.  If the argument is a JInput object that object will become
	 *                          the application's input object, otherwise a default input object is created.
	 * @since   3.2
	 */
	public function __construct(Input $input = null)
	{
		// If a input object is given use it.
		if ($input instanceof Input)
		{
			$this->input = $input;
		}
		// Create the input based on the application logic.
		else
		{
			$this->input = new Input;
		}
	}

	/**
	 * Enqueue a system message.
	 *
	 * @param   string  $msg   The message to enqueue.
	 * @param   string  $type  The message type. Default is message.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function enqueueMessage($msg, $type = 'message')
	{
		if ($this->messageStatus) {
			$event = new Event('onApplicationEnqueueMessage');
			$event->addArgument('message', $msg);
			$event->addArgument('type', $type);
			Factory::getDispatcher()->triggerEvent($event);
		}
	}

	/**
	 * @param boolean $status
	 */
	public function messageStatus($status)
	{
		$this->messageStatus = $status;
	}

	/**
	 * Returns a reference to the global Application object, only creating it if it doesn't already exist.
	 *
	 * @return  Application
	 */
	public static function getInstance()
	{
		if (!static::$instance)
		{
			static::$instance = new Application();
		}
		return static::$instance;
	}

	/**
	 * Is admin interface?
	 *
	 * @return  boolean  True if this application is administrator.
	 *
	 * @since   3.2
	 */
	public function isAdmin()
	{
		$event = new Event('onApplicationIsAdmin');
		Factory::getDispatcher()->triggerEvent($event);
		return $event->getArgument('admin', false);
	}

	/**
	 * Login authentication function.
	 *
	 * Username and encoded password are passed the onUserLogin event which
	 * is responsible for the user validation. A successful validation updates
	 * the current session record with the user's details.
	 *
	 * Username and encoded password are sent as credentials (along with other
	 * possibilities) to each observer (authentication plugin) for user
	 * validation.  Successful validation will update the current session with
	 * the user details.
	 *
	 * @param   array  $credentials  Array('username' => string, 'password' => string)
	 * @param   array  $options      Array('remember' => boolean)
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function login($credentials, $options = array())
	{
		$event = new Event('onApplicationLogin');

		$event->addArgument('credentials', $credentials);
		$event->addArgument('options', $options);

		Factory::getDispatcher()->triggerEvent($event);

		return $event->getArgument('status', false);
	}

	/**
	 * Logout authentication function.
	 *
	 * Passed the current user information to the onUserLogout event and reverts the current
	 * session record back to 'anonymous' parameters.
	 * If any of the authentication plugins did not successfully complete
	 * the logout routine then the whole method fails. Any errors raised
	 * should be done in the plugin as this provides the ability to give
	 * much more information about why the routine may have failed.
	 *
	 * @param   integer  $userid   The user to load - Can be an integer or string - If string, it is converted to ID automatically
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.2
	 */
	public function logout($userid = null)
	{
		$event = new Event('onApplicationLogout');

		$event->addArgument('userid', $userid);

		Factory::getDispatcher()->triggerEvent($event);

		return $event->getArgument('status', false);
	}

	/**
	 * Redirect to another URL.
	 *
	 * If the headers have not been sent the redirect will be accomplished using a "301 Moved Permanently"
	 * or "303 See Other" code in the header pointing to the new location. If the headers have already been
	 * sent this will be accomplished using a JavaScript statement.
	 *
	 * @param   string   $url    The URL to redirect to. Can only be http/https URL
	 * @param   boolean  $moved  True if the page is 301 Permanently Moved, otherwise 303 See Other is assumed.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function redirect($url, $moved = false)
	{
		$event = new Event('onApplicationRedirect');

		$event->addArgument('url', $url);
		$event->addArgument('moved', $moved);

		Factory::getDispatcher()->triggerEvent($event);
	}

	/**
	 * Retrieves the source of the avatar for a Joomla supported component
	 *
	 * @return string url of the default avatar
	 */
	public function getDefaultAvatar()
	{
		$event = new Event('onApplicationGetDefaultAvatar');
		Factory::getDispatcher()->triggerEvent($event);

		return $event->getArgument('avatar', false);
	}

	/**
	 * Creates a JFusion Joomla compatible URL
	 *
	 * @param string  $url    string url to be parsed
	 * @param string  $itemid string itemid of the JFusion menu item or the name of the plugin for direct link
	 * @param string  $jname  optional jname if available to prevent having to find it based on itemid
	 * @param boolean $route  boolean optional switch to send url through JRoute::_() (true by default)
	 * @param boolean $xhtml  boolean optional switch to turn & into &amp; if $route is true (true by default)
	 *
	 * @return string Parsed URL
	 */
	public function routeURL($url, $itemid, $jname = '', $route = true, $xhtml = true)
	{
		$event = new Event('onApplicationRoute');

		$event->setArgument('url', $url);
		$event->setArgument('itemid', $itemid);
		$event->setArgument('jname', $jname);
		$event->setArgument('route', $route);
		$event->setArgument('xhtml', $xhtml);

		Factory::getDispatcher()->triggerEvent($event);

		return $event->getArgument('url');
	}

	/**
	 * Load Script language
	 *
	 * @param string|array $keys
	 */
	public static function loadScriptLanguage($keys) {
		if (!empty($keys)) {
			$event = new Event('onApplicationLoadScriptLanguage');

			$event->setArgument('keys', $keys);

			Factory::getDispatcher()->triggerEvent($event);
		}
	}

	/**
	 * Load Script language
	 */
	public static function getUser() {
		$event = new Event('onApplicationGetUser');

		Factory::getDispatcher()->triggerEvent($event);

		$user = $event->getArgument('user', null);

		if ($user instanceof Userinfo) {
			return $user;
		} else {
			return new Userinfo(null);
		}
	}
}
