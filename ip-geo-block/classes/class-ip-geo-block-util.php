<?php
/**
 * IP Geo Block - Utilities
 *
 * @package   IP_Geo_Block
 * @author    tokkonopapa <tokkonopapa@yahoo.com>
 * @license   GPL-2.0+
 * @link      http://www.ipgeoblock.com/
 * @copyright 2013-2016 tokkonopapa
 */

class IP_Geo_Block_Util {

	/**
	 * Return local time of day.
	 *
	 */
	public static function localdate( $timestamp = FALSE, $fmt = NULL ) {
		static $offset = NULL;
		static $format = NULL;

		if ( NULL === $offset )
			$offset = wp_timezone_override_offset() * HOUR_IN_SECONDS;

		if ( NULL === $format )
			$format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );

		return date_i18n( $fmt ? $fmt : $format, $timestamp ? (int)$timestamp + $offset : FALSE );
	}

	/**
	 * Download zip/gz file, uncompress and save it to specified file
	 *
	 * @param string $url URL of remote file to be downloaded.
	 * @param array $args request headers.
	 * @param string $filename full path to the downloaded file.
	 * @param int $modified time of last modified on the remote server.
	 * @return array status message.
	 */
	public static function download_zip( $url, $args, $filename, $modified ) {
		if ( ! function_exists( 'download_url' ) )
			include_once( ABSPATH . 'wp-admin/includes/file.php' );

		// if the name of src file is changed, then update the dst
		if ( basename( $filename ) !== ( $base = pathinfo( $url, PATHINFO_FILENAME ) ) ) {
			$filename = dirname( $filename ) . '/' . $base;
		}

		// check file
		if ( ! file_exists( $filename ) )
			$modified = 0;

		// set 'If-Modified-Since' request header
		$args += array(
			'headers'  => array(
				'If-Modified-Since' => gmdate( DATE_RFC1123, (int)$modified ),
			),
		);

		// fetch file and get response code & message
		$src = wp_remote_head( ( $url = esc_url_raw( $url ) ), $args );

		if ( is_wp_error( $src ) )
			return array(
				'code' => $src->get_error_code(),
				'message' => $src->get_error_message(),
			);

		$code = wp_remote_retrieve_response_code   ( $src );
		$mssg = wp_remote_retrieve_response_message( $src );
		$data = wp_remote_retrieve_header( $src, 'last-modified' );
		$modified = $data ? strtotime( $data ) : $modified;

		if ( 304 == $code )
			return array(
				'code' => $code,
				'message' => __( 'Your database file is up-to-date.', IP_Geo_Block::TEXT_DOMAIN ),
				'filename' => $filename,
				'modified' => $modified,
			);

		elseif ( 200 != $code )
			return array(
				'code' => $code,
				'message' => $code.' '.$mssg,
			);

		// downloaded and unzip
		try {
			// download file
			$src = download_url( $url );

			if ( is_wp_error( $src ) )
				throw new Exception(
					$src->get_error_code() . ' ' . $src->get_error_message()
				);

			// get extension
			$args = strtolower( pathinfo( $url, PATHINFO_EXTENSION ) );

			// unzip file
			if ( 'gz' === $args && function_exists( 'gzopen' ) ) {
				if ( FALSE === ( $gz = gzopen( $src, 'r' ) ) )
					throw new Exception(
						sprintf(
							__( 'Unable to read %s. Please check permission.', IP_Geo_Block::TEXT_DOMAIN ),
							$src
						)
					);

				if ( FALSE === ( $fp = @fopen( $filename, 'wb' ) ) )
					throw new Exception(
						sprintf(
							__( 'Unable to write %s. Please check permission.', IP_Geo_Block::TEXT_DOMAIN ),
							$filename
						)
					);

				// same block size in wp-includes/class-http.php
				while ( $data = gzread( $gz, 4096 ) )
					fwrite( $fp, $data, strlen( $data ) );

				gzclose( $gz );
				fclose ( $fp );
			}

			elseif ( 'zip' === $args && class_exists( 'ZipArchive' ) ) {
				// https://codex.wordpress.org/Function_Reference/unzip_file
				WP_Filesystem();
				$ret = unzip_file( $src, dirname( $filename ) ); // @since 2.5

				if ( is_wp_error( $ret ) )
					throw new Exception(
						$ret->get_error_code() . ' ' . $ret->get_error_message()
					);
			}

			@unlink( $src );
		}

		// error handler
		catch ( Exception $e ) {
			if ( 'gz' === $args && function_exists( 'gzopen' ) ) {
				! empty( $gz ) and gzclose( $gz );
				! empty( $fp ) and fclose ( $fp );
			}

			! is_wp_error( $src ) and @unlink( $src );

			return array(
				'code' => $e->getCode(),
				'message' => $e->getMessage(),
			);
		}

		return array(
			'code' => $code,
			'message' => sprintf(
				__( 'Last update: %s', IP_Geo_Block::TEXT_DOMAIN ),
				self::localdate( $modified )
			),
			'filename' => $filename,
			'modified' => $modified,
		);
	}

	/**
	 * Converts IP address to in_addr representation
	 *
	 */
	private static function inet_pton( $ip ) {
		// available on Windows platforms after PHP 5.3.0
		if ( function_exists( 'inet_pton' ) )
			return inet_pton( $ip );

		// http://stackoverflow.com/questions/14459041/inet-pton-replacement-function-for-php-5-2-17-in-windows
		else {
			// ipv4
			if ( FALSE !== filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
				if ( FALSE === strpos( $ip, ':' ) ) {
					$ip = pack( 'N', ip2long( $ip ) );
				} else {
					$ip = explode( ':', $ip );
					$ip = pack( 'N', ip2long( $ip[ count( $ip ) - 1 ] ) );
				}
			}

			// ipv6
			elseif ( FALSE !== filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
				$ip = explode( ':', $ip );
				$parts = 8 - count( $ip );
				$res = '';
				$replaced = 0;
				foreach ( $ip as $seg ) {
					if ( $seg != '' ) {
						$res .= str_pad( $seg, 4, '0', STR_PAD_LEFT );
					} elseif ( $replaced == 0 ) {
						for ( $i = 0; $i <= $parts; $i++ )
							$res .= '0000';
						$replaced = 1;
					} elseif ( $replaced == 1 ) {
						$res .= '0000';
					}
				}
				$ip = pack( 'H' . strlen( $res ), $res );
			}
		}

		return $ip;
	}

	/**
	 * DNS lookup
	 *
	 */
	public static function gethostbyaddr( $ip ) {
		// available on Windows platforms after PHP 5.3.0
		if ( function_exists( 'gethostbyaddr' ) )
			$host = gethostbyaddr( $ip );

		// if not available
		if ( empty( $host ) ) {
			if ( function_exists( 'dns_get_record' ) ) {
				// generate in-addr.arpa notation
				if ( FALSE !== filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
					$ptr = implode( ".", array_reverse( explode( ".", $ip ) ) ) . ".in-addr.arpa";
				}

				elseif ( FALSE !== filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
					$ptr = self::inet_pton( $ip );
					$ptr = implode(".", array_reverse( str_split( bin2hex( $ptr ) ) ) ) . ".ip6.arpa";
				}

				if ( isset( $ptr ) and $ptr = @dns_get_record( $ptr, DNS_PTR ) ) {
					$host = $ptr[0]['target'];
				}
			}
		}

		// For compatibility with versions before PHP 5.3.0
		// on some operating systems, try the PEAR class Net_DNS
		if ( empty( $host ) ) {
			include_once( IP_GEO_BLOCK_PATH . 'includes/Net/DNS2.php' );

			// use google public dns
			$r = new Net_DNS2_Resolver(
				array( 'nameservers' => array( '8.8.8.8' ) )
			);

			try {
				$result = $r->query( $ip, 'PTR' );
			}
			catch ( Net_DNS2_Exception $e ) {
				$result = $e->getMessage();
			}

			if ( isset( $result->answer ) ) {
				foreach ( $result->answer as $obj ) {
					if ( 'PTR' === $obj->type ) {
						$host = $obj->ptrdname;
						break;
					}
				}
			}
		}

		return isset( $host ) ? $host : $ip;
	}

}