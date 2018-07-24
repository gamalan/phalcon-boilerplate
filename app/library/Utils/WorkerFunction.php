<?php

/**
 * Created by PhpStorm.
 * User: gamalan
 * Date: 3/27/17
 * Time: 7:49 PM
 */

namespace Application\Utils;

use Box\Spout\Common\Type;
use Box\Spout\Writer\WriterFactory;
use Application\Core\AutomationLog;
use Application\Core\AutomationStep;
use Application\Core\AutoresponderList;
use Application\Core\Autoresponder;
use Application\Core\AutoresponderRecipient;
use Application\Core\Campaign;
use Application\Core\CampaignList;
use Application\Core\CampaignSegment;
use Application\Core\CampaignRecipient;
use Application\Core\ConfirmationEmails;
use Application\Core\Forms;
use Application\Core\GeolocationDb;
use Application\Core\Lists;
use Application\Core\Segments;
use Application\Core\Subscriber;
use Application\Core\SubscriberField;
use Application\Core\SubscriberFieldValues;
use Application\Core\SubscriberGeoData;
use Application\Core\SubscriberList;
use Application\Core\SubscriberTag;
use Application\Core\User;
use Application\Core\UserInfo;
use Application\Core\UserSender;
use Application\Utils\AutomatedProcessor;
use Application\Utils\EmailSender;
use Application\Utils\SimpleCrypt;
use Application\Core\UserNotification;
use Application\Models\Subscribers;
use Application\Traits\DataCleanerTrait;
use Application\Traits\SupervisordDebugTrait;
use League\HTMLToMarkdown\HtmlConverter;
use Phalcon\Mvc\User\Component;
use Phalcon\Queue\Beanstalk\Job;

class WorkerFunction extends Component {

	use DataCleanerTrait;

	use SupervisordDebugTrait;

	public function echoHello( $name ) {
		echo "Hello " . $name . "\n";
	}

	/**
	 * @param $params array
	 * @param $job Job
	 */
	public function deleteSubscriberListByListId( $params, $job ) {
		$subscriber_list = new SubscriberList();
		$sublist_set     = $subscriber_list->getData( $params );
		if ( is_object( $sublist_set ) ) {
			try {
				foreach ( $sublist_set as $sublist ) {
					$this->print_d( "Delete subscriber list" . $sublist->id );
					$subscriber_list->deleteSubscriberList( $sublist->id );
				}
				$job->delete();
			} catch ( \Throwable $exc ) {
				$this->sendError( $exc );
				$job->bury();
			}
		} else {
			$this->print_d( "Cant found subscriber list with list id" );
			$job->delete();
		}
	}

	/**
	 * Manage import subscriber
	 *
	 * @param $params array
	 * @param $job Job
	 */
	public function importSubscriber( $params, $job ) {
		//$logger = new \Phalcon\Logger\Adapter\File("app/logs/import.log");
		$csubscriber           = new Subscriber();
		$csubscriberfield      = new SubscriberField();
		$csubscriberfieldvalue = new SubscriberFieldValues();
		$csubscriberlist       = new SubscriberList();
		try {
			$list_id   = $params['list_id'];
			$fields    = $params['fields'];
			$source    = $this->cleanDataString( $params['source'] );
			$user_guid = $params['user_guid'];

			$import_data  = explode( PHP_EOL, $source );
			$total_import = count( $import_data );

			if ( $total_import > 0 ) {
				$full_name_array_key = array_search( 'full_name', $fields );
				$email_array_key     = array_search( 'email', $fields );

				if ( $full_name_array_key != false ) {
					unset( $fields[ $full_name_array_key ] );
				}
				if ( $email_array_key != false ) {
					unset( $fields[ $email_array_key ] );
				}

				ksort( $fields );
				$fields = array_values( $fields );
				foreach ( $import_data as $row ) {
					$columns = explode( ',', $row );
					if ( is_bool( $full_name_array_key ) && $full_name_array_key == false ) {
						$full_name = '  ';
					} else {
						$full_name = $columns[ $full_name_array_key ];
					}
					$email = $this->full_trim( $columns[ $email_array_key ] );

					if ( $full_name_array_key != false ) {
						unset( $columns[ $full_name_array_key ] );
					}
					if ( $email_array_key != false ) {
						unset( $columns[ $email_array_key ] );
					}

					ksort( $columns );
					$columns = array_values( $columns );

					$values = [];
					foreach ( $columns as $idx => $val ) {
						$values[ $fields[ $idx ] ] = $val;
					}

					//TODO:check before create
					$this->print_d( $columns );
					$this->print_d( $full_name );
					$this->print_d( $email );
					$subscriber = null;
					if ( filter_var( $this->full_trim( $email ), FILTER_VALIDATE_EMAIL ) ) {
						$subscriber = $csubscriber->createSubscriber( [
							'user_guid'  => $user_guid,
							'full_name'  => ( isset( $full_name ) ? $this->full_trim( $full_name ) : 'unknown' ),
							'email'      => $this->full_trim( $email ),
							'email_hash' => hash( 'sha256', $this->full_trim( $email ) ),
							'ip_address' => 0,
							'status'     => 'subscribed'
						] );
					} else {
						continue;
					}
					if ( count( $subscriber ) > 0 && isset( $subscriber[0] ) && $subscriber[0] ) {
						$obj = $subscriber[2];

						$subscriber_id = $obj->id;

						// save subscriber fields
						foreach ( $values as $key => $value ) {
							$field_values = [
								'subscriber_id'       => $subscriber_id,
								'subscriber_field_id' => $key
							];

							$sf = $csubscriberfield->getByIdAndGuid( $key, $user_guid );
							if ( in_array( $sf->type, [ 'checkbox', 'radio', 'dropdown' ] ) ) {
								$allowedValue = $sf->getOptions();
								if ( ! in_array( $value, $allowedValue ) ) {
									//$logger->error("Invalid value (".$value.") for field ".$sf->name);
									continue;
								}

								$field_values['value'] = json_encode( $value );
							} else {
								$field_values['value'] = $value;
							}

							$csubscriberfieldvalue->createSubscriberFieldValue( $field_values );
						}

						$csubscriberlist->saveSubscriberList( $subscriber_id, [ $list_id ], $obj->status );
					} else {
						continue;
					}
				}
			}
			$this->print_d( $source );
			$this->print_d( "Job delete" );
			$job->delete();
		} catch ( \Throwable $exc ) {
			$this->print_d( "Job bury" );
			$this->sendError( $exc );
			$job->delete();
		}
	}

