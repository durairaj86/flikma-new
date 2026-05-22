(function ($) {
    $.fn.valid = function () {
        let form = this[0];
        let isValid = true;

        // Scope tab operations to the tabs inside the same modal/container as the form
        let $modalTabs = $(form).closest('.modal, .modal-body, [id$="Modal"]').find('#modalTabs');
        if (!$modalTabs.length) $modalTabs = $('#modalTabs');

        // Remove previous errors
        $(form).find(".is-invalid").removeClass("is-invalid");
        $(form).find(".error-tooltip-top").remove();
        $(form).find("label").removeClass("label-error");
        $modalTabs.find(".nav-item button").removeClass("tab-has-error");

        $(form).find("input, select, textarea").each(function () {
            let $field = $(this);
            if (!this.checkValidity()) {
                isValid = false;
                $field.addClass("is-invalid");

                let message = this.validationMessage || "This field is required.";

                // Wrap input if not already wrapped
                if (!$field.parent().hasClass("position-relative")) {
                    $field.wrap('<div class="position-relative w-100"></div>');
                }

                // Mark the label as error state
                $field.closest(".form-group").find("label").addClass("label-error");

                // Highlight tab header — scoped to modal tabs only
                let tabPane = $field.closest(".tab-pane");
                if (tabPane.length) {
                    let tabId = tabPane.attr("id");
                    $modalTabs.find(`button[data-bs-target="#${tabId}"]`).addClass("tab-has-error");
                }
            }
        });

        // Open first error tab
        if (!isValid) {
            let firstErrorTab = $modalTabs.find("button.tab-has-error").first();
            if (firstErrorTab.length) {
                let tab = new bootstrap.Tab(firstErrorTab[0]);
                tab.show();
            }
        }

        return isValid;
    };

    // Remove error state when input becomes valid
    $(document).on("input change", "input, select, textarea", function () {
        let $field = $(this);
        let $modalTabs = $field.closest('.modal, .modal-body, [id$="Modal"]').find('#modalTabs');
        if (!$modalTabs.length) $modalTabs = $('#modalTabs');

        if (this.checkValidity()) {
            $field.removeClass("is-invalid");
            $field.next(".error-tooltip-top").remove();

            // Remove label error state
            $field.closest(".form-group").find("label").removeClass("label-error");

            // Remove tab error if no more invalid fields in that tab — scoped to modal tabs only
            let tabPane = $field.closest(".tab-pane");
            if (tabPane.length && tabPane.find(".is-invalid").length === 0) {
                let tabId = tabPane.attr("id");
                $modalTabs.find(`button[data-bs-target="#${tabId}"]`).removeClass("tab-has-error");
            }
        } else {
            // Re-apply label error if field becomes invalid again
            $field.closest(".form-group").find("label").addClass("label-error");
        }
    });
})(jQuery);
