<?php

namespace App\Helpers;

/**
 * Default column definitions for each module's "Column Settings" feature.
 *
 * fields  – all available fields shown in the left panel of the modal.
 *            Each field: key, label, category, fixed (bool, cannot be removed).
 *
 * columns – the default selected & ordered columns shown in the right panel.
 *            Each column: key, label, type ('parent'), children ([{key, label}]).
 *            A column with children renders all values stacked in one cell.
 */
class ModuleDefaultColumns
{
    public static function get(string $pageName): array
    {
        return match ($pageName) {
            'quotation' => static::quotation(),
            'job'       => static::job(),
            default     => ['fields' => [], 'columns' => []],
        };
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Quotation
    // ──────────────────────────────────────────────────────────────────────────
    public static function quotation(): array
    {
        $fields = [
            // ── General ───────────────────────────────────────────────────────
            ['key' => 'row_no',            'label' => 'Quote No',           'category' => 'General', 'min_width' => 140, 'fixed' => true],
            ['key' => 'client_name',       'label' => 'Client',             'category' => 'General', 'min_width' => 200],
            ['key' => 'posted_at',         'label' => 'Date',               'category' => 'General', 'min_width' => 100, 'orderable' => true],
            ['key' => 'valid_until',       'label' => 'Valid To',           'category' => 'General', 'min_width' => 90,  'orderable' => true],
            ['key' => 'status',            'label' => 'Status',             'category' => 'General', 'min_width' => 90],
            ['key' => 'activity_name',     'label' => 'Activity',           'category' => 'General', 'min_width' => 150],
            ['key' => 'salesperson_name',  'label' => 'Sales Person',       'category' => 'General', 'min_width' => 120],
            ['key' => 'services',          'label' => 'Services',           'category' => 'General', 'min_width' => 130],
            ['key' => 'prepared_by',       'label' => 'Prepared By',        'category' => 'General', 'min_width' => 120],
            ['key' => 'revision_count',    'label' => 'Revisions',          'category' => 'General', 'min_width' => 80],
            ['key' => 'created_at',        'label' => 'Created At',         'category' => 'General', 'min_width' => 130, 'orderable' => true],

            // ── Routing ───────────────────────────────────────────────────────
            ['key' => 'pol',               'label' => 'Origin (POL)',       'category' => 'Routing', 'min_width' => 130, 'orderable' => true],
            ['key' => 'pod',               'label' => 'Destination (POD)',  'category' => 'Routing', 'min_width' => 160, 'orderable' => true],
            ['key' => 'place_of_receipt',  'label' => 'Place of Receipt',   'category' => 'Routing', 'min_width' => 140],
            ['key' => 'place_of_delivery', 'label' => 'Place of Delivery',  'category' => 'Routing', 'min_width' => 140],
            ['key' => 'final_destination', 'label' => 'Final Destination',  'category' => 'Routing', 'min_width' => 150],

            // ── Cargo ─────────────────────────────────────────────────────────
            ['key' => 'incoterm',          'label' => 'INCO Term',          'category' => 'Cargo',   'min_width' => 90],
            ['key' => 'carrier',           'label' => 'Carrier',            'category' => 'Cargo',   'min_width' => 140],
            ['key' => 'shipment_mode',     'label' => 'Shipment Mode',      'category' => 'Cargo',   'min_width' => 120],
            ['key' => 'shipment_category', 'label' => 'Shipment Category',  'category' => 'Cargo',   'min_width' => 150],
            ['key' => 'shipper',           'label' => 'Shipper',            'category' => 'Cargo',   'min_width' => 130],
            ['key' => 'commodity',         'label' => 'Commodity',          'category' => 'Cargo',   'min_width' => 120],
            ['key' => 'pickup_date',       'label' => 'Pickup Date',        'category' => 'Cargo',   'min_width' => 110],
            ['key' => 'pickup_address',    'label' => 'Pickup Address',     'category' => 'Cargo',   'min_width' => 200],

            // ── Notes ─────────────────────────────────────────────────────────
            ['key' => 'notes',             'label' => 'Remarks / Notes',    'category' => 'Notes',   'min_width' => 200],
            ['key' => 'terms',             'label' => 'Terms & Conditions', 'category' => 'Notes',   'min_width' => 200],
        ];

        $columns = [
            ['key' => 'row_no',         'label' => 'Quote No',    'type' => 'parent', 'children' => []],
            ['key' => 'client_name',    'label' => 'Client',      'type' => 'parent', 'children' => []],
            ['key' => 'posted_at',      'label' => 'Date',        'type' => 'parent', 'children' => [
                ['key' => 'valid_until', 'label' => 'Valid To'],
            ]],
            ['key' => 'status',         'label' => 'Status',      'type' => 'parent', 'children' => []],
            ['key' => 'activity_name',  'label' => 'Activity',    'type' => 'parent', 'children' => []],
            ['key' => 'pol',            'label' => 'Origin',      'type' => 'parent', 'children' => [
                ['key' => 'pod', 'label' => 'Destination'],
            ]],
            ['key' => 'salesperson_name','label'=> 'Sales Person','type' => 'parent', 'children' => []],
            ['key' => 'incoterm',       'label' => 'INCO Term',   'type' => 'parent', 'children' => [
                ['key' => 'carrier', 'label' => 'Carrier'],
            ]],
        ];

        return compact('fields', 'columns');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Job  (fields from jobs + job_clearances)
    // ──────────────────────────────────────────────────────────────────────────
    public static function job(): array
    {
        $fields = [
            // ── General ───────────────────────────────────────────────────────
            ['key' => 'row_no',           'label' => 'Job No',           'category' => 'General',   'min_width' => 130, 'fixed' => true],
            ['key' => 'customer_name',    'label' => 'Customer',         'category' => 'General',   'min_width' => 180],
            ['key' => 'status',           'label' => 'Status',           'category' => 'General',   'min_width' => 90],
            ['key' => 'services',         'label' => 'Services',         'category' => 'General',   'min_width' => 140],
            ['key' => 'activity_name',    'label' => 'Activity',         'category' => 'General',   'min_width' => 150],
            ['key' => 'shipment_mode',    'label' => 'Shipment Mode',    'category' => 'General',   'min_width' => 120],
            ['key' => 'posted_at',        'label' => 'Job Date',         'category' => 'General',   'min_width' => 100, 'orderable' => true],
            ['key' => 'created_at',       'label' => 'Created At',       'category' => 'General',   'min_width' => 130, 'orderable' => true],

            // ── Routing ───────────────────────────────────────────────────────
            ['key' => 'pol',              'label' => 'Origin (POL)',     'category' => 'Routing',   'min_width' => 130],
            ['key' => 'pod',              'label' => 'Destination (POD)','category' => 'Routing',   'min_width' => 160],
            ['key' => 'place_of_receipt', 'label' => 'Place of Receipt', 'category' => 'Routing',   'min_width' => 140],
            ['key' => 'place_of_delivery','label' => 'Place of Delivery','category' => 'Routing',   'min_width' => 140],
            ['key' => 'final_destination','label' => 'Final Destination','category' => 'Routing',   'min_width' => 150],
            ['key' => 'incoterm',         'label' => 'INCO Term',        'category' => 'Routing',   'min_width' => 90],

            // ── Vessel ────────────────────────────────────────────────────────
            ['key' => 'carrier',              'label' => 'Carrier',          'category' => 'Vessel',    'min_width' => 140],
            ['key' => 'shipping_reference_no','label' => 'Shipping Ref',     'category' => 'Vessel',    'min_width' => 130],
            ['key' => 'etd',                  'label' => 'ETD',              'category' => 'Vessel',    'min_width' => 100, 'orderable' => true],
            ['key' => 'eta',                  'label' => 'ETA',              'category' => 'Vessel',    'min_width' => 100, 'orderable' => true],
            ['key' => 'atd',                  'label' => 'ATD',              'category' => 'Vessel',    'min_width' => 100],
            ['key' => 'ata',                  'label' => 'ATA',              'category' => 'Vessel',    'min_width' => 100],

            // ── Cargo ─────────────────────────────────────────────────────────
            ['key' => 'commodity',        'label' => 'Commodity',        'category' => 'Cargo',     'min_width' => 120],
            ['key' => 'awb_number',       'label' => 'AWB No',           'category' => 'Cargo',     'min_width' => 130],
            ['key' => 'hbl_number',       'label' => 'HBL No',           'category' => 'Cargo',     'min_width' => 130],
            ['key' => 'weight',           'label' => 'Weight',           'category' => 'Cargo',     'min_width' => 90],
            ['key' => 'volume',           'label' => 'Volume',           'category' => 'Cargo',     'min_width' => 90],
            ['key' => 'no_of_pieces',     'label' => 'No. of Pieces',    'category' => 'Cargo',     'min_width' => 110],
            ['key' => 'shipper',          'label' => 'Shipper',          'category' => 'Cargo',     'min_width' => 140],
            ['key' => 'consignee',        'label' => 'Consignee',        'category' => 'Cargo',     'min_width' => 140],
            ['key' => 'pickup_date',      'label' => 'Pickup Date',      'category' => 'Cargo',     'min_width' => 110],
            ['key' => 'delivery_date',    'label' => 'Delivery Date',    'category' => 'Cargo',     'min_width' => 120],
            ['key' => 'hs_code',          'label' => 'HS Code',          'category' => 'Cargo',     'min_width' => 110],
            ['key' => 'remarks',          'label' => 'Remarks',          'category' => 'Cargo',     'min_width' => 200],

            // ── Clearance (jobs + job_clearances) ─────────────────────────────
            ['key' => 'clearance_status', 'label' => 'Clearance Status', 'category' => 'Clearance', 'min_width' => 140],
            ['key' => 'clearance_date',   'label' => 'Clearance Date',   'category' => 'Clearance', 'min_width' => 130],
            ['key' => 'bayan_no',         'label' => 'Bayan No',         'category' => 'Clearance', 'min_width' => 130],
            ['key' => 'bayan_date',       'label' => 'Bayan Date',       'category' => 'Clearance', 'min_width' => 110],
            ['key' => 'doc_received',     'label' => 'Doc Received',     'category' => 'Clearance', 'min_width' => 120],
            ['key' => 'bl_receive_date',  'label' => 'BL Received',      'category' => 'Clearance', 'min_width' => 120],
            ['key' => 'do_date',          'label' => 'DO Date',          'category' => 'Clearance', 'min_width' => 100],
            ['key' => 'do_no',            'label' => 'DO No',            'category' => 'Clearance', 'min_width' => 110],
            ['key' => 'demurrage_date',   'label' => 'Demurrage Date',   'category' => 'Clearance', 'min_width' => 130],
            ['key' => 'declaration_no',   'label' => 'Declaration No',   'category' => 'Clearance', 'min_width' => 130],
        ];

        $columns = [
            ['key' => 'row_no',        'label' => 'Job No',     'type' => 'parent', 'children' => [
                ['key' => 'services', 'label' => 'Services'],
            ]],
            ['key' => 'customer_name', 'label' => 'Customer',   'type' => 'parent', 'children' => []],
            ['key' => 'pol',           'label' => 'Origin',     'type' => 'parent', 'children' => [
                ['key' => 'pod', 'label' => 'Destination'],
            ]],
            ['key' => 'carrier',       'label' => 'Carrier',    'type' => 'parent', 'children' => [
                ['key' => 'etd', 'label' => 'ETD'],
                ['key' => 'eta', 'label' => 'ETA'],
            ]],
            ['key' => 'status',        'label' => 'Status',     'type' => 'parent', 'children' => []],
            ['key' => 'posted_at',     'label' => 'Job Date',   'type' => 'parent', 'children' => []],
        ];

        return compact('fields', 'columns');
    }
}
