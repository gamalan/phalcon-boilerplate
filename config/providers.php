<?php

return [
	// Application Service Providers
	Application\Provider\Config\ServiceProvider::class,
	Application\Provider\UrlResolver\ServiceProvider::class,
	Application\Provider\ModelsCache\ServiceProvider::class,
	Application\Provider\ViewCache\ServiceProvider::class,
	Application\Provider\FileSystem\ServiceProvider::class,
	Application\Provider\Logger\ServiceProvider::class,
	Application\Provider\Security\ServiceProvider::class,
	Application\Provider\Session\ServiceProvider::class,
	Application\Provider\VoltTemplate\ServiceProvider::class,
	Application\Provider\View\ServiceProvider::class,
	Application\Provider\Tag\ServiceProvider::class,
	Application\Provider\Database\ServiceProvider::class,
	Application\Provider\ModelsManager\ServiceProvider::class,
	Application\Provider\ModelsMetadata\ServiceProvider::class,
	Application\Provider\Queue\ServiceProvider::class,
	Application\Provider\Routing\ServiceProvider::class,
	Application\Provider\Dispatcher\ServiceProvider::class,
	Application\Provider\Crypt\ServiceProvider::class,
	//Application\Provider\Markdown\ServiceProvider::class,
	//Application\Provider\Notifications\ServiceProvider::class,
	Application\Provider\Flash\ServiceProvider::class,
	//Application\Provider\SearchEngine\ServiceProvider::class,
	Application\Provider\Avatar\ServiceProvider::class,
	Application\Provider\Timezone\ServiceProvider::class,
	//Application\Provider\Breadcrumbs\ServiceProvider::class,
	//Application\Provider\Captcha\ServiceProvider::class,
	Application\Provider\Annotations\ServiceProvider::class,
	//Application\Provider\Email\ServiceProvider::class,
	Application\Provider\Mailer\ServiceProvider::class,
	Application\Provider\Assets\ServiceProvider::class,
	//Application\Provider\Discord\ServiceProvider::class,

	// Third Party Providers
	// ...
];
