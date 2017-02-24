<?php
namespace nl\naturalis\bioportal\Test;
use nl\naturalis\bioportal\Condition as Condition;
use nl\naturalis\bioportal\QuerySpec as QuerySpec;

class QuerySpecTest extends \PHPUnit_Framework_TestCase
{
	/*
	 * Set size; setFrom() uses exactly the same logic,
	 * so testing that would be superfluous.
	 */
	public function testSetSizeWithValidInteger ()
	{
		$expected = json_encode(10);
		$query = new QuerySpec();
		$query->setSize(10);
		$this->assertEquals($expected, $query->getSize());
	}
	
	public function testSetSizeWithValidString ()
	{
		$expected = json_encode(10);
		$query = new QuerySpec();
		$query->setSize('10');
		$this->assertEquals($expected, $query->getSize());
	}

	public function testSetSizeWithEmptyString ()
	{
		$query = new QuerySpec();
		$e = new \stdClass();
		try {
			$query->setSize();
		} catch (\Exception $e) {}
		$this->assertEquals('InvalidArgumentException', get_class($e));
	}
	
	public function testSetSizeWithInvalidInteger ()
	{
		$query = new QuerySpec();
		$e = new \stdClass();
		try {
			$query->setSize(-1);
		} catch (\Exception $e) {}
		$this->assertEquals('InvalidArgumentException', get_class($e));
	}
	
	public function testSetSizeWithInvalidString ()
	{
		$query = new QuerySpec();
		$e = new \stdClass();
		try {
			$query->setSize('a');
		} catch (\Exception $e) {}
		$this->assertEquals('InvalidArgumentException', get_class($e));
	}

	/*
	 * Logical operator
	 */
	public function testSetCorrectlyConstructedLogicalOperator ()
	{
		$expected = json_encode('AND');
		$query = new QuerySpec();
		$query->setLogicalOperator('and');
		$this->assertEquals($expected, $query->getLogicalOperator());
	}
	
	public function testSetEmptyLogicalOperator ()
	{
		$query = new QuerySpec();
		$e = new \stdClass();
		try {
			$query->setLogicalOperator();
		} catch (\Exception $e) {}
		$this->assertEquals('UnexpectedValueException', get_class($e));
	}

	public function testSetIncorrectlyConstructedLogicalOperator ()
	{
		$query = new QuerySpec();
		$e = new \stdClass();
		try {
			$query->setLogicalOperator('maybe');
		} catch (\Exception $e) {}
		$this->assertEquals('UnexpectedValueException', get_class($e));
	}
	
	/*
	 * Fields
	 */
	public function testSetCorrectlyConstructedFields ()
	{
		$expected = json_encode(['acceptedName.genusOrMonomial', 
			'acceptedName.specificEpithet']);
		$query = new QuerySpec();
		$query->setFields(['acceptedName.genusOrMonomial', 
			'acceptedName.specificEpithet']);
		$this->assertEquals($expected, $query->getFields());
	}
	
	public function testSetEmptyFields ()
	{
		$query = new QuerySpec();
		$e = new \stdClass();
		try {
			$query->setFields();
		} catch (\Exception $e) {}
		$this->assertEquals('InvalidArgumentException', get_class($e));
	}

	public function testSetFieldsIncorrectlyConstructedAsString ()
	{
		$query = new QuerySpec();
		$e = new \stdClass();
		try {
			$query->setFields('acceptedName.genusOrMonomial', 
				'acceptedName.specificEpithet');
		} catch (\Exception $e) {}
		$this->assertEquals('InvalidArgumentException', get_class($e));
	}
	
	/*
	 * Condition
	 */
	public function testSetCorrectlyConstructedCondition ()
	{
		$expected = '[{"field":"acceptedName.genusOrMonomial","operator":"EQUALS_IC","value":"larus"}]';
		$condition = new Condition('acceptedName.genusOrMonomial', 'EQUALS_IC', 'larus');
		$query = new QuerySpec();
		$query->addCondition($condition);
		$this->assertEquals($expected, $query->getConditions());
	}
	
	public function testSetIncorrectlyConstructedCondition ()
	{
		$query = new QuerySpec();
		$e = new \stdClass();
		try {
			$query->addCondition('acceptedName.genusOrMonomial', 'EQUALS_IC', 'larus');
		} catch (\Exception $e) {}
		$this->assertEquals('InvalidArgumentException', get_class($e));
	}
	
