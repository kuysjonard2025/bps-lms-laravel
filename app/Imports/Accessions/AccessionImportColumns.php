<?php

namespace App\Imports\Accessions;

final class AccessionImportColumns
{
    public const TABLE = 'accessions';

    public const UNIQUE_KEY = 'accession_number';

    /**
     * Column definitions keyed by internal field name.
     *
     * @return array<string, array{
     *     heading: string,
     *     required: bool,
     *     max_length: int|null,
     *     allowed_values: array<int, string>|null,
     *     lookup: string|null,
     *     is_text_identifier: bool,
     *     example: string,
     *     description: string
     * }>
     */
    public static function definitions(): array
    {
        return [
            'accession_number' => [
                'heading'            => 'Accession #',
                'required'           => false,
                'max_length'         => 100,
                'allowed_values'     => null,
                'lookup'             => null,
                'is_text_identifier' => true,
                'example'            => '',
                'description'        => 'Optional accession barcode. Leave blank to auto-generate, or supply an existing Accession # to update.',
            ],
            'acquisition_number' => [
                'heading'            => 'ACQ Ref',
                'required'           => true,
                'max_length'         => 100,
                'allowed_values'     => null,
                'lookup'             => 'acquisitions.acquisition_number',
                'is_text_identifier' => true,
                'example'            => 'ACQ-2026-001',
                'description'        => 'Existing acquisition reference number in the acquisitions table.',
            ],
            'condition' => [
                'heading'            => 'Condition',
                'required'           => true,
                'max_length'         => null,
                'allowed_values'     => [
                    'new',
                    'good',
                    'minor damaged',
                    'severe damaged',
                    'lost',
                    'missing',
                ],
                'lookup'             => null,
                'is_text_identifier' => false,
                'example'            => 'new',
                'description'        => 'Physical condition of the copy.',
            ],
            'status' => [
                'heading'            => 'Status',
                'required'           => true,
                'max_length'         => null,
                'allowed_values'     => [
                    'available',
                    'on loan',
                    'stored',
                    'under maintenance',
                    'dumped',
                ],
                'lookup'             => null,
                'is_text_identifier' => false,
                'example'            => 'stored',
                'description'        => 'Current accession item status.',
            ],
            'remarks' => [
                'heading'            => 'Remarks',
                'required'           => false,
                'max_length'         => 1000,
                'allowed_values'     => null,
                'lookup'             => null,
                'is_text_identifier' => false,
                'example'            => 'Initial batch intake',
                'description'        => 'Optional notes for the accession item.',
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function headings(): array
    {
        return array_map(
            static fn (array $def): string => $def['heading'],
            self::definitions()
        );
    }

    /**
     * @return array<int, string>
     */
    public static function requiredHeadings(): array
    {
        return array_values(array_map(
            static fn (array $def): string => $def['heading'],
            array_filter(
                self::definitions(),
                static fn (array $def): bool => $def['required']
            )
        ));
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function allowedValuesByHeading(): array
    {
        $result = [];

        foreach (self::definitions() as $def) {
            if ($def['allowed_values'] !== null) {
                $result[$def['heading']] = $def['allowed_values'];
            }
        }

        return $result;
    }

    /**
     * @return array<int, string>
     */
    public static function textIdentifierHeadings(): array
    {
        return array_values(array_map(
            static fn (array $def): string => $def['heading'],
            array_filter(
                self::definitions(),
                static fn (array $def): bool => $def['is_text_identifier']
            )
        ));
    }
}
