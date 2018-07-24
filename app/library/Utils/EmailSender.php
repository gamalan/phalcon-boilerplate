<?php

namespace Application\Utils;


use Application\Traits\DataCleanerTrait;
use Swift_Message;
use Swift_SmtpTransport;
use Phalcon\Mvc\User\Component;
use Swift_Mailer;
use Swift_Signers_DKIMSigner;

class EmailSender extends Component {
	protected $transport;
	use DataCleanerTrait;

	public function __construct() {
		$this->transport = false;
	}

	public function confirmationEmailSend( $content, $content_plain, $subject, $to ) {
		// Settings
		$config       = container()->getShared( 'config' );
		$mailSettings = $config->get( 'mail' );
		// Create the message
		$message = Swift_Message::newInstance()
		                        ->attachSigner( $this->createSigner() )
		                        ->setSubject( $subject )
		                        ->setTo( $this->full_trim( $to ) )
		                        ->setFrom( [
			                        $this->full_trim( $mailSettings->fromEmail ) => $mailSettings->fromName
		                        ] )
		                        ->setBody( $content )
		                        ->setContentType( 'text/html' )
		                        ->addPart( $content_plain, 'text/plain' );
		if ( ! $this->transport ) {
			$this->transport = $this->createTransport( $mailSettings->host, $mailSettings->port, $mailSettings->encryption,
				$mailSettings->username, $mailSettings->password, $mailSettings->ssl_option );
		}
		//print_r($mailSettings);
		//print_r($this->transport);
		// Create the Mailer using your created Transport
		$mailer = Swift_Mailer::newInstance( $this->transport );

		return $mailer->send( $message );
	}

	public function notificationEmailSend( $content, $content_plain, $subject, $to ) {
		// Settings
		$config       = container()->getShared( 'config' );
		$mailSettings = $config->get( 'mail' );
		// Create the message
		$message = Swift_Message::newInstance()
		                        ->attachSigner( $this->createSigner() )
		                        ->setSubject( $subject )
		                        ->setTo( $this->full_trim( $to ) )
		                        ->setFrom( [
			                        $this->full_trim( "no-reply@kirim.email" ) => "Kirim.Email Notification"
		                        ] )
		                        ->setBody( $content )
		                        ->setContentType( 'text/html' )
		                        ->addPart( $content_plain, 'text/plain' );
		if ( ! $this->transport ) {
			$this->transport = $this->createTransport( $mailSettings->host, $mailSettings->port, $mailSettings->encryption,
				$mailSettings->username, $mailSettings->password, $mailSettings->ssl_option );
		}
		//print_r($mailSettings);
		//print_r($this->transport);
		// Create the Mailer using your created Transport
		$mailer = Swift_Mailer::newInstance( $this->transport );

		return $mailer->send( $message );
	}

	public function sendExportedSubscriber( $file_link, $to ) {
		$subject = 'Exported Subscribers File';
		$content = $content_plain = 'Here is subscribers you exported. You can download it by click <a href="' . $file_link . '" target="_blank">this link</a>';
		// Settings
		$config       = container()->getShared( 'config' );
		$mailSettings = $config->get( 'mail' );
		// Create the message
		$message = Swift_Message::newInstance()
		                        ->attachSigner( $this->createSigner() )
		                        ->setSubject( $subject )
		                        ->setTo( $this->full_trim( $to ) )
		                        ->setFrom( [
			                        $this->full_trim( "no-reply@kirim.email" ) => "Kirim.Email Notification"
		                        ] )
		                        ->setBody( $content )
		                        ->setContentType( 'text/html' )
		                        ->addPart( $content_plain, 'text/plain' );
		if ( ! $this->transport ) {
			$this->transport = $this->createTransport( $mailSettings->host, $mailSettings->port, $mailSettings->encryption,
				$mailSettings->username, $mailSettings->password, $mailSettings->ssl_option );
		}
		//print_r($mailSettings);
		//print_r($this->transport);
		// Create the Mailer using your created Transport
		$mailer = Swift_Mailer::newInstance( $this->transport );

		return $mailer->send( $message );
	}

	public function subscriberConfirmationSend( $content, $content_plain, $subject, $to, $fromEmail, $fromName ) {
		// Settings
		$config       = container()->getShared( 'config' );
		$mailSettings = $config->get( 'mail' );
		// Create the message
		$message = Swift_Message::newInstance()
		                        ->attachSigner( $this->createSigner() )
		                        ->setSubject( $subject )
		                        ->setTo( $this->full_trim( $to ) )
		                        ->setReturnPath( $this->full_trim( $mailSettings->fromEmail ) )
		                        ->setFrom( [
			                        $this->full_trim( $fromEmail ) => $fromName
		                        ] )
		                        ->setBody( $content )
		                        ->setContentType( 'text/html' )
		                        ->addPart( $content_plain, 'text/plain' );
		if ( ! $this->transport ) {
			$this->transport = $this->createTransport( $mailSettings->host, $mailSettings->port, $mailSettings->encryption,
				$mailSettings->username, $mailSettings->password, $mailSettings->ssl_option );
		}
		//print_r($mailSettings);
		//print_r($this->transport);
		// Create the Mailer using your created Transport
		$mailer = Swift_Mailer::newInstance( $this->transport );

		return $mailer->send( $message );
	}

