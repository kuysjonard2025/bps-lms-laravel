<?php // app/Imports/Catalogs/CatalogImportColumns.php

namespace App\Imports\Catalogs;

use App\Models\AssetType;
use App\Models\Author;
use App\Models\GeneralReference;
use App\Models\Publisher;

class CatalogImportColumns
{
    public const TABLE = 'catalogs';

    public const NON_AUTHOR_ASSET_TYPES = [
        'NEWSPAPER',
        'NEWSPAPERS',
        'MAGAZINE',
        'MAGAZINES',
        'JOURNAL',
        'JOURNALS',
        'PERIODICAL',
        'PERIODICALS',
        'SERIAL',
        'SERIALS',
    ];

    public const UNIQUE_KEY_COLUMNS = [
        'title',
        'asset_type',
        'publisher',
        'general_reference',
        'publication_year',
        'author',
    ];

    /**
     * Shared column definition for Catalogs .xlsx template and import reader.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function definitions(): array
    {
        $currentYear = (int) date('Y');
        $minYear = $currentYear - 10;

        return [
            'title' => [
                'heading'            => 'title',
                'label'              => 'Title',
                'column'             => 'title',
                'required'           => true,
                'required_note'      => 'Required',
                'type'               => 'string',
                'excel_text'         => true,
                'min_length'         => 3,
                'max_length'         => 255,
                'allowed_values'     => null,
                'lookup_model'       => null,
                'lookup_column'      => null,
                'lookup_must_exist'  => false,
                'part_of_unique_key' => true,
                'example'            => 'Introduction to Modern Library Systems',
            ],
            'author' => [
                'heading'            => 'author',
                'label'              => 'Author',
                'column'             => 'author_id',
                'required'           => false,
                'required_note'      => 'Required unless asset_type is a periodical/non-author type',
                'type'               => 'lookup',
                'excel_text'         => true,
                'min_length'         => null,
                'max_length'         => 255,
                'allowed_values'     => null,
                'lookup_model'       => Author::class,
                'lookup_column'      => 'name',
                'lookup_must_exist'  => true,
                'part_of_unique_key' => true,
                'example'            => 'Juan Dela Cruz',
            ],
            'asset_type' => [
                'heading'            => 'asset_type',
                'label'              => 'Asset Type',
                'column'             => 'asset_type_id',
                'required'           => true,
                'required_note'      => 'Required (must already exist)',
                'type'               => 'lookup',
                'excel_text'         => true,
                'min_length'         => null,
                'max_length'         => 255,
                'allowed_values'     => null,
                'lookup_model'       => AssetType::class,
                'lookup_column'      => 'name',
                'lookup_must_exist'  => true,
                'part_of_unique_key' => true,
                'example'            => 'Book',
            ],
            'publisher' => [
                'heading'            => 'publisher',
                'label'              => 'Publisher',
                'column'             => 'publisher_id',
                'required'           => true,
                'required_note'      => 'Required (must already exist)',
                'type'               => 'lookup',
                'excel_text'         => true,
                'min_length'         => null,
                'max_length'         => 255,
                'allowed_values'     => null,
                'lookup_model'       => Publisher::class,
                'lookup_column'      => 'name',
                'lookup_must_exist'  => true,
                'part_of_unique_key' => true,
                'example'            => 'Rex Book Store',
            ],
            'general_reference' => [
                'heading'            => 'general_reference',
                'label'              => 'General Reference',
                'column'             => 'general_reference_id',
                'required'           => true,
                'required_note'      => 'Required (must already exist)',
                'type'               => 'lookup',
                'excel_text'         => true,
                'min_length'         => null,
                'max_length'         => 255,
                'allowed_values'     => null,
                'lookup_model'       => GeneralReference::class,
                'lookup_column'      => 'name',
                'lookup_must_exist'  => true,
                'part_of_unique_key' => true,
                'example'            => 'Z',
            ],
            'call_number' => [
                'heading'            => 'call_number',
                'label'              => 'Call Number',
                'column'             => 'call_number',
                'required'           => true,
                'required_note'      => 'Required',
                'type'               => 'string',
                'excel_text'         => true,
                'min_length'         => 1,
                'max_length'         => 50,
                'allowed_values'     => null,
                'lookup_model'       => null,
                'lookup_column'      => null,
                'lookup_must_exist'  => false,
                'part_of_unique_key' => false,
                'example'            => 'QA76.73.P55 S65 2026',
            ],
            'isbn_issn' => [
                'heading'            => 'isbn_issn',
                'label'              => 'ISBN/ISSN',
                'column'             => 'isbn_issn',
                'required'           => false,
                'required_note'      => 'Optional (8 to 20 digits)',
                'type'               => 'digits_string',
                'excel_text'         => true,
                'min_length'         => 8,
                'max_length'         => 20,
                'allowed_values'     => null,
                'lookup_model'       => null,
                'lookup_column'      => null,
                'lookup_must_exist'  => false,
                'part_of_unique_key' => false,
                'example'            => '9789710123456',
            ],
            'edition' => [
                'heading'            => 'edition',
                'label'              => 'Edition',
                'column'             => 'edition',
                'required'           => false,
                'required_note'      => 'Optional',
                'type'               => 'string',
                'excel_text'         => true,
                'min_length'         => null,
                'max_length'         => 50,
                'allowed_values'     => null,
                'lookup_model'       => null,
                'lookup_column'      => null,
                'lookup_must_exist'  => false,
                'part_of_unique_key' => false,
                'example'            => '2nd Edition',
            ],
            'publication_year' => [
                'heading'            => 'publication_year',
                'label'              => 'Publication Year',
                'column'             => 'publication_year',
                'required'           => true,
                'required_note'      => "Required (4-digit year, {$minYear} to {$currentYear})",
                'type'               => 'year',
                'excel_text'         => true,
                'min_value'          => $minYear,
                'max_value'          => $currentYear,
                'digits'             => 4,
                'allowed_values'     => null,
                'lookup_model'       => null,
                'lookup_column'      => null,
                'lookup_must_exist'  => false,
                'part_of_unique_key' => true,
                'example'            => (string) $currentYear,
            ],
            'description' => [
                'heading'            => 'description',
                'label'              => 'Description',
                'column'             => 'description',
                'required'           => false,
                'required_note'      => 'Optional',
                'type'               => 'string',
                'excel_text'         => true,
                'min_length'         => null,
                'max_length'         => 255,
                'allowed_values'     => null,
                'lookup_model'       => null,
                'lookup_column'      => null,
                'lookup_must_exist'  => false,
                'part_of_unique_key' => false,
                'example'            => 'Reference textbook for computer science and library courses',
            ],
        ];
    }

    /**
     * Ordered row-1 headings for Catalogs import template and import reader.
     *
     * @return array<int, string>
     */
    public static function headings(): array
    {
        return array_column(self::definitions(), 'heading');
    }

    /**
     * Whether author can be omitted for the given asset type name.
     */
    public static function isAuthorOptionalForAssetType(?string $assetTypeName): bool
    {
        if ($assetTypeName === null || trim($assetTypeName) === '') {
            return false;
        }

        return in_array(strtoupper(trim($assetTypeName)), self::NON_AUTHOR_ASSET_TYPES, true);
    }
}
