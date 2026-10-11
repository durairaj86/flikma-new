@php
    $host = request()->getHost();
    $port = request()->getPort();
    $root = request()->getSchemeAndHttpHost();
    $step = fn ($n) => '<span class="badge rounded-circle bg-primary me-2" style="width:24px;height:24px;line-height:16px">' . $n . '</span>';
@endphp
<style>
    .guide-card pre{background:#f8f9fa;border:1px solid #e9ecef;border-radius:8px;padding:10px 12px;font-size:12.5px;white-space:pre-wrap;margin:6px 0 10px}
    .guide-card h6{font-weight:700}
    .guide-card li{margin-bottom:4px}
    .guide-step{display:flex;gap:10px;margin-bottom:12px}
    .guide-step>.n{flex:0 0 26px;height:26px;border-radius:50%;background:#0d6efd;color:#fff;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center}
</style>

<div class="card border-0 shadow-sm rounded-3 mb-3 guide-card"><div class="card-body small">
    <h6 class="fs-6">{{ __('Which method should I use?') }}</h6>
    <div class="table-responsive"><table class="table table-sm align-middle mb-0">
        <thead class="table-light"><tr><th>{{ __('Your machine / system') }}</th><th>{{ __('Method') }}</th><th>{{ __('Who contacts whom') }}</th></tr></thead>
        <tbody>
            <tr><td>{{ __('ZKTeco, eSSL, Realand, Biomax and other terminals that have "Cloud Server / ADMS" settings') }}</td><td><a href="#guideZk" onclick="gotoGuide('guideZk')"><strong>A. {{ __('ZKTeco / ADMS push') }}</strong></a></td><td>{{ __('Machine → Flikma (automatic, real time)') }}</td></tr>
            <tr><td>{{ __('Any machine, software or middleware that can call a web address when someone punches (webhook / HTTP push)') }}</td><td><a href="#guideHook" onclick="gotoGuide('guideHook')"><strong>B. {{ __('Callback URL (webhook)') }}</strong></a></td><td>{{ __('Their system → Flikma') }}</td></tr>
            <tr><td>{{ __('Cloud attendance service with its own API (you get an API URL + key/token from the vendor)') }}</td><td><a href="#guidePull" onclick="gotoGuide('guidePull')"><strong>C. {{ __('Vendor API (we fetch)') }}</strong></a></td><td>{{ __('Flikma → their API, every few minutes') }}</td></tr>
            <tr><td>{{ __('Machine with only USB / Excel export, no network') }}</td><td><a href="{{ route('payroll.punch-import') }}"><strong>{{ __('Import CSV / Excel') }}</strong></a></td><td>{{ __('You upload the file') }}</td></tr>
        </tbody>
    </table></div>
</div></div>

<div class="card border-0 shadow-sm rounded-3 mb-3 guide-card"><div class="card-body small">
    <h6 class="fs-6">{{ __('Step 0 — Do this for every method: tell Flikma who is who') }}</h6>
    <div class="guide-step"><div class="n">1</div><div>{{ __('Every employee has an ID on the machine (the number or PIN used when enrolling the fingerprint / face / card).') }}</div></div>
    <div class="guide-step"><div class="n">2</div><div>{{ __('Open Masters → Employees → Edit → Employment and type that number in "Machine ID". If you leave it empty Flikma also tries the Employee Code: machine ID 7 matches EMP-0007.') }}</div></div>
    <div class="guide-step"><div class="n">3</div><div>{{ __('Do not worry if you forget some: punches from unknown IDs are saved and shown in Payroll → Punch Log. Pick the employee there and they are added to attendance immediately.') }}</div></div>
</div></div>

<div class="card border-0 shadow-sm rounded-3 mb-3 guide-card" id="guideZk"><div class="card-body small">
    <h6 class="fs-6">A. {{ __('ZKTeco / ADMS push (machine sends to Flikma)') }}</h6>
    <div class="guide-step"><div class="n">1</div><div><strong>{{ __('Find the serial number.') }}</strong> {{ __('On the machine: Menu → System Info → Device Info → Serial Number (also printed on the back label), e.g. CEXX123456789.') }}</div></div>
    <div class="guide-step"><div class="n">2</div><div><strong>{{ __('Add the device here.') }}</strong> {{ __('Devices tab → Add Device → Type "ZKTeco / ADMS push" → enter the serial number exactly as on the machine and choose its time zone → Save.') }}</div></div>
    <div class="guide-step"><div class="n">3</div><div><strong>{{ __('Set the server on the machine.') }}</strong> {{ __('Menu → Comm. (or Communication) → Cloud Server Setting (some models call it ADMS) and enter:') }}
<pre>Server Address  : {{ $host }}
Server Port     : {{ $port }}
Enable Domain Name : On  (only if you typed a name, not an IP address)
HTTPS           : {{ request()->isSecure() ? 'On' : 'Off' }}
Proxy           : Off</pre>
    {{ __('The machine must be connected to the internet (LAN cable or Wi-Fi) and its own network settings must have a valid IP, gateway and DNS.') }}</div></div>
    <div class="guide-step"><div class="n">4</div><div><strong>{{ __('Check it.') }}</strong> {{ __('Within a minute the device shows a green dot and "Last Seen" on the Devices tab. Ask someone to punch — the punch appears in Payroll → Punch Log and in Payroll → Attendance.') }}</div></div>
    <div class="text-muted">{{ __('No field mapping is needed: ZKTeco always sends the same format (ID, time, in/out status). In/out is read from the machine\'s status: 0/4 = in, 1/5 = out.') }}</div>
</div></div>

<div class="card border-0 shadow-sm rounded-3 mb-3 guide-card" id="guideHook"><div class="card-body small">
    <h6 class="fs-6">B. {{ __('Callback URL / webhook (their system sends to Flikma)') }}</h6>
    <div class="guide-step"><div class="n">1</div><div><strong>{{ __('Add the device.') }}</strong> {{ __('Add Device → Type "HTTP / JSON API" → option "The device / vendor sends to us" → Save. You land on the device\'s Configure page.') }}</div></div>
    <div class="guide-step"><div class="n">2</div><div><strong>{{ __('Copy the Callback URL.') }}</strong> {{ __('Configure → Connection shows the Callback URL of this device (looks like') }} <code>{{ $root }}/api/attendance/hook/xxxxxxxx-xxxx-…</code>). {{ __('Paste it into the vendor / machine software where it asks for "Server URL", "Webhook URL", "HTTP push" or "Callback". The URL itself is the password — do not share it. If it leaks, delete the device and add it again.') }}</div></div>
    <div class="guide-step"><div class="n">3</div><div><strong>{{ __('Choose the format.') }}</strong> {{ __('Use POST with JSON. Example of what Flikma understands:') }}
<pre>POST {{ $root }}/api/attendance/hook/&lt;callback-id&gt;
Content-Type: application/json

{ "user_id": "17", "timestamp": "2026-10-11 08:02:10", "type": "in" }

# or several at once
{ "data": [ {"pin":"17","time":"2026-10-11 08:02:10"}, {"pin":"21","time":"2026-10-11 08:05:44"} ] }</pre></div></div>
    <div class="guide-step"><div class="n">4</div><div><strong>{{ __('Map the columns if your device uses different names.') }}</strong> {{ __('Configure → Field Mapping. Paste one real sample the device sends; Flikma lists its fields — choose which field is the employee ID, the time, and (optionally) in/out. The preview shows exactly how the punches will be read before you save.') }}</div></div>
    <div class="guide-step"><div class="n">5</div><div><strong>{{ __('Alternative with an API key.') }}</strong> {{ __('Systems that can add a header may instead call') }} <code>POST {{ $root }}/api/attendance/punch</code> {{ __('with') }} <code>Authorization: Bearer &lt;device API key&gt;</code>. {{ __('The key is shown once when the device is created; use the key icon to generate a new one.') }}</div></div>
    <div class="text-muted">{{ __('For devices that can only open a web address (GET) you can use:') }} <code>{{ $root }}/api/attendance/hook/&lt;callback-id&gt;?pin=17&amp;time=2026-10-11 08:02:10</code></div>
</div></div>

<div class="card border-0 shadow-sm rounded-3 mb-3 guide-card" id="guidePull"><div class="card-body small">
    <h6 class="fs-6">C. {{ __('Vendor API (Flikma fetches the punches)') }}</h6>
    <div class="guide-step"><div class="n">1</div><div><strong>{{ __('Get these from the vendor:') }}</strong>
        <ul class="mb-0 mt-1">
            <li>{{ __('The API endpoint that lists attendance / punch logs (a full https:// address).') }}</li>
            <li>{{ __('How to authenticate: usually an API key or token sent in a header, e.g. Authorization: Bearer abc123 or X-API-Key: abc123.') }}</li>
            <li>{{ __('Which parameters limit the date range (from / to), and whether it is GET or POST.') }}</li>
        </ul></div></div>
    <div class="guide-step"><div class="n">2</div><div><strong>{{ __('Add the device.') }}</strong> {{ __('Add Device → Type "HTTP / JSON API" → option "We fetch from the vendor API" → Save.') }}</div></div>
    <div class="guide-step"><div class="n">3</div><div><strong>{{ __('Enter their endpoint.') }}</strong> {{ __('Configure → Connection: API endpoint, method, headers (one per line, "Name: value" — stored encrypted), parameters and how often to fetch (default every 15 minutes). In the URL / parameters you can use placeholders that Flikma fills in each time:') }}
<pre>{from}  {to}            2026-10-11 08:00:00        (device time zone)
{from_date} {to_date}   2026-10-11
{from_epoch} {to_epoch} 1791705600

Example parameters:  start={from_date}&amp;end={to_date}&amp;page_size=500</pre></div></div>
    <div class="guide-step"><div class="n">4</div><div><strong>{{ __('Test the connection.') }}</strong> {{ __('Configure → Test → "Test fetch" calls their API once and shows what came back and how it was understood — nothing is saved. If the fields are not recognised, go to Field Mapping.') }}</div></div>
    <div class="guide-step"><div class="n">5</div><div><strong>{{ __('Map the columns') }}</strong> {{ __('(Field Mapping tab) and press "Fetch & import now". After that Flikma fetches automatically on the interval you set.') }}</div></div>
    <div class="text-muted">{{ __('Automatic fetching needs the server scheduler to be running (cron entry "* * * * * php artisan schedule:run"). The status of the last fetch is shown on the Configure page. Internal / private network addresses (192.168.x.x, localhost) cannot be fetched from the cloud server — for machines on your own LAN use method A or B instead.') }}</div>
</div></div>

<div class="card border-0 shadow-sm rounded-3 mb-3 guide-card"><div class="card-body small">
    <h6 class="fs-6">{{ __('Field mapping — what each setting means') }}</h6>
    <div class="table-responsive"><table class="table table-sm align-middle">
        <thead class="table-light"><tr><th>{{ __('Setting') }}</th><th>{{ __('Meaning') }}</th><th>{{ __('Example') }}</th></tr></thead>
        <tbody>
            <tr><td><strong>{{ __('Records path') }}</strong></td><td>{{ __('Where the list of punches sits inside the response. Leave empty if the response itself is the list.') }}</td><td><code>data.logs</code></td></tr>
            <tr><td><strong>{{ __('Employee field') }}</strong></td><td>{{ __('The column that holds the person\'s ID on the machine.') }}</td><td><code>emp_code</code> {{ __('or') }} <code>user.id</code></td></tr>
            <tr><td><strong>{{ __('Match employee by') }}</strong></td><td>{{ __('Machine ID (default, falls back to Employee Code) · Employee Code · Flikma employee id.') }}</td><td>—</td></tr>
            <tr><td><strong>{{ __('Time field') }}</strong></td><td>{{ __('The column with the punch date-time. If the date is in a different column, also set "Date field".') }}</td><td><code>punch_time</code></td></tr>
            <tr><td><strong>{{ __('Time format') }}</strong></td><td>{{ __('Auto handles ISO (2026-10-11 08:02:10), dd-mm-yyyy and epoch. Choose a custom format only if the device uses something unusual.') }}</td><td><code>d/m/Y h:i A</code></td></tr>
            <tr><td><strong>{{ __('Direction field') }}</strong></td><td>{{ __('Optional column that says in or out, with the values that mean "in" and "out". If the machine does not send it, leave empty: the first punch of the day = check-in, the last = check-out.') }}</td><td><code>state</code> · in: <code>0,I</code> · out: <code>1,O</code></td></tr>
        </tbody>
    </table></div>
</div></div>

<div class="card border-0 shadow-sm rounded-3 mb-5 guide-card"><div class="card-body small">
    <h6 class="fs-6">{{ __('How punches become attendance') }}</h6>
    <ul class="mb-3">
        <li>{{ __('First punch of the day = check-in, latest punch = check-out. Status becomes Present.') }}</li>
        <li>{{ __('Two taps within 1 minute count once. Manual attendance edits are never overwritten.') }}</li>
        <li>{{ __('Punches for a month where payroll is already generated are saved but marked "Locked" and not applied.') }}</li>
        <li>{{ __('Times are read in the device\'s time zone (set when adding the device).') }}</li>
    </ul>
    <h6 class="fs-6">{{ __('Troubleshooting') }}</h6>
    <div class="table-responsive"><table class="table table-sm align-middle mb-0">
        <thead class="table-light"><tr><th>{{ __('Problem') }}</th><th>{{ __('Cause / fix') }}</th></tr></thead>
        <tbody>
            <tr><td>{{ __('Device never shows "Last Seen"') }}</td><td>{{ __('Wrong server address/port on the machine, no internet, or the serial number here does not match the machine exactly.') }}</td></tr>
            <tr><td>401 / "Invalid or inactive device token"</td><td>{{ __('Wrong or old API key, or the device is switched off. Generate a new key and update the sender.') }}</td></tr>
            <tr><td>404 on the callback URL</td><td>{{ __('Callback URL copied incompletely, or the device was deleted / switched off.') }}</td></tr>
            <tr><td>422 "No valid punches found"</td><td>{{ __('The sender uses other column names. Open Field Mapping and map the employee and time fields.') }}</td></tr>
            <tr><td>{{ __('Punch is in the log as "Unmapped"') }}</td><td>{{ __('That machine ID is not linked to an employee. Link it in Payroll → Punch Log or set "Machine ID" on the employee.') }}</td></tr>
            <tr><td>{{ __('Punch is "Locked"') }}</td><td>{{ __('Payroll for that employee and month already exists. Disapprove/delete the payroll record, then punches can be applied (re-send them).') }}</td></tr>
            <tr><td>{{ __('Wrong time (hours off)') }}</td><td>{{ __('Change the device time zone (Devices → pencil icon).') }}</td></tr>
        </tbody>
    </table></div>
</div></div>
<script>
    function gotoGuide(id) { var el = document.getElementById(id); if (el) el.scrollIntoView({behavior: 'smooth', block: 'start'}); return false; }
</script>
