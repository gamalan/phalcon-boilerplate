<?php

namespace Application\Utils;

use Application\Core\Subscriber;
use Application\Core\SubscriberField;
use Application\Core\UserInfo;

/**
 * Email utility
 * User: rizts
 * Date: 20/03/17
 * Time: 14:15
 */
class EmailUtil {

	/**
	 * Parse email content to get URLs
	 *
	 * @param $content = email content
	 *
	 * @return array|false
	 */
	public static function parseContentUrls( $content ) {
		$links = [];
		if ( preg_match_all( '#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|\'|"))#', $content, $matches2 ) ) {
			foreach ( $matches2[0] as $lnk ) {
				if ( self::isValidUrl( $lnk ) ) {
					$links[] = ( substr( $lnk, strlen( $lnk ) - 1 ) == "'" || substr( $lnk, strlen( $lnk ) - 1 ) == "\"" ? substr( $lnk, 0, strlen( $lnk ) - 1 ) : $lnk );
				}
			}

			return array_values( array_unique( $links ) );
		}

		return false;
	}

	private static function isValidUrl( $url ) {
		$parsed_url = parse_url( $url );

		return isset( $parsed_url['scheme'] );
	}

	public static function replaceCustomField( $email_subscriber, $user_guid, $content, $message, $campaign ) {
		$temp        = $content;
		$result      = $temp;
		$csubscriber = new Subscriber();
		$hash_email  = hash( 'sha256', $email_subscriber );
		$subs        = $csubscriber->getActiveSubscriberByHashAndUserGUIDEFL( $user_guid, $hash_email, substr( $email_subscriber, 0, 1 ), substr( $hash_email, 0, 2 ) );
		if ( is_object( $subs ) ) {
			$subfieldset = $csubscriber->getSubscriberCustomValues( $user_guid, $subs->id );
			$lookfor     = [];
			foreach ( $subfieldset as $fieldval ) {
				$lookfor[ $fieldval->personalization_tag ] = $fieldval->value;
			}
			$start = '::';
			$end   = '::';
			preg_match_all( '#(' . preg_quote( $start ) . ')(.*?)(' . preg_quote( $end ) . ')#si', $temp, $matches, PREG_SET_ORDER );
			$search   = [];
			$replace  = [];
			$efl      = substr( $subs->email, 0, 1 );
			$htfl     = substr( $subs->email_hash, 0, 2 );
			$efl_htfl = '/' . $efl . '/' . $htfl;
			$efl_htfl = str_replace( '//', '', $efl_htfl );
			foreach ( $matches as $match ) {
				$search[] = $match[0];
				switch ( $match[2] ) {
					case "full_name":
						$replace[] = $subs->full_name;
						break;
					case "email":
						$replace[] = $subs->email;
						break;
					case "today":
						$replace[] = date( "d-m-Y" );
						break;
					case "timestamp":
						$replace[] = date( "d-m-Y h:i:s" );
						break;
					case "campaign_name":
						$replace[] = $campaign->title;
						break;
					case "message_subject":
						$replace[] = $message->subject;
						break;
					case "webcopy":
						$replace[] = getenv( "STATIC_BASE_URI" ) . 'webcopy/' . $message->track_id . '/' . hash( "sha256", $email_subscriber ) . $efl_htfl;
						break;
					case "footer_info":
						$userinfo  = new UserInfo();
						$replace[] = $userinfo->getHtmlUserInfo( $user_guid );
						break;
					default:
						if ( isset( $lookfor[ $match[2] ] ) ) {
							$replace[] = $lookfor[ $match[2] ];
						} else {
							$replace[] = "";
						}
						break;
				}
			}
			$result = str_replace( $search, $replace, $temp );
		}

		return $result;
	}

	public static function replaceCustomFieldWithoutSubs( $content, $message, $campaign ) {
		$temp   = $content;
		$result = $temp;

		$start = '::';
		$end   = '::';
		preg_match_all( '#(' . preg_quote( $start ) . ')(.*?)(' . preg_quote( $end ) . ')#si', $temp, $matches, PREG_SET_ORDER );
		$search  = [];
		$replace = [];
		foreach ( $matches as $match ) {
			$search[] = $match[0];
			switch ( $match[2] ) {
				case "today":
					$replace[] = date( "d-m-Y" );
					break;
				case "timestamp":
					$replace[] = date( "d-m-Y h:i:s" );
					break;
				case "campaign_name":
					$replace[] = $campaign->title;
					break;
				case "message_subject":
					$replace[] = $message->subject;
					break;
				default:
					$replace[] = "";
					break;
			}
		}
		$result = str_replace( $search, $replace, $temp );

		return $result;
	}

	public function dumped() {

	}
}
