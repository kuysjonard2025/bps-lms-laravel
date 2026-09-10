<?php

namespace App\Livewire\Components;

use Livewire\Component;

class Sidebar extends Component
{
    public function render()
    {
        $user = auth()->user();
        $isLibrarian = $user && $user->hasRole('librarian');

        $sections = [
            'Core' => [
                [
                    'name' => 'Dashboard',
                    'route' => 'dashboard',
                    'icon' => 'home',
                ],
            ],

            'Process' => [
                [
                    'name' => 'Acquisitions',
                    'route' => 'acquisitions',
                    'icon' => 'shopping-cart',
                ],
                [
                    'name' => 'Borrower Logs',
                    'route' => 'patron-logs',
                    'icon' => 'clock',
                ],
                [
                    'name' => 'Circulations',
                    'route' => 'circulations',
                    'icon' => 'arrow-right',
                ],
                [
                    'name' => 'Borrower Records',
                    'route' => 'patron-records',
                    'icon' => 'identification',
                ],
                [
                    'name' => 'Inventory Management',
                    'route' => 'inventory-management',
                    'icon' => 'clipboard-document-list',
                ],
            ],
        ];

        // Append Maintenance and System Logs sections strictly for full Librarians
        if ($isLibrarian) {
            $sections['Maintenance'] = [
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
                    'name' => 'Vendors',
                    'route' => 'vendors',
                    'icon' => 'truck',
                ],
                [
                    'name' => 'Accessions',
                    'route' => 'accessions',
                    'icon' => 'square-3-stack-3d',
                ],
                [
                    'name' => 'Academics Info',
                    'route' => 'academic-info',
                    'icon' => 'academic-cap',
                ],
                [
                    'name' => 'Registrations',
                    'route' => 'registrations',
                    'icon' => 'user-group',
                ],
                [
                    'name' => 'Circulation Policy',
                    'route' => 'circulation-policy',
                    'icon' => 'adjustments-horizontal',
                ],
            ];

            $sections['System Log & Backup'] = [
                [
                    'name' => 'Authentication Logs',
                    'route' => 'authentication-logs',
                    'icon' => 'chart-bar',
                ],
                [
                    'name' => 'User Activity Logs',
                    'route' => 'user-activity-logs',
                    'icon' => 'queue-list',
                ],
                [
                    'name' => 'Backups & Recovery',
                    'route' => 'database-backups',
                    'icon' => 'server',
                ]
            ];
        }

        return view('livewire.components.sidebar', compact('sections'));
    }
}