	public function formForwarderSend( $content, $content_plain, $subject, $to, $fromEmail, $fromName ) {
		// Settings
		$config       = container()->getShared( 'config' );
		$mailSettings = $config->get( 'mail' );
		// Create the message
		$message = Swift_Message::newInstance()
		                        ->attachSigner( $this->createSigner() )
		                        ->setSubject( $subject )
		                        ->setTo( $this->full_trim( $to ) )
		                        ->setReturnPath( $this->full_trim( $mailSettings->fromEmail ) )
		                        ->setFrom( [
			                        $this->full_trim( $fromEmail ) => $fromName
		                        ] )
		                        ->setBody( $content )
		                        ->setContentType( 'text/html' )
		                        ->addPart( $content_plain, 'text/plain' );
		if ( ! $this->transport ) {
			$this->transport = $this->createTransport( $mailSettings->host, $mailSettings->port, $mailSettings->encryption,
				$mailSettings->username, $mailSettings->password, $mailSettings->ssl_option );
		}
		//print_r($mailSettings);
		//print_r($this->transport);
		// Create the Mailer using your created Transport
		$mailer = Swift_Mailer::newInstance( $this->transport );

		return $mailer->send( $message );
	}

	public function sendCampaignEmail( $return_path, $from, $fromName, $to, $subject, $content, $plain, $header, $mailSettings ) {
		// Create the message
		$message  = Swift_Message::newInstance()
		                         ->attachSigner( $this->createSigner() )
		                         ->setSubject( $subject )
		                         ->setTo( $this->full_trim( $to ) )
		                         ->setReplyTo( [
			                         $this->full_trim( $from ) => $fromName
		                         ] )
		                         ->setFrom( [
			                         $this->full_trim( $from ) => $fromName
		                         ] )
		                         ->setBody( $content )
		                         ->setContentType( 'text/html' )
		                         ->addPart( $plain, 'text/plain' )
		                         ->setReturnPath( $return_path );
		$m_header = $message->getHeaders();
		foreach ( $header as $header_key => $header_val ) {
			$m_header->addTextHeader( $header_key, $header_val );
		}
		if ( ! $this->transport ) {
			$this->transport = $this->createTransport( $mailSettings['host'], $mailSettings['port'], $mailSettings['encryption'],
				$mailSettings['username'], $mailSettings['password'], $mailSettings['ssl_option'] );
		}
		$mailer = Swift_Mailer::newInstance( $this->transport );

		return $mailer->send( $message );
	}

	public function sendAutomatedEmail( $content, $content_plain, $subject, $from, $to ) {
		// Settings
		$config       = container()->getShared( 'config' );
		$mailSettings = $config->get( 'mail' );
		if ( is_null( $from ) ) {
			return false;
		}
		// Create the message
		$message = Swift_Message::newInstance()
		                        ->attachSigner( $this->createSigner() )
		                        ->setSubject( $subject )
		                        ->setTo( $this->full_trim( $to ) )
		                        ->setFrom( [
			                        $this->full_trim( $from->email ) => $from->full_name
		                        ] )
		                        ->setBody( $content )
		                        ->setContentType( 'text/html' )
		                        ->addPart( $content_plain, 'text/plain' );
		if ( ! $this->transport ) {
			$this->transport = $this->createTransport( $mailSettings->host, $mailSettings->port, $mailSettings->encryption,
				$mailSettings->username, $mailSettings->password, $mailSettings->ssl_option );
		}

		// Create the Mailer using your created Transport
		$mailer = Swift_Mailer::newInstance( $this->transport );

		return $mailer->send( $message );
	}

	public function previewSend( $content, $content_plain, $subject, $to, $fromEmail, $fromName ) {
		// Settings
		$config       = container()->getShared( 'config' );
		$mailSettings = $config->get( 'mail' );
		// Create the message
		$message = Swift_Message::newInstance()
		                        ->attachSigner( $this->createSigner() )
		                        ->setSubject( $subject )
		                        ->setTo( $this->full_trim( $to ) )
		                        ->setReturnPath( $this->full_trim( $mailSettings->fromEmail ) )
		                        ->setFrom( [
			                        $this->full_trim( $fromEmail ) => $fromName
		                        ] )
		                        ->setBody( $content )
		                        ->setContentType( 'text/html' )
		                        ->addPart( $content_plain, 'text/plain' );
		if ( ! $this->transport ) {
			$this->transport = $this->createTransport( $mailSettings->host, $mailSettings->port, $mailSettings->encryption,
				$mailSettings->username, $mailSettings->password, $mailSettings->ssl_option );
		}
		//print_r($mailSettings);
		//print_r($this->transport);
		// Create the Mailer using your created Transport
		$mailer = Swift_Mailer::newInstance( $this->transport );

		return $mailer->send( $message );
	}

	public function createTransport( $host, $port, $encryption, $username, $password, $ssl_options = null ) {
		$config       = container()->getShared( 'config' );
		$mailSettings = $config->get( 'mail' );
		if ( $ssl_options == null ) {
			$ssl_options = $mailSettings->ssl_option;
		}

		return Swift_SmtpTransport::newInstance(
			$host,
			$port,
			$encryption
		)->setUsername( $this->full_trim( $username ) )
		                          ->setPassword( $this->full_trim( $password ) )
		                          ->setStreamOptions( [ $ssl_options ] );
	}

	protected function createSigner() {
		$privateKey = file_get_contents( BASE_DIR . 'dkim/senddefault.private' );
		$domainName = 'kirim.email';
		$selector   = 'senddefault';
		$signer     = new Swift_Signers_DKIMSigner( $privateKey, $domainName, $selector );

		return $signer;
	}

	protected function full_trim( $string ) {
		return $this->cleanDataString( trim( $string, " \t\n\r\0\x0B0\xA0" ) );
	}
}