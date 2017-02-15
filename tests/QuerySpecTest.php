<?php
namespace nl\naturalis\bioportal\Test;
use nl\naturalis\bioportal\QuerySpec as QuerySpec;

class QuerySpecTest extends \PHPUnit_Framework_TestCase
{
	public function testSetSizeWithValidInteger ()
	{
		$query = new QuerySpec();
		$query->setSize(10);
		$this->assertEquals(10, $query->getSize());
	}
	
	public function testSetSizeWithValidString ()
	{
		$query = new QuerySpec();
		$query->setSize('10');
		$this->assertEquals(10, $query->getSize());
	}

	/*
	 * Expects an InvalidArgumentException
	 */
	public function testSetSizeWithEmptyString ()
	{
		$query = new QuerySpec();
		$e = new \stdClass();
		try {
			$query->setSize();
		} catch (\Exception $e) {}
		$this->assertEquals('InvalidArgumentException', get_class($e));
	}
	
	/*
	 * Expects an InvalidArgumentException
	 */
	public function testSetSizeWithInvalidInteger ()
	{
		$query = new QuerySpec();
		$e = new \stdClass();
		try {
			$query->setSize(-1);
		} catch (\Exception $e) {}
		$this->assertEquals('InvalidArgumentException', get_class($e));
	}
	
	/*
	 * Expects an InvalidArgumentException
	 */
	public function testSetSizeWithInvalidString ()
	{
		$query = new QuerySpec();
		$e = new \stdClass();
		try {
			$query->setSize('a');
		} catch (\Exception $e) {}
		$this->assertEquals('InvalidArgumentException', get_class($e));
	}
	
	
}