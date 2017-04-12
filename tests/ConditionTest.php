<?php
namespace nl\naturalis\bioportal\Test;
use nl\naturalis\bioportal\Condition as Condition;

class ConditionTest extends \PHPUnit_Framework_TestCase
{
	public function testCorrectlyConstructedCondition () {
		$expected = '{"field":"acceptedName.genusOrMonomial",' .
			'"operator":"EQUALS_IC","value":"larus"}';
		$condition = new Condition('acceptedName.genusOrMonomial', 'EQUALS_IC', 'larus');
		$this->assertEquals($expected, $condition->getCondition());
	}
	
	public function testCorrectlyConstructedsetAndConditionWithThreeParameters () {
		$expected = '{"field":"acceptedName.genusOrMonomial","operator":"EQUALS_IC",' .
			'"value":"larus","and":[{"field":"acceptedName.specificEpithet",' .
			'"operator":"MATCHES","value":"fuscus"}]}';
		$condition = new Condition('acceptedName.genusOrMonomial', 'EQUALS_IC', 'larus');
		$condition->setAnd('acceptedName.specificEpithet', 'MATCHES', 'fuscus');
		$this->assertEquals($expected, $condition->getCondition());
	}
	
	public function testCorrectlyConstructedsetAndConditionWithCondition () {
		$expected = '{"field":"acceptedName.genusOrMonomial","operator":"EQUALS_IC",' .
				'"value":"larus","and":[{"field":"acceptedName.specificEpithet",' .
				'"operator":"MATCHES","value":"fuscus"}]}';
		$condition = new Condition('acceptedName.genusOrMonomial', 'EQUALS_IC', 'larus');
		$condition2 = new Condition('acceptedName.specificEpithet', 'MATCHES', 'fuscus');
		$condition->setAnd($condition2);
		$this->assertEquals($expected, $condition->getCondition());
	}
	
	public function testCorrectlyConstructedsetAndConditionWithIncorrectConditionAsArray () {
		$condition = new Condition('acceptedName.genusOrMonomial', 'EQUALS_IC', 'larus');
		$condition2 = ['acceptedName.specificEpithet', 'MATCHES', 'fuscus'];
		$e = new \stdClass();
		try {
			$condition->setAnd($condition2);
		} catch (\Exception $e) {}
		$this->assertEquals('InvalidArgumentException', get_class($e));
	}	
	
	public function testCorrectlyConstructedsetOrConditionWithThreeParameters () {
		$expected = '{"field":"acceptedName.genusOrMonomial","operator":"EQUALS_IC",' .
				'"value":"larus","or":[{"field":"acceptedName.specificEpithet",' .
				'"operator":"MATCHES","value":"fuscus"}]}';
		$condition = new Condition('acceptedName.genusOrMonomial', 'EQUALS_IC', 'larus');
		$condition->setOr('acceptedName.specificEpithet', 'MATCHES', 'fuscus');
		$this->assertEquals($expected, $condition->getCondition());
	}
	
	public function testCorrectlyConstructedsetOrConditionWithCondition () {
		$expected = '{"field":"acceptedName.genusOrMonomial","operator":"EQUALS_IC",' .
				'"value":"larus","or":[{"field":"acceptedName.specificEpithet",' .
				'"operator":"MATCHES","value":"fuscus"}]}';
		$condition = new Condition('acceptedName.genusOrMonomial', 'EQUALS_IC', 'larus');
		$condition2 = new Condition('acceptedName.specificEpithet', 'MATCHES', 'fuscus');
		$condition->setOr($condition2);
		$this->assertEquals($expected, $condition->getCondition());
	}
	
	public function testCorrectlyConstructedsetOrConditionWithIncorrectConditionAsArray () {
		$condition = new Condition('acceptedName.genusOrMonomial', 'EQUALS_IC', 'larus');
		$condition2 = ['acceptedName.specificEpithet', 'MATCHES', 'fuscus'];
		$e = new \stdClass();
		try {
			$condition->setOr($condition2);
		} catch (\Exception $e) {}
		$this->assertEquals('InvalidArgumentException', get_class($e));
	}
	
	public function testCorrectlyConstructedConditionWithoutValue () {
		$expected = '{"field":"acceptedName.genusOrMonomial","operator":"NOT_EQUALS"}';
		$condition = new Condition('acceptedName.genusOrMonomial', 'NOT_EQUALS');
		$this->assertEquals($expected, $condition->getCondition());
	}
	
	public function testCorrectlyConstructedConditionWithZeroValue () {
		$expected = '{"field":"taxonCount","operator":"GT","value":0}';
		$condition = new Condition('taxonCount', 'GT', 0);
		$this->assertEquals($expected, $condition->getCondition());
	}

	public function testIncorrectlyConstructedConditionWithEmptyString () {
		$e = new \stdClass();
		try {
			$condition = new Condition('acceptedName.genusOrMonomial', 'EQUALS_IC', '');
		} catch (\Exception $e) {}
		$this->assertEquals('InvalidArgumentException', get_class($e));
	}
	
