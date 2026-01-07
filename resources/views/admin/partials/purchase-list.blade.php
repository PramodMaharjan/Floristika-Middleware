<div class="flex-shrink-0 flex-grow-1 flex-lg-grow-0" style="min-width: 160px;">
    <button class="btn btn-warning w-100 purchase-list-btn" data-bs-toggle="modal" data-bs-target="#purchaseListModal">
        <i class="bi bi-card-checklist me-2"></i> Purchase List
  </button>
</div>
<div class="modal fade" id="purchaseListModal" tabindex="-1" aria-labelledby="purchaseListLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title" id="purchaseListLabel">Purchase List</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
        <div>
          <strong class="text-secondary me-2">Order No:</strong>
          <span id="purchase-order-no"></span>
        </div>
      </div>
      <div class="modal-body p-2">
        <div class="table-responsive" >
          <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
              <tr>
                <th class="border-0"></th>
                <th class="border-0">Product</th>
                <th class="border-0 ">SKU</th>
                <th class="border-0">Quantity</th>
              </tr>
            </thead>
            <tbody id="purchase-list-body">
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-sm btn-danger" id="export-purchase-pdf">
          <i class="bi bi-file-earmark-pdf-fill me-1"></i> Export to PDF
        </button>
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>