<?php

class TOGoS_PNGChunks_Parser
{
	/**
	 * @param callable $chunkCallback will be called with array(
	 *   'typeBytes' => 4-byte string representing type,
	 *   'data' => chunk data,
	 *   'crcBytes'  => 4-byte CRC
	 * )
	 */
	protected $chunkCallback;
	public function __construct( $chunkCallback ) {
		$this->chunkCallback = $chunkCallback;
	}
	
	protected $buffer = '';
	public function data( $buffer ) {
		$this->buffer .= $buffer;
		while( $this->_update() );
	}
	
	const STATE_BEGIN = 'begin';
	const STATE_HEADER_READ = 'header-read';
	
	protected $state = self::STATE_BEGIN;
	
	protected function _update() {
		switch( $this->state ) {
		case self::STATE_BEGIN:
			if( strlen($this->buffer) >= 8 ) {
				$this->buffer = substr($this->buffer, 8);
				$this->state = self::STATE_HEADER_READ;
				return true;
			}
			return false;
		case self::STATE_HEADER_READ:
			if( strlen($this->buffer) >= 12 ) { // 12 being the minimum chunk length
				$unpacked = unpack('Nlen', $this->buffer);
				$length = $unpacked['len'];
				$chunkLen = $length+12;
				if( strlen($this->buffer) >= $chunkLen ) {
					$typeBytes = substr($this->buffer,4,4);
					$data = substr($this->buffer, 8, $length);
					$crcBytes = substr($this->buffer, $chunkLen-4, 4);
					call_user_func($this->chunkCallback, array(
						'data' => $data,
						'typeBytes' => $typeBytes,
						'crcBytes' => $crcBytes
					));
					$this->buffer = substr($this->buffer,$chunkLen);
					return true;
				}
			}
			return false;
		default:
			throw new Exception("Invalid state: ".$this->state);
		}
	}
	
	public function end() {
		if( strlen($this->buffer) != 0 ) {
			throw new Exception(strlen($this->buffer)." bytes of extra data at end of PNG stream!");
		}
	}
}