	public function testConditionWithoutConstructor () {
		$e = new \stdClass();
		try {
			$condition = new Condition();
		} catch (\Exception $e) {}
		$this->assertEquals('InvalidArgumentException', get_class($e));
	}
	
	public function testConditionWithoutField () {
		$e = new \stdClass();
		try {
			$condition = new Condition(null, 'EQUALS_IC', 'larus');;
		} catch (\Exception $e) {}
		$this->assertEquals('InvalidArgumentException', get_class($e));
	}
	
	public function testConditionWithoutOperator () {
		$e = new \stdClass();
		try {
			$condition = new Condition('acceptedName.genusOrMonomial', null, 'larus');;
		} catch (\Exception $e) {}
		$this->assertEquals('InvalidArgumentException', get_class($e));
	}
	
	public function testConditionSetValue () {
		$expected = 'argentatus';
		$condition = new Condition('acceptedName.specificEpithet', 'MATCHES', 'fuscus');
		$condition->setValue('argentatus');
		$this->assertEquals($expected, $condition->getValue());
	}
	
	public function testConditionSetField () {
		$expected = 'acceptedName.genusOrMonomial';
		$condition = new Condition('acceptedName.specificEpithet', 'MATCHES', 'larus');
		$condition->setField('acceptedName.genusOrMonomial');
		$this->assertEquals($expected, $condition->getField());
	}
	
	public function testConditionSetOperator () {
		$expected = 'EQUALS';
		$condition = new Condition('acceptedName.specificEpithet', 'MATCHES', 'fuscus');
		$condition->setOperator('EQUALS');
		$this->assertEquals($expected, $condition->getOperator());
	}
	
	public function testResetCompleteCondition () {
		$expected = '{"field":"acceptedName.genusOrMonomial",' .
			'"operator":"EQUALS_IC","value":"larus"}';
		$condition = new Condition('acceptedName.specificEpithet', 'MATCHES', 'fuscus');
		$condition
			->setField('acceptedName.genusOrMonomial')
			->setOperator('EQUALS_IC')
			->setValue('larus');
		$this->assertEquals($expected, $condition->getCondition());
	}
	
	public function testCompleteConditionSetWithBoostConstantScoreAndNot () {
		$expected = '{"field":"acceptedName.specificEpithet",' .
				'"operator":"MATCHES","value":"fuscus","not":"NOT","boost":1.3,' .
				'"constantScore":true}';
		$condition = new Condition('acceptedName.specificEpithet', 'MATCHES', 'fuscus');
		$condition
			->setNot()
			->setBoost(1.3)
			->setConstantScore();
		$this->assertEquals($expected, $condition->getCondition());
	}
	
	public function testConditionNegate () {
		$expected = '{"field":"acceptedName.specificEpithet",' .
				'"operator":"MATCHES","value":"fuscus","boost":1.3,' .
				'"constantScore":true}';
		$condition = new Condition('acceptedName.specificEpithet', 'MATCHES', 'fuscus');
		$condition
			->setNot()
			->setBoost(1.3)
			->setConstantScore();
		$condition->negate();
		$this->assertEquals($expected, $condition->getCondition());
	}
	
	public function testConditionIsConstantScoreIsTrue () {
		$expected = true;
		$condition = new Condition('acceptedName.specificEpithet', 'MATCHES', 'fuscus');
		$condition->setConstantScore();
		$this->assertEquals($expected, $condition->isConstantScore());
	}
	
	public function testConditionIsNegatedIsTrue () {
		$expected = true;
		$condition = new Condition('acceptedName.specificEpithet', 'MATCHES', 'fuscus');
		$condition->negate();
		$this->assertEquals($expected, $condition->isNegated());
	}
	
	public function testConditionWithIncorrectlyConstructedOperator () {
		$e = new \stdClass();
		try {
			$condition = new Condition('acceptedName.genusOrMonomial', 'AMTCHES', 'larus');;
		} catch (\Exception $e) {}
		$this->assertEquals('UnexpectedValueException', get_class($e));
	}
	
	public function testConditionWithIncorrectlyConstructedEmptyValue () {
		$e = new \stdClass();
		try {
			$condition = new Condition('acceptedName.genusOrMonomial', 'NOT_LIKE');
		} catch (\Exception $e) {}
		$this->assertEquals('InvalidArgumentException', get_class($e));
	}
	
	public function testSetCorrectBoost () {
		$expected = 1.3;
		$condition = new Condition('acceptedName.genusOrMonomial', 'EQUALS_IC', 'larus');
		$condition->setBoost(1.3);
		$this->assertEquals($expected, $condition->getBoost());
	}

	public function testSetIncorrectBoost () {
		$e = new \stdClass();
		try {
			$condition = new Condition('acceptedName.genusOrMonomial', 'NOT_LIKE');
			$condition->setBoost('a lot');
		} catch (\Exception $e) {}
		$this->assertEquals('InvalidArgumentException', get_class($e));
	}
	
}