	/**
	 * Adding subscriber to Autoresponder Recipient automatically
	 * once it's subscribed
	 *
	 * @param $params array
	 * @param $job Job
	 */
	public function addAutoresponderRecipient( $params, $job ) {
		$list_id       = $params['list_id'];
		$subscriber_id = $params['subscriber_id'];
		$deleted       = $params['deleted'];

		try {
			$arlist     = new AutoresponderList();
			$arlist_set = $arlist->findByListId( $list_id );
			if ( is_object( $arlist_set ) ) {
				$this->print_d( "get ar list" );
				foreach ( $arlist_set as $obj ) {
					$subscriber = new Subscriber();
					$sb         = $subscriber->getById( $subscriber_id );

					if ( is_object( $sb ) ) {
						$email = $sb->email;

						$arrecipient = new AutoresponderRecipient();
						$arObj       = $arrecipient->findFirstByParams( [
							'conditions' => 'autoresponder_guid = :autoresponder_guid: and email = :email:',
							'bind'       => [
								'autoresponder_guid' => $obj->autoresponder_guid,
								'email'            => strtolower( $email )
							]
						] );

						$ar_data = [
							'autoresponder_guid' => $obj->autoresponder_guid,
							'autoresponder_id'   => $obj->autoresponder_id,
							'email'              => strtolower( $email ),
							'is_deleted'         => ( $deleted ) ? 1 : 0,
							'deleted_at'         => ( $deleted ? strtotime( 'now' ) : 0 )
						];
						$this->print_d( "update recipient" );
						if ( is_object( $arObj ) ) {
							echo "update";
							$result = $arrecipient->editAutoresponderRecipient( $arObj->id, $ar_data, true );
						} else {
							echo "create";
							$result = $arrecipient->createAutoresponderRecipient( $ar_data );
						}
						//print_r( var_dump( $result[1] ) );
						$this->print_d( "finish update recipient" );
					} else {
						$this->print_d( "cant find subscriber" );
					}
				}
			}
			$this->print_d( "Delete job" );
			$job->delete();
		} catch ( \Throwable $exc ) {
			$this->print_d( "Bury" . $exc->getMessage() );
			$this->sendError( $exc );
			$job->bury();
		}
	}

