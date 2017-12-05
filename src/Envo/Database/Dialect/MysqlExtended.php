<?php

namespace Envo\Database\Dialect;

use Phalcon\Db\Exception;
use Phalcon\Db\Dialect\Mysql;

/**
 * Phalcon\Db\Dialect\MysqlExtended
 *
 * Source: https://raw.githubusercontent.com/phalcon/incubator/master/Library/Phalcon/Db/Dialect/MysqlExtended.php
 * https://github.com/phalcon/incubator
 *
 * Generates database specific SQL for the MySQL RDBMS.
 *
 * This is an extended MySQL dialect that introduces workarounds for some common MySQL-only functions like
 * search based on FULLTEXT indexes and operations with date intervals.
 *
 * <code>
 * use Phalcon\Db\Adapter\Pdo\Mysql;
 * use Phalcon\Db\Dialect\MysqlExtended;
 *
 * $connection = new Mysql([
 *     'host'         => 'localhost',
 *     'username'     => 'root',
 *     'password'     => 'secret',
 *     'dbname'       => 'enigma',
 *     'dialectClass' => MysqlExtended::class,
 * ]);
 * </code>
 *
 * @package Phalcon\Db\Dialect
 */
class MysqlExtended extends Mysql
{
	/**
	 * Transforms an intermediate representation for a expression into a database system valid expression.
	 *
	 * @param array  $expression
	 * @param string $escapeChar
	 * @param mixed  $bindCounts
	 *
	 * @return string
	 *
	 * @throws \Phalcon\Db\Exception
	 * @throws \Exception
	 */
	public function getSqlExpression(array $expression, $escapeChar = null, $bindCounts = null)
	{
		if ( $expression['type'] === 'functionCall') {
			switch (strtoupper($expression['name'])) {
				case 'DATE_INTERVAL':
					if (count($expression['arguments']) !== 2) {
						throw new Exception('DATE_INTERVAL requires 2 parameters');
					}
					
					switch ($expression['arguments'][1]['value']) {
						case "'MICROSECOND'":
							return 'INTERVAL ' . $this->getSqlExpression($expression['arguments'][0]) . ' MICROSECOND';
						case "'SECOND'":
							return 'INTERVAL ' . $this->getSqlExpression($expression['arguments'][0]) . ' SECOND';
						case "'MINUTE'":
							return 'INTERVAL ' . $this->getSqlExpression($expression['arguments'][0]) . ' MINUTE';
						case "'HOUR'":
							return 'INTERVAL ' . $this->getSqlExpression($expression['arguments'][0]) . ' HOUR';
						case "'DAY'":
							return 'INTERVAL ' . $this->getSqlExpression($expression['arguments'][0]) . ' DAY';
						case "'WEEK'":
							return 'INTERVAL ' . $this->getSqlExpression($expression['arguments'][0]) . ' WEEK';
						case "'MONTH'":
							return 'INTERVAL ' . $this->getSqlExpression($expression['arguments'][0]) . ' MONTH';
						case "'QUARTER'":
							return 'INTERVAL ' . $this->getSqlExpression($expression['arguments'][0]) . ' QUARTER';
						case "'YEAR'":
							return 'INTERVAL ' . $this->getSqlExpression($expression['arguments'][0]) . ' YEAR';
						default:
							throw new Exception('DATE_INTERVAL unit is not supported');
					}
					break;
				
				case 'FULLTEXT_MATCH':
					if (count($expression['arguments']) < 2) {
						throw new Exception('FULLTEXT_MATCH requires 2 parameters');
					}
					
					$arguments = [];
					$length = count($expression['arguments']) - 1;
					for ($i = 0; $i < $length; $i++) {
						$arguments[] = $this->getSqlExpression($expression['arguments'][ $i]);
					}
					
					return 'MATCH(' . implode(', ', $arguments) . ') AGAINST (' .
						$this->getSqlExpression($expression['arguments'][ $length]) . ')';
					break;
				
				case 'FULLTEXT_MATCH_BMODE':
					if (count($expression['arguments']) < 2) {
						throw new Exception('FULLTEXT_MATCH requires 2 parameters');
					}
					
					$arguments = [];
					$length = count($expression['arguments']) - 1;
					for ($i = 0; $i < $length; $i++) {
						$arguments[] = $this->getSqlExpression($expression['arguments'][ $i]);
					}
					
					return 'MATCH(' . implode(', ', $arguments) . ') AGAINST (' .
						$this->getSqlExpression($expression['arguments'][ $length]) . ' IN BOOLEAN MODE)';
					break;
				
				case 'REGEXP':
					if (count($expression['arguments']) !== 2) {
						throw new Exception('REGEXP requires 2 parameters');
					}
					
					return $this->getSqlExpression($expression['arguments'][0]) .
						' REGEXP (' . $this->getSqlExpression($expression['arguments'][1]) . ')';
					break;
			}
		}
		
		return parent::getSqlExpression($expression, $escapeChar, $bindCounts);
	}
}