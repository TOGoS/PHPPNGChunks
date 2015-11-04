<?php

class TOGoS_PNGChunks_PNGBlob extends Nife_AbstractBlob
{
	protected $chunkInfos;
	/**
	 * @param array $chunkInfos array of array('typeBytes'=>'IHDR' (or whatever), 'data'=>'asdf1234')
	 */
	public function __construct( array $chunkInfos ) {
		$this->chunkInfos = $chunkInfos;
	}
	
	public function writeTo( $callback ) {
		call_user_func( $callback, TOGoS_PNGChunks_Util::PNG_HEADER );
		$emittedIEnd = false;
		foreach( $this->chunkInfos as $chunkInfo ) {
			TOGoS_PNGChunks_Emitter::emit($chunkInfo, $callback);
			if( $chunkInfo['typeBytes'] === 'IEND' ) $emittedIEnd = true;
		}
		if( !$emittedIEnd ) {
			TOGoS_PNGChunks_Emitter::emit(array(
				'typeBytes' => 'IEND',
				'data' => '',
			), $callback);
		}
	}
}
