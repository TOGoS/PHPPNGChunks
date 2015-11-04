<?php

class TOGoS_PNGChunks_Parser
{
	const OPT_VALIDATE = 'validate';
	
	/**
	 * @param callable $chunkCallback will be called with array(
	 *   'typeBytes' => 4-byte string representing type,
	 *   'data' => chunk data,
	 *   'crcBytes'  => 4-byte CRC
	 * )
	 */
	protected $chunkCallback;
	protected $validating;
	public function __construct( $chunkCallback, array $options=array() ) {
		$this->chunkCallback = $chunkCallback;
		$this->validating = !empty($options[self::OPT_VALIDATE]);
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
				if( $this->validating ) {
					$header = substr($this->buffer, 0, 8);
					if( $header !== TOGoS_PNGChunks_Util::PNG_HEADER ) {
						throw new TOGoS_PNGChunks_MalformedDataException("Bad PNG header.");
					}
				}
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
					$chunkInfo = array(
						'data' => $data,
						'typeBytes' => $typeBytes,
						'crcBytes' => $crcBytes
					);
					if( $this->validating ) {
						TOGoS_PNGChunks_Util::verifyChunkCrc($chunkInfo);
					}
					call_user_func($this->chunkCallback, $chunkInfo);
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
			throw new TOGoS_PNGChunks_MalformedDataException(strlen($this->buffer)." bytes of extra data at end of PNG stream!");
		}
	}
}
