$(document).ready(function () {
    // Notification popup

    $(".notification .bell-icon").click(function (e) {
        e.stopPropagation();
        $(".notification .notification-content").fadeToggle();
        $(".profile-dropdown .profile-dropdown-content").fadeOut();
    });

    $(".notification .notification-content").click(function (event) {
        event.stopPropagation();
    });

    $("body, html").on("click", function () {
        $(".notification .notification-content").fadeOut();
    });

    // Profile dropdown

    $(".profile-dropdown .profile-head").click(function (e) {
        e.stopPropagation();
        $(".profile-dropdown .profile-dropdown-content").fadeToggle();
        $(".notification .notification-content").fadeOut();
    });

    $(".profile-dropdown .profile-dropdown-content").click(function (event) {
        event.stopPropagation();
    });

    $("body, html").on("click", function () {
        $(".profile-dropdown .profile-dropdown-content").fadeOut();
    });

    // Niceselect
    // $("select").niceSelect();

    // Sub meu dropdown
    $(".menu-item.has-children").on("click", function () {
        $(this).find(".sub-menu").slideToggle("fast");
        $(this).find(".menu-link").toggleClass("active");
    });

    // hamburger
    $("#hamburger").on("click", function () {
        $(".sidebar").addClass("toggle");
        $(".main-right-content").addClass("overlayer");
        $("body").addClass("overflow-hidden");
    });

    $(".main-right-content").on("click", function () {
        $(".sidebar").removeClass("toggle");
        $(this).removeClass("overlayer");
        $("body").removeClass("overflow-hidden");
    });

    // if (window.matchMedia("(max-width: 768px)").matches) {
    //     $("#hamburger").on("click", function () {
    //         $(".sidebar").addClass("toggle");
    //         $(".main-right-content").addClass("overlayer");
    //         $("body").addClass("overflow-hidden");
    //     });

    //     $(".main-right-content").on("click", function () {
    //         $(".sidebar").removeClass("toggle");
    //         $(this).removeClass("overlayer");
    //         $("body").removeClass("overflow-hidden");
    //     });
    // } else if (window.matchMedia("(max-width: 1200px)").matches) {
    //     $(".sidebar").toggleClass("icon-toggle");
    //     $("#hamburger").on("click", function () {
    //         $(".sidebar").toggleClass("medium");
    //         $("body").addClass("overflow-hidden");
    //         $(".main-right-content").addClass("overlayer");
    //     });

    //     $(".main-right-content").on("click", function () {
    //         $(".sidebar").removeClass("medium");
    //         $(this).removeClass("overlayer");
    //         $("body").removeClass("overflow-hidden");
    //     });
    // } else {
    //     $("#hamburger").on("click", function () {
    //         $(".sidebar").toggleClass("icon-toggle");
    //         $(".top-bar").toggleClass("icon-toggle");
    //         $(".main-right-content").toggleClass("icon-toggle");
    //         $(".app-footer").toggleClass("icon-toggle");
    //     });
    // }

    $("#add-purchase-item-btn").on("click", () => {
        $(".purchase-item-append").append(`
        <div class="purchase-item px-4">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" placeholder="Purchase Entry ID">
                        <label>Purchase Entry ID</label>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" placeholder="Product ID">
                        <label>Product ID</label>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" placeholder="Invoice No.">
                        <label>Invoice No.</label>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" placeholder="Rate">
                        <label>Rate</label>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" placeholder="Quantity">
                        <label>Quantity</label>
                    </div>
                </div>
            </div>
        </div>
        `);
    });

    $(".search input").on("keyup", function () {
        if ($(this).val() == false) {
            $(this).siblings(".cross-input-btn").addClass("invisible");
            $(this).siblings(".bi-search").removeClass("invisible");
        } else {
            $(this).siblings(".cross-input-btn").removeClass("invisible");
            $(this).siblings(".bi-search").addClass("invisible");
        }
    });

    $(".cross-input-btn").click(function () {
        $(this).siblings("input").val("");
        $(this).addClass("invisible");
        $(this).siblings(".bi-search").removeClass("invisible");
    });

    $(".cultivation-timeline").height($(".time-event-wrapper").height() + 150);

    //Datrange picker

    // $("input.dates").daterangepicker();

    $("#purchase-next-btn").on("click", function () {
        $("#purchase-entry-form").hide();
        $("#purchase-item-table").show();
        $(".steps .step-each.active").removeClass("active").addClass("done");
        $(".steps .step-each:last-child").addClass("active");
    });

    //Permission Table
    $(".permission-table .title-row").on("click", function () {
        let ss = $(this).attr("data-target");
        $(ss).toggleClass("d-none");
    });

    // Select Picker
    // $(document).ready(function () {
    //     $(".bs-picker").selectpicker({
    //         liveSearch: true,
    //         showSubtext: true,
    //     });
    // });
});
