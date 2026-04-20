(function ($) {
    $.fn.valid = function () {
        let form = this[0];
        let isValid = true;

        // Remove previous errors
        $(form).find(".is-invalid").removeClass("is-invalid");
        $(form).find(".error-tooltip-top").remove();
        $(".nav-item button").removeClass("text-danger");

        $(form).find("input, select, textarea").each(function () {
            let $field = $(this);
            if (!this.checkValidity()) {
                isValid = false;
                $field.addClass("is-invalid");//temporary hide for border red

                let message = this.validationMessage || "This field is required.";

                // Wrap input if not already wrapped
                if (!$field.parent().hasClass("position-relative")) {
                    $field.wrap('<div class="position-relative w-100"></div>');
                }

                // Add tooltip above input//message hide
                /*if ($field.next(".error-tooltip-top").length === 0) {
                    let $tooltip = $(`
                        <div class="error-tooltip-top">
                            <div class="tooltip-arrow"></div>
                            <div class="tooltip-inner">${message}</div>
                        </div>
                    `);
                    $field.after($tooltip);
                }*/

                // Highlight tab header
                let tabPane = $field.closest(".tab-pane");
                if (tabPane.length) {
                    let tabId = tabPane.attr("id");
                    //$(`.nav-item button[data-bs-target="#${tabId}"]`).addClass("text-danger");//for tab required column
                }
            }
        });

        // Open first error tab
        if (!isValid) {
            let firstErrorTab = $(".nav-item button.text-danger").first();
            if (firstErrorTab.length) {
                let tab = new bootstrap.Tab(firstErrorTab[0]);
                tab.show();
            }
        }

        return isValid;
    };

    // Remove tooltip when input is valid
    $(document).on("input change", "input, select, textarea", function () {
        let $field = $(this);
        if (this.checkValidity()) {
            $field.removeClass("is-invalid");
            $field.next(".error-tooltip-top").remove();

            let tabPane = $field.closest(".tab-pane");
            if (tabPane.length && tabPane.find(".is-invalid").length === 0) {
                let tabId = tabPane.attr("id");
                $(`.nav-item button[data-bs-target="#${tabId}"]`).removeClass("text-danger");
            }
        }
    });
})(jQuery);
