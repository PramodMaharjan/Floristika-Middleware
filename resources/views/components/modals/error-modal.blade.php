<div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm rounded-3">
      <div class="modal-header border-0">
        <h5 class="modal-title text-danger mb-0">Shipment Error</h5>
        <span id="failedOrderLabel" class="text-muted fw-bold ms-2"></span>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body d-flex align-items-start pt-0 px-3 pb-4 ">
        <div class="me-3 flex-shrink-0">
          <span class="fs-2 text-danger">&#9888;</span>
        </div>
        <div>
          <p class="mb-1"><strong>Message:</strong> <span id="modal-error-message">Something went wrong.</span></p>
          <div id="modal-error-reason-container">
            <p class="mb-0"><strong>Reason:</strong> <span id="modal-error-reason">Please try again later.</span></p>
          </div>
        </div>
      </div>
      
    </div>
  </div>
</div>