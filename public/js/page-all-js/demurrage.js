DEMURRAGE = {
    load() {
        // These pages are not tables, so refresh the whole page after a save.
        window.afterModalSave = () => setTimeout(() => window.location.reload(), 600);
        $('.dm-edit').off().on('click', function () {
            webModal.openGlobalModal({
                title: 'Container ' + $(this).data('title'),
                url: GLOBAL_FN.buildUrl('operation/demurrage/' + $(this).data('id')),
                content: null,
                size: 'lg',
                scroll: true,
            });
        });
    },
};