	/**
	 * Saving Campaign recipients
	 *
	 * @param $params array
	 * @param $job Job
	 */
	public function saveCampaignRecipients( $params, $job ) {
		$csegment           = new Segments();
		$csubscriber        = new Subscriber();
		$ccampaignrecipient = new CampaignRecipient();
		$ccampaignlist      = new CampaignList();
		$ccampaignsegment   = new CampaignSegment();
		$ccampaign          = new Campaign();

		$user_guid     = $params['user_guid'];
		$campaign_guid = $params['campaign_guid'];


		try {
			$lists    = [];
			$cblist   = $ccampaignlist->findByCampaignGuid( $campaign_guid );
			$campaign = $ccampaign->getByGuid( $campaign_guid );
			if ( is_object( $campaign ) && strtolower( $campaign->status ) == 'running' ) {
				//$this->print_d($cblist,$campaign_guid);
				foreach ( $cblist as $lst ) {
					$lists[] = $lst->list_id;
					$this->print_d( $lst->list_id . " " . $campaign_guid );
				}
				if ( count( $lists ) == 0 ) {
					$this->print_d( "Empty List " . $campaign_guid );
					//$this->print_d($lists,$campaign_guid);
				} else {
					$this->print_d( "Not empty list " . $campaign_guid );
					$this->print_d( $lists, $campaign_guid );
				}
				$this->print_d( $campaign_guid );
				$segment = $ccampaignsegment->findByCampaignGuid( $campaign_guid );

				if ( is_object( $segment ) && $segment->segment_id > 0 ) {
					$this->print_d( 'Use Segment : ' . $campaign_guid );
					$this->print_d( "Segment" );
					$this->print_d( $segment );
					$record = 0;
					$total  = 0;
					$this->db->begin();

					$subsrcibers = $csegment->getSubscribers( $user_guid, $segment->segment_id, implode( ',', $lists ) );
					$this->print_d( 'Guid : ' . $user_guid );
					$this->print_d( 'Segment ID : ' . $user_guid );
					$campaign = $ccampaign->getByGuid( $campaign_guid );
					$ccampaign->editCampaign( $campaign->id, [ 'total_recipient' => count( $subsrcibers ) ], $user_guid );
					foreach ( $subsrcibers as $sub ) {
						$rcptData = [
							'campaign_guid' => $campaign_guid,
							'subject'       => '-',
							'content'       => '-',
							'content_plain' => '-',
							'message_guid'  => '-',
							'status'        => 'queue',
							'email'         => strtolower( $sub->email )
						];

						$param_search = [
							'campaign_guid = :campaign_guid: AND email = :email:',
							'bind' => [
								'campaign_guid' => $campaign_guid,
								'email'         => strtolower( $sub->email )
							],
						];
						//$this->print_d( $rcptData, $campaign_guid );
						$cr = $ccampaignrecipient->findFirstByParams( $param_search );
						$total ++;
						if ( ! is_object( $cr ) ) {
							$ccampaignrecipient->createCampaignRecipient( $rcptData );
							$record ++;
							//$this->print_d( "add campaign recipient " . $record . "/" . $total, $campaign_guid );
						}


						if ( $record == 1000 ) {
							$this->db->commit();
							$this->print_d( "Commit", $campaign_guid );
							$record = 0;
							$this->db->begin();
						}
					}

					$this->db->commit();
					$campaign = $ccampaign->getByGuid( $campaign_guid );
					$ccampaign->editCampaign( $campaign->id, [ 'total_recipient' => $total == 0 ? count( $subsrcibers ) : $total ], $user_guid );
					$this->print_d( "Total Recipient " . $total, $campaign_guid );
					//$campaign->update();
				} else {
					$this->print_d( 'Not Use Segment : ' . $campaign_guid );
					$this->print_d( $lists, $campaign_guid );
					$resultset = $csubscriber->getByUserGuidAndLists( $user_guid, $lists );
					$this->print_d( $resultset, $campaign_guid );
					if ( is_object( $resultset ) ) {
						$campaign = $ccampaign->getByGuid( $campaign_guid );
						$ccampaign->editCampaign( $campaign->id, [ 'total_recipient' => count( $resultset ) ], $user_guid );
						$this->print_d( "From list", $campaign_guid );
						$this->print_d( $resultset );
						$record = 0;
						$total  = 0;
						$this->db->begin();
						foreach ( $resultset as $row ) {
							$rcptData = [
								'campaign_guid' => $campaign_guid,
								'subject'       => '-',
								'content'       => '-',
								'content_plain' => '-',
								'message_guid'  => '-',
								'status'        => 'queue',
								'email'         => $row->email
							];

							$param_search = [
								'campaign_guid = :campaign_guid: AND email = :email:',
								'bind' => [
									'campaign_guid' => $campaign_guid,
									'email'         => $row->email
								],
							];
							//$this->print_d( $rcptData, $campaign_guid );
							$cr = $ccampaignrecipient->findFirstByParams( $param_search );
							$total ++;
							if ( ! is_object( $cr ) ) {
								$ccampaignrecipient->createCampaignRecipient( $rcptData );
								$record ++;
								//$this->print_d( "add campaign recipient " . $record . "/" . $total, $campaign_guid );
							}


							if ( $record == 1000 ) {
								$this->db->commit();
								$this->print_d( "Commit", $campaign_guid );
								$record = 0;
								$this->db->begin();
							}
						}

						$this->db->commit();
						$campaign = $ccampaign->getByGuid( $campaign_guid );
						$ccampaign->editCampaign( $campaign->id, [ 'total_recipient' => $total == 0 ? count($resultset) : $total ], $user_guid );
						$this->print_d( "Total Recipient " . $total, $campaign_guid );
						//$campaign->update();
					}
				}

				$this->print_d( "Delete job", $campaign_guid );
				$job->delete();
			} else {
				$job->delete();
			}
		} catch ( \Throwable $exc ) {
			$this->print_d( "Bury" . $exc->getMessage(), $campaign_guid );
			$this->sendError( $exc );
			$job->bury();
		}
	}

	/**
	 * Saving Autoresponder recipients
	 *
	 * @param $params array
	 * @param $job Job
	 */
	public function saveAutoresponderRecipients( $params, $job ) {
		$csubscriber             = new Subscriber();
		$cautoresponderrecipient = new AutoresponderRecipient();
		$cautoresponderlist      = new AutoresponderList();

		$user_guid          = $params['user_guid'];
		$autoresponder_id   = $params['autoresponder_id'];
		$autoresponder_guid = $params['autoresponder_guid'];

		try {
			$lists  = [];
			$cblist = $cautoresponderlist->findByAutoresponderGuid( $autoresponder_guid );
			foreach ( $cblist as $lst ) {
				$lists[] = $lst->list_id;
			}

			//save recipient
			$subscriber_params = [
				'list'      => $lists,
				'status'    => 'subscribed',
				'user_guid' => $user_guid
			];

			$resultset = $csubscriber->getData( $subscriber_params );

			if ( $resultset ) {
				$record = 0;
				$this->db->begin();
				foreach ( $resultset as $row ) {
					$params_search = [
						'autoresponder_guid = :autoresponder_guid: AND email = :email:',
						'bind' => [
							'autoresponder_guid' => $autoresponder_id,
							'email'            => $row->email
						],
					];

					$ar = $cautoresponderrecipient->findFirstByParams( $params_search );

					$ar_data = [
						'autoresponder_guid' => $autoresponder_guid,
						'autoresponder_id'   => (int) $autoresponder_id,
						'email'              => $row->email,
						'step'               => 0
					];
					$this->print_d( $ar_data );
					if ( ! is_object( $ar ) ) {
						$arr = $cautoresponderrecipient->createAutoresponderRecipient( $ar_data );
						$record ++;
					}

					if ( $record == 1000 ) {
						$this->db->commit();
						$record = 0;
						$this->print_d( "Commit" );
						$this->db->begin();
					}
				}

				$this->db->commit();
			}

			$this->print_d( "Delete job" );
			$job->delete();
		} catch ( \Throwable $exc ) {
			$this->print_d( "Bury" . $exc->getMessage() );
			$this->sendError( $exc );
			$job->bury();
		}
	}

