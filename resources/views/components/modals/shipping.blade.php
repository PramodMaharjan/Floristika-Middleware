<div class="modal fade" id="editShippingModal" tabindex="-1" aria-labelledby="editShippingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editShippingModalLabel">Edit Shipping Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editShippingForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="shippingCountry" class="form-label">Country/Region</label>
                        <select class="form-select" id="shippingCountry" name="country">
                            <option value="Malaysia" selected>Malaysia</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="shippingFirstName" class="form-label">First name</label>
                            <input type="text" class="form-control" id="shippingFirstName" name="first_name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="shippingLastName" class="form-label">Last name</label>
                            <input type="text" class="form-control" id="shippingLastName" name="last_name">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="shippingCompany" class="form-label">Company</label>
                        <input type="text" class="form-control" id="shippingCompany" name="company">
                    </div>
                    <div class="mb-3">
                        <label for="shippingAddress1" class="form-label">Address</label>
                        <input type="text" class="form-control" id="shippingAddress1" name="address1">
                    </div>
                    <div class="mb-3">
                        <label for="shippingAddress2" class="form-label">Apartment, suite, etc.</label>
                        <input type="text" class="form-control" id="shippingAddress2" name="address2">
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="shippingPostcode" class="form-label">Postcode</label>
                            <input type="text" class="form-control" id="shippingPostcode" name="postcode">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="shippingCity" class="form-label">City</label>
                            <input type="text" class="form-control" id="shippingCity" name="city">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="shippingProvince" class="form-label">State/territory</label>
                            <input type="text" class="form-control" id="shippingProvince" name="city">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="shippingPhone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="shippingPhone" name="phone">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>