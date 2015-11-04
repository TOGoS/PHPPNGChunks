<?php

class TOGoS_PNGChunks_Emitter
{
	public function emit( array $chunkInfo, $callback ) {
		if( !isset($chunkInfo['crcBytes']) ) $chunkInfo['crc'] = TOGoS_PNGChunks_Util::calculateChunkCrcBytes($chunkInfo);
		$chunkInfo['lengthBytes'] = pack('N', strlen($chunkInfo['data']));
		call_user_func( $callback, $chunkInfo['lengthBytes'].$chunkInfo['typeBytes'] );
		call_user_func( $callback, $chunkInfo['data'] );
		call_user_func( $callback, $chunkInfo['crcBytes'] );
	}
}
