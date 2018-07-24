<?php
/**
 * Created by PhpStorm.
 * User: gamalan
 * Date: 4/8/18
 * Time: 2:51 PM
 */

namespace Application\Task;

use Carbon\Carbon;
use Application\Core\GeolocationDb;
use Application\Html\EnhancedDomDocument;
use Application\Queue\Server;
use Application\Worker\ProcessMessageUtil;
use League\HTMLToMarkdown\HtmlConverter;
use Swift_SmtpTransport;
use Application\Core\User;
use Application\Core\SmtpManager;
use Swift_Mailer;
use Swift_Signers_DKIMSigner;
use Swift_Message;

use Application\Console\AbstractTask;

class Example extends AbstractTask {



}