<?php
/**
 * Created by PhpStorm.
 * User: gamalan
 * Date: 4/7/18
 * Time: 12:58 PM
 */

namespace Application\Task;


use Application\Console\AbstractTask;

class Storage extends AbstractTask {

	/**
	 * @Doc("Clear temporary storage data")
	 */
	public function clear() {
		$param = $this->parseOption();
		if ( isset( $param['a'] ) ) {
			switch ( $param['a'] ) {
				case 'export':
					$this->clearExport();
					break;
				default:
					break;
			}
		} else {
			$this->output( "No selected data to clear" );
		}
	}

	/**
	 * @Doc()
	 */
	public function clearExport() {
		try {
			$this->output( "Delete temporary exported subscriber files : " );
			foreach ( glob( public_path( 'temp' ) . '/*.*' ) as $file ) {
				if ( strpos( $file, 'index.html' ) === false ) {
					$modified_datetime = new \DateTime();
					$modified_datetime->setTimestamp( filemtime( $file ) );
					$lifetime = date_diff( new \DateTime( 'now' ), $modified_datetime );
					$this->output( $file . " Lifetime : " . $lifetime );
					if ( $lifetime->d >= 5 ) {
						$this->output( 'Delete ' . $file . ' after ' . $lifetime->d . ' day(s) ' );
						unlink( $file );
					}
				}
			}
		} catch ( \Throwable $exc ) {
			$this->output( $exc->getMessage() );
		}
	}
}