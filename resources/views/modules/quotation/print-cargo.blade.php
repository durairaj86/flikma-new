{{-- Shipment and cargo block of the quotation print: route, the containers (size, type, qty, GW / NW / CBM) and the packages (pcs, L x W x H, GW, CW),
     plus the cargo totals kept on the quotation itself (air: pieces, gross / volume / chargeable weight). --}}
@php
    $q = $quotation;
    $num = fn($v) => rtrim(rtrim(number_format((float) $v, 2), '0'), '.');
    $has = fn($v) => $v !== null && $v !== '' && (float) $v != 0;
    $route = collect([
        ['Mode', 'الشحن', shipmentMode()[$q->shipment_mode] ?? null],
        ['POL', 'ميناء التحميل', $q->pol],
        ['POD', 'ميناء التفريغ', $q->pod],
        ['Incoterm', 'شرط التسليم', $q->incoterm],
        ['Carrier', 'الناقل', $q->carrier],
        ['Commodity', 'البضاعة', $q->commodity],
    ])->filter(fn($r) => filled($r[2]));
    $unitW = $q->weight_unit ?: 'KG';
    $header = collect([
        ['Pieces', 'عدد القطع', $has($q->no_of_pcs) ? $num($q->no_of_pcs) : null],
        ['Gross Wt', 'الوزن القائم', $has($q->gross_weight) ? $num($q->gross_weight) . ' ' . $unitW : null],
        ['Volume Wt', 'الوزن الحجمي', $has($q->volume_weight) ? $num($q->volume_weight) . ' ' . $unitW : null],
        ['Volume', 'الحجم', $has($q->volume) ? $num($q->volume) . ' ' . ($q->volume_unit ?: 'CBM') : null],
        ['Chargeable Wt', 'الوزن المحتسب', $has($q->volume_weight) || $has($q->gross_weight) ? $num(max((float) $q->volume_weight, (float) $q->gross_weight)) . ' ' . ($q->chargeable_unit ?: $unitW) : null],
    ])->filter(fn($r) => filled($r[2]));
    $containers = $q->containers ?? collect();
    $packages = $q->packages ?? collect();
@endphp
@if($route->isNotEmpty() || $header->isNotEmpty() || $containers->isNotEmpty() || $packages->isNotEmpty())
    <div style="margin-top: 22px; page-break-inside: avoid;">
        <div class="label" style="color: #111827; font-size: 9pt;">Shipment &amp; Cargo | <span class="ar">تفاصيل الشحنة والبضاعة</span></div>
        @foreach([$route, $header] as $facts)
            @if($facts->isNotEmpty())
                <table style="margin-top: 8px;"><tr>
                    @foreach($facts as [$en, $ar, $value])
                        <td valign="top" style="padding-right: 18px;">
                            <div class="label">{{ $en }} | <span class="ar">{{ $ar }}</span></div>
                            <div style="font-size: 9pt; color: #1f2937; margin-top: 2px;">{{ $value }}</div>
                        </td>
                    @endforeach
                </tr></table>
            @endif
        @endforeach

        @if($containers->isNotEmpty())
            <table class="items" style="margin-top: 12px;">
                <thead><tr>
                    <th align="left" style="width: 4%;">#</th>
                    <th align="left">Container Size <span class="ar">حجم الحاوية</span></th>
                    <th align="left">Type <span class="ar">النوع</span></th>
                    <th align="right">Qty <span class="ar">العدد</span></th>
                    <th align="right">Gross Wt <span class="ar">الوزن القائم</span></th>
                    <th align="right">Net Wt <span class="ar">الوزن الصافي</span></th>
                    <th align="right">CBM <span class="ar">الحجم</span></th>
                    <th align="left">Hazardous <span class="ar">خطرة</span></th>
                </tr></thead>
                <tbody>
                @foreach($containers as $i => $c)
                    <tr>
                        <td class="muted">{{ $i + 1 }}</td>
                        <td>{{ $c->container_size ?: '-' }}</td>
                        <td>{{ $c->container_type ? ucfirst($c->container_type) : '-' }}</td>
                        <td class="num">{{ $has($c->qty) ? $num($c->qty) : ($has($c->quantity) ? $num($c->quantity) : 1) }}</td>
                        <td class="num">{{ $has($c->gross_weight) ? $num($c->gross_weight) . ' ' . ($c->weight_unit ?: 'KG') : '-' }}</td>
                        <td class="num">{{ $has($c->net_weight) ? $num($c->net_weight) . ' ' . ($c->weight_unit ?: 'KG') : '-' }}</td>
                        <td class="num">{{ $has($c->volume) ? $num($c->volume) : '-' }}</td>
                        <td>{{ $c->hazardous ? 'Yes' : 'No' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif

        @if($packages->isNotEmpty())
            <table class="items" style="margin-top: 12px;">
                <thead><tr>
                    <th align="left" style="width: 4%;">#</th>
                    <th align="left">Goods <span class="ar">البضاعة</span></th>
                    <th align="left">Package <span class="ar">العبوة</span></th>
                    <th align="right">Pcs <span class="ar">القطع</span></th>
                    <th align="right">L x W x H (cm) <span class="ar">الأبعاد</span></th>
                    <th align="right">Gross Wt <span class="ar">الوزن القائم</span></th>
                    <th align="right">Chargeable Wt <span class="ar">الوزن المحتسب</span></th>
                </tr></thead>
                <tbody>
                @foreach($packages as $i => $p)
                    <tr>
                        <td class="muted">{{ $i + 1 }}</td>
                        <td>{{ $p->description_goods ?: (commodityType()[$p->commodity_type] ?? $p->commodity_type ?: '-') }}@if($p->hs_code)<div class="muted" style="font-size: 8pt;">HS {{ $p->hs_code }}</div>@endif</td>
                        <td>{{ packageType()[$p->package_type] ?? (is_numeric($p->package_type) ? '-' : ($p->package_type ?: '-')) }}</td>
                        <td class="num">{{ $has($p->quantity) ? $num($p->quantity) : '-' }}</td>
                        <td class="num">{{ ($has($p->length) || $has($p->width) || $has($p->height)) ? $num($p->length) . ' x ' . $num($p->width) . ' x ' . $num($p->height) : '-' }}</td>
                        <td class="num">{{ $has($p->total_weight) ? $num($p->total_weight) . ' KG' : ($has($p->package_weight) ? $num($p->package_weight) . ' KG' : '-') }}</td>
                        <td class="num">{{ $has($p->chargeable_weight) ? $num($p->chargeable_weight) . ' KG' : '-' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endif
