<?php

class TOGoS_PNGChunks_Util
{
	const PNG_HEADER = "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A";
	
	public static function calculateChunkCrc( array $chunkInfo ) {
		return crc32( $chunkInfo['typeBytes'].$chunkInfo['data'] );
	}
	
	public static function calculateChunkCrcBytes( array $chunkInfo ) {
		return pack('N', self::calculateChunkCrc($chunkInfo));
	}
	
	public static function verifyChunkCrc( array $chunkInfo ) {
		if( $chunkInfo['crcBytes'] !== self::calculateChunkCrcBytes($chunkInfo) ) {
			throw new TOGoS_PNGChunks_CRCMismatchException();
		}
	}
}
