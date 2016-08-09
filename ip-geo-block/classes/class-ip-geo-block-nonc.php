<?php
/**
 * IP Geo Block - Creates a cryptographic token and verification
 *
 * @package   IP_Geo_Block
 * @author    tokkonopapa <tokkonopapa@yahoo.com>
 * @license   GPL-2.0+
 * @link      http://www.ipgeoblock.com/
 * @copyright 2016 tokkonopapa
 */

class IP_Geo_Block_Nonce {

	/**
	 * Creates a cryptographic tied to the action, user, session, and time.
	 *
	 */
	public static function create_nonce( $action = -1 ) {
		$uid = self::get_current_user();
		$tok = self::get_session_token();
		$exp = self::nonce_tick();

		return substr( self::hash_nonce( $exp . '|' . $action . '|' . $uid . '|' . $tok ), -12, 10 );
	}

	/**
	 * Verify that correct nonce was used with time limit.
	 *
	 */
	public static function verify_nonce( $nonce, $action = -1 ) {
		$uid = self::get_current_user();
		$tok = self::get_session_token();
		$exp = self::nonce_tick();

		// Nonce generated 0-12 hours ago
		$expected = substr( self::hash_nonce( $exp . '|' . $action . '|' . $uid . '|' . $tok ), -12, 10 );

		// PHP 5 >= 5.6.0 or wp-includes/compat.php
		if ( function_exists( 'hash_equals' ) )
			return hash_equals( $expected, (string)$nonce );
		else
			return self::hash_equals( $expected, (string)$nonce );
	}

	/**
	 * Get hash of given string for nonce.
	 *
	 */
	private static function hash_nonce( $data ) {
		// PHP 5 >= 5.1.2, PECL hash >= 1.1 or wp-includes/compat.php
		if ( function_exists( 'hash_hmac' ) )
			return hash_hmac( 'md5', $data, NONCE_KEY . NONCE_SALT );
		else
			return self::hash_hmac( 'md5', $data, NONCE_KEY . NONCE_SALT );
	}

	/**
	 * Retrieve the current session token from the logged_in cookie.
	 *
	 */
	private static function get_session_token() {
		$cookie = self::parse_auth_cookie( 'logged_in' );
		return ! empty( $cookie['token'] ) ? $cookie['token'] : AUTH_KEY . AUTH_SALT;
	}

	private static function parse_auth_cookie( $scheme ) {
		foreach ( array_keys( $_COOKIE ) as $key ) {
			if ( FALSE !== strpos( $key, $scheme ) ) {
				$elements = explode( '|', $_COOKIE[ $key ] );
				if ( count( $elements ) === 4 ) {
					list( $username, $expiration, $token, $hmac ) = $elements;
					return compact( 'username', 'expiration', 'token', 'hmac' );
				}
			}
		}

		return FALSE;
	}

	/**
	 * Get the time-dependent variable for nonce creation.
	 *
	 */
	private static function nonce_tick() {
		return ceil( time() / ( DAY_IN_SECONDS / 2 ) );
	}

	/**
	 * Retrieve the current user identification.
	 *
	 */
	private static function get_current_user() {
		require_once( IP_GEO_BLOCK_PATH . 'classes/class-ip-geo-block-lkup.php' );

		$sum = 0;
		$num = '';

		foreach ( unpack( 'C*', IP_Geo_Block_Lkup::inet_pton( IP_Geo_Block::get_ip_address() ) ) as $byte ) {
			$sum += $byte;
			$num .= (string)( $byte % 10 );
		}

		$num += $sum;

		// add something which a visitor can't control
//		$num .= substr( SECURE_AUTH_KEY, 1, 6 ); // @since 2.6

		// add something unique
//		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) && is_string( $_SERVER['HTTP_USER_AGENT'] ) )
//			$num .= preg_replace( '/[^-,:!*+\.\/\w\s]/', '', $_SERVER['HTTP_USER_AGENT'] );

		return $num;
	}

	/**
	 * Alternative function of hash_equals() from wp-includes/compat.php
	 *
	 * @link http://php.net/manual/en/function.hash-equals.php#115635
	 */
	private static function hash_equals( $a, $b ) {
		if( ( $i = strlen( $a ) ) !== strlen( $b ) )
			return FALSE;

		$exp = $a ^ $b; // 1 === strlen( 'a' ^ 'ab' )
		$ret = 0;

		while ( --$i >= 0 )
			$ret |= ord( $exp[ $i ] );

		return 0 === $ret;
	}

	/**
	 * Alternative function of hash_hmac() from wp-includes/compat.php
	 *
	 * @link http://php.net/manual/en/function.hash-hmac.php#93440
	 */
	private static function hash_hmac( $algo, $data, $key, $raw_output = FALSE ) {
		$packs = array( 'md5' => 'H32', 'sha1' => 'H40' );

		if ( ! isset( $packs[ $algo ] ) )
			return FALSE;

		$pack = $packs[ $algo ];

		if ( strlen( $key ) > 64 )
			$key = pack( $pack, $algo( $key ) );

		$key = str_pad( $key, 64, chr(0) );

		$ipad = substr( $key, 0, 64 ) ^ str_repeat( chr(0x36), 64 );
		$opad = substr( $key, 0, 64 ) ^ str_repeat( chr(0x5C), 64 );

		$hmac = $algo( $opad . pack( $pack, $algo( $ipad . $data ) ) );

		return $raw_output ? pack( $pack, $hmac ) : $hmac;
	}

}