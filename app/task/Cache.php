<?php
/**
 * Created by PhpStorm.
 * User: gamalan
 * Date: 4/6/18
 * Time: 3:31 PM
 */

namespace Application\Task;


use Application\Console\AbstractTask;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Cache extends AbstractTask {
	protected $excludeFileNames = [
		'.',
		'..',
		'.gitkeep',
		'.gitignore',
	];

	/**
	 * @Doc("Clearing the application cache")
	 */
	public function clear() {
		$this->output( 'Start' );

		$this->output( 'Clear file cache...' );
		$this->clearFileCache( cache_path() );

		$this->output( 'Clear models cache...' );
		$this->clearCache( 'modelsCache' );

		$this->output( 'Clear view cache...' );
		$this->clearCache( 'viewCache' );

		$this->output( 'Clear annotations cache...' );
		$this->clearCache( 'annotations' );

		$this->output( 'Done' );
	}

	/**
	 * @Doc("Clear log")
	 */
	public function clearLog() {
		$this->output( 'Start' );

		$this->output( 'Clear log data...' );
		$this->clearFileCache( storage_path('logs') );
		$this->output( 'Done' );
	}

	protected function clearFileCache( $path ) {
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $path ),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ( $iterator as $entry ) {
			if ( $entry->isDir() || in_array( $entry->getBasename(), $this->excludeFileNames ) ) {
				continue;
			}

			unlink( $entry->getPathname() );
		}
	}

	protected function clearCache( $service ) {
		if ( ! container()->has( $service ) ) {
			return;
		}

		$service = container( $service );

		$service->flush();
	}
}