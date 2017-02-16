<?php
namespace nl\naturalis\bioportal\Test;
use nl\naturalis\bioportal\Condition as Condition;

class ConditionTest extends \PHPUnit_Framework_TestCase
{
	public function testCorrectlyConstructedCondition ()
	{
		$expected = '{"field":"acceptedName.genusOrMonomial",' .
			'"operator":"EQUALS_IC","value":"larus"}';
		$condition = new Condition('acceptedName.genusOrMonomial', 'EQUALS_IC', 'larus');
		$this->assertEquals($expected, $condition->getCondition());
	}
	
	public function testCorrectlyConstructedAddAndCondition ()
	{
		$expected = '{"field":"acceptedName.genusOrMonomial","operator":"EQUALS_IC",' .
				'"value":"larus","and":[{"field":"acceptedName.specificEpithet",' .
				'"operator":"MATCHES","value":"fuscus"}]}';
		$condition = new Condition('acceptedName.genusOrMonomial', 'EQUALS_IC', 'larus');
		$condition->addAnd('acceptedName.specificEpithet', 'MATCHES', 'fuscus');
		$this->assertEquals($expected, $condition->getCondition());
	}
	
	public function testCorrectlyConstructedAddOrCondition ()
	{
		$expected = '{"field":"acceptedName.genusOrMonomial","operator":"EQUALS_IC",' .
				'"value":"larus","or":[{"field":"acceptedName.specificEpithet",' .
				'"operator":"MATCHES","value":"fuscus"}]}';
		$condition = new Condition('acceptedName.genusOrMonomial', 'EQUALS_IC', 'larus');
		$condition->addOr('acceptedName.specificEpithet', 'MATCHES', 'fuscus');
		$this->assertEquals($expected, $condition->getCondition());
	}
	
	public function testConditionWithCorrectlyConstructedEmptyValue ()
	{
		$expected = '{"field":"acceptedName.genusOrMonomial","operator":"NOT_EQUALS"}';
		$condition = new Condition('acceptedName.genusOrMonomial', 'NOT_EQUALS');
		$this->assertEquals($expected, $condition->getCondition());
	}
	
	public function testConditionWithoutConstructor ()
	{
		$e = new \stdClass();
		try {
			$condition = new Condition();
		} catch (\Exception $e) {}
		$this->assertEquals('InvalidArgumentException', get_class($e));
	}
	
	public function testConditionWithoutField ()
	{
		$e = new \stdClass();
		try {
			$condition = new Condition(null, 'EQUALS_IC', 'larus');;
		} catch (\Exception $e) {}
		$this->assertEquals('InvalidArgumentException', get_class($e));
	}
	
	public function testConditionWithoutOperator ()
	{
		$e = new \stdClass();
		try {
			$condition = new Condition('acceptedName.genusOrMonomial', null, 'larus');;
		} catch (\Exception $e) {}
		$this->assertEquals('InvalidArgumentException', get_class($e));
	}
	
	public function testConditionWithIncorrectlyConstructedOperator ()
	{
		$e = new \stdClass();
		try {
			$condition = new Condition('acceptedName.genusOrMonomial', 'AMTCHES', 'larus');;
		} catch (\Exception $e) {}
		$this->assertEquals('UnexpectedValueException', get_class($e));
	}
	
	public function testConditionWithIncorrectlyConstructedEmptyValue ()
	{
		$e = new \stdClass();
		try {
			$condition = new Condition('acceptedName.genusOrMonomial', 'NOT_LIKE');;
		} catch (\Exception $e) {}
		$this->assertEquals('InvalidArgumentException', get_class($e));
	}
	
	
	
}