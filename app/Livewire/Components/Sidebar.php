<?php

namespace App\Livewire\Components;

use Livewire\Component;

class Sidebar extends Component
{
    public function render()
    {
        $sections = [
            'Core' => [
                [
                    'name' => 'Dashboard',
                    'route' => 'dashboard',
                    'icon' => 'home',
                ],
            ],

            'Maintenance' => [
                [
                    'name' => 'Asset Details',
                    'route' => 'asset-details',
                    'icon' => 'document-text',
                ],
                [
                    'name' => 'Catalogs',
                    'route' => 'catalogs',
                    'icon' => 'list-bullet',
                ],
                [
                    'name' => 'Suppliers',
                    'route' => 'suppliers',
                    'icon' => 'truck',
                ],
                [
                    'name' => 'Accessions',
                    'route' => 'accessions',
                    'icon' => 'square-3-stack-3d',
                ],
                [
                    'name' => 'Academics',
                    'route' => 'academics',
                    'icon' => 'academic-cap',
                ],
                [
                    'name' => 'Registrations',
                    'route' => 'registrations',
                    'icon' => 'user-group',
                ],
                [
                    'name' => 'Circulations Policy',
                    'route' => 'circulations-policy',
                    'icon' => 'adjustments-horizontal',
                ],
            ],

            'Process' => [
                [
                    'name' => 'Acquisitions',
                    'route' => 'acquisition',
                    'icon' => 'shopping-cart',
                ],
                [
                    'name' => 'Time Logs',
                    'route' => 'time-logs',
                    'icon' => 'clock',
                ],
                [
                    'name' => 'Circulations',
                    'route' => 'circulations',
                    'icon' => 'arrow-right',
                ],
                [
                    'name' => 'Patron Records',
                    'route' => 'patron-records',
                    'icon' => 'identification',
                ],
                [
                    'name' => 'Accession Disposal',
                    'route' => 'accession-disposal',
                    'icon' => 'trash',
                ],
            ],
            'System Reports' => [
                [
                    'name' => 'System Logs',
                    'route' => 'system-logs',
                    'icon' => 'chart-bar',
                ],
                [
                    'name' => 'System User Activity',
                    'route' => 'system-user-activity',
                    'icon' => 'queue-list',
                ]
            ],
        ];

        return view('livewire.components.sidebar', compact('sections'));
    }
}
