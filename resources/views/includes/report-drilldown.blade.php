{{-- Click an account balance on a report: the postings behind it open in the global modal. Defined once, used by Profit & Loss and Balance Sheet. --}}
<script>
    window.reportDrill = window.reportDrill || function (code, title, from, to) {
        webModal.openGlobalModal({
            title: title,
            url: '/reports/account-postings?code=' + encodeURIComponent(code) + '&to=' + encodeURIComponent(to) + (from ? '&from=' + encodeURIComponent(from) : ''),
            size: 'lg', scroll: true, minHeight: '', maxHeight: ''
        });
    };
</script>
<style>.rpt-drill { cursor: pointer; } .rpt-drill:hover { text-decoration: underline; }</style>
