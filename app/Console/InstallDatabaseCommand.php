<?php


namespace App\Console;


use Nette\Database\Context;
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallDatabaseCommand extends BaseCommand
{

	/** @var Context */
	protected $database;

	public function __construct(Context $database)
	{
		parent::__construct();
		$this->database = $database;
	}

	public function configure(): void
	{
		$this->setName('app:install-database')
			->setDescription('Creates a database');
	}

	public function execute(InputInterface $input, OutputInterface $output): int
	{
		try {
			$this->database->getConnection()->query(FileSystem::read(__DIR__ . '/install.sql'));

			return 0;
		} catch (\Exception $exception) {
			$output->writeln($exception->getMessage());
			return 1;
		}
	}

}