	/**
	 * Saving Exported Subscribers
	 *
	 * @param $params array
	 * @param $job Job
	 */
	public function saveExportedSubscribers( $params, $job ) {
		$cuser                 = new User();
		$clist                 = new Lists();
		$csubscriber           = new Subscriber();
		$csubscriberfield      = new SubscriberField();
		$csubscriberfieldvalue = new SubscriberFieldValues();
		$csubscribertag        = new SubscriberTag();

		$user_guid = $params['user_guid'];
		$list_id   = $params['list_id'];
		$status    = $params['status'];
		$fields    = $params['fields'];

		try {
			$user = $cuser->getByGUID( $user_guid );
			if ( is_object( $user ) ) {
				$params = [
					'status'    => $status,
					'list'      => $list_id,
					'user_guid' => $user_guid
				];

				$resultset = $csubscriber->getData( $params );

				$writer   = WriterFactory::create( Type::CSV ); // for CSV files
				$list     = $clist->getByGuidAndID( $this->user_guid, $list_id );
				$filename = 'export-' . $user->username . '-' . $list->name . '-' . time() . '.csv';
				$writer->openToFile( BASE_DIR . 'public/temp/' . $filename );

				$header = [ 'Full Name', 'Email', 'Tag', 'Date Added' ];

				unset( $fields[1] );
				unset( $fields[0] );
				foreach ( $fields as $field ) {
					$subfield = $csubscriberfield->getById( $field );
					$header[] = $subfield->name;
				}

				$writer->addRow( $header ); // add a row at a time

				foreach ( $resultset as $row ) {
					$the_tags   = '';
					$tag_result = $csubscribertag->getData( [ 'concat' => true, 'subscriber_id' => $row->id ] );
					if ( count( $tag_result ) > 0 ) {
						$the_tags = $tag_result[0]->tags;
					}
					$content = [ $row->full_name, $row->email, $the_tags, $row->date_added ];

					if ( $fields ) {
						foreach ( $fields as $subscriber_field ) {
							$subscriber_field_value = $csubscriberfieldvalue->getData( [
								'subscriber_id'       => $row->id,
								'subscriber_field_id' => $subscriber_field
							] );
							$value                  = '';

							if ( isset( $subscriber_field_value[0] ) ) {
								$value = $subscriber_field_value[0]->value;
							}

							$content[] = $value;
						}
					}

					$writer->addRow( $content );
				}

				$writer->close();

				$the_file = 'temp/' . $filename;
				$this->print_d( "File link = " . $the_file );
				$file_link = $this->url->getStaticBaseUri() . 'download/' . SimpleCrypt::encrypt( $the_file );
				$this->print_d( "File URL = " . $file_link );
				$email_sender = new EmailSender();
				$email_sender->sendExportedSubscriber( $file_link, $user->email );
			}

			$this->print_d( "Delete job" );
			$job->delete();
		} catch ( \Throwable $exc ) {
			$this->print_d( "Bury" . $exc->getMessage() );
			$this->sendError( $exc );
			$job->bury();
		}
	}

	/**
	 * Send Notification for each subscribed contact
	 *
	 * @param $params array
	 * @param $job Job
	 */
	public function sendSubscribedNotification( $params, $job ) {
		$cuser      = new User();
		$usernotifc = new UserNotification();

		try {
			$user         = $cuser->getByGUID( $params['user_guid'] );
			$notifc       = $usernotifc->getByGuid( $params['user_guid'] );
			$notif_active = $notifc->new_subscriber;
			if ( $notif_active ) {
				$email_notif = new EmailNotification();
				$email_notif->sendNotif( EmailNotification::NEW_SUBS, [
					'email' => $params['email'],
				], $user->email );
			}

			$this->print_d( "Delete job" );
			$job->delete();
		} catch ( \Throwable $exc ) {
			$this->print_d( "Bury" . $exc->getMessage() );
			$this->sendError( $exc );
			$job->bury();
		}
	}

