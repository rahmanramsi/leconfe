<?php

namespace PdfJs;

use App\Classes\Plugin;
use App\Facades\Hook;
use CustomHeader\Pages\CustomHeaderPage;
use Filament\Panel;

class PdfJsPlugin extends Plugin
{
	public function boot()
	{
		Hook::add('Frontend::PaperGalley', function ($hookName, $galley, &$returner) {
			if (!$galley->isPdf()) {
				return;
			}

			$media = $galley->file->media;

			$returner = response()
				->file($media->getPath(), [
					'Content-Type' => $media->mime_type,
					'Content-Disposition' => 'inline; filename="' . $media->file_name . '"',
					'Content-Length' => $media->size,
					'Content-Transfer-Encoding' => 'binary',
					'Accept-Ranges' => 'bytes',
				]);
		});
	}
}
