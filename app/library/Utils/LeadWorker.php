<?php
/**
 * Created by PhpStorm.
 * User: gamalan
 * Date: 4/11/17
 * Time: 7:24 PM
 */

namespace Application\Utils;


use Facebook\Facebook;
use FacebookAds\Object\CustomAudience;
use FacebookAds\Object\Values\CustomAudienceTypes;
use Application\Core\FacebookAudience;
use Application\Core\FacebookData;
use Application\Core\FacebookIntegration;
use Application\Core\FacebookLog;
use Application\Core\FacebookUser;
use Application\Core\Subscriber;
use Application\Core\SubscriberField;
use Application\Core\SubscriberFieldValues;
use Application\Core\SubscriberList;
use Application\Core\User;
use Application\Core\UserInfo;
use Application\Models\UserFacebookAudienceSync;
use Application\Traits\DataCleanerTrait;
use Application\Traits\SupervisordDebugTrait;
use Phalcon\Mvc\User\Component;
use GuzzleHttp\Client;
use Phalcon\Queue\Beanstalk\Job;
use FacebookAds\Api as FbApi;

class LeadWorker extends Component
{
    use SupervisordDebugTrait;
    use DataCleanerTrait;
    /** @var  User $cuser */
    protected $cuser;
    /** @var  Subscriber $csubscriber */
    protected $csubscriber;
    /** @var  SubscriberList $csusbcriberlist */
    protected $csusbcriberlist;
    /** @var  SubscriberField $csubscriberfield */
    protected $csubscriberfield;
    /** @var  SubscriberFieldValues $csubscriberfieldvalue */
    protected $csubscriberfieldvalue;

    protected $config;

    public function __construct()
    {
        $this->cuser = new User();
        $this->csubscriber = new Subscriber();
        $this->csusbcriberlist = new SubscriberList();
        $this->csubscriberfield = new SubscriberField();
        $this->csubscriberfieldvalue = new SubscriberFieldValues();
        $this->config = $this->getDI()->getShared('config');
    }

    /**
     * @param $job Job
     */
    public function processLead($job)
    {
        $jobBody = $job->getBody();
        $data =  $jobBody['data'];
        if ($data['type'] == 'fb') {
            $this->processFacebook($job);
        } else {
            $this->processTwitter($job);
        }
    }

    /**
     * @param $job Job
     */
    public function processFacebook($job)
    {
        $fbclient = new Facebook([
            'app_id' => $this->config->get('fb')->app_id,
            'app_secret' => $this->config->get('fb')->app_secret,
            'default_graph_version' => $this->config->get('fb')->app_version,
        ]);
        $fb_data = new FacebookData();
        $fb_integration = new FacebookIntegration();
        $fb_user = new FacebookUser();
        $fb_log = new FacebookLog();
        $jobBody = $job->getBody()['data'];
        $user = $this->cuser->getByGUID($jobBody['user_guid']);
        if (is_object($user) && $this->cuser->isUserAllowed($user->user_guid, 'subscribers', $user->role)) {
            $db_lead = $fb_data->getByID($jobBody['id'], $user->user_guid);
            $user_access_token = $fb_user->getFbAccessCode($user->user_guid);
            if (is_object($db_lead)) {
                $db_sync = $fb_integration->getByID($db_lead->integration_id, $user->user_guid);
                if (is_object($db_sync)) {
                    try {
                        $page = $fbclient->get('/' . $db_sync->user_fbpage_id . '?fields=access_token', $user_access_token)->getDecodedBody();
                        $lead = $fbclient->get('/' . $db_lead->leadgen_id, $page['access_token'])->getDecodedBody();
                        $email = null;
                        $full_name = null;
                        $fields = [];
                        $field_datas = $lead['field_data'];
                        $this->print_a($lead);
                        foreach ($field_datas as $field_data) {
                            $value = $field_data['values'];
                            switch ($field_data['name']) {
                                case 'email':
                                case 'EMAIL':
                                case 'Email':
                                    $email = trim($value[0]);
                                    break;
                                case 'full_name':
                                    $full_name = $value[0];
                                    break;
                                case 'first_name':
                                    $full_name .= $value[0];
                                    break;
                                case 'last_name':
                                    $full_name .= $value[0];
                                    break;
                                default:
                                    $fields[$field_data['name']] = $value[0];
                                    break;
                            }
                        }
                        if (!is_null($email) && strlen($email) > 0 && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            if (strlen($full_name) <= 0) {
                                $full_name = $email;
                            }
                            $data_subs = [
                                'full_name' => $full_name,
                                'email' => $email,
                                'user_guid' => $user->user_guid
                            ];
                            $result_sub = $this->csubscriber->createSubscriber($data_subs);
                            if ($result_sub[0]) {
                                $subscriber = $result_sub[2];
                                $this->print_a($subscriber->id);
                                $this->print_a($db_sync->list_id);
                                $result_list = $this->csusbcriberlist->saveSubscriberList($subscriber->id, [$db_sync->list_id], "subscribed");
                                $this->print_a($result_list);
                                $fb_log->create([
                                    'data' => json_encode($lead),
                                    'data_result' => $result_sub[1],
                                    'result' => 'success',
                                    'user_guid' => $user->user_guid,
                                    'leadgen_id'=>$db_lead->leadgen_id
                                ]);
                                $db_lead->is_processed = 1;
                                $db_lead->update();
                                $job->delete();
                                foreach ($fields as $subfield_key => $subfield_val) {
                                    $subfield = $this->csubscriberfield->getByTagAndGuid($subfield_key, $user->user_guid);
                                    if (is_object($subfield)) {
                                        $this->csubscriberfieldvalue->createSubscriberFieldValue(
                                            [
                                                'subscriber_field_id' => $subfield->id,
                                                'subscriber_id' => $subscriber->id,
                                                'value' => $subfield_val
                                            ]
                                        );
                                    } else {
                                        $result_subfield = $this->csubscriberfield->createSubscriberField(
                                            [
                                                'name' => $subfield_key,
                                                'personalization_tag' => $subfield_key,
                                                'type' => 'text',
                                                'user_guid' => $user->user_guid
                                            ]
                                        );
                                        if ($result_subfield[0]) {
                                            $subfield = $result_subfield[2];
                                            $this->csubscriberfieldvalue->createSubscriberFieldValue(
                                                [
                                                    'subscriber_field_id' => $subfield->id,
                                                    'subscriber_id' => $subscriber->id,
                                                    'value' => $subfield_val
                                                ]
                                            );
                                        }
                                    }
                                }
                            } else {
                                $fb_log->create([
                                    'data' => json_encode($lead),
                                    'data_result' => $result_sub[1],
                                    'result' => 'error',
                                    'user_guid' => $user->user_guid,
                                    'leadgen_id'=>$db_lead->leadgen_id
                                ]);
                                $db_lead->is_processed = 1;
                                $db_lead->update();
                                $job->delete();
                            }
                        } else {
                            $fb_log->create([
                                'data' => print_r($lead),
                                'data_result' => 'Invalid email',
                                'result' => 'error',
                                'user_guid' => $user->user_guid,
                                'leadgen_id'=>$db_lead->leadgen_id
                            ]);
                            $db_lead->is_processed = 1;
                            $db_lead->update();
                            $job->delete();
                        }
                    } catch (\Throwable $exc) {
                        $this->sendError($exc);
                        $this->print_a($exc->getTraceAsString());
                        $fb_log->create([
                            'data' => $exc->getMessage(),
                            'data_result' => 'Invalid Lead',
                            'result' => 'error',
                            'user_guid' => $user->user_guid,
                            'leadgen_id'=>$db_lead->leadgen_id
                        ]);
                        $job->delete();
                    }
                } else {
                    $job->delete();
                }
            } else {
                $job->delete();
            }
        } else {
            $job->delete();
        }

    }

