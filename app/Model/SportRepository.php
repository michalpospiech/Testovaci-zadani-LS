<?php


namespace App\Model;


use Nette\Database\ResultSet;

class SportRepository extends Repository
{

	/**
	 * Zapise sporty do databaze dle znormalizovanych dat
	 *
	 * @param array $sports
	 * @return ResultSet
	 */
	public function addSports(array $sports): ResultSet
	{
		return $this->connection->query('INSERT INTO sport', $sports, 'ON DUPLICATE KEY UPDATE name = VALUES(name)');
	}

}