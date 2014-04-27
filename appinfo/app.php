<?php

// In Unix system serches for tessdata location and creates link to that directory in apps/images_ocr folder.
if (! stristr(PHP_OS, 'WIN')) {
	
	if (!file_exists ('apps/images_ocr/tess')) {
		$depth = 1;
		
		while ($depth < 6) {
			$depth += 1;
			exec('find / 2>/dev/null -maxdepth '.$depth.' -name "tessdata" -type d', $result);
			if ($result != null) {
				if (strlen($result[0]) > 0) {
					exec('ln -s '.$result[0].' apps/images_ocr/tess');
					break;
				}
			}
		}
	}
	
	exec('tesseract -v 2>&1', $result);
	$versioning = explode(' ', $result[0]);
	if ($versioning[0] == "tesseract") {
		$versions = explode('.', $versioning[1]);
		$versionNumbers = count($versions);
		if ($versionNumbers > 2) {
			if ($versions[0] > 3) {
				$pdfSupport = true;
			} elseif ($versions[0] == 3 && $versions[1] >= 2 && $versions[2] >= 1) {
				$pdfSupport = true;
			} else {
				$pdfSupport = false;
			}
		} else {
			if ($versions[0] > 3) {
				$pdfSupport = true; 
			} elseif ($versions[0] == 3 && $versions[1] > 2) {
				$pdfSupport = true;
			} else {
				$pdfSupport = false;
			}
		}
	} else {
		$pdfSupport = false;
	}
	
} else {
	$pdfSupport = false;
}

OCP\Util::addStyle('images_ocr', 'ocrmenu');

OCP\Util::addScript('images_ocr', 'ocr');

if ($pdfSupport == true) {
	OCP\Util::addScript('images_ocr', 'ocrPdfSupport');
} else {
	OCP\Util::addScript('images_ocr', 'ocrNoPdfSupport');
}

