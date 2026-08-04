<?php

namespace Components\AdminBar;

use App\Services\Site\EditModeService;
use Modules\Main\Auth;
use Modules\Main\BaseComponent;

class AdminBar extends BaseComponent
{
	protected function isEditableInAdmin(): bool
	{
		return false;
	}

	protected function prepareData(array $params = []): void
	{
		$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
		$isAdminArea = str_starts_with($currentPath, '/admin/');
		$isEditMode = (new EditModeService())->isActive();

		$this->setParam('show', Auth::getInstance()->isAdmin() && !$isAdminArea);
		$this->setParam('back_url', $currentPath);
		$this->setParam('is_edit_mode', $isEditMode);
		$this->setParam('edit_toggle_url', $this->buildEditToggleUrl($currentPath, $isEditMode));
	}

	private function buildEditToggleUrl(string $currentPath, bool $isEditMode): string
	{
		$query = $_GET;

		if ($isEditMode) {
			$query['edit'] = '0';
		} else {
			$query['edit'] = 'true';
		}

		$queryString = http_build_query($query);
		return $currentPath . ($queryString !== '' ? '?' . $queryString : '');
	}
}