	/**
	 * @param $params
	 * @param $job Job
	 */
	public function updateGeoData( $params, $job ) {
		$csubgeo      = new SubscriberGeoData();
		$cgeolocation = new GeolocationDb();
		try {
			/** @var \Application\Models\GeolocationDb $geodata */
			$geodata = $cgeolocation->lookupIp( $params['ip_address'] );
			if ( ! is_null( $geodata ) ) {
				$result = $csubgeo->createSubsGeoData( $params['email'], $params['hash'], [
					'geo_city'            => $geodata->city,
					'geo_district'        => $geodata->district,
					'geo_zipcode'         => $geodata->zipcode,
					'geo_country'         => $geodata->country,
					'geo_state'           => $geodata->stateprov,
					'geo_timezone'        => $geodata->timezone_name,
					'geo_timezone_offset' => $geodata->timezone_offset,
					'ip_address'          => $params['ip_address']
				] );
				$this->print_d( $result );
				$job->delete();
			} else {
				$obj = $csubgeo->getByEmailAndHash( $params['email'], $params['hash'] );
				if ( is_object( $obj ) ) {
					$result = $csubgeo->createSubsGeoData( $params['email'], $params['hash'], [
						'geo_city'            => $obj->city,
						'geo_district'        => $obj->district,
						'geo_zipcode'         => $obj->zipcode,
						'geo_country'         => $obj->country,
						'geo_state'           => $obj->stateprov,
						'geo_timezone'        => $obj->timezone_name,
						'geo_timezone_offset' => $obj->timezone_offset,
						'ip_address'          => $params['ip_address']
					] );
					$this->print_d( $result );
				} else {
					$result = $csubgeo->createSubsGeoData( $params['email'], $params['hash'], [
						'geo_city'            => "",
						'geo_district'        => "",
						'geo_zipcode'         => "",
						'geo_country'         => "",
						'geo_state'           => "",
						'geo_timezone'        => "",
						'geo_timezone_offset' => "",
						'ip_address'          => $params['ip_address']
					] );
					$this->print_d( $result );
				}
				$job->delete();
			}
		} catch ( \Throwable $e ) {
			$this->sendError( $e );
			$this->print_d( $e );
			$job->delete();
		}
	}

	/**
	 * @param $params
	 * @param $job Job
	 */
	public function sendFbAccessDownNotification( $params, $job ) {
		$cuser      = new User();
		$usernotifc = new UserNotification();
		try {
			$user         = $cuser->getByGUID( $params['user_guid'] );
			$notifc       = $usernotifc->getByGuid( $params['user_guid'] );
			$notif_active = true;
			if ( $notif_active ) {
				$email_notif = new EmailNotification();
				$email_notif->sendNotif( EmailNotification::FACEBOOK_ACCESS_TOKEN, [
					'email' => $params['email'],
				], $user->email );
			}

			$this->print_d( "Delete job" );
			$job->delete();
		} catch ( \Throwable $exc ) {
			$this->print_d( "Bury" . $exc->getMessage() );
			$this->sendError( $exc );
			$job->bury();
		}

	}

	/**
	 * @param $params
	 * @param $job Job
	 */
	public function sendFormConfirmation( $params, $job ) {
		$converter = new HtmlConverter( array( 'strip_tags' => true, 'hard_break' => true ) );
		$cform     = new Forms();
		$cconfirm  = new ConfirmationEmails();
		//$csubscriber = new Subscriber();
		$cuserinfo   = new UserInfo();
		$cusersender = new UserSender();
		try {
			$this->print_d( 'form_notification' );
			$user_guid        = $params['user_guid'];
			$form_id          = $params['form_id'];
			$subscriber_hash  = $params['email_hash'];
			$subscriber_email = $params['email'];
			$form             = $cform->getByUserGuidAndId( $user_guid, $form_id );
			//$subscriber = $csubscriber->getByHashAndUserGUIDEFL($user_guid,$subscriber_hash,substr($subscriber_email,0,1),substr($subscriber_hash,0,2));
			if ( $form->confirmation_email > 0 ) {
				$confirmation_email = $cconfirm->getByUserGuidAndId( $user_guid, $form->confirmation_email );
				if ( is_object( $confirmation_email ) && is_string( $subscriber_email ) && strlen( $subscriber_email ) > 0 ) {
					$sender = $cusersender->getByIdAndGuid( $confirmation_email->user_sender, $user_guid );
					if ( is_object( $sender ) ) {
						$this->print_d( 'form_notification try to sent' );
						$message     = str_replace( '%confirmation_link%', $this->url->getStaticBaseUri() . "confirmform/" . $subscriber_hash . "/" . $form->url, $confirmation_email->content );
						$html_footer = $cuserinfo->getHtmlUserInfo( $user_guid );
						$message     .= "<hr>" . $html_footer;

						$email_sender = new EmailSender();
						$email_sender->subscriberConfirmationSend(
							$message, $converter->convert( $message ), $confirmation_email->subject, $subscriber_email, $sender->email, $sender->full_name
						);
						$this->print_d( 'form_notification sent' );
						$job->delete();
					} else {
						$job->delete();
						$this->print_d( 'form_notification delete' );
					}
				} else {
					$job->delete();
					$this->print_d( 'form_notification delete' );
				}
			} else {
				$job->delete();
				$this->print_d( 'form_notification delete' );
			}
		} catch ( \Throwable $e ) {
			$this->sendError( $e );
			$this->print_d( 'form_notification bury ' . $e );
			$this->print_d( 'form_notification bury' );
			$job->bury();
		}
	}

	protected function sendError( \Throwable $e ) {
		if ( $e instanceof \PDOException ) {
			/** @var \PDOException $e */
			if ( strstr( strtoupper( $e->getMessage() ), strtoupper( "server has gone away" ) ) == false ) {
				$this->di->getShared( 'sentry' )->logException( $e, [], 3 );
			}
		} else {
			$this->di->getShared( 'logger' )->error( $e->getCode() . "-" . $e->getMessage() . PHP_EOL . $e->getTraceAsString() );
			$this->di->getShared( 'sentry' )->logException( $e, [], 3 );
		}
	}

