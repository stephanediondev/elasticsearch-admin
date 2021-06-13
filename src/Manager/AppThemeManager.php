<?php

namespace App\Manager;

use App\Manager\AbstractAppManager;

class AppThemeManager extends AbstractAppManager
{
    private $predefined = [
        'dark' => [
            'body' => 'bg-dark text-light',
            'navbar' => 'sticky-top navbar-expand-lg navbar-dark bg-dark',
            'nav_link' => 'text-light',
            'dropdown_menu' => 'dropdown-menu-dark border border-secondary',
            'block' => 'shadow p-3 mb-4 bg-dark rounded',
            'modal_content' => 'bg-dark',
            'form_control' => 'bg-dark text-light',
            'form_required' => 'badge bg-light text-dark ml-1',
            'table' => 'table-dark table-hover table-sm',
            'link_primary' => 'text-info',
            'link_secondary' => 'text-light',
            'jumbotron' => 'mt-3 bg-secondary text-light border border-light',
            'color_1' => 'dark',
            'color_2' => 'light',
        ],
        'light' => [
            'body' => 'bg-light text-dark',
            'navbar' => 'sticky-top navbar-expand-lg navbar-light bg-light',
            'nav_link' => 'text-dark',
            'dropdown_menu' => 'dropdown-menu-light border border-secondary',
            'block' => 'shadow p-3 mb-4 bg-light rounded',
            'modal_content' => 'bg-light',
            'form_control' => 'bg-light text-dark',
            'form_required' => 'badge bg-dark text-light ml-1',
            'table' => 'table-light table-hover table-sm',
            'link_primary' => 'text-primary',
            'link_secondary' => 'text-dark',
            'jumbotron' => 'mt-3 bg-secondary text-light border border-light',
            'color_1' => 'light',
            'color_2' => 'dark',
        ],
    ];

    public function keys(): array
    {
        return [
            'body',
            'navbar',
            'nav_link',
            'dropdown_menu',
            'block',
            'modal_content',
            'form_control',
            'form_required',
            'table',
            'link_primary',
            'link_secondary',
            'jumbotron',
            'color_1',
            'color_2',
        ];
    }

    public function predefinedList(): array
    {
        return $this->predefined;
    }

    public function predefinedThemes(): array
    {
        return array_keys($this->predefined);
    }

    public function getPredefined(string $theme): array
    {
        return $this->predefined[$theme];
    }
}
