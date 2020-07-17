<?php


namespace App\Console;


use Nette\Neon\Neon;
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class InstallCommand extends BaseCommand
{

	public function configure(): void
	{
		$this->setName('app:install')
			->setDescription('Install application');
	}

	public function execute(InputInterface $input, OutputInterface $output): int
	{
		try {
			$question = $this->getHelper('question');
			$host = $question->ask($input, $output, (new Question('MySQL host:', '127.0.0.1')));
			$database = $question->ask($input, $output, (new Question('MySQL database:')));
			$user = $question->ask($input, $output, (new Question('MySQL username:')));
			$pass = $question->ask($input, $output, (new Question('MySQL password:')));

			FileSystem::write(__DIR__ . '/../config/local.neon', Neon::encode([
				'database' => [
					'dsn' => "mysql:host=$host;dbname=$database",
					'user' => $user,
					'password' => $pass,
					'options' => [
						'PDO::MYSQL_ATTR_LOCAL_INFILE' => true
					]
				]
			], Neon::BLOCK));

			FileSystem::createDir(__DIR__ . '/../../output');

			return 0;
		} catch (\Exception $exception) {
			$output->writeln($exception->getMessage());
			return 1;
		}
	}

}