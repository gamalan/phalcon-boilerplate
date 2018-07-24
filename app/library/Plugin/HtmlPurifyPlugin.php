<?php
/**
 * Created by PhpStorm.
 * User: gamalan
 * Date: 11/20/17
 * Time: 1:45 PM
 */

namespace Application\Plugin;


use Phalcon\Events\Event;

class HtmlPurifyPlugin {
	public function afterRender( Event $event, $view ) {
		/*$config = \HTMLPurifier_Config::createDefault();
		$config->set( 'HTML.Doctype', 'XHTML 1.0 Transitional' );
		$config->set( 'Attr.AllowedClasses', 'special' );
		$htmlpurify = new \HTMLPurifier( $config );*/


		/*$view->setContent(
			(string) $htmlpurify->purify( $view->getContent() )
		);*/
		$tidyConfig = [
			"clean"          => true,
			"output-xhtml"   => true,
			"show-body-only" => true,
			"wrap"           => 0,
		];

		$tidy = tidy_parse_string(
			$view->getContent(),
			$tidyConfig,
			"UTF8"
		);

		$tidy->cleanRepair();

		$view->setContent(
			(string) $tidy
		);
	}
}