	private function full_trim( $string ) {
		return $this->cleanDataString( trim( $string, " \t\n\r\0\x0B0\xA0" ) );
	}

	// Automations
	public function processOpenedCampaignAutomations($params, $job)
	{
		$campaign_guid = $params['campaign_guid'];
		$email = $params['email'];
		$message_guid = $params['message_guid'];
		
		try {
			$ccampaign = new Campaign();
			// print('<pre>');
        	$campaign = $ccampaign->getByGuid($campaign_guid);
        	if (is_object($campaign)) {
            	$user_guid = $campaign->user_guid;
            	$automations = $ccampaign->getAutomations($user_guid, $campaign_guid);
            	foreach ($automations as $auto) {
					// print($auto->id);
                	$csubscriber = new Subscriber();
                	$subscriber = $csubscriber->getByGuidAndEmail($user_guid, $email);

                	if (is_object($subscriber)) {
						// print('Subscriber Exists');
                    	$cautolog = new AutomationLog();
                    	$latest_step = $cautolog->getLatestAutomationLog($user_guid, $auto->id, $subscriber->id);

						// print_r($latest_step->toArray());
                    	$last_step_id = 0;
                    	if (count($latest_step) > 0) {
                        	$last_step_id = $latest_step[0]->step_id;
                    	}

                    	//next step
                    	$cautostep = new AutomationStep();
                    	$next_step = $cautostep->getNextAutomationStep($user_guid, $auto->id, $last_step_id);
						// print('Next Step: '. $next_step->type);
						if (is_object($next_step)) {
							$detailed = json_decode($next_step->detailed, true);
							// print_r($detailed);
                        	$action = $detailed['action'];
							// print($action);
                        	if (in_array($next_step->type, ["Trigger", "Decision"])) {
                           		if ($detailed['campaign_guid'] == $campaign_guid || $detailed['campaign_guid'] == '0') {
                                //unset($detailed['action']);

                                	if (($action == 'open' || $action == 'open_bc') && ($detailed['campaign_guid'] == '0' || $detailed['campaign_guid'] == $campaign_guid)) {
										AutomatedProcessor::{$action}($user_guid, $auto->id, $next_step->id, $email, $detailed, $campaign_guid, $message_guid);
                                	}
                            	}
                        	} else if ($next_step->type == "Split") {
                            	AutomatedProcessor::split($user_guid, $auto->id, $next_step->id, $email);
                        	} else if ($next_step->type == "Response") {
                            	$prev_step = $cautostep->getById($next_step->after);
                            	if (is_object($prev_step)) {
                                	$prev_detailed = json_decode($prev_step->detailed, true);
                                	$prev_action = $prev_detailed['action'];
                                	//unset($detailed['action']);

                                	if (($prev_action == 'open' || $prev_action == 'open_bc') && ($prev_detailed['campaign_guid'] == '0' || $prev_detailed['campaign_guid'] == $campaign_guid)) {
                                    	AutomatedProcessor::{$action}($user_guid, $auto->id, $next_step->id, $email, $detailed, $campaign_guid, $message_guid);
                                	}
                            	}
                        	}
                    	}
                	}
            	}
        	}
		    
			$this->print_a("Delete job");
            $job->delete();
        } catch (\Throwable $exc) {
            $this->print_a("Bury" . $exc->getMessage());
            $this->sendError($exc);
            $job->bury();
        }
		// die();
	}

	public function processClickedCampaignAutomations($params, $job)
	{
		$campaign_guid = $params['campaign_guid'];
		$email = $params['email'];
		$track_id = $params['tracklink_id'];
		$message_guid = $params['message_guid'];

		try {
        	$ccampaign = new Campaign();
			// print('<pre>');
        	$campaign = $ccampaign->getByGuid($campaign_guid);
			// print_r($campaign->toArray());
			if (is_object($campaign)) {
            	$user_guid = $campaign->user_guid;
            	$automations = $ccampaign->getAutomations($user_guid, $campaign_guid);
				// print_r($automations->toArray());die();
				foreach ($automations as $auto) {
                	$csubscriber = new Subscriber();
                	$subscriber = $csubscriber->getByGuidAndEmail($user_guid, $email);
				
                	if (is_object($subscriber)) {
                    	$cautolog = new AutomationLog();
						// print_r([$user_guid, $auto->id, $subscriber->id]);
                    	$latest_step = $cautolog->getLatestAutomationLog($user_guid, $auto->id, $subscriber->id);

                    	$last_step_id = 0;
                    	if (count($latest_step) > 0) {
                        	$last_step_id = $latest_step[0]->step_id;
                    	}
						// print_r($latest_step->toArray());
                    	//next step
                    	$cautostep = new AutomationStep();
                    	$next_step = $cautostep->getNextAutomationStep($user_guid, $auto->id, $last_step_id);
						// print_r($next_step->toArray());die();
                    	if (is_object($next_step)) {
                        	$detailed = json_decode($next_step->detailed, true);
                        	$action = $detailed['action'];
							// print($action);die();
                        	if (in_array($next_step->type, ["Trigger", "Decision"])) {
                            	if ($detailed['campaign_guid'] == $campaign_guid || $detailed['campaign_guid'] == '0') {
                                	//unset($detailed['action']);

                                	if (($action == 'click' || $action == 'click_bc') && ($detailed['campaign_guid'] == '0' || $detailed['campaign_guid'] == $campaign_guid) && (($detailed['campaign_link'] == '0' || $detailed['campaign_link'] == $track_id))) {
                                    	echo 'Entered Here';
										AutomatedProcessor::{$action}($user_guid, $auto->id, $next_step->id, $email, $detailed, $campaign_guid, $message_guid);
                                	}
                            	}
                        	} else if ($next_step->type == "Split") {
                            	AutomatedProcessor::split($user_guid, $auto->id, $next_step->id, $email);
                        	} else if ($next_step->type == "Response") {
                            	$prev_step = $cautostep->getById($next_step->after);
                            	if (is_object($prev_step)) {
                                	$prev_detailed = json_decode($prev_step->detailed, true);
                                	$prev_action = $prev_detailed['action'];
                                	//unset($detailed['action']);

                                	if (($prev_action == 'click' || $prev_action == 'click_bc') && ($prev_detailed['campaign_guid'] == '0' || $prev_detailed['campaign_guid'] == $campaign_guid) && (($detailed['campaign_link'] == '0' || $detailed['campaign_link'] == $track_id))) {
                                    	AutomatedProcessor::{$action}($user_guid, $auto->id, $next_step->id, $email, $detailed, $campaign_guid, $message_guid);
                                	}
                            	}
                        	}
                    	}
                	}
            	}
        	}
			$this->print_a("Delete job");
            $job->delete();
        } catch (\Throwable $exc) {
            $this->print_a("Bury" . $exc->getMessage());
            $this->sendError($exc);
            $job->bury();
        }
		// die();
    }
	
