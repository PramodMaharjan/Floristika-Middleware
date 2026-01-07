$(document).ready(function () {
    const {
        setOrderField,
        createShipmentLineclear,
        createShipmentLalamove,
        createShipmentDetrack,
        getLalamoveQuote,
        productImages,
        getDetrackVehicles,
    } = window.routes || {};
    let selectedOrderIds = new Set();
    let selectedOrderNos = new Set();
    let ordersProducts = {};
    let ordersData = [];
    let filterOptionsLoaded = false;
    let firstDraw = true;

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

    function updateSelectAllCheckboxState() {
        const totalCheckboxes = $(".order-select-checkbox").length;
        const selectedCheckboxes = $(".order-select-checkbox:checked").length;
        $("#select-all-orders").prop(
            "checked",
            totalCheckboxes > 0 && totalCheckboxes === selectedCheckboxes
        );
    }

    $(document).on("change", ".order-select-checkbox", function () {
        const orderId = $(this).data("order-id");
        const orderNumber = $(this).data("order-number");
        if ($(this).prop("checked")) {
            selectedOrderIds.add(orderId);
            selectedOrderNos.add(orderNumber);
        } else {
            selectedOrderIds.delete(orderId);
            selectedOrderNos.delete(orderNumber);
        }
        updateSelectAllCheckboxState();
    });

    $(document).on("change", "#select-all-orders", function () {
        const isChecked = $(this).prop("checked");
        $(".order-select-checkbox").prop("checked", isChecked);

        $(".order-select-checkbox").each(function () {
            const orderId = $(this).data("order-id");
            const orderNumber = $(this).data("order-number");
            if (isChecked) {
                selectedOrderIds.add(orderId);
                selectedOrderNos.add(orderNumber);
            } else {
                selectedOrderIds.delete(orderId);
                selectedOrderNos.delete(orderNumber);
            }
        });
        updateSelectAllCheckboxState();
    });

    const initialStartDate = $("#min-date").val();
    const initialEndDate = $("#max-date").val();
    if (!initialStartDate && !initialEndDate) {
        const today = moment().format("YYYY-MM-DD");
        $("#date-range-select").val("today");
        $("#min-date").val(today);
        $("#max-date").val(today);
    }

    const columnFilters = {
        delivery_partner: "",
        subzone: "",
        zone: "",
        fulfillment_status: "",
        pl_no: "",
        mc_no: "",
        do_no: "",
    };
    const table = $("table.table-listing").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "/orders/list",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            data: function (d) {
                d.start_date = $("#min-date").val();
                d.end_date = $("#max-date").val();
                d.delivery_partner = columnFilters.delivery_partner;
                d.subzone = columnFilters.subzone;
                d.zone = columnFilters.zone;
                d.fulfillment_status = columnFilters.fulfillment_status;
                d.pl_no = columnFilters.pl_no;
                d.mc_no = columnFilters.mc_no;
                d.do_no = columnFilters.do_no;
                if (!filterOptionsLoaded) {
                    d.load_filters = true;
                }
            },
            dataSrc: function (json) {
                if (json.filters) {
                    updateFilterDropdowns(json.filters);
                    filterOptionsLoaded = true;
                }
                if (json.summary) {
                    updateSummaryCards(json.summary);
                }
                if (!Array.isArray(json.data)) {
                    console.warn("Invalid or missing data in JSON response.");
                    return [];
                }
                syncOrdersData(json.data);
                extractOrderProducts(json.data);
                return json.data;
            },
        },
        columns: [
            { data: 0 },
            {
                data: 1,
                render: function (data, type, row) {
                    const url = row?.DT_RowAttr?.["data-url"];
                    if (type === "display" && url) {
                        return `
                            ${data}
                            <a href="${url}" class="cell-overlay-link"></a>
                        `;
                    }
                    return data;
                },
            },
            { data: 2 },
            { data: 3 },
            {
                data: 4,
                className: "delivery-partner-column",
                render: function (data, type, row) {
                    if (data === "Self Collect") {
                        return "<strong>" + data + "</strong>";
                    }
                    return data;
                },
            },
            { data: 5 },
            { data: 6 },
            { data: 7 },
            { data: 8 },
            { data: 9 },
            { data: 10 },
            { data: 11 },
            { data: 12, className: "fulfillment_status-column" },
            { data: 13, className: "shipment-status-column" },
            { data: 14, className: "pl_no-column" },
            { data: 15, className: "mc_no-column" },
            { data: 16, className: "do_no-column" },
        ],
        paging: true,
        ordering: true,
        info: true,
        searching: true,
        scrollX: true,
        pageLength: 50,
        lengthMenu: [
            [10, 25, 50, 100, 200, 500],
            [10, 25, 50, 100, 200, 500],
        ],
        language: {
            search: "",
            searchPlaceholder: "Search",
            paginate: {
                next: '<i class="fa fa-angle-right fa-md custom-arrow"></i>',
                previous: '<i class="fa fa-angle-left fa-md custom-arrow"></i>',
            },
        },
        columnDefs: [{ orderable: false, targets: 0 }],
        order: [[1, "desc"]],
        preDrawCallback: function () {
            if (!firstDraw) {
                $(".table-overlay-loader").removeClass("d-none");
            }
        },
        drawCallback: function () {
            if (firstDraw) firstDraw = false;
            $(".table-overlay-loader").addClass("d-none");
            $(".order-select-checkbox").each(function () {
                const orderId = $(this).data("order-id");
                if (selectedOrderIds.has(orderId)) {
                    $(this).prop("checked", true);
                } else {
                    $(this).prop("checked", false);
                }
            });
            updateSelectAllCheckboxState();
        },
        initComplete: function () {
            let api = this.api();
            api.columns([4, 9, 10, 12, 14, 15, 16]).every(function () {
                let column = this;
                let header = $(column.header());
                let title = header.text();

                header.empty().append(`
                        <div class="filter-header">
                            <span>${title}</span>
                            <i class="fa fa-filter filter-icon"></i>
                            <div class="filter-dropdown">
                                <select class="column-filter">
                                    <option value="">All</option>
                                </select>
                            </div>
                        </div>
                    `);

                let dropdown = header.find(".filter-dropdown");
                let select = dropdown.find("select");

                column
                    .data()
                    .unique()
                    .sort()
                    .each(function (d) {
                        if (d != null && d.toString().trim() !== "") {
                            select.append(`<option value="${d}">${d}</option>`);
                        }
                    });

                header.find(".filter-icon").on("click", function (e) {
                    e.stopPropagation();
                    let currentDropdown = $(this).siblings(".filter-dropdown");
                    $(".filter-dropdown").not(currentDropdown).hide();
                    currentDropdown.toggle();
                });

                dropdown.on("click", function (e) {
                    e.stopPropagation();
                });

                select.on("change", function (e) {
                    e.stopPropagation();
                    let val = $(this).val() || "";
                    let colIndex = column.index();

                    if (colIndex === 4) {
                        columnFilters.delivery_partner = val;
                    } else if (colIndex === 9) {
                        columnFilters.subzone = val;
                    } else if (colIndex === 10) {
                        columnFilters.zone = val;
                    } else if (colIndex === 12) {
                        columnFilters.fulfillment_status = val;
                    } else if (colIndex === 14) {
                        columnFilters.pl_no = val;
                    } else if (colIndex === 15) {
                        columnFilters.mc_no = val;
                    } else if (colIndex === 16) {
                        columnFilters.do_no = val;
                    }

                    table.draw();
                    dropdown.hide();
                    let icon = header.find(".filter-icon");
                    if (val) {
                        icon.addClass("active-filter");
                    } else {
                        icon.removeClass("active-filter");
                    }
                });

                $("#order-table").fadeIn(200);
                $("#table-loader").hide();
            });

            const dataTableWrapper = $(api.table().container());
            const searchBox = dataTableWrapper.find(".dataTables_filter");
            const exportBtn = $(
                '<button class="btn btn-sm btn btn-outline-secondary me-2" id="export-orders"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Export</button>'
            );
            searchBox.prepend(exportBtn);

            $(document).on("click", function () {
                $(".filter-dropdown").hide();
            });
            $("th:eq(0)").removeClass("sorting sorting_asc sorting_desc");
        },
    });

    $("table.table-listing tbody").on("click", "tr", function (e) {
        const target = $(e.target);
        const td = target.closest("td");
        const tdIndex = td.index();

        if (tdIndex !== 1) {
            return;
        }

        const url = $(this).data("url");
        if (url) {
            window.location.href = url;
        }
    });

    $(document).on("click", ".order-action", function (e) {
        e.preventDefault();

        const status = $(this).data("status");
        if (status === "Returned") {
            return;
        }
        const selectedIds = Array.from(selectedOrderIds);

        if (!selectedIds.length) {
            showToast("Please select at least one order.", "warning");
            return;
        }

        updateOrderField(setOrderField, selectedIds, "fulfillment_status", {
            value: status,
        });

        selectedIds.forEach((id) => {
            const row = $(`#order-table-body tr`).filter(function () {
                return (
                    $(this).find(".order-select-checkbox").data("order-id") ===
                    id
                );
            });

            if (status === "Fulfilled") {
                row.removeClass("default-text unfulfilled");
                row.addClass("fulfilled");
            } else {
                row.addClass("default-text");
                row.removeClass("unfulfilled fulfilled");
            }
        });
    });

    $(".bulk-print-pl").on("click", function () {
        handleBulkPrint("pl_no");
    });

    $(".bulk-print-mc").on("click", function () {
        const selectedIds = Array.from(selectedOrderIds);

        if (!selectedIds.length) {
            showToast("Please select at least one order.", "warning");
            return;
        }
        const html = generatePrintCardsHtml(selectedIds);
        if (!html) return;
        handleBulkPrint("mc_no");
        printCards(html);
    });

    $(".bulk-print-do").on("click", function () {
        handleBulkPrint("do_no");
    });

    function getBase64Image(img, scale = 2) {
        return new Promise((resolve, reject) => {
            const canvas = document.createElement("canvas");
            canvas.width = img.width * scale;
            canvas.height = img.height * scale;
            const ctx = canvas.getContext("2d");
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            resolve(canvas.toDataURL("image/png"));
        });
    }

    $("#export-purchase-pdf").on("click", async function () {
        const $rows = $("#purchase-list-body tr");
        const formattedOrderNos = $("#purchase-order-no")
            .text()
            .match(/#\d+/g)
            .join(", ");
        const body = [];

        body.push([
            { text: "", bold: true },
            { text: "Product", bold: true },
            { text: "SKU", bold: true },
            { text: "Quantity", bold: true },
        ]);

        for (let i = 0; i < $rows.length; i++) {
            const $cells = $($rows[i]).find("td");
            const rowData = [];

            const $img = $($cells[0]).find("img");
            if ($img.length) {
                const base64 = await getBase64Image($img[0]);
                rowData.push({
                    stack: [{ image: base64, width: 45, margin: [0, 5, 0, 5] }],
                    alignment: "center",
                    margin: [0, 0, 0, 0],
                });
            } else {
                rowData.push("");
            }

            for (let j = 1; j < $cells.length; j++) {
                rowData.push($($cells[j]).text().trim());
            }

            body.push(rowData);
        }

        const docDefinition = {
            pageMargins: [72, 72, 72, 72],
            content: [
                { text: "Purchase List", style: "header" },
                {
                    text: `Order No: ${formattedOrderNos}`,
                    style: "subheader",
                    margin: [0, 0, 0, 10],
                },
                {
                    table: {
                        headerRows: 1,
                        widths: [50, "*", 90, 50],
                        body: body,
                        dontBreakRows: true,
                    },
                },
            ],
            styles: {
                header: { fontSize: 16, bold: true, marginBottom: 10 },
            },
            defaultStyle: { fontSize: 12 },
        };

        pdfMake.createPdf(docDefinition).download("purchase-list.pdf");
    });

    $(document).on("click", "#export-orders", function () {
        const table = $("table.table-listing").DataTable();
        const headers = [
            "Order Number",
            "TikTok Order Number",
            "Source",
            "Delivery Partner",
            "Delivery Date",
            "Delivery Time",
            "Products",
            "City",
            "SubZone",
            "Zone",
            "Postal Code",
            "Status",
            "Shipment Status",
            "PL",
            "MC",
            "DO",
        ];

        const csvRows = [headers.join(",")];

        table.rows({ search: "applied" }).every(function () {
            const rowNode = this.node();
            const $cells = $("td", rowNode);
            const selectedCells = $cells.slice(1, 16);
            const rowValues = selectedCells
                .map(function () {
                    const text = $(this).text().trim();
                    return `"${text.replace(/"/g, '""')}"`;
                })
                .get();

            if (rowValues.some((val) => val !== '""')) {
                csvRows.push(rowValues.join(","));
            }
        });

        const csvContent = csvRows.join("\n");
        const blob = new Blob([csvContent], {
            type: "text/csv;charset=utf-8;",
        });
        const link = document.createElement("a");
        const filename = "orders-report.csv";

        const url = URL.createObjectURL(blob);
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    });

    $(".purchase-list-btn").on("click", function () {
        const selectedOrders = Array.from(selectedOrderIds);
        const selectedOrderNumbers = Array.from(selectedOrderNos);
        const tbody = $("#purchase-list-body").empty();
        $("#export-purchase-pdf").prop("disabled", true);
        if (selectedOrders.length) {
            const badgeHtml = selectedOrderNumbers
                .map(
                    (no) =>
                        `<span class="badge bg-secondary me-1">#${no}</span>`
                )
                .join("");
            $("#purchase-order-no").html(badgeHtml);
        }
        if (!selectedOrders.length) {
            tbody.html(`
            <tr>
                <td colspan="4" class="text-center text-muted py-3">
                    Please select at least one order
                </td>
            </tr>
        `);
            return;
        }
        const aggregatedProducts = {};
        const requiredProductIds = new Set();
        const variants = [];

        selectedOrders.forEach((orderId) => {
            (ordersProducts[orderId] || []).forEach((product) => {
                if (!product || !product.product_id) return;

                requiredProductIds.add(product.product_id);

                if (product.variant_id) {
                    variants.push({
                        product_id: product.product_id,
                        variant_id: product.variant_id,
                    });
                }

                const key = `${product.name}_${product.sku}`;
                if (aggregatedProducts[key]) {
                    aggregatedProducts[key].quantity += product.quantity;
                } else {
                    aggregatedProducts[key] = { ...product };
                }
            });
        });

        const idsToFetch = Array.from(requiredProductIds);
        tbody.html(`
            <tr>
                <td colspan="4" class="text-center py-4">
                    <div class="spinner-border text-secondary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </td>
            </tr>
        `);

        $.ajax({
            url: productImages,
            method: "POST",
            data: { product_ids: idsToFetch, variants, _token: csrfToken },
            success: function (response) {
                const imageMap = response?.images || {};
                Object.values(aggregatedProducts).forEach((product) => {
                    if (product.product_id && imageMap[product.product_id])
                        product.image = imageMap[product.product_id];
                });
                renderPurchaseListTable(aggregatedProducts);
                $("#export-purchase-pdf").prop("disabled", false);
            },
            error: function (err) {
                tbody.html(`
                <tr>
                    <td colspan="4" class="text-center text-danger py-4">
                        Failed to load products.
                    </td>
                </tr>`);
            },
        });
    });

    function renderPurchaseListTable(products) {
        const tbody = $("#purchase-list-body").empty();
        Object.values(products).forEach((product) => {
            tbody.append(`
            <tr class="no-page-break">
                <td><img src="${product.image || ""}" alt="${
                product.name
            }" class="product-image" crossorigin="anonymous"></td>
                <td >${product.name}</td>
                <td>${product.sku}</td>
                <td class="text-center">${product.quantity}</td>
            </tr>
        `);
        });
    }

    function generatePrintCardsHtml(ids) {
        let html = "";
        ids.forEach((id) => {
            const order = ordersData.find((o) => Number(o.order_id) === id);
            if (!order?.raw_json) return;

            const data =
                typeof order.raw_json === "string"
                    ? JSON.parse(order.raw_json)
                    : order.raw_json;
            const note = (data.note || "").trim();

            const name = data.shipping_address?.name || "";

            if (!note) return;

            html += `
            <div class="page-break">
                <div class="card-content-wrapper">
                    <div class="top-half"></div>
                    <div class="bottom-half">
                        <div class="card-content">
                            <p>${note}</p>
                            <p>${name}</p>
                        </div>
                    </div>
                </div>
            </div>`;
        });
        return html;
    }

    function printCards(content) {
        const printArea = document.createElement("div");
        printArea.id = "print-area";
        printArea.innerHTML = `${content}`;
        document.body.appendChild(printArea);

        const printStyles = `
            @page { margin: 0; }
             html, body, #print-area {
                height: 100%;
                margin: 0;
                font-size: 15px;
            }
            .page-break {
                height: 100% !important; 
                box-sizing: border-box;
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
            .page-break { page-break-after: always; }
        `;

        printJS({
            printable: "print-area",
            type: "html",
            style: printStyles,
            targetStyles: ["*"],
            scanStyles: false,
        });

        setTimeout(() => printArea.remove(), 1000);
    }

    function handleBulkPrint(field) {
        const selectedIds = Array.from(selectedOrderIds);

        if (!selectedIds.length) {
            showToast("Please select at least one order.", "warning");
            return;
        }
        const printableFields = ["pl_no", "do_no"];
        if (printableFields.includes(field)) {
            const baseUrl =
                "https://admin.shopify.com/store/floristika-malaysia/apps/order-printer-emailer/print/orders";
            const queryString = $.param({ ids: selectedIds });
            const url = `${baseUrl}?${queryString}`;
            window.open(url, "_blank");
        }

        updateOrderField(setOrderField, selectedIds, field);
    }

    function updateOrderField(url, orderIds, field, options = {}) {
        const { value } = options;

        if (!orderIds || !orderIds.length) return;

        let values = {};

        orderIds.forEach((id) => {
            const row = $(`#order-table-body tr`).filter(function () {
                return (
                    $(this).find(".order-select-checkbox").data("order-id") ===
                    id
                );
            });

            let currentValue;
            if (value !== undefined) {
                currentValue = value;
            } else {
                const colSelector = `td.${field}-column`;
                currentValue = parseInt(row.find(colSelector).text()) || 0;
                currentValue += 1;
            }

            const colSelector =
                value !== undefined
                    ? `td.${field}-column`
                    : `td.${field}-column`;
            row.find(colSelector).text(currentValue);

            values[id] = currentValue;
        });
        $.ajax({
            url: url,
            type: "POST",
            data: {
                ids: orderIds,
                field: field,
                values: values,
                _token: csrfToken,
            },
            success: function (res) {
                console.log("🚀 ~ updateOrderField ~ res:", res);
            },
            error: function (err) {
                console.log("🚀 ~ updateOrderField ~ err:", err);
            },
        });
    }

    function updateFilterDropdowns(filters) {
        const columnsToUpdate = {
            4: "delivery_partner",
            9: "subzone",
            10: "zone",
            12: "fulfillment_status",
            14: "pl_no",
            15: "mc_no",
            16: "do_no",
        };

        for (const [colIndex, key] of Object.entries(columnsToUpdate)) {
            const colIndexNum = parseInt(colIndex);
            const column = table.column(colIndexNum);
            const header = $(column.header());
            const select = header.find("select.column-filter");

            if (select.length && filters[key] && Array.isArray(filters[key])) {
                const currentValue = select.val() || "";
                select.empty().append('<option value="">All</option>');
                filters[key].forEach((val) => {
                    if (val != null && val.toString().trim() !== "") {
                        const valStr = val.toString();
                        const selected =
                            valStr === currentValue ? " selected" : "";
                        select.append(
                            `<option value="${valStr}"${selected}>${valStr}</option>`
                        );
                    }
                });
                if (currentValue) {
                    select.val(currentValue);
                }
            }
        }
    }

    function updateSummaryCards(summary) {
        const numberFormatter = new Intl.NumberFormat("en-MY");
        const toNumber = (value) => Number(value) || 0;

        $("#total-orders").text(
            numberFormatter.format(summary.total_orders || 0)
        );
        $("#orders-delivered").text(
            numberFormatter.format(summary.fulfilled_count || 0)
        );
        $("#pending-delivered").text(
            numberFormatter.format(summary.unfulfilled_count || 0)
        );

        $("#current-month-sales").text(
            toNumber(summary.current_month_sales).toLocaleString("en-MY", {
                style: "currency",
                currency: "MYR",
            })
        );
        $("#total-sales").text(
            toNumber(summary.total_sales).toLocaleString("en-MY", {
                style: "currency",
                currency: "MYR",
            })
        );
        $("#pending-sales").text(
            toNumber(summary.unfulfilled_sales).toLocaleString("en-MY", {
                style: "currency",
                currency: "MYR",
            })
        );
        $("#delivered-sales").text(
            toNumber(summary.fulfilled_sales).toLocaleString("en-MY", {
                style: "currency",
                currency: "MYR",
            })
        );
    }

    function syncOrdersData(data) {
        data.forEach((row) => {
            const orderNumber = row[1];
            const existingIndex = ordersData.findIndex(
                (o) => o.order_number === orderNumber
            );

            if (existingIndex === -1) {
                ordersData.push(row);
            } else {
                ordersData[existingIndex] = row;
            }
        });
    }

    function extractOrderProducts(data) {
        data.forEach((order) => {
            if (!order.raw_json) return;

            try {
                const parsed = JSON.parse(order.raw_json);
                const lineItems = Array.isArray(parsed.line_items)
                    ? parsed.line_items
                    : [];

                ordersProducts[order.order_id] = lineItems.map((item) => {
                    return {
                        name: item?.name ?? null,
                        sku: item?.sku ?? null,
                        quantity: item?.quantity ?? 0,
                        product_id: item?.product_id ?? null,
                        variant_id: item?.variant_id ?? null,
                    };
                });
            } catch (error) {
                console.warn(`Invalid JSON for order ${order.order_id}`, error);
            }
        });
    }

    const dateColumnIndex = $("th.delivery-date").index();
    const fp = flatpickr("#custom-date-range", {
        mode: "range",
        dateFormat: "Y-m-d",
        onChange: function (selectedDates, dateStr, instance) {
            if (selectedDates.length === 2) {
                const startDate = moment(selectedDates[0]).format("YYYY-MM-DD");
                const endDate = moment(selectedDates[1]).format("YYYY-MM-DD");
                applyDateFilter(startDate, endDate);
            } else if (selectedDates.length === 1) {
                const date = moment(selectedDates[0]).format("YYYY-MM-DD");
                applyDateFilter(date, date);
            }
        },
    });

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        const minDate = $("#min-date").val();
        const maxDate = $("#max-date").val();

        if (!minDate && !maxDate) {
            return true;
        }

        const rowDate = moment(data[dateColumnIndex], "DD-MM-YYYY");
        const startDateMoment = moment(minDate, "YYYY-MM-DD");
        const endDateMoment = moment(maxDate, "YYYY-MM-DD");

        if (rowDate.isBetween(startDateMoment, endDateMoment, "days", "[]")) {
            return true;
        }

        return false;
    });

    $("#date-range-select").on("change", function () {
        const selectedRange = $(this).val();
        let startDate = null;
        let endDate = null;

        $("#custom-date-fields").hide();

        switch (selectedRange) {
            case "all":
                break;
            case "today":
                startDate = moment().format("YYYY-MM-DD");
                endDate = moment().format("YYYY-MM-DD");
                break;
            case "yesterday":
                startDate = moment().subtract(1, "days").format("YYYY-MM-DD");
                endDate = moment().subtract(1, "days").format("YYYY-MM-DD");
                break;
            case "last7days":
                startDate = moment().subtract(6, "days").format("YYYY-MM-DD");
                endDate = moment().format("YYYY-MM-DD");
                break;
            case "last30days":
                startDate = moment().subtract(29, "days").format("YYYY-MM-DD");
                endDate = moment().format("YYYY-MM-DD");
                break;
            case "thismonth":
                startDate = moment().startOf("month").format("YYYY-MM-DD");
                endDate = moment().endOf("month").format("YYYY-MM-DD");
                break;
            case "lastmonth":
                startDate = moment()
                    .subtract(1, "month")
                    .startOf("month")
                    .format("YYYY-MM-DD");
                endDate = moment()
                    .subtract(1, "month")
                    .endOf("month")
                    .format("YYYY-MM-DD");
                break;
            case "custom":
                $("#custom-date-fields").show();
                return;
        }
        applyDateFilter(startDate, endDate);
    });

    function applyDateFilter(startDate, endDate) {
        $("#min-date").val(startDate);
        $("#max-date").val(endDate);
        filterOptionsLoaded = false;
        table.draw();
    }

    $("#date-range-select").trigger("change");

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

    $(".line-clear").on("click", function () {
        const selectedOrderNumbers = Array.from(selectedOrderNos);
        const selectedIds = Array.from(selectedOrderIds);
        if (!selectedOrderNumbers.length) {
            showToast("Please select at least one order.", "warning");
            return;
        }

        const orderMap = {};
        selectedOrderNumbers.forEach((no, index) => {
            orderMap[no] = selectedIds[index];
        });

        const tbody = $("#lineClearTableBody");
        tbody.empty();

        selectedOrderNumbers.forEach((orderNumber) => {
            const orderId = orderMap[orderNumber];
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
        });
        lineClearModal.show();
    });

    $(".lalamove").on("click", function () {
        const selectedOrderNumbers = Array.from(selectedOrderNos);

        if (!selectedOrderNumbers.length) {
            showToast("Please select at least one order.", "warning");
            return;
        }

        const container = $("#lalamove-order-no");
        container.html("");

        selectedOrderNumbers.forEach((orderNumber) => {
            container.append(`
                <span class="badge bg-secondary me-1">#${orderNumber}</span>
            `);
        });
        lalamoveModal.show();
    });

    let shipmentDescriptionText = "";
    let serviceType = "";
    let specialRequest = [];
    let selectedQuotation = null;

    function collectSpecialRequest() {
        if (serviceType === "Motorcycle") {
            return $("#thermalBagToggle").is(":checked")
                ? ["THERMAL_BAG_1"]
                : [];
        }

        if (serviceType === "Car") {
            return $("#doorToDoorToggle").is(":checked")
                ? ["DOOR_TO_DOOR_1DRIVER"]
                : [];
        }

        return [];
    }

    $(document).on("change", ".sizeOption, .dimensionOption", function () {
        validateForm({
            requiredSelectors: [
                ".sizeOption",
                ".dimensionOption",
                "#shipmentDescription",
            ],
            buttonSelector: "#saveLineclearBtn",
        });
    });

    $("#shipmentDescription").on("input", function () {
        shipmentDescriptionText = $(this).val().trim();
        validateForm({
            requiredSelectors: [
                ".sizeOption",
                ".dimensionOption",
                "#shipmentDescription",
            ],
            buttonSelector: "#saveLineclearBtn",
        });
    });

    $(document).on("input", ".notesInput", function () {
        validateForm({
            requiredSelectors: [".notesInput"],
            buttonSelector: "#saveDetrackBtn",
        });
    });

    $(document).on("change", "#serviceType", function () {
        const selectedLalamoveService = $(this).val();
        serviceType = selectedLalamoveService;
        $(".additional-service").addClass("d-none");

        if (selectedLalamoveService === "Motorcycle") {
            $("#additionalServicesSection").removeClass("d-none");
            $("#motorcycleService").removeClass("d-none");
        } else if (selectedLalamoveService === "Car") {
            $("#additionalServicesSection").removeClass("d-none");
            $("#carService").removeClass("d-none");
        } else {
            $("#additionalServicesSection").addClass("d-none");
        }

        validateForm({
            requiredSelectors: ["#serviceType"],
            buttonSelector: "#getQuoteBtn",
        });
    });

    $("#saveLineclearBtn").on("click", function () {
        sendToLineclear();
        lineClearModal.hide();
    });

    $("#saveLalamoveBtn").on("click", function () {
        placeOrder();
        lalamoveQuoteModal.hide();
    });

    $("#saveDetrackBtn").on("click", function () {
        placeDetrackOrders();
        detrackModal.hide();
    });

    $("#getQuoteBtn").on("click", async function () {
        const singleOrderId = $("#deliveryPartnerSelect").data("order-id");
        lalamoveModal.hide();
        await getQuotation(singleOrderId);
    });

    async function sendToLineclear() {
        const selectedIds = Array.from(selectedOrderIds);
        const ordersData = $("#lineClearTableBody tr")
            .toArray()
            .map((row) => {
                const $row = $(row);
                return {
                    orderId: $row.data("order-id"),
                    size: $row.find(".sizeOption").val(),
                    dimension: $row.find(".dimensionOption").val(),
                };
            });

        $("#overlay-spinner .spinner-card div.fw-bold").text(
            `Sending your orders via Line Clear.`
        );
        $("#overlay-spinner").removeClass("d-none");
        try {
            const res = await $.ajax({
                url: createShipmentLineclear,
                type: "POST",
                contentType: "application/json",
                data: JSON.stringify({
                    orders: ordersData,
                    description: shipmentDescriptionText,
                }),
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                },
            });
            $("#overlay-spinner").addClass("d-none");
            if (res.Status === false || res.success === false) {
                const reason = parseError(res.Reason);
                showErrorModal(res.Message, reason);
            } else {
                showToast("Shipment created successfully!", "success");
                const waybillNo = res?.ResponseData?.[0]?.WayBill?.[0];
                if (waybillNo) {
                    $(".download-waybill-col").removeClass("d-none");
                    $("#downloadWaybillBtn").attr("data-waybill", waybillNo);
                    $(".view-pod-col").removeClass("d-none");
                    $("#downloadPodBtn").attr("data-waybill", waybillNo);
                }

                const deliveryPartner = "Line Clear";
                const lineClearShipmentStatus = "Awaiting Shipment Handover";
                selectedIds.forEach((id) => {
                    const row = $(`#order-table-body tr`).filter(function () {
                        return (
                            $(this)
                                .find(".order-select-checkbox")
                                .data("order-id") === id
                        );
                    });
                    row.find("td.delivery-partner-column").text(
                        deliveryPartner
                    );
                    row.find("td.shipment-status-column").text(
                        lineClearShipmentStatus
                    );
                });
            }
        } catch (err) {
            $("#overlay-spinner").addClass("d-none");
            console.error("AJAX error:", err);
            showErrorModal("Network Error", "Please try again later.");
        }
    }

    async function getQuotation(singleOrderId = null) {
        try {
            const ids = singleOrderId
                ? [singleOrderId]
                : Array.from(selectedOrderIds);
            const optimizeRoute = $("#optimizeRouteToggle").is(":checked");
            specialRequest = collectSpecialRequest();
            const orderPayload = {
                serviceType,
                optimizeRoute,
                specialRequest,
                orders: ids.map((id) => ({ orderId: id.toString() })),
            };

            $("#overlay-spinner")
                .removeClass("d-none")
                .find(".spinner-card div.fw-bold")
                .text("Fetching Lalamove quotes...");

            const res = await $.ajax({
                url: getLalamoveQuote,
                type: "POST",
                data: { ...orderPayload, _token: csrfToken },
            });

            $("#overlay-spinner").addClass("d-none");

            const modalFields = [
                "#quotationId",
                "#quotationServiceType",
                "#stopsList",
                "#basePrice",
                "#extraMileage",
                "#totalBeforeOptimization",
                "#totalExcludePriorityFee",
                "#totalPrice",
                "#currency",
                "#distance",
                "#distanceUnit",
            ];
            modalFields.forEach((selector) => $(selector).text(""));
            $("#stopsList").empty();

            const quotation = res;

            $("#quotationId").text(quotation.quotationId);
            $("#quotationServiceType").text(quotation.serviceType);
            if (Array.isArray(quotation.stops) && quotation.stops.length) {
                quotation.stops.forEach((stop, idx) => {
                    const icon =
                        '<i class="bi bi-geo-alt-fill text-success"></i>';
                    $("#stopsList").append(
                        `<li class="list-group-item">${icon} ${stop.address}</li>`
                    );
                });
            }

            const price = quotation.price ?? {};
            $("#basePrice").text(price.base ?? "-");
            $("#extraMileage").text(price.extraMileage ?? "-");
            $("#totalBeforeOptimization").text(
                price.totalBeforeOptimization ?? "-"
            );
            $("#totalExcludePriorityFee").text(
                price.totalExcludePriorityFee ?? "-"
            );
            $("#totalPrice").text(price.total ?? "-");
            $("#currency").text(price.currency ?? "-");

            if (quotation.distance?.value) {
                const distanceKm = (
                    parseFloat(quotation.distance.value) / 1000
                ).toFixed(2);
                $("#distance").text(distanceKm);
                $("#distanceUnit").text("km");
            }

            selectedQuotation = {
                orderedOrderIds: quotation.orderedOrderIds,
                quotationId: quotation.quotationId,
                serviceType: quotation.serviceType,
                stops: quotation.stops,
            };

            $("#lalamoveQuoteModal").modal("show");
        } catch (err) {
            $("#overlay-spinner").addClass("d-none");
            let message = "Failed to fetch Lalamove quotes.";

            if (err.responseJSON) {
                const errors = err.responseJSON.errors;
                if (Array.isArray(errors)) {
                    message = errors.map((e) => e.message).join(", ");
                } else if (err.responseJSON.message) {
                    message = err.responseJSON.message;
                }
            }

            showErrorModal("Quotation Error", message);
        }
    }

    async function placeOrder() {
        const selectedIds = Array.from(selectedOrderIds);
        const singleOrderId = $("#deliveryPartnerSelect").data("order-id");

        if (!selectedQuotation) return;
        if (singleOrderId) {
            selectedQuotation.orderedOrderIds = [singleOrderId];
        }

        $("#overlay-spinner")
            .removeClass("d-none")
            .find(".spinner-card div.fw-bold")
            .text("Placing Lalamove orders...");

        try {
            const res = await $.ajax({
                url: createShipmentLalamove,
                type: "POST",
                data: { orders: selectedQuotation, _token: csrfToken },
            });

            $("#overlay-spinner").addClass("d-none");
            handleLalamoveResponse(res, selectedIds);
            $("#lalamoveQuoteModal").modal("hide");
        } catch (err) {
            $("#overlay-spinner").addClass("d-none");
            showErrorModal("Network Error", "Failed to place Lalamove orders.");
        }
    }

    function handleLalamoveResponse(res, selectedIds = []) {
        const failedOrders = res.results.filter((r) => r.success === false);
        if (failedOrders.length > 0) {
            const failedOrderNumbers = failedOrders
                .map((order) =>
                    order.order_number ? `#${order.order_number}` : ""
                )
                .join(", ");

            const errorMessages = failedOrders
                .map((order) =>
                    Array.isArray(order.errors)
                        ? order.errors
                              .map((e) => `${e.id}: ${e.message}`)
                              .join("<br>")
                        : order.message
                )
                .join("<br><br>");

            $("#failedOrderLabel").html(failedOrderNumbers);
            showErrorModal(
                "Failed to create Lalamove shipment.",
                errorMessages
            );
        } else {
            showToast("Shipment created successfully!", "success");
            const deliveryPartner = "Lalamove";
            const shipmentStatus = "Assigning Driver";
            selectedIds.forEach((id) => {
                const row = $(`#order-table-body tr`).filter(function () {
                    return (
                        $(this)
                            .find(".order-select-checkbox")
                            .data("order-id") === id
                    );
                });

                row.find("td.delivery-partner-column").text(deliveryPartner);
                row.find("td.shipment-status-column").text(shipmentStatus);
            });
        }
    }

    function handleDetrackResponse(res, selectedIds = []) {
        const failedOrders = res.results.filter((r) => r.success === false);
        if (failedOrders.length > 0) {
            const failedOrderNumbers = failedOrders
                .map((order) =>
                    order.order_number ? `#${order.order_number}` : ""
                )
                .join(", ");
            const errorMessages = failedOrders
                .map((order) => order.error)
                .join(", ");
            $("#failedOrderLabel").html(failedOrderNumbers);
            showErrorModal("Failed to create Detrack job.", errorMessages);
        } else {
            showToast("Shipment created successfully!", "success");
            $(".view-detrack-col").removeClass("d-none");
            const deliveryPartner = "Detrack";
            const shipmentStatus = "Dispatched";
            selectedIds.forEach((id) => {
                const row = $(`#order-table-body tr`).filter(function () {
                    return (
                        $(this)
                            .find(".order-select-checkbox")
                            .data("order-id") === id
                    );
                });
                row.find("td.delivery-partner-column").text(deliveryPartner);
                row.find("td.shipment-status-column").text(shipmentStatus);
            });
        }
    }

    $(".detrack").on("click", async function () {
        const selectedOrderNumbers = Array.from(selectedOrderNos);
        const selectedIds = Array.from(selectedOrderIds);

        if (!selectedOrderNumbers.length) {
            showToast("Please select at least one order.", "warning");
            return;
        }

        const orderMap = {};
        selectedOrderNumbers.forEach(
            (no, index) => (orderMap[no] = selectedIds[index])
        );

        const tbody = $("#detrackTableBody");
        tbody.empty();

        let rowsHtml = "";
        selectedOrderNumbers.forEach((orderNumber) => {
            const orderId = orderMap[orderNumber];
            rowsHtml += `
            <tr data-order-id="${orderId}">
                <td class="text-center orderNumber">#${orderNumber}</td>
                <td><textarea class="form-control notesInput" rows="2" placeholder="Enter notes"></textarea></td>
            </tr>
        `;
        });
        tbody.html(rowsHtml);
        detrackModal.show();
    });

    async function placeDetrackOrders() {
        const selectedIds = Array.from(selectedOrderIds);
        const ordersData = $("#detrackTableBody tr")
            .toArray()
            .map((row) => {
                const $row = $(row);
                return {
                    orderId: $row.data("order-id"),
                    note: $row.find(".notesInput").val().trim(),
                };
            });
        $("#overlay-spinner")
            .removeClass("d-none")
            .find(".spinner-card div.fw-bold")
            .text("Placing Detrack orders...");

        try {
            const res = await $.ajax({
                url: createShipmentDetrack,
                type: "POST",
                contentType: "application/json",
                data: JSON.stringify({
                    _token: csrfToken,
                    orders: ordersData,
                }),
            });
            $("#overlay-spinner").addClass("d-none");
            handleDetrackResponse(res, selectedIds);
        } catch (err) {
            $("#overlay-spinner").addClass("d-none");
            showErrorModal("Network Error", "Failed to create Detrack job.");
        }
    }

    function validateForm({ requiredSelectors = [], buttonSelector }) {
        let isValid = true;

        requiredSelectors.forEach((selector) => {
            $(selector).each(function () {
                const value = $(this).val();
                if (
                    !value ||
                    (typeof value === "string" && value.trim() === "")
                ) {
                    isValid = false;
                }
            });
        });

        $(buttonSelector).prop("disabled", !isValid);
    }

    function parseError(reason) {
        try {
            const errorsArray = JSON.parse(reason);
            if (Array.isArray(errorsArray) && errorsArray.length) {
                return errorsArray.map((err) => err.errorMessage).join("<br>");
            }
            return reason;
        } catch (e) {
            return reason;
        }
    }

    function showErrorModal(title, reason) {
        $("#modal-error-message").text(title);
        $("#modal-error-reason").html(reason);
        const errorModal = new bootstrap.Modal($("#errorModal")[0]);
        errorModal.show();
    }

    function showToast(message, type = "info") {
        const toastId = `toast-${Date.now()}`;
        let bgClass = "bg-primary text-white";
        switch (type) {
            case "success":
                bgClass = "bg-success text-white";
                break;
            case "error":
                bgClass = "bg-danger text-white";
                break;
            case "warning":
                bgClass = "bg-warning";
                break;
        }
        const $toast = $(`
            <div id="${toastId}" class="toast align-items-center border-0 shadow-lg mb-2" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-body fw-normal ${bgClass}">
                    ${message}
                </div>
            </div>
        `);
        $("#toast-container").append($toast);
        $toast.addClass("toast-slide-in");
        const toast = new bootstrap.Toast($toast[0], {
            autohide: true,
            delay: 1500,
        });
        toast.show();
    }
});
