<div class="modal fade" id="editSenderModal" tabindex="-1" aria-labelledby="editSenderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSenderModalLabel">Edit Sender Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editSenderForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="senderCountry" class="form-label">Country/Region</label>
                        <select class="form-select" id="senderCountry" name="country">
                            <option value="Malaysia" selected>Malaysia</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="senderFirstName" class="form-label">First name</label>
                            <input type="text" class="form-control" id="senderFirstName" name="first_name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="senderLastName" class="form-label">Last name</label>
                            <input type="text" class="form-control" id="senderLastName" name="last_name">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="senderCompany" class="form-label">Company</label>
                        <input type="text" class="form-control" id="senderCompany" name="company">
                    </div>
                    <div class="mb-3">
                        <label for="senderAddress1" class="form-label">Address</label>
                        <input type="text" class="form-control" id="senderAddress1" name="address1">
                    </div>
                    <div class="mb-3">
                        <label for="senderAddress2" class="form-label">Apartment, suite, etc.</label>
                        <input type="text" class="form-control" id="senderAddress2" name="address2">
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="senderZip" class="form-label">Postcode</label>
                            <input type="text" class="form-control" id="senderZip" name="postcode">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="senderCity" class="form-label">City</label>
                            <input type="text" class="form-control" id="senderCity" name="city">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="senderProvince" class="form-label">State/territory</label>
                            <input type="text" class="form-control" id="senderProvince" name="state">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="senderPhone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="senderPhone" name="phone">
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