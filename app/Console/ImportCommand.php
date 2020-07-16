<?php


namespace App\Console;


use App\Libs\Csv;
use App\Model\ParticipantRepository;
use App\Model\SportRepository;
use Nette\Utils\Finder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommand extends BaseCommand
{

	/** @var SportRepository */
	protected $sportRepository;

	/** @var ParticipantRepository */
	protected $participantRepository;

	public function __construct(SportRepository $sportRepository, ParticipantRepository $participantRepository)
	{
		parent::__construct();
		$this->sportRepository = $sportRepository;
		$this->participantRepository = $participantRepository;
	}

	protected function configure(): void
	{
		$this->setName('app:import')
			->setDescription('Import data');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		try {
			// sports
			$sports = [];
			foreach ($this->getSportsFiles() as $fileName => $file) {
				$sports = array_merge($sports, Csv::normalizeData($file));
			}
			$this->sportRepository->addSports($sports);

			// participants
			$participants = [];
			foreach ($this->getParticipantsFiles() as $fileName => $file) {
				$participants = array_merge($participants, Csv::normalizeData($file, 'sport_id'));
			}
			$this->participantRepository->addParticipantsByNormalizedData($participants);

			return 0;
		} catch (\Exception $exception) {
			$output->writeln($exception->getMessage());
			return 1;
		}
	}

	private function getFiles($mask): array
	{
		$files = [];
		foreach (Finder::findFiles($mask)->in(__DIR__ . '/../../input') as $key => $file) {
			$files[$key] = $file;
		}

		return $files;
	}

	private function getSportsFiles(): array
	{
		return $this->getFiles('sport_*.csv');
	}

	private function getParticipantsFiles(): array
	{
		return $this->getFiles('participant_*.csv');
	}

}