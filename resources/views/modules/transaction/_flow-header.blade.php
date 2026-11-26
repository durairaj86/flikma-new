{{-- Form header of an advance / refund: grey as every form, with the bottom border and the Save buttons (#show-buttons, filled
     by startup.js) in the colour of which way the money goes: out = warning, in = success. A payment / collection against
     invoices (info) keeps the plain header, only its Save and Approve button is the primary colour. Pass $flowTone. --}}
<style>
    .flow-head.flow-warning { --flow: var(--bs-warning); --flow-text: #000; --flow-emphasis: var(--bs-warning-text-emphasis); }
    .flow-head.flow-success { --flow: var(--bs-success); --flow-text: #fff; --flow-emphasis: var(--bs-success-text-emphasis); }
    .flow-head:is(.flow-warning, .flow-success) { border-bottom: 3px solid var(--flow) !important; }
    .flow-head:is(.flow-warning, .flow-success) #modalSaveApproveBtn { background: var(--flow); border-color: var(--flow); color: var(--flow-text); }
    .flow-head:is(.flow-warning, .flow-success) #modalSaveApproveBtn:hover { filter: brightness(.92); }
    .flow-head:is(.flow-warning, .flow-success) #modalSaveAsDraftBtn { border-color: var(--flow); color: var(--flow-emphasis); }
    .flow-head:is(.flow-warning, .flow-success) #modalSaveAsDraftBtn:hover { background: var(--flow); color: var(--flow-text); }
    .flow-head.flow-info #modalSaveApproveBtn { background: var(--bs-primary); border-color: var(--bs-primary); color: #fff; }
    .flow-head.flow-info #modalSaveApproveBtn:hover { filter: brightness(.92); }
</style>