    /**
     * @var Job $job
     */
    public function processFBAudienceSync($job){
        $fbclient = new Facebook([
            'app_id' => $this->config->get('fb')->app_id,
            'app_secret' => $this->config->get('fb')->app_secret,
            'default_graph_version' => $this->config->get('fb')->app_version,
        ]);
        $jobBody = $job->getBody();
        $fb_user = new FacebookUser();
        $fb_audience = new FacebookAudience();
        $csubscriber = new Subscriber();
        try{
            $this->print_a($jobBody);
            $data = $jobBody['data'];
            $access_token = $fb_user->getFbAccessCode($data['user_guid']);
            FbApi::init($fbclient->getApp()->getId(), $fbclient->getApp()->getSecret(), $access_token);
            $fbadsapi = FbApi::instance();
            /** @var UserFacebookAudienceSync $audience */
            $audience = $fb_audience->getBySyncGuid($data['audience_guid'],$data['user_guid']);
            $list_arr = $fb_audience->getAudienceList($data['audience_guid'],false);
            $lists = [];
            foreach ($list_arr as $item){
                $lists[] = $item['type_id'];
            }
            $subscribers = $csubscriber->getByUserGuidAndLists($data['user_guid'],$lists);
            $custom_audience = new CustomAudience($audience->audience_id,$audience->fb_adaccount_id,$fbadsapi);
            $items=[];
            $items_hash=[];
            $this->print_a("Total subscriber : ".count($subscribers));
            foreach ($subscribers as $subscriber){
                $this->print_a(trim($this->cleanDataString($subscriber->email)));
                $items[]=trim($this->cleanDataString($subscriber->email));
                $items_hash[]=hash('sha256',trim($this->cleanDataString($subscriber->email)));
                if(count($items)==8000){
                    $custom_audience->addUsers($items,CustomAudienceTypes::EMAIL);
                    $custom_audience->addUsers($items_hash,CustomAudienceTypes::EMAIL);
                    $items = [];
                    $items_hash = [];
                    $this->print_a("batch part");
                }
            }
            if(count($items)>0){
                $custom_audience->addUsers($items,CustomAudienceTypes::EMAIL);
                $custom_audience->addUsers($items_hash,CustomAudienceTypes::EMAIL);
                $this->print_a("complete");
            }
            $job->delete();
        }catch (\Throwable $exc){
            $this->sendError($exc);
            $this->print_a($exc->getTraceAsString());
            $job->delete();
        }
    }

    /**
     * @param $job Job
     */
    public function processTwitter($job)
    {

    }

    protected function sendError(\Throwable $e){
        if($e instanceof \PDOException){
            /** @var \PDOException $e */
            if(strstr(strtoupper($e->getMessage()),strtoupper("server has gone away"))==false){
                $this->di->getShared('sentry')->logException($e,[],3);
            }
        }else{
            $this->di->getShared('logger')->error($e->getCode()."-".$e->getMessage().PHP_EOL.$e->getTraceAsString());
            $this->di->getShared('sentry')->logException($e,[],3);
        }
    }
}