	/*
	 * Sort by
	 */
	public function testSetCorrectlyConstructedSortByWithoutSortOrder ()
	{
		$expected = '[{"path":"acceptedName.genusOrMonomial","sortOrder":"ASC"}]';
		$query = new QuerySpec();
		$query->sortBy('acceptedName.genusOrMonomial');
		$this->assertEquals($expected, $query->getSortFields());
	}
	
	public function testSetCorrectlyConstructedSortByWithSortOrder ()
	{
		$expected = '[{"path":"acceptedName.genusOrMonomial","sortOrder":"DESC"}]';
		$query = new QuerySpec();
		$query->sortBy('acceptedName.genusOrMonomial', 'desc');
		$this->assertEquals($expected, $query->getSortFields());
	}
	
	public function testSetIncorrectlyConstructedSortBy ()
	{
		$expected = '[{"path":"acceptedName.genusOrMonomial","sortOrder":"ASC"},' .
				'{"path":"acceptedName.specificEpithet","sortOrder":"DESC"}]';
		$query = new QuerySpec();
		$query->setSortFields([
				['acceptedName.genusOrMonomial'],
				['acceptedName.specificEpithet', 'desc']
		]);
		$this->assertEquals($expected, $query->getSortFields());
	}
	
	// Mix up sortBy() with setSortFields()
	public function testSetIncorrectlyConstructedSortByAsArray ()
	{
		$query = new QuerySpec();
		$e = new \stdClass();
		try {
			$query->sortBy([
				['acceptedName.genusOrMonomial'],
				['acceptedName.specificEpithet', 'desc']
			]);;
		} catch (\Exception $e) {}
		$this->assertEquals('InvalidArgumentException', get_class($e));
	}
	
	public function testSetCorrectlyConstructedSetSortFields ()
	{
		$expected = '[{"path":"acceptedName.genusOrMonomial","sortOrder":"ASC"},' .
				'{"path":"acceptedName.specificEpithet","sortOrder":"DESC"}]';
		$query = new QuerySpec();
		$query->setSortFields([
			['acceptedName.genusOrMonomial'],
			['acceptedName.specificEpithet', 'desc']
		]);
		$this->assertEquals($expected, $query->getSortFields());
	}
	
	public function testSetEmptySortBy ()
	{
		$query = new QuerySpec();
		$e = new \stdClass();
		try {
			$query->sortBy();
		} catch (\Exception $e) {}
		$this->assertEquals('InvalidArgumentException', get_class($e));
	}

	public function testIncorrectlyConstructedSortBySortOrder ()
	{
		$query = new QuerySpec();
		$e = new \stdClass();
		try {
			$query->sortBy('acceptedName.genusOrMonomial', 'ascending');
		} catch (\Exception $e) {}
		$this->assertEquals('UnexpectedValueException', get_class($e));
	}

	/*
	 * Test nicely decked out spec
	 */
	public function testSetCorrectlyConstructedFullQuerySpec ()
	{
		$expected = '{"conditions":[{"field":"sourceSystem.name","operator":"EQUALS",' . 
			'"value":"Naturalis - Nederlands Soortenregister"},{"field":"defaultClassification.genus",' . 
			'"operator":"EQUALS_IC","value":"larus","or":[{"field":"acceptedName.genusOrMonomial",' . 
			'"operator":"LIKE","value":"larus"}]}],"from":5,"logicalOperator":"AND","size":25}';
		$c = new Condition('defaultClassification.genus', 'EQUALS_IC', 'larus');
		$c->setOr('acceptedName.genusOrMonomial', 'LIKE', 'larus');		
		$d = new Condition('sourceSystem.name', 'EQUALS', 'Naturalis - Nederlands Soortenregister');
		$query = new QuerySpec();
		$query
			->setFrom('5')
			->setSize(25)
			->setLogicalOperator('and')
			->addCondition($d)
			->addCondition($c);
		$this->assertEquals($expected, $query->getQuerySpec(false));
	}
	
	public function testSetConstantScore () {
		$expected = true;
		$query = new QuerySpec();
		$query->setConstantScore();
		$this->assertEquals($expected, $query->isConstantScore());
	}
	
	
}