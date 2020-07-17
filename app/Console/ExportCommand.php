<?php


namespace App\Console;


use App\Model\ParticipantRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends BaseCommand
{

	/** @var ParticipantRepository */
	protected $participantRepository;

	const CSV_FILE = __DIR__ . '/../../output/participant_without_football.csv';

	public function __construct(ParticipantRepository $participantRepository)
	{
		parent::__construct();
		$this->participantRepository = $participantRepository;
	}

	public function configure(): void
	{
		$this->setName('app:export')
			->setDescription('Export data');
	}

	public function execute(InputInterface $input, OutputInterface $output): int
	{
		try {
			$csvFile = fopen(self::CSV_FILE, 'w');
			fputcsv($csvFile, ['Participant ID', 'Participant name', 'Sport']);

			foreach ($this->participantRepository->getParticipantsBySport('Football', true) as $row) {
				fputcsv($csvFile, $row->toArray());
			}
			fclose($csvFile);

			return 0;
		} catch (\Exception $exception) {
			$output->writeln($exception->getMessage());
			return 1;
		}
	}

}