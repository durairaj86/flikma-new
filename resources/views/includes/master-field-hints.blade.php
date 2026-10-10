{{--
    One-line help under every input of the Masters create/edit forms (full text on hover).
    Texts are keyed by page path, then by field name. The form is loaded into the global modal after the
    page opens, so the hints are added when the form appears.
--}}
@php
    $mHints = [
        // Operations, finance, B/L, payroll, inventory: only the fields people ask about.
        'operation/jobs' => [
            'activity_id' => 'The department that handles this job, for example FCL Export or Air Import.',
            'cargo_type' => 'Kind of cargo, for example general, dangerous or perishable.',
            'suppliers' => 'Suppliers (agents, carriers, truckers) involved in this job. Their bills are matched to it.',
            'awb_number' => 'Master bill of lading (sea) or master airway bill (air) number.',
            'hbl_number' => 'House bill of lading or house airway bill number issued to the customer.',
            'voyage_flight_no' => 'Vessel voyage number or flight number carrying the cargo.',
            'incoterm' => 'Trade term agreed with the buyer, for example FOB or CIF. It says who pays each leg.',
            'volume' => 'Total volume in cubic metres (m3).',
            'weight' => 'Total gross weight in kilograms.',
            'no_of_pieces' => 'Total number of packages or pieces.',
            'shipping_reference_no' => 'Reference number given by the shipping line or agent.',
            'client_ref' => "The customer's own reference for this shipment, printed on invoices.",
            'place_of_receipt' => 'Where the carrier takes the cargo over, if different from the port of loading.',
            'transshipment_port' => 'Port where the cargo changes vessel on the way, if any.',
            'final_destination' => 'The final stop of the whole journey, for multi-leg shipments.',
            'hs_code' => 'Customs commodity code. It decides the duty rate.',
            'declaration_no' => 'Customs declaration number issued for this shipment.',
            'customs_broker' => 'The broker who files the customs declaration.',
            'port_clearance' => 'Port or customs office where the cargo is cleared.',
            'type_of_clearance' => 'Kind of customs clearance, for example import, export or transit.',
            'bayan_no' => 'Saudi customs (Bayan) declaration number.',
            'do_no' => 'Delivery order number released by the shipping line.',
            'duty_amount' => 'Customs duty your company pays on this shipment.',
            'duty_amount_client' => 'Customs duty you will bill the customer for.',
            'lab_clearance' => 'Yes if the goods need a lab test before release.',
            'inspection' => 'Yes if customs or another authority must inspect the cargo.',
            'clearance_status' => 'Where the customs clearance stands right now.',
        ],
        'invoice/proforma' => [
            'job_id' => 'The job this proforma is for. Its customer and charges can be picked up from it.',
            'reference_no' => "Your customer's PO or reference, shown on the proforma.",
        ],
        'invoice/supplier' => [
            'job_id' => 'The job this bill belongs to. Its cost shows against that job in job reports.',
            'invoice_number' => "The supplier's own invoice number. Must be unique for that supplier.",
        ],
        'invoice/customer' => [
            'job_id' => 'The job you are billing. Its charges can be picked up from it.',
        ],
        'adjustment/credit-note' => [
            'credit_note_type' => 'Credit notes are raised against an approved customer invoice.',
            'invoice_id' => 'The approved invoice being credited. Currency and rate are copied from it.',
            'job_id' => 'The job of that invoice, for your records.',
            'reason' => 'Why you are crediting the customer, for example rate correction or duplicate billing.',
        ],
        'adjustment/debit-note' => [
            'invoice_id' => 'The approved supplier invoice you are debiting. Currency and rate are copied from it.',
            'job_id' => 'The job of that invoice, for your records.',
            'reason' => 'Why you are debiting the supplier, for example an overcharge or a service not provided.',
        ],
        'finance/expense' => [
            'customer' => 'Fill only if this expense is for a customer, so it can be tracked against them.',
            'supplier' => 'The vendor you paid. Leave empty for petty expenses without a supplier.',
            'reference_number' => 'Receipt or voucher number of the expense.',
            'main_account' => 'Where the money came from: cash, bank or card account.',
        ],
        'transaction/payments' => [
            'supplier' => 'The supplier you are paying. Their open bills can then be settled.',
            'reference_no' => 'Cheque, transfer or voucher number of the payment.',
        ],
        'transaction/collections' => [
            'customer' => 'The customer who paid you. Their open invoices can then be settled.',
            'reference_no' => 'Cheque, transfer or receipt number of the collection.',
            'bank_charges' => 'Bank fees deducted from the amount received.',
            'other_charges' => 'Any other deduction from the amount received, for example a discount.',
        ],
        'bl/airway-bill' => [
            'job_id' => 'The job this airway bill belongs to.',
            'shipment_type' => 'What kind of shipment this is, for example general cargo or express.',
            'service_type' => 'Speed or level of service, for example standard or priority.',
            'payment_method' => 'Who pays the freight: prepaid by the shipper or collect from the consignee.',
            'special_instructions' => 'Handling notes for the carrier, for example keep upright or temperature range.',
        ],
        'bl/seaway' => [
            'job_id' => 'The job this bill of lading belongs to.',
            'voyage_number' => 'Voyage number of the vessel.',
            'shipment_type' => 'What kind of shipment this is, for example FCL or LCL.',
            'service_type' => 'Level of service, for example port to port or door to door.',
            'payment_method' => 'Who pays the freight: prepaid by the shipper or collect from the consignee.',
            'special_instructions' => 'Handling notes for the carrier or terminal.',
        ],
        'bl/waybill' => [
            'job_id' => 'The job this waybill belongs to.',
            'shipment_type' => 'What kind of shipment this is.',
            'service_type' => 'Level of service, for example standard or express.',
            'payment_method' => 'Who pays for the transport: sender or receiver.',
            'special_instructions' => 'Handling notes for the driver.',
        ],
        // Sales: only the fields people ask about. Obvious ones (customer name, remarks ...) have no hint.
        'sales/enquiries' => [
            'prospect' => 'Use when the enquiry is from someone who is not a customer yet. Pick either a customer or a prospect.',
            'activity_id' => 'The department that will handle this shipment, for example FCL Export or Air Import.',
            'shipment_category' => 'How the cargo is packed for transport: full container, part container, loose cargo.',
            'services[]' => 'The services the customer is asking for. They carry over to the quotation.',
            'place_of_receipt' => 'Where the carrier takes the cargo over, if different from the port of loading.',
            'shipper' => 'The company that sends the goods, as on the bill of lading.',
            'volume' => 'Total cargo volume in cubic metres (m3). Used to estimate space and freight.',
            'incoterm' => 'Trade term agreed with the buyer, for example FOB or CIF. It says who pays each leg.',
            'pol' => 'Port the cargo is loaded at (port of loading).',
            'pod' => 'Port the cargo is unloaded at (port of discharge).',
        ],
        'sales/quotations' => [
            'prospect' => 'Use when quoting someone who is not a customer yet. Pick either a customer or a prospect.',
            'services[]' => 'The services you are quoting for. They decide which charges you can add below.',
            'activity_id' => 'The department that will handle the shipment, for example FCL Export or Air Import.',
            'place_of_receipt' => 'Where the carrier takes the cargo over, if different from the origin port.',
            'pol' => 'Port or airport the shipment starts from.',
            'pod' => 'Port or airport the shipment goes to.',
            'place_of_delivery' => 'Where the cargo is handed to the consignee, if beyond the destination port.',
            'final_destination' => 'The final stop of the whole journey, for multi-leg shipments.',
            'carrier' => 'Shipping line or airline carrying the cargo.',
            'shipper' => 'The company that sends the goods, as on the bill of lading.',
            'incoterm' => 'Trade term agreed with the buyer, for example FOB or CIF. It says who pays each leg.',
            'commodity' => 'What is being shipped, in short, for example Auto parts.',
            'terms' => 'Terms and conditions printed on the quotation. Pick a saved one or type your own.',
        ],
        'suppliers' => [
            'currency' => 'Currency this supplier bills you in. It cannot be changed once bills or payments exist.',
            'business_type' => 'Registered suppliers have a CR and VAT number; their VAT is claimed as input VAT.',
            'vat_number' => 'Supplier tax registration number (15 digits in Saudi Arabia). Needed to claim input VAT.',
            'credit_limit' => 'Most you are willing to owe this supplier at any time. Leave empty for no limit.',
            'credit_days' => 'Days you have to pay after the bill date, used to calculate due dates.',
            'building_number' => 'Four-digit building number from the national address.',
        ],
        'prospects' => [
            'salesperson_id' => 'The salesperson who owns this lead and follows it up.',
        ],
        'customers' => [
            'business_type' => 'Registered customers have a CR and VAT number; they are needed for tax invoices.',
            'vat_number' => 'Customer tax registration number (15 digits in Saudi Arabia). Printed on tax invoices.',
            'currency' => 'Currency this customer is billed in. It cannot be changed once invoices exist.',
            'credit_limit' => 'Most this customer may owe you at any time. Leave empty for no limit.',
            'credit_days' => 'Days allowed to pay after the invoice date, used to calculate due dates.',
            'building_number' => 'Four-digit building number from the national address.',
            'salesperson_id' => 'The salesperson responsible for this customer.',
            'preferred_shipping' => 'How this customer usually ships, used as the default on enquiries.',
            'default_port' => 'Port used by default on new enquiries for this customer.',
            'payment_terms' => 'Payment terms agreed with the customer, for example Net 30.',
        ],
        'masters/banks' => [
            'bank' => 'Name of the bank, for example Al Rajhi Bank.',
            'branch' => 'Branch where the account is held.',
            'account_holder' => 'Name the account is registered under, as the bank shows it.',
            'account_number' => 'The bank account number used for payments and collections.',
            'iban_code' => 'International Bank Account Number. Printed on invoices so customers can pay you.',
            'swift_code' => 'SWIFT/BIC code, needed for international transfers.',
            'sort_code' => 'Bank or branch routing code, if your bank uses one.',
            'currency' => 'Currency this account is kept in.',
            'bank_address' => 'Postal address of the bank branch.',
        ],
        'masters/descriptions' => [
            'description' => 'Charge name in English, for example Ocean Freight. Shown on invoices and quotations.',
            'description_local' => 'The same charge name in Arabic.',
            'sale_account' => 'Income account this charge is posted to when you bill a customer.',
            'purchase_account' => 'Expense account this charge is posted to when a supplier bills you.',
        ],
        'masters/departments' => [
            'name' => 'Department name, for example Sales or Operations.',
            'code' => 'Short code for the department, used in lists and reports.',
            'is_active' => 'Switch off to stop using this department for new users.',
        ],
        'masters/services' => [
            'name' => 'The department as your team calls it, for example FCL Export or Air Import. Shown on enquiries, quotations and jobs.',
            'mode' => 'How the goods travel: sea, air or land.',
            'type' => 'Whether the department handles imports, exports or both.',
        ],
        'masters/package/codes' => [
            'package_code' => 'Short unique code, for example CTN for carton.',
            'package_name' => 'Full packing type name, for example Carton.',
            'description' => 'Optional note, for example the usual size or contents.',
        ],
        'masters/period-closing' => [
            'year' => 'Financial year this closing belongs to.',
            'closing_date' => 'Nothing dated on or before this day can be added or changed after closing.',
            'notes' => 'Why the period was closed, for your records.',
        ],
        'masters/quotation-terms' => [
            'title' => 'Short name to recognise these terms in the list.',
            'terms' => 'The wording printed at the bottom of the quotation.',
            'activity_id' => 'Use these terms automatically for quotations of this logistic department.',
            'is_general' => 'On: the terms can be used on any quotation, whatever the logistic department.',
            'is_active' => 'Switch off to stop offering these terms on new quotations.',
        ],
        'masters/salesperson' => [
            'name' => 'Name of the salesperson as it should appear on customers and quotations.',
        ],
        'masters/transport/directories/seaports' => [
            'port_code' => 'UN/LOCODE or your short code for the port, for example SAJED.',
            'port_name' => 'Port name as shown on enquiries, jobs and bills of lading.',
            'country' => 'Country the port is in.',
        ],
        'masters/transport/directories/airports' => [
            'port_code' => 'IATA airport code, for example RUH.',
            'port_name' => 'Airport name as shown on air enquiries, jobs and airway bills.',
            'country' => 'Country the airport is in.',
        ],
        'masters/units' => [
            'unit_name' => 'Full unit name, for example Kilogram.',
            'unit_symbol' => 'Short symbol printed on invoice lines, for example kg.',
        ],
        'masters/users' => [
            'name' => 'Full name of the person.',
            'email' => 'Sign-in email. Must be unique.',
            'phone' => 'Mobile number with country code.',
            'alt_email' => 'Backup email for notifications, optional.',
            'department' => 'Department the user works in.',
            'status' => 'Active users can work in the system; terminated users cannot.',
            'login_permission' => 'Turn off to keep the person on file without letting them sign in.',
            'password' => 'At least 8 characters. Leave empty when editing to keep the current one.',
            'confirm_password' => 'Type the same password again.',
            'address1' => 'Street address, first line.',
            'address2' => 'Second address line, optional.',
            'city' => 'City of residence.',
            'state' => 'State or region.',
            'postal_code' => 'Postal or ZIP code.',
            'remark' => 'Any internal note about this user.',
            'role' => 'What the user may do: the role decides which menus and actions they can use.',
            'country' => 'Country of residence.',
        ],
    ];
    $mFieldHints = $mHints[trim(request()->path(), '/')] ?? null;
@endphp
@if($mFieldHints)
<script>
    (function () {
        var hints = @json($mFieldHints);
        function esc(t) { return $('<div>').text(t).html(); }
        function inject(root) {
            $(root).find('input[name], select[name], textarea[name]').each(function () {
                var name = this.getAttribute('name');
                var text = hints[name];
                if (!text || this.type === 'hidden') return;
                var $el = $(this);
                // Each field is hinted exactly once (radios: once per name). A flag on the field itself, not a lookup in
                // its wrapper, so a hint placed outside the wrapper can never be added again by the observer.
                if ($el.attr('data-hinted') === '1') return;
                if (this.type === 'radio' && $(root).find('[name="' + name.replace(/"/g, '\\"') + '"][data-hinted="1"]').length) { $el.attr('data-hinted', '1'); return; }
                var group = $el.closest('.form-group, [class*="col-"]').first();
                if (group.find('.field-hint-wrap').length) { $el.attr('data-hinted', '1'); return; }
                $el.attr('data-hinted', '1');
                // Switches/checkboxes: the hint goes under the whole switch row; selects: under the field itself.
                var anchor = $el.closest('.form-check, .form-switch, .input-group');
                if (this.type === 'radio' && anchor.length) anchor = anchor.parent();
                if (!anchor.length) anchor = $el;
                var ts = $el.next('.ts-wrapper');
                if (ts.length) anchor = ts;
                var $hint = $('<div class="field-hint-wrap"><div class="field-hint" tabindex="0"></div></div>').attr('data-full', text);
                $hint.find('.field-hint').text(text);
                // A field sitting in a flex row (e.g. a switch in a d-flex group) would put the hint beside it:
                // wrap field + hint together so the hint drops underneath.
                var parent = anchor.parent();
                if (parent.css('display') === 'flex' && parent.css('flex-direction').indexOf('row') === 0) {
                    anchor.wrap('<div class="w-100"></div>');
                    anchor.parent().append($hint);
                } else {
                    anchor.after($hint);
                }
            });
        }
        function start() {
            var target = document.getElementById('globalModalBody');
            if (!target) return;
            var busy = false, timer = null;
            var observer = new MutationObserver(function () {
                if (busy) return;
                clearTimeout(timer);
                // Wait for the form (and Tom Select) to finish building, then add the hints once.
                timer = setTimeout(function () {
                    busy = true;
                    try { inject(target); } finally { busy = false; observer.takeRecords(); }
                }, 80);
            });
            observer.observe(target, { childList: true, subtree: true });
        }
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start); else start();
    })();
</script>
@endif
