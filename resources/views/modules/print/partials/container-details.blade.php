{{-- Container Details box: the job's containers (number, how many, type), the same block the Stylish layout prints.
     Shared by the table-based layouts (Bilingual, FastFatoora, AI Fatoora, FastFatoora Classic); the heading row takes the
     layout's --accent colour. Left out when the invoice's job has no containers. --}}
@if(isset($jobContainers) && filled($jobContainers))
    <table class="keep-together" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 10px; page-break-inside: avoid;">
        <tr>
            <td style="font-size: 8.5pt; font-weight: bold; padding-bottom: 4px;">
                Container Details / <span class="ar" dir="rtl" lang="ar" style="font-weight: bold; unicode-bidi: isolate; display: inline;">تفاصيل الحاويات</span>
            </td>
        </tr>
        <tr>
            <td>
                <table cellpadding="0" cellspacing="0" border="0" width="60%" style="border-collapse: collapse; font-size: 8pt; border: 1px solid #e5e7eb;">
                    <tr>
                        <th align="left" style="padding: 4px 8px; background: var(--accent); color: #ffffff; font-weight: bold;">Container Number</th>
                        <th align="left" style="padding: 4px 8px; background: var(--accent); color: #ffffff; font-weight: bold;">No. of Containers</th>
                        <th align="left" style="padding: 4px 8px; background: var(--accent); color: #ffffff; font-weight: bold;">Type</th>
                    </tr>
                    @foreach($jobContainers as $container)
                        <tr>
                            <td style="padding: 4px 8px; border-top: 1px solid #e5e7eb;">{{ $container['container_number'] }}</td>
                            <td style="padding: 4px 8px; border-top: 1px solid #e5e7eb;">{{ $container['qty'] ?? '' }}</td>
                            <td style="padding: 4px 8px; border-top: 1px solid #e5e7eb;">{{ $container['container_size'] ? containerSize($container['container_size']) : '' }}</td>
                        </tr>
                    @endforeach
                </table>
            </td>
        </tr>
    </table>
@endif
