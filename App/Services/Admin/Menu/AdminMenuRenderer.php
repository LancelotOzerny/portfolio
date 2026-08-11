<?php

namespace App\Services\Admin\Menu;

final class AdminMenuRenderer
{
	/**
	 * @param list<AdminMenuItem> $items
	 */
	public function render(array $items, string $currentPath): string
	{
		$html = '';

		foreach ($items as $item) {
			$html .= $this->renderTopLevel($item, $currentPath);
		}

		return $html;
	}

	private function renderTopLevel(AdminMenuItem $item, string $currentPath): string
	{
		$active = $item->isActive($currentPath) ? ' is-active' : '';
		$icon = htmlspecialchars($item->getIcon(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
		$label = htmlspecialchars($item->getLabel(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
		$href = htmlspecialchars($item->getHref(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

		if (!$item->hasChildren()) {
			return <<<HTML
				<a class="admin-sideout__link{$active}" href="{$href}">
					<span class="admin-sideout__icon">{$icon}</span>
					<span class="admin-sideout__label">{$label}</span>
				</a>

HTML;
		}

		$childrenHtml = $this->renderFlyoutChildren($item->getChildren(), $currentPath);

		return <<<HTML
				<div class="admin-sideout__item{$active}">
					<a class="admin-sideout__link admin-sideout__link--has-flyout{$active}" href="{$href}">
						<span class="admin-sideout__icon">{$icon}</span>
						<span class="admin-sideout__label">{$label}</span>
					</a>
					<div class="admin-sideout__flyout">
{$childrenHtml}					</div>
				</div>

HTML;
	}

	/**
	 * @param list<AdminMenuItem> $children
	 */
	private function renderFlyoutChildren(array $children, string $currentPath): string
	{
		$html = '';

		foreach ($children as $child) {
			$html .= $this->renderFlyoutItem($child, $currentPath);
		}

		return $html;
	}

	private function renderFlyoutItem(AdminMenuItem $item, string $currentPath): string
	{
		$active = $item->isActive($currentPath) ? ' is-active' : '';
		$label = htmlspecialchars($item->getLabel(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
		$href = htmlspecialchars($item->getHref(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

		if (!$item->hasChildren()) {
			return <<<HTML
						<a class="admin-sideout__flyout-link{$active}" href="{$href}">{$label}</a>

HTML;
		}

		$nested = $this->renderFlyoutChildren($item->getChildren(), $currentPath);

		return <<<HTML
						<div class="admin-sideout__flyout-item{$active}">
							<a class="admin-sideout__flyout-link admin-sideout__flyout-link--has-flyout{$active}" href="{$href}">{$label}</a>
							<div class="admin-sideout__flyout">
{$nested}							</div>
						</div>

HTML;
	}
}