	public function processOpenedAutoresponderAutomations($params, $job) {
        $autoresponder_guid = $params['autoresponder_guid'];
		$email = $params['email'];
		$message_guid = $params['message_guid'];

		try {
			$cautoresponder = new Autoresponder();
			//print('<pre>');print('Testing Open AR');
        	$autoresponder = $cautoresponder->getByGuid($autoresponder_guid);
			print_r($autoresponder->toArray());
        	if (is_object($autoresponder)) {
				//print('AR is object');
            	$user_guid = $autoresponder->user_guid;
				print_r([$user_guid, $autoresponder_guid]);
            	$automations = $cautoresponder->getAutomations($user_guid, $autoresponder_guid);
				//print_r($automations->toArray());
            	foreach ($automations as $auto) {
                	$csubscriber = new Subscriber();
                	$subscriber = $csubscriber->getByGuidAndEmail($user_guid, $email);

                	if (is_object($subscriber)) {
                    	$cautolog = new AutomationLog();
                    	$latest_step = $cautolog->getLatestAutomationLog($user_guid, $auto->id, $subscriber->id);
						//print_r($latest_step->toArray());
                    	$last_step_id = 0;
                    	if (count($latest_step) > 0) {
                        	$last_step_id = $latest_step[0]->step_id;
                    	}

                    	//next step
                    	$cautostep = new AutomationStep();
                    	$next_step = $cautostep->getNextAutomationStep($user_guid, $auto->id, $last_step_id);
                        //print_r($next_step->toArray());
                    	if (is_object($next_step)) {
                        	$detailed = json_decode($next_step->detailed, true);
                        	$action = $detailed['action'];
                        	if (in_array($next_step->type, ["Trigger", "Decision"])) {
                            	if ($detailed['autoresponder_guid'] == $autoresponder_guid || $detailed['autoresponder_guid'] == '0') {
                                	//unset($detailed['action']);
									//print('* message_guid: ' . $message_guid . PHP_EOL);
                                	if ($action == 'open_ar' && ($detailed['autoresponder_guid'] == '0' || $detailed['autoresponder_guid'] == $autoresponder_guid)) {
                                        //print('AutomatedProcessor '. $action);
                                    	AutomatedProcessor::{$action}($user_guid, $auto->id, $next_step->id, $email, $detailed, $autoresponder_guid, $message_guid);
                                	}
                            	}
                        	} else if ($next_step->type == "Split") { // deprecated
                            	AutomatedProcessor::split($user_guid, $auto->id, $next_step->id, $email);
                        	} else if ($next_step->type == "Response") {
                            	$prev_step = $cautostep->getById($next_step->after);
                            	if (is_object($prev_step)) {
                                	$prev_detailed = json_decode($prev_step->detailed, true);
                                	$prev_action = $prev_detailed['action'];
                                	//unset($detailed['action']);

                                	if ($prev_action == 'open_ar' && ($prev_detailed['autoresponder_guid'] == '0' || $prev_detailed['autoresponder_guid'] == $autoresponder_guid)) {
                                    	AutomatedProcessor::{$action}($user_guid, $auto->id, $next_step->id, $email, $detailed, $autoresponder_guid, $message_guid);
                                	}
                            	}
                        	}
                    	}
                	}
            	}
        	}
			$this->print_a("Delete job");
            $job->delete();
        } catch (\Throwable $exc) {
            $this->print_a("Bury" . $exc->getMessage());
            $this->sendError($exc);
            $job->bury();
        }
		// die();
    }

