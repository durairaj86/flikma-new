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
                window.copyText(r.url).then(function (copied) {
                    if (copied) {
                        toastr.success(trans('Link copied') + ': ' + r.url);
                    } else {
                        // the browser would not allow copying: show the link selected so it can be copied with Ctrl+C
                        $.alert({
                            title: trans('Copy this link'),
                            content: '<input type="text" class="form-control" readonly value="' + $('<div>').text(r.url).html() + '" onfocus="this.select()" autofocus>',
                            onContentReady: function () { this.$content.find('input').trigger('focus').select(); },
                        });
                    }
                });
            });
        });
    },
};
