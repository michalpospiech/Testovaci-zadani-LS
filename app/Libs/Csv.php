<?php


namespace App\Libs;


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

	public static function normalizeData(\SplFileInfo $fileInfo, $key = 'id', $idByAutoIncrement = null): array
	{
		$idAIKey = $key === 'id' ? 'id_ai' : 'id';

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

			$vals = [
				$key => $row[$cols[$key]],
				'name' => $row[$cols['name']]
			];

			if (is_int($idByAutoIncrement)) {
				$vals[$idAIKey] = $idByAutoIncrement;
				$idByAutoIncrement++;
			}

			$values[] = $vals;
		}

		return $values;
	}

}