<?php
class File {
    private $dump_folder;

    public function __construct($dump_folder) {
        $this->dump_folder = $dump_folder;
    }

    public function getUploadedFiles() {
        $root = $_SERVER['DOCUMENT_ROOT'];
        $file_path = $this->dump_folder;

        if (!file_exists($file_path)) {
            return [];
        }

        $content = file_get_contents($file_path);
        $files = unserialize($content);
        return $files;
    }

	public function groupFilesByYear($files) {
		$grouped = [];

		foreach ($files as $file) {
			// Use DateTime to parse the date correctly
			$dateTime = DateTime::createFromFormat('d/m/Y H:i:s', $file['date']);
			if ($dateTime) {
				$year = $dateTime->format('Y'); // Get the year from the DateTime object
			} else {
				// Handle the case where date parsing failed
				$year = 'Unknown'; // or some other fallback
			}
			
			$grouped[$year][] = $file;
		}

		return $grouped;
	}
}
