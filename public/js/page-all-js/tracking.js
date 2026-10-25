TRACKING = {
    load() {
        // These pages are not tables, so refresh the whole page after a save.
        window.afterModalSave = () => setTimeout(() => window.location.reload(), 600);
        $('.trk-update').off().on('click', function () {
            webModal.openGlobalModal({
                title: trans('Update Tracking') + ' - ' + $(this).data('title'),
                url: GLOBAL_FN.buildUrl('operation/tracking/' + $(this).data('id')),
                content: null,
                size: 'lg',
                scroll: true,
            });
        });
        $('.trk-share').off().on('click', function () {
            $.post(GLOBAL_FN.buildUrl('operation/tracking/' + $(this).data('id') + '/share'), {_token: $('meta[name="csrf-token"]').attr('content')}, function (r) {
                if (navigator.clipboard) navigator.clipboard.writeText(r.url);
                toastr.success('Link copied: ' + r.url);
            });
        });
    },
};