	public function processClickedAutoresponderAutomations($params, $job) {
		$autoresponder_guid = $params['autoresponder'];
		$email = $params['email'];
		$track_id = $params['tracklink_id'];
		$message_guid = $params['message_guid'];
        
		try {
			$cautoresponder = new Autoresponder();

        	$autoresponder = $cautoresponder->getByGuid($autoresponder_guid);

        	if (is_object($autoresponder)) {
            	$user_guid = $autoresponder->user_guid;
            	$automations = $cautoresponder->getAutomations($user_guid, $autoresponder_guid);
            	foreach ($automations as $auto) {
                	$csubscriber = new Subscriber();
                	$subscriber = $csubscriber->getByGuidAndEmail($user_guid, $email);

                	if (is_object($subscriber)) {
                    	$cautolog = new AutomationLog();
                    	$latest_step = $cautolog->getLatestAutomationLog($user_guid, $auto->id, $subscriber->id);

                    	$last_step_id = 0;
                    	if (count($latest_step) > 0) {
                        	$last_step_id = $latest_step[0]->step_id;
                    	}

                    	//next step
                    	$cautostep = new AutomationStep();
                    	$next_step = $cautostep->getNextAutomationStep($user_guid, $auto->id, $last_step_id);
                    	if (is_object($next_step)) {
                        	$detailed = json_decode($next_step->detailed, true);
                        	$action = $detailed['action'];
                        	if (in_array($next_step->type, ["Trigger", "Decision"])) {
                            	if ($detailed['autoresponder_guid'] == $autoresponder_guid || $detailed['autoresponder_guid'] == '0') {
                                	//unset($detailed['action']);

                                	if ($action == 'click_ar' && ($detailed['autoresponder_guid'] == '0' || $detailed['autoresponder_guid'] == $autoresponder_guid) && ($detailed['autoresponder_link'] == '0' || $detailed['autoresponder_link'] == $track_id)) {
                                    	AutomatedProcessor::{$action}($user_guid, $auto->id, $next_step->id, $email, $detailed, $autoresponder_guid, $message_guid);
                                	}
                            	}
                        	} else if ($next_step->type == "Split") {
                            	AutomatedProcessor::split($user_guid, $auto->id, $next_step->id, $email);
                        	} else if ($next_step->type == "Response") {
                            	$prev_step = $cautostep->getById($next_step->after);
                            	if (is_object($prev_step)) {
                                	$prev_detailed = json_decode($prev_step->detailed, true);
                                	$prev_action = $prev_detailed['action'];
                                	//unset($detailed['action']);

                                	if ($prev_action == 'click_ar' && ($prev_detailed['autoresponder_guid'] == '0' || $prev_detailed['autoresponder_guid'] == $autoresponder_guid) && ($detailed['autoresponder_link'] == '0' || $detailed['autoresponder_link'] == $track_id)) {
                                    	AutomatedProcessor::{$action}($user_guid, $auto->id, $next_step->id, $email, $detailed, $autoresponder_guid, $message_guid);
                                	}
                            	}
                        	}
                    	}
                	}
            	}
        	}
		$this->print_a("Delete job");
            $job->delete();
        } catch (\Throwable $exc) {
            $this->print_a("Bury" . $exc->getMessage());
            $this->sendError($exc);
            $job->bury();
        }
		// die();
    }

	public function processSubscribeInList($params, $job) {
		try {
			$subscriber_id = $params['subscriber_id'];
			$list_id       = $params['list_id'];

			// TODO: get running Automations

			// print('subs: '.$subscriber_id. ' '.$list_id. ' '. PHP_EOL);
			$clist = new Lists();
			$list  = $clist->getById($list_id);

			if (is_object($list)) {
				$user_guid       = $list->user_guid;
				$csubscriberlist = new SubscriberList();
				$automations     = $csubscriberlist->getAutomations( $user_guid, $list_id );
				
				// print_r($automations->toArray());die();
				foreach( $automations as $auto ) {
					// get subscriber
					$csubscriber = new Subscriber();
					$subscriber  = $csubscriber->getByGuidAndID($user_guid, $subscriber_id);
					$email       = $subscriber->email;
					
					if (is_object($subscriber)) {
						// print('Subscriber Exists');die();
						$cautolog = new AutomationLog();
						$latest_step = $cautolog->getLatestAutomationLog($user_guid, $auto->id, $subscriber->id);

						// print_r($latest_step->toArray());
						$last_step_id = 0;
						if (count($latest_step) > 0) {
							$last_step_id = $latest_step[0]->step_id;
						}

						// next step
						$cautostep = new AutomationStep();
						$next_step = $cautostep->getNextAutomationStep($user_guid, $auto->id, $last_step_id);
						// print('Next Step: '. $next_step->type);die();
						if (is_object($next_step)) {
							$detailed = json_decode($next_step->detailed, true);
							// print_r($detailed);
							$action = $detailed['action'];
							if (in_array($next_step->type, ["Trigger", "Decision"])) {
                            	if ($detailed['list'] == $list_id || $detailed['list'] == '0') {
                                	//unset($detailed['action']);

                                	if ($action == 'in_list' && ($detailed['list'] == '0' || $detailed['list'] == $list_id)) {
                                    	AutomatedProcessor::{$action}($user_guid, $auto->id, $next_step->id, $email, $detailed, 0, 0);
                                	}
                            	}
                        	} 
							
							if ($next_step->type == "Response") {
								$prev_step = $cautostep->getById($next_step->after);
								if (is_object($prev_step)) {
									AutomatedProcessor::{$action}($user_guid, $auto->id, $next_step->id, $subscriber->email, $detailed, 0, 0);
								}
							}
						}
					}
				}
			}
			$job->delete();
		} catch (\Throwable $exc) {
			$this->print_a("Bury" . $exc->getMessage());
			$this->sendError($exc);
			$job->bury();
		}

	}
}
