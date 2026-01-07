<div class="modal fade" id="lalamoveModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">

      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Lalamove Quote</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <p class="mb-3 text-muted">Select a service type and configure options before fetching a quote.</p>

        <!-- Orders -->
        <div class="mb-3">
          <label class="form-label fw-semibold text-secondary">Orders</label>
          <div id="lalamove-order-no" class="d-flex flex-wrap gap-2"></div>
        </div>

        <!-- Service Type -->
        <div class="mb-3">
          <label class="form-label fw-semibold">Service Type</label>
          <select id="serviceType" class="form-select">
            <option value="">Select</option>
            <option value="Motorcycle">Motorcycle</option>
            <option value="Car">Car</option>
          </select>
        </div>

        <!-- Optimize Route Toggle -->
        <div class="d-flex align-items-center justify-content-between p-3 border rounded bg-light">
          <div>
            <span class="fw-semibold">Optimize Route</span>
            <p class="mb-0 text-muted small">Automatically optimize the route for multiple stops.</p>
          </div>
          <div class="form-check form-switch m-0">
            <input 
              class="form-check-input" 
              type="checkbox" 
              id="optimizeRouteToggle" 
              checked 
            >          
          </div>
        </div>

        <!-- Additional Services -->
        <div class="pt-3 d-none" id="additionalServicesSection">
          <div class="p-3 border rounded bg-light">
          <span class="fw-semibold">Additional Services</span>
            
            <!-- Motorcycle option -->
            <div class="d-flex align-items-center justify-content-between additional-service d-none" id="motorcycleService">
              <span class="mb-0 text-muted small">Thermal Bag</span>
              <div class="form-check form-switch m-0">
                <input class="form-check-input" type="checkbox" id="thermalBagToggle" checked>
              </div>
            </div>

            <!-- Car option -->
            <div class="d-flex align-items-center justify-content-between additional-service d-none" id="carService">
              <span class="mb-0 text-muted small">Door-to-Door (loading & unloading by driver)</span>
              <div class="form-check form-switch m-0">
                <input class="form-check-input" type="checkbox" id="doorToDoorToggle" checked>
              </div>
            </div>

          </div>
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn btn-info" id="getQuoteBtn" disabled>Get Quote</button>
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

<!-- Quotation Modal -->
<div class="modal fade" id="lalamoveQuoteModal" tabindex="-1" aria-labelledby="quotationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="quotationModalLabel">Quotation Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">

        <div class="mb-3">
          <span class="fw-bold">Quotation ID:</span> <span id="quotationId"></span><br>
          <span class="fw-bold">Service Type:</span> <span id="quotationServiceType"></span>
        </div>

        <div class="mb-4">
          <h6>Stops</h6>
          <ul class="list-group" id="stopsList">
          </ul>
        </div>

        <div class="mb-4">
          <h6>Price Breakdown</h6>
          <div class="card shadow-sm">
            <div class="card-body p-3">
              <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between">
                  <span>Base</span>
                  <span id="basePrice"></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                  <span>Extra Mileage</span>
                  <span id="extraMileage"></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                  <span>Total Before Optimization</span>
                  <span id="totalBeforeOptimization"></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                  <span>Total Exclude Priority Fee</span>
                  <span id="totalExcludePriorityFee"></span>
                </li>
                <li class="list-group-item d-flex justify-content-between fw-bold">
                  <span>Total</span>
                  <span id="totalPrice"></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                  <span>Currency</span>
                  <span id="currency"></span>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <div class="mb-0">
          <h6>Distance</h6>
          <p><span id="distance"></span> <span id="distanceUnit"></span></p>
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-primary" id="saveLalamoveBtn">Place Order</button>
      </div>

    </div>
  </div>
</div>

<div class="modal fade" id="lalamoveOrderModal">
    <div class="modal-dialog modal-md modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Lalamove Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="lalamoveDriverModal" tabindex="-1" aria-labelledby="lalamoveDriverModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="lalamoveDriverModalLabel">Driver Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
