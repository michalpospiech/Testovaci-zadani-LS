<?php


namespace App\Model;


use App\Libs\Csv;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Database\Context;
use Nette\Database\ResultSet;
use Nette\Database\Table\Selection;
use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Tracy\Debugger;
use Tracy\ILogger;

class ParticipantRepository extends Repository
{

	/** @var Cache */
	protected $cache;

	/** @var string */
	private $tempDir;

	const CSV_PARTICIPANTS = 'input_participants.csv';
	const CSV_PARTICIPANTS_SPORTS = 'input_participants_sports.csv';

	public function __construct(Context $database, IStorage $IStorage)
	{
		parent::__construct($database);
		$this->cache = new Cache($IStorage, 'participantRepository');
	}

	/**
	 * Nastavi cestu k temp adresari
	 *
	 * @param string $tempDir
	 */
	public function setTempDir($tempDir): void
	{
		$this->tempDir = $tempDir;
	}

	/**
	 * Vrati vsechny data dle klice s vyuzitim cache
	 *
	 * @param string $nameKey
	 * @param bool $useCache
	 * @return array
	 */
	public function getDataByNameKey($nameKey, $useCache = true): array
	{
		$cacheKey = 'nameKey_' . $nameKey;

		if ($useCache && $this->cache->load($cacheKey)) {
			return $this->cache->load($cacheKey);
		}

		$data = $this->getTable()->where('name_key', $nameKey)->fetchPairs('id', 'name');

		try {
			$this->cache->save($cacheKey, $data, [
				Cache::EXPIRE => '1 hour',
				Cache::TAGS => ['nameKey', "nameKey/$nameKey"]
			]);
		} catch (\Throwable $exception) {
			Debugger::log($exception->getMessage(), ILogger::EXCEPTION);
		}

		return $data;
	}

	/**
	 * Vrati ID dle vyhledavaneho jmena s vyuzitim cache
	 *
	 * @param string $name
	 * @param bool $useCache
	 * @return int|null
	 */
	public function getIdByName($name, $useCache = true): ?int
	{
		if (!$useCache) {
			return $this->getTable()->where('name', $name)->fetchField('id');
		}

		$id = array_search($name, $this->getDataByNameKey(Strings::substring($name, 0, 1)));
		return $id ? $id : null;
	}

	/**
	 * Vrati sportovce dle konkretniho sportu a nebo vylouci sportovce z konkretniho sportu
	 *
	 * @param string $sportName
	 * @param bool $without
	 * @return Selection
	 */
	public function getParticipantsBySport($sportName, $without = false): Selection
	{
		$query = $this->connection->table('participant_has_sport')
			->select('participant.id AS participant_id, participant.name AS participant_name, sport.name AS sport_name');

		if (!$without) {
			$query->where('sport.name', $sportName);
		} else {
			$query->where('sport.name != ?', $sportName);
		}

		return $query;
	}

	/**
	 * Zalozi data v databazi na zaklade znormalizovanych dat
	 *
	 * @param array $data
	 * @throws \Exception
	 */
	public function addParticipantsByNormalizedData(array $data): void
	{
		try {
			$participantsDataFile = Csv::createFile(
				$this->tempDir . '/' . self::CSV_PARTICIPANTS,
				array_unique(array_map(function ($v) {
					return [$v['name'], Strings::substring($v['name'], 0, 1)];
				}, $data), SORT_REGULAR)
			);
			$this->addParticipantsFromCsv($participantsDataFile);

			$participantsSportsTableData = array_map(function ($v) {
				$participantId = $this->getIdByName($v['name']);
				if (!$participantId) {
					return false;
				}

				return [$this->getIdByName($v['name']), $v['sport_id']];
			}, $data);
			$participantsSportsFile = Csv::createFile($this->tempDir . '/' . self::CSV_PARTICIPANTS_SPORTS, $participantsSportsTableData);
			$this->addParticipantsSportsFromCsv($participantsSportsFile);

			FileSystem::delete($this->tempDir . '/' . self::CSV_PARTICIPANTS);
			FileSystem::delete($this->tempDir . '/' . self::CSV_PARTICIPANTS_SPORTS);
		} catch (\Exception $exception) {
			throw $exception;
		}
	}

	/**
	 * Vytvori zaznamy se sportovci v databazi z CSV souboru
	 *
	 * @param string $csvFilePath
	 * @return ResultSet
	 */
	public function addParticipantsFromCsv($csvFilePath): ResultSet
	{
		$this->cache->clean([
			Cache::TAGS => ['nameKey']
		]);

		return $this->connection->query('
			LOAD DATA LOCAL INFILE ? INTO TABLE participant
			FIELDS TERMINATED BY ?
			ENCLOSED BY ?
			(name, name_key)
		', $csvFilePath, ',', '"');
	}

	/**
	 * Vytvori zaznamy v databazi s propojenim sportovcu se sporty z CSV souboru
	 *
	 * @param string $csvFilePath
	 * @return ResultSet
	 */
	public function addParticipantsSportsFromCsv($csvFilePath): ResultSet
	{
		return $this->connection->query('
			LOAD DATA LOCAL INFILE ? INTO TABLE participant_has_sport
			FIELDS TERMINATED BY ?
			(participant_id, sport_id)
		', $csvFilePath, ',');
	}

}