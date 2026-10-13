{{-- Same look as the Chart of Accounts import page: grey page, centred column, white cards --}}
<style>
    .imp-page { min-height: 100%; }
    .imp-wrap { max-width: 1400px; margin: 0 auto; padding: 18px 28px 8px; }
    .imp-crumb { font-size: .9rem; color: #475467; }
    .imp-crumb a { color: #0d6efd; }
    .imp-h1 { font-size: 1.55rem; font-weight: 700; letter-spacing: .02em; text-transform: uppercase; color: #1d2939; margin: 6px 0 2px; }
    .imp-sub { color: #667085; font-size: .92rem; }
    .imp-card { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(16, 24, 40, .08); padding: 20px 22px; }
    .imp-card h6 { font-weight: 700; color: #1d2939; display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
    .imp-step { width: 24px; height: 24px; border-radius: 50%; background: #0d6efd; color: #fff; font-size: .8rem; display: inline-flex; align-items: center; justify-content: center; flex: 0 0 auto; }
    .imp-help { color: #667085; font-size: .88rem; }
    .rc-layout { display: flex; gap: 20px; align-items: flex-start; }
    .rc-main { flex: 1 1 0; min-width: 0; }
    .rc-aside { width: 310px; flex: 0 0 310px; position: sticky; top: 12px; margin-top: 4px; }
    .rc-steps { list-style: none; counter-reset: rc; padding: 0; margin: 0; }
    .rc-steps li { counter-increment: rc; position: relative; padding: 0 0 12px 34px; font-size: .86rem; color: #475467; }
    .rc-steps li::before { content: counter(rc); position: absolute; left: 0; top: 0; width: 24px; height: 24px; border-radius: 50%; background: #eaf2ff; color: #0d6efd; font-weight: 700; font-size: .78rem; display: flex; align-items: center; justify-content: center; }
    .rc-steps li b { display: block; color: #1d2939; font-size: .9rem; }
    .rc-how { font-size: .9rem; font-weight: 600; text-decoration: none; white-space: nowrap; }
    .rc-lbl { font-size: .74rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: #667085; margin-bottom: 4px; }
</style>
