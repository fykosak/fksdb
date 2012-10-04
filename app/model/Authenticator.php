<?php
/**
 * Users authenticator.
 */
class Authenticator extends NObject implements IAuthenticator
{
	/** @var NConnection */
	private $database;



	public function __construct(NConnection $database)
	{
		$this->database = $database;
	}



	/**
	 * Performs an authentication.
	 * @return NIdentity
	 * @throws NAuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;
		$row = $this->database->table('users')->where('username', $username)->fetch();

		if (!$row) {
			throw new NAuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);
		}

		if ($row->password !== $this->calculateHash($password, $row->password)) {
			throw new NAuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
		}

		unset($row->password);
		return new NIdentity($row->id, $row->role, $row->toArray());
	}



	/**
	 * Computes salted password hash.
	 * @param  string
	 * @return string
	 */
	public static function calculateHash($password, $salt = NULL)
	{
		if ($password === NStrings::upper($password)) { // perhaps caps lock is on
			$password = NStrings::lower($password);
		}
		return crypt($password, ($tmp=$salt) ? $tmp : '$2a$07$' . NStrings::random(22));
	}

}
