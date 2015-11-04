<?php

class TOGoS_PNGChunks_ReadWriteReadTest extends PHPUnit_Framework_TestCase
{
	public function testRead() {
		$testPngData = file_get_contents('test/pnggrad8rgb.png');
		if( $testPngData === false ) throw new Exception("Failed to open test PNG file.");
		
		$collector = new EarthIT_Collector();
		$parser = new TOGoS_PNGChunks_Parser($collector, array(
			TOGoS_PNGChunks_Parser::OPT_VALIDATE => true
		));
		
		$parser->data($testPngData);
		$parser->end();
		
		$readChunks = $collector->collection;
		
		$rewrittenBlob = new TOGoS_PNGChunks_PNGBlob($readChunks);
		
		$this->assertEquals( $testPngData, (string)$rewrittenBlob );
	}
}
