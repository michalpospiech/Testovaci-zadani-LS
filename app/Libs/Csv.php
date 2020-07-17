<?php


namespace App\Libs;


use Nette\Utils\AssertionException;
use Nette\Utils\Validators;
use Tracy\Debugger;
use Tracy\ILogger;

class Csv
{

	public static function createFile($file, array $data): string
	{
		$csvFile = fopen($file, 'w');
		foreach ($data as $row) {
			fputcsv($csvFile, $row);
		}
		fclose($csvFile);

		return $file;
	}

	public static function normalizeData(\SplFileInfo $fileInfo, $key = 'id'): array
	{
		$file = $fileInfo->openFile();
		$file->setFlags(\SplFileObject::READ_CSV);

		$cols = [];
		$values = [];
		foreach ($file as $row) {
			if (!$cols) {
				$cols[$key] = array_search($key, $row);
				$cols['name'] = array_search('name', $row);
				continue;
			}

			try {
				Validators::assertField($row, $cols[$key], 'numericint');

				$vals = [
					$key => $row[$cols[$key]],
					'name' => $row[$cols['name']]
				];

				$values[] = $vals;
			} catch (AssertionException $exception) {
				Debugger::log($exception->getMessage(), ILogger::EXCEPTION);
			}
		}

		return $values;
	}

}