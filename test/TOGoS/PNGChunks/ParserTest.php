<?php

class TOGoS_PNGChunks_ParserTest extends PHPUnit_Framework_TestCase
{
	public function testRead() {
		$fh = fopen('test/pnggrad8rgb.png', 'rb');
		if( $fh === false ) throw new Exception("Failed to open test PNG file.");
		
		$collector = new EarthIT_Collector();
		$parser = new TOGoS_PNGChunks_Parser($collector, array(
			TOGoS_PNGChunks_Parser::OPT_VALIDATE => true
		));
		
		while( ($dat = fread($fh,100)) !== false and strlen($dat) > 0 ) {
			$parser->data($dat);
		}
		$parser->end();

		$x = array();
		foreach( $collector->collection as $chunk ) {
			$x[] = array('typeBytes'=>$chunk['typeBytes'], 'dataLength'=>strlen($chunk['data']));
			//echo "{$chunk['typeBytes']} (".strlen($chunk['data'])." bytes)\n";
		}
		$this->assertEquals( array(
			array('typeBytes'=>'IHDR', 'dataLength'=> 13),
			array('typeBytes'=>'IDAT', 'dataLength'=>919),
			array('typeBytes'=>'IEND', 'dataLength'=>0)
		), $x );
	}

	public function testReadInvalid() {
		$collector = new EarthIT_Collector();
		$parser = new TOGoS_PNGChunks_Parser($collector, array(
			TOGoS_PNGChunks_Parser::OPT_VALIDATE => true
		));
		
		$caught = false;
		try {
			$parser->data("ASDF!@#$");
		} catch( TOGoS_PNGChunks_MalformedDataException $e ) {
			$caught = true;
		}
		
		$this->assertTrue($caught, "Should have thrown a MalformedDataException");
	}
}
