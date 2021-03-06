<?php
/**
 * Ldap strategy for Opauth
 *
 * More information on Opauth: http://opauth.org
 *
 * @copyright    Copyright © 2015 FlexCoders Ltd (http://flexcoders.co.uk)
 * @link         http://flexcoders.co.uk
 * @package      Opauth.LdapStrategy
 * @license      MIT License
 */

/**
 * Ldap strategy for Opauth
 *
 * @package			Opauth.Ldap
 */
class LdapStrategy extends OpauthStrategy{

	/**
	 * Compulsory config keys, listed as unassociative arrays
	 */
	public $expects = array(
		'server',
		'port',
		'bind-cn',
		'bind-dn',
		'bind-password',
		'attributes',
	);

	/**
	 * Optional config keys, without predefining any default values.
	 */
	public $optionals = array(
		'options',
	);

	/**
	 * Optional config keys with respective default values, listed as associative arrays
	 * eg. array('scope' => 'email');
	 */
	public $defaults = array(
		'expiry' => 86400,
	);

	/**
	 * ldap connection object
	 */
	protected $ldap;

	/**
	 * Auth request
	 */
	public function request()
	{
		// bail out if we didn't get a username and password passed
		if (empty($this->env['username']) or empty($this->env['password']))
		{
			$error = array(
				'code' => 'credentials_error',
				'message' => 'LDAP user credentials not passed in the request',
				'raw' => array(),
			);

			$this->errorCallback($error);
		}

		// create an ldap binding
		$this->ldap_login(
			str_replace('$username$', $this->env['username'], $this->strategy['bind-cn']).','.$this->strategy['bind-dn'],
			str_replace('$password$', $this->env['password'], $this->strategy['bind-password']),
			$this->strategy['server'],
			isset($this->strategy['options']) ? $this->strategy['options'] : array()
		);

		// fetch the users attributes
		try
		{
			$attrs = ldap_search(
				$this->ldap,
				$this->strategy['bind-dn'],
				'('.str_replace('$username$', $this->env['username'], $this->strategy['bind-cn']).')'
			);

			$attrs = ldap_get_entries($this->ldap, $attrs);
		}
		catch (Exception $e)
		{
			$error = array(
				'code' => 'bind_error',
				'message' => $e->getMessage(),
				'raw' => array(),
			);

			$this->errorCallback($error);
		}

		// attribute mapping
		$mapping = array_merge(array(
			'uid'       => 'uid',
			'name'      => 'name',
			'email'     => 'email',
			'username'  => 'username',
		), $this->strategy['attributes']);

		// fetch the attribute data
		foreach ($mapping as $k => $v)
		{
			if (isset($attrs[0][$v][0]))
			{
				$mapping[$k] = $attrs[0][$v][0];
			}
			else
			{
				$error = array(
					'code' => 'fetch_error',
					'message' => 'Required attribute "'.$k.'" not found in LDAP search',
					'raw' => array(),
				);

				$this->errorCallback($error);
			}
		}

		// construct the response array
		$this->auth = array(
			'uid' => $mapping['uid'],
			'info' => array(
				'name' => $mapping['name'],
				'email' => $mapping['email'],
				'nickname' => $mapping['username'],
			),
			'credentials' => array(
				'token' => 0,
				'expires' => date('c', time() + isset($this->strategy['expiry']) ? $this->strategy['expiry'] : 86400)
			),
			'raw' => $attrs
		);

		// and process the callback
		$this->callback();
	}

	/**
	 * Execute the LDAP login
	 */
	protected function ldap_login($username, $password, $server, $options = array())
	{
		// create the ldap instance if needed
		if ( ! $this->ldap)
		{
			// create the connection object
			$this->ldap = ldap_connect($server);

			// set any LDAP options passed
			foreach ($options as $k => $v)
			{
				ldap_set_option($this->ldap, $k, $v);
			}
		}

		// create the binding
		try
		{
			ldap_bind($this->ldap, $username, $password);
		}
		catch (Exception $e)
		{
			$error = array(
				'code' => 'bind_error',
				'message' => $e->getMessage(),
				'raw' => array(),
			);

			$this->errorCallback($error);
		}
	}
}
