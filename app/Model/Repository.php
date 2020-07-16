<?php


namespace App\Model;


use Nette\Database\Context;
use Nette\Database\Table\IRow;
use Nette\Database\Table\Selection;
use Nette\SmartObject;
use Tracy\Debugger;
use Tracy\ILogger;

abstract class Repository
{

	use SmartObject;

	/** @var Context */
	protected $connection;

	/** @var string */
	protected $keycol = 'id';

	/** @mixed Selection | ActiveRow */
	protected $table;

	/** @var string */
	protected $tableName;

	/** @var boolean */
	public static $TRANSACTION = false;

	public function __construct(Context $database)
	{
		$this->connection = $database;
	}

	/**
	 * Vrati pripojeni k tabulce
	 *
	 * @return Selection
	 */
	protected function getTable(): Selection
	{
		return $this->connection->table($this->getTableName());
	}

	/**
	 * Vrati nazev tabulky v databazi
	 *
	 * @return string
	 */
	protected function getTableName(): string
	{
		if (!$this->tableName) {
			$name = [];
			preg_match('~(\w+)Repository$~', get_class($this), $name);

			$this->tableName = strtolower(preg_replace('~([a-z])([A-Z])~', '$1_$2', $name[1]));
		}

		return $this->tableName;
	}

	/**
	 * Vypise vsechny zaznamy z konkretni tabulky
	 *
	 * @return Selection
	 */
	public function findAll(): Selection
	{
		return $this->getTable();
	}

	/**
	 * Vypise konkretni zaznamy z tabulky
	 *
	 * @param array $where
	 * @return Selection
	 */
	public function findBy(array $where): Selection
	{
		return $this->getTable()->where($where);
	}

	/**
	 * Vrati pole ID zaznamu z tabulky
	 *
	 * @param array $where
	 * @return array
	 */
	public function findIdsBy(array $where): array
	{
		return $this->getTable()->where($where)->fetchPairs(null, 'id');
	}

	/**
	 * Vrati konkretni zaznam z tabulky
	 *
	 * @param array $where
	 * @return IRow
	 */
	public function findOneBy(array $where): IRow
	{
		return $this->findBy($where)->limit(1)->fetch();
	}

	/**
	 * Vrati konkretni zaznam dle ID
	 *
	 * @param string|int $id
	 * @return IRow
	 */
	public function findOneById($id): IRow
	{
		return $this->findOneBy([$this->keycol => $id]);
	}

	/**
	 * Zapne transakci
	 *
	 * @return bool
	 */
	public function beginTransaction(): bool
	{
		if (self::$TRANSACTION) {
			return false;
		}

		self::$TRANSACTION = true;
		$this->connection->beginTransaction();
		return true;
	}

	/**
	 * Ukonci transakci
	 */
	public function commit(): void
	{
		$this->connection->commit();
		self::$TRANSACTION = false;
	}

	/**
	 * Vrati transakci zpet
	 */
	public function rollback(): void
	{
		try {
			$this->connection->rollBack();
		} catch (\Exception $exception) {
			Debugger::log($exception, ILogger::EXCEPTION);
		}
		self::$TRANSACTION = false;
	}

}