<div class="modal fade" id="lineClearModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Lineclear</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <table class="table table-bordered align-middle">
          <thead>
            <tr class="text-center">
              <th>Order Number</th>
              <th>Premium / Freshbox</th>
              <th>Size</th>
            </tr>
          </thead>
          <tbody id="lineClearTableBody">
          </tbody>
        </table>

        <div>
          <label class="form-label fw-bold">Shipment Description</label>
          <textarea class="form-control" id="shipmentDescription" rows="3"></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-primary" id="saveLineclearBtn" disabled>Save</button>
      </div>
    </div>
  </div>
</div>