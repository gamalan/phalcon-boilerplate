<?php

namespace Application\Models;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Behavior\Timestampable;

class BaseModel extends Model {

	public $timestamp = false;
	public $userstamp = false;
	public $softdelete = false;
	//
	public $is_deleted;
	public $created_at;
	public $modified_at;
	public $deleted_at;

	public static function find( $parameters = null ) {
		return parent::find( $parameters );
	}

	public static function findFirst( $parameters = null ) {
		return parent::findFirst( $parameters );
	}

	public function beforeValidationOnCreate() {
		if ( $this->timestamp ) {
			$this->created_at  = time();
			$this->modified_at = time();
		}
		if ( $this->softdelete ) {
			if ( ! isset( $this->is_deleted ) ) {
				$this->is_deleted = 0;
			}
			if ( ! isset( $this->deleted_at ) ) {
				$this->deleted_at = 0;
			}
		}
	}

	public function beforeValidationOnUpdate() {
		if ( $this->timestamp && empty( $this->is_deleted ) ) {
			$this->modified_at = time();
		} else {
			$this->deleted_at = time();
		}
	}

	public function softdelete() {
		$this->is_deleted = 1;
		$this->update();
	}

}
