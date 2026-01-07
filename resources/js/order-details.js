$(document).ready(function () {
    const { orderRawJson, order } = window.orderData || {};
    const {
        cardNoteRoute,
        deliveryDetailsRoute,
        shippingAddressRoute,
        billingAddressRoute,
        updateDeliveryPartner,
        setOrderField,
        waybillDownloadRoute,
        PODRoute,
        lalamovePodImageRoute,
        detrackPODRoute,
        getDetrackVehicles,
    } = window.routes || {};
    const orderId = orderRawJson?.id;
    const orderNumber = order?.order_number;
    const detrackAssignedTo = order?.detrack_assigned_to;
    const shippingAddress = orderRawJson?.shipping_address || {};
    const billingAddress = orderRawJson?.billing_address || {};
    const noteAttribute = orderRawJson?.note_attributes || [];
    const source = order?.source.toLowerCase();
    let fedexTrackingData = null;

    const getNoteAttribute = (key) => {
        const attribute = noteAttribute.find((attr) => attr.name === key);
        return attribute ? attribute.value : "";
    };

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

    function updateOrderFieldCount(field) {
        if (!setOrderField || !orderId || !field) return;
        $.ajax({
            url: setOrderField,
            type: "POST",
            data: {
                ids: [orderId],
                field: field,
                _token: csrfToken,
            },
        });
    }

    function updateOrder(url, data, reloadPage = true) {
        $.ajax({
            url: url,
            type: "POST",
            data: { ...data, _token: csrfToken, order_id: orderId },
            success: function (res) {
                if (res.success) {
                    reloadPage && location.reload();
                } else {
                    console.log("🚀 ~ Update failed:");
                }
            },
            error: function (err) {
                console.error("🚀 ~ Something went wrong", err);
            },
        });
    }

    // Card modal
    $("#editCardForm").on("submit", function (e) {
        e.preventDefault();
        const note = $("#cardNote").val();
        updateOrder(cardNoteRoute, { note });
    });

    //Delivery partner
    $("#editDeliveryPartnerBtn").on("click", function () {
        const deliveryPartner = $("#deliveryPartnerSelect").val();
        updateOrder(updateDeliveryPartner, {
            delivery_partner: deliveryPartner,
        });
    });

    // Delivery modal
    $("#editDeliveryForm").on("submit", function (e) {
        e.preventDefault();
        const delivery_date = $("#deliveryDate").val();
        const time_slot = $("#timeSlot").val();
        updateOrder(deliveryDetailsRoute, {
            delivery_date,
            time_slot,
        });
    });

    // Shipping modal
    $("#editShippingForm").on("submit", function (e) {
        e.preventDefault();
        const shipping_address = {
            first_name: $("#shippingFirstName").val(),
            last_name: $("#shippingLastName").val(),
            company: $("#shippingCompany").val(),
            address1: $("#shippingAddress1").val(),
            address2: $("#shippingAddress2").val(),
            city: $("#shippingCity").val(),
            province: $("#shippingProvince").val(),
            zip: $("#shippingPostcode").val(),
            country: $("#shippingCountry").val(),
            phone: $("#shippingPhone").val(),
        };
        updateOrder(shippingAddressRoute, { shipping_address });
    });

    // Sender modal
    $("#editSenderForm").on("submit", function (e) {
        e.preventDefault();
        const billing_address = {
            first_name: $("#senderFirstName").val(),
            last_name: $("#senderLastName").val(),
            company: $("#senderCompany").val(),
            address1: $("#senderAddress1").val(),
            address2: $("#senderAddress2").val(),
            city: $("#senderCity").val(),
            province: $("#senderProvince").val(),
            zip: $("#senderZip").val(),
            country: $("#senderCountry").val(),
            phone: $("#senderPhone").val(),
        };

        updateOrder(billingAddressRoute, { billing_address });
    });

    $("#editCardBtn").on("click", function () {
        const currentNote = orderRawJson.note === null ? "" : orderRawJson.note;
        $("#cardNote").val(currentNote);
    });

    $("#editDeliveryBtn").on("click", function () {
        let deliveryDate = "";
        let timeSlot = "";
        if (source === "tiktok") {
            deliveryDate = order.delivery_date;
            timeSlot = order.delivery_time;
        } else {
            deliveryDate = getNoteAttribute("date");
            timeSlot = getNoteAttribute("timeslot");
        }
        $("#deliveryDate").val(deliveryDate);
        $("#timeSlot").val(timeSlot);
    });

    $("#editShippingBtn").on("click", function () {
        $("#shippingFirstName").val(shippingAddress.first_name);
        $("#shippingLastName").val(shippingAddress.last_name);
        $("#shippingCompany").val(shippingAddress.company);
        $("#shippingAddress1").val(shippingAddress.address1);
        $("#shippingAddress2").val(shippingAddress.address2);
        $("#shippingCity").val(shippingAddress.city);
        $("#shippingProvince").val(shippingAddress.province);
        $("#shippingPostcode").val(shippingAddress.zip);
        $("#shippingCountry").val(shippingAddress.country);
        $("#shippingPhone").val(shippingAddress.phone);
    });

    $("#editSenderBtn").on("click", function () {
        $("#senderFirstName").val(billingAddress.first_name);
        $("#senderLastName").val(billingAddress.last_name);
        $("#senderCompany").val(billingAddress.company);
        $("#senderAddress1").val(billingAddress.address1);
        $("#senderAddress2").val(billingAddress.address2);
        $("#senderCity").val(billingAddress.city);
        $("#senderProvince").val(billingAddress.province);
        $("#senderZip").val(billingAddress.zip);
        $("#senderCountry").val(billingAddress.country);
        $("#senderPhone").val(billingAddress.phone);
    });

    $("#printCard").on("click", function () {
        const note = orderRawJson.note || "";
        const shippingAddressName = shippingAddress.name || "";

        const printContent = $(`
            <div class="card-content-wrapper" id="print-area">
                <div class="top-half"></div>
                <div class="bottom-half">
                    <div class="card-content">
                        <p>${note}</p>
                        <p>${shippingAddressName}</p>
                    </div>
                </div>
            </div>
        `).appendTo("body");

        const printStyles = `
            @page { margin: 0; }
            html, body {
                height: 100%;
                margin: 0;
                font-size: 15px;
            }
            .card-content-wrapper {
                height: 100% !important;
                display: flex;
                flex-direction: column;
            }
            .top-half { height: 50%; }
            .bottom-half {
                height: 50%;
                display: flex;
                justify-content: center;
                align-items: center;
            }
            .card-content {
                padding: 20px;
                text-align: center;
                box-sizing: border-box;
                max-width: 300px;
            }
        `;

        printJS({
            printable: "print-area",
            type: "html",
            style: printStyles,
            targetStyles: ["*"],
            scanStyles: false,
        });

        setTimeout(() => printContent.remove(), 1000);
        updateOrderFieldCount("mc_no");
    });

    $("#editPrint").on("click", function () {
        const baseUrl =
            "https://admin.shopify.com/store/floristika-malaysia/apps/order-printer-emailer/print/orders";
        const queryString = $.param({ ids: orderId });
        const url = `${baseUrl}?${queryString}`;
        window.open(url, "_blank");
        updateOrderFieldCount("pl_no");
    });

    $("#fulfillOrder").on("click", function () {
        const selectedPartner = $("#deliveryPartnerSelect").val();
        if (selectedPartner === "FedEx") {
            if (!fedexTrackingData) {
                return;
            }
            updateOrder(
                updateDeliveryPartner,
                { delivery_partner: selectedPartner },
                false
            );
            fulfillOrder($(this), fedexTrackingData);
        } else {
            fulfillOrder($(this));
        }
    });

    $("#saveTrackingBtn").on("click", function () {
        const trackingNumber = $("#trackingNumber").val().trim();
        const selectedPartner = $("#deliveryPartnerSelect").val();
        if (!trackingNumber) {
            return;
        }
        fedexTrackingData = {
            company: selectedPartner,
            number: trackingNumber,
            url: `https://www.fedex.com/fedextrack/?trknbr=${trackingNumber}`,
        };

        $("#fedexModal").modal("hide");
    });

    const lineClearModal =
        $("#lineClearModal").length &&
        new bootstrap.Modal($("#lineClearModal"));
    const lalamoveModal =
        $("#lalamoveModal").length && new bootstrap.Modal($("#lalamoveModal"));

    const lalamoveQuoteModal =
        $("#lalamoveQuoteModal").length &&
        new bootstrap.Modal($("#lalamoveQuoteModal"));

    const detrackModal =
        $("#detrackModal").length && new bootstrap.Modal($("#detrackModal"));

    const driverInfoModal =
        $("#driverInfoModal").length &&
        new bootstrap.Modal($("#driverInfoModal"));

    $("#deliveryPartnerSelect").on("change", function () {
        const value = $(this).val();

        switch (value) {
            case "FedEx":
                $("#fedexModal").modal("show");
                break;
            case "Line Clear":
                openLineClearModal();
                break;
            case "Lalamove":
                openLalamoveModal();
                break;
            case "Detrack":
                openDetrackModal();
                break;
        }
    });

    $("#saveLineclearBtn").on("click", function () {
        lineClearModal.hide();
    });

    $("#saveLalamoveBtn").on("click", function () {
        lalamoveQuoteModal.hide();
    });

    $("#getQuoteBtn").on("click", async function () {
        lalamoveModal.hide();
    });

    $("#saveDetrackBtn").on("click", function () {
        detrackModal.hide();
    });

    function openLineClearModal() {
        const tbody = $("#lineClearTableBody");
        tbody.empty();
        tbody.append(`
            <tr data-order-id="${orderId}">
                <td class="text-center orderNumber">#${orderNumber}</td>
                <td>
                    <select class="form-select sizeOption">
                        <option value="">Select</option>
                        <option value="Premium">Premium</option>
                        <option value="Freshbox">Freshbox</option>
                    </select>
                </td>

                <td>
                    <select class="form-select dimensionOption">
                        <option value="">Select</option>
                        <option value="Small (15x20x60)">Small (15×20×60)</option>
                        <option value="Medium (25x25x60)">Medium (25×25×60)</option>
                        <option value="Large (35x35x60)">Large (35×35×60)</option>
                    </select>
                </td>
            </tr>
        `);
        lineClearModal.show();
    }

    function openLalamoveModal() {
        const container = $("#lalamove-order-no");
        container.html("");
        container.append(`
                <span class="badge bg-secondary me-1">#${orderNumber}</span>
            `);
        lalamoveModal.show();
    }

    function openDetrackModal() {
        const tbody = $("#detrackTableBody");
        tbody.empty();
        tbody.append(`
              <tr data-order-id="${orderId}">
                <td class="text-center orderNumber">#${orderNumber}</td>
                <td><textarea class="form-control notesInput" rows="2" placeholder="Enter notes"></textarea></td>
            </tr>
        `);
        detrackModal.show();
    }

    function formatTimeAgo(timestamp) {
        const parsed = moment(timestamp);
        return parsed.fromNow();
    }

    function prepareVehicleInfo(vehicle) {
        const status = vehicle?.status;
        let statusLabel = "Offline";
        let statusClass = "bg-secondary";

        if (status === "normal") {
            statusLabel = "Online";
            statusClass = "bg-success";
        } else if (status === "off") {
            statusLabel = "Offline";
            statusClass = "bg-warning text-dark";
        }

        return {
            id: vehicle?.detrack_id || "",
            name: vehicle?.name || "-",
            mobile: vehicle?.mobile_number || "-",
            statusLabel,
            statusClass,
            lastSeen: formatTimeAgo(vehicle?.connected_at),
        };
    }

    function renderVehicleInfo(vehicle) {
        const vehicleInfo = prepareVehicleInfo(vehicle);
        $("#driverInfoModalBody").html(`
        <div class="p-3 bg-light border rounded shadow-sm d-flex align-items-center gap-3 flex-wrap">
            <div class="flex-grow-1">
                <div class="fw-bold mb-1">${vehicleInfo.name}</div>

                <div class="small text-muted d-flex flex-wrap gap-3">
                    <span><strong>Detrack ID:</strong> ${vehicleInfo.id}</span>
                    <span><strong>Phone:</strong> ${vehicleInfo.mobile}</span>
                </div>

                <div class="text-muted small mt-1 d-flex align-items-center gap-2">
                    <span class="badge rounded-pill ${vehicleInfo.statusClass} d-inline-flex align-items-center justify-content-center pt-2 pb-1 px-3 fs-7">
                        ${vehicleInfo.statusLabel}
                    </span>
                    <span>Last seen: ${vehicleInfo.lastSeen}</span>
                </div>
            </div>

            <a href="tel:${vehicleInfo.mobile}"
               class="btn btn-success d-flex align-items-center gap-1 px-3 py-2 rounded-pill fw-semibold shadow-sm">
                <i class="bi bi-telephone-fill"></i>
                Call
            </a>
        </div>
    `);
    }

    function renderVehicleError() {
        $("#driverInfoModalBody").html(
            `<div class="p-3 bg-light border rounded shadow-sm d-flex align-items-center justify-content-center flex-column">
                <div class="text-danger small text-center">
                    Something went wrong while fetching driver data.
                </div>
            </div>`
        );
    }

    $(document).on("click", "#viewVehicleInfoBtn", async function () {
        driverInfoModal.show();
        try {
            const vehicles = await $.ajax({
                url: getDetrackVehicles,
                type: "GET",
            });
            const vehiclesArray = Array.isArray(vehicles) ? vehicles : [];
            const vehicle = vehiclesArray.find(
                (v) =>
                    v.name?.trim().toLowerCase() ===
                    detrackAssignedTo.trim().toLowerCase()
            );
            renderVehicleInfo(vehicle);
        } catch (error) {
            renderVehicleError();
        }
    });

    function fulfillOrder($btn, trackingData = null) {
        $.ajax({
            url: setOrderField,
            type: "POST",
            data: {
                ids: [orderId],
                field: "fulfillment_status",
                values: { [orderId]: "Fulfilled" },
                tracking_company: trackingData?.company ?? null,
                tracking_number: trackingData?.number ?? null,
                tracking_url: trackingData?.url ?? null,
                _token: csrfToken,
            },
            beforeSend: () => {
                $btn.prop("disabled", true).html(
                    `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...`
                );
            },
            success: (response) => {
                $btn.text("Fulfilled")
                    .removeClass("btn-success")
                    .addClass("btn-secondary");
                fedexTrackingData = null;
            },
            error: (error) => {
                $btn.prop("disabled", false).text("Mark as Fulfilled");
            },
        });
    }

    async function downloadWaybill(waybills, $btn) {
        try {
            const blob = await $.ajax({
                url: waybillDownloadRoute,
                type: "POST",
                contentType: "application/json",
                data: JSON.stringify({ waybills }),
                headers: { "X-CSRF-TOKEN": csrfToken },
                xhrFields: { responseType: "blob" },
                beforeSend: () => {
                    $btn.prop("disabled", true).html(
                        `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...`
                    );
                },
            });
            const fileName =
                waybills
                    .split(",")
                    .map((w) => w.trim())
                    .join("_") + ".pdf";

            downloadBlob(blob, fileName);
        } catch (err) {
            showErrorModal(
                "Waybill Error",
                "Failed to download Waybill",
                "The shipment has been cancelled"
            );
        } finally {
            $btn.prop("disabled", false).text("Download Waybill");
        }
    }

    const podModal =
        $("#podModal").length && new bootstrap.Modal($("#podModal"));
    const detrackPodModal =
        $("#detrackPodModal").length &&
        new bootstrap.Modal($("#detrackPodModal"));

    async function downloadPOD(waybillNo, $btn) {
        try {
            $btn.prop("disabled", true).html(
                `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...`
            );
            const response = await fetch(PODRoute, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({ waybillNo }),
            });

            const contentType = response.headers.get("content-type");

            if (contentType && contentType.includes("application/json")) {
                const res = await response.json();
                if (res.success === false) {
                    podModal.hide();
                    showErrorModal(
                        "POD Error",
                        "Error downloading POD",
                        res.message
                    );
                    return;
                }
            }
            const blob = await response.blob();
            const fileName = `${waybillNo}POD.zip`;
            downloadBlob(blob, fileName);
        } catch (error) {
            showErrorModal("POD Error", "Error downloading POD", error.message);
        } finally {
            $btn.prop("disabled", false).html("Download");
        }
    }

    async function downloadDetrackPOD(deliveryOrderNumber, $btn) {
        try {
            $btn.prop("disabled", true).html(
                `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...`
            );
            const response = await fetch(detrackPODRoute, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({ do_number: deliveryOrderNumber }),
            });
            const contentType = response.headers.get("content-type");

            if (contentType && contentType.includes("application/json")) {
                const res = await response.json();
                if (res.success === false) {
                    detrackPodModal.hide();
                    showErrorModal(
                        "POD Error",
                        "Error downloading POD",
                        res.message
                    );
                    return;
                }
            }
            const blob = await response.blob();
            const fileName = `${deliveryOrderNumber}POD.zip`;
            downloadBlob(blob, fileName);
        } catch (error) {
            showErrorModal("POD Error", "Error downloading POD", error.message);
        } finally {
            $btn.prop("disabled", false).html("Download");
        }
    }

    function downloadBlob(blob, fileName) {
        const url = URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = fileName;
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
    }

    function downloadLalamovePodImage(url, lalamoveOrderId) {
        const params = new URLSearchParams();
        params.set("url", url);
        if (lalamoveOrderId) {
            params.set("orderId", lalamoveOrderId);
        }
        const proxiedUrl = `${lalamovePodImageRoute}?${params.toString()}`;
        const a = document.createElement("a");
        a.href = proxiedUrl;
        document.body.appendChild(a);
        a.click();
        a.remove();
    }

    function formatStatus(status) {
        if (!status) return "-";
        const words = status.toLowerCase().split("_");
        return words
            .map((w) => w.charAt(0).toUpperCase() + w.slice(1))
            .join(" ");
    }

    function renderLalamoveModal(order) {
        const formattedStatus = formatStatus(order.status) || "-";

        const podHtml =
            (order.stops || [])
                .filter((stop) => stop.POD)
                .map((stop, index) => {
                    const { POD } = stop;
                    const { status = "-", image } = POD;
                    const podStatus = formatStatus(status);
                    const stopNumber = index + 1;

                    return `
                <div class="card mb-2 shadow-sm">
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong>Stop #${stopNumber}</strong>
                            <span class="badge bg-secondary">${podStatus}</span>
                        </div>
                        ${
                            image
                                ? `<button class="btn btn-sm btn-outline-primary" 
                                    data-pod-url="${image}" 
                                    data-lalamove-order-id="${
                                        order.orderId || ""
                                    }" 
                                    id="lalamoveDownloadPod">
                                        Download POD
                                   </button>`
                                : `<small class="text-muted">No POD uploaded</small>`
                        }
                    </div>
                </div>
            `;
                })
                .join("") ||
            `<div class="text-muted fst-italic">No POD available</div>`;

        return `
        <div class="modal-content p-3">
            <div class="mb-3">
                <h5 class="modal-title">Order Status</h5>
                <p class="mb-0">${formattedStatus}</p>
            </div>

            <div>
                <h6>POD Details</h6>
                ${podHtml}
            </div>
        </div>
    `;
    }

    async function viewLalamoveOrder(lalamoveOrderId, $btn) {
        if (!lalamoveOrderId) return;

        try {
            $btn.prop("disabled", true).html(
                `<span class="spinner-border spinner-border-sm me-2"></span>Processing...`
            );

            const response = await $.ajax({
                url: `/lalamove/order/${lalamoveOrderId}`,
                type: "GET",
                dataType: "json",
            });

            if (!response?.success)
                throw new Error(response?.message || "Failed to fetch order");

            $("#lalamoveOrderModal .modal-body").html(
                renderLalamoveModal(response.data)
            );

            $("#lalamoveOrderModal").modal("show");
        } catch (err) {
            console.error(err);
        } finally {
            $btn.prop("disabled", false).text("View Lalamove Order");
        }
    }

    $("#downloadWaybillBtn").on("click", function () {
        const waybills = $(this).data("waybill");
        downloadWaybill(waybills, $(this));
    });

    $("#viewPodBtn").on("click", function () {
        podModal.show();
        resetDownloadButton();
    });

    $("#viewDetrackBtn").on("click", function () {
        detrackPodModal.show();
    });

    $("#downloadPodBtn").on("click", function () {
        const waybillNo = $(this).data("waybill");
        downloadPOD(waybillNo, $(this));
    });

    $("#downloadDetrackPodBtn").on("click", function () {
        const formattedOrderNumber = `DO-${orderNumber}`;
        downloadDetrackPOD(formattedOrderNumber, $(this));
    });

    $("#viewLalamoveBtn").on("click", function () {
        const lalamoveOrderId = $(this).data("orderId");
        viewLalamoveOrder(lalamoveOrderId, $(this));
    });

    $(document).on("click", "#lalamoveDownloadPod", function () {
        const url = $(this).data("pod-url");
        const lalamoveOrderId = $(this).data("lalamove-order-id");
        downloadLalamovePodImage(url, lalamoveOrderId);
    });

    function resetDownloadButton() {
        const $btn = $("#downloadPodBtn");
        $btn.prop("disabled", false).html("Download");
    }

    function showErrorModal(title, message, reason) {
        $("#errorModal .modal-title").text(title);
        $("#modal-error-message").text(message);
        $("#modal-error-reason").text(reason);
        const errorModal = new bootstrap.Modal($("#errorModal")[0]);
        errorModal.show();
    }
});
