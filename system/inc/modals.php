    <!-- Modals -->
    
    <!-- Modal: New transaction modal -->
    <?php
        // Only show this modal if the user has permission to create transactions
        if (collector_is_logged_in()): 
            // get options for customer select
            $options = '';
            $customers = collector_get_customers();
            foreach ($customers as $customer) {
                $options .= '<option value="' . sanitize(ucwords($customer['customer_name']) . ',' . $customer['customer_account_number']) . '">' . sanitize(ucwords($customer['customer_name']) . ',' . $customer['customer_account_number']) . '</option>';
            }
    ?>
    <div class="modal fade" id="transactionModal" tabindex="-1" aria-labelledby="transactionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom-0 pb-0">
                    <h1 class="modal-title fs-5" id="transactionModalLabel">New transaction</h1>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="add-transaction-form" method="POST">
                        <div class="mb-4">
                            <label class="form-label" for="select_customer">Select customer</label>
                            <select class="form-select" id="select_customer" name="select_customer" data-choices required>
                                <option value="">...</option>
                                <?php echo $options; ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="default_amount">Amount <span id="label-defualt-amount"></span></label>
                            <input class="form-control" id="default_amount" name="default_amount" type="number" min="0.00" step="0.01" readonly placeholder="Enter amount" required />
                        </div>
                         <div class="mb-4">
                            <label class="form-label" for="payment_mode">Mode of payment</label>
                            <select class="form-select" id="payment_mode" name="payment_mode" data-choices required>
                                <option value="">Mode</option>
                                <option value="bank">Bank</option>
                                <option value="cash" selected>Cash</option>
                                <option value="airteltigomoney">AirtelTigo Money</option>
                                <option value="mtnmobilemoney">MTN Mobile Money</option>
                                <option value="telecelcash">Tecel Cash</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="totday_date">Date</label>
                            <input class="form-control" id="totday_date" name="today_date" type="text" data-flatpickr readonly value="<?= date('Y-m-d'); ?>" required />
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="note">Note (optional)</label>
                            <textarea class="form-control" id="note" name="note" rows="3" data-autosize></textarea>
                            <div class="form-text">Limit 500</div>
                        </div>
                        <!-- check for advance payment or not -->
                        <label class="form-label" for="note">is advance payment</label>
                        <input type="checkbox" id="is_advance_payment" name="is_advance_payment" value="no" />
                        <div class="mb-4 mt-2" id="advance_payment_div" style="display: none;">
                            <label class="form-label" for="advance_payment">Advance payment</label>
                            <select class="form-select" id="advance_payment" name="advance_payment" required>
                                <option value="1">1</option>
                                <?php for ($i = 2; $i <= 30; $i++) {
                                    echo '<option value="' . $i . '">' . $i . '</option>';
                                } ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-secondary w-100 mt-4" id="submit-transaction" name="submit-transaction">Add transaction</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Offcanvas: Product -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="productModal" aria-labelledby="productModalLabel">
      <div class="offcanvas-body">
        <!-- Header -->
        <div class="row">
          <div class="col-auto">
            <div class="avatar avatar-xl rounded">
              <img class="avatar-img" src="./assets/img/products/earbuds.jpg" alt="..." />
            </div>
          </div>
          <div class="col">
            <small class="text-body-secondary">Audio</small>
            <h2 class="fs-5 mb-1">Noise-Canceling Earbuds</h2>
            <div class="rating" aria-label="5 out of 5 stars" style="--stars: 5"></div>
          </div>
          <div class="col-auto">
            <span class="fs-lg text-body-secondary">$129.99</span>
          </div>
          <div class="col-auto">
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
          </div>
        </div>
    
        <!-- Divider -->
        <hr class="my-6" />
    
        <!-- Description -->
        <p>
          Experience unparalleled audio quality with our Noise-Canceling Earbuds, designed to block out unwanted noise and immerse you in pure sound. Featuring
          advanced active noise cancellation (ANC), these earbuds reduce ambient distractions, letting you focus on your music, calls, or podcasts with
          crystal-clear clarity.
        </p>
    
        <!-- Divider -->
        <hr class="my-6" />
    
        <!-- Header -->
        <h3 class="fs-6 mb-5">Details</h3>
    
        <!-- Details -->
        <div class="vstack gap-3">
          <div class="row align-items-center gx-4">
            <div class="col-auto">
              <span class="text-body-secondary">Availability</span>
            </div>
            <div class="col">
              <hr class="my-0 border-style-dotted" />
            </div>
            <div class="col-auto">
              <span class="badge bg-success-subtle text-success">In Stock</span>
            </div>
          </div>
          <div class="row align-items-center gx-4">
            <div class="col-auto">
              <span class="text-body-secondary">Shipping</span>
            </div>
            <div class="col">
              <hr class="my-0 border-style-dotted" />
            </div>
            <div class="col-auto"><span class="material-symbols-outlined text-body-tertiary me-1">globe</span> Worldwide</div>
          </div>
        </div>
    
        <!-- Divider -->
        <hr class="my-6" />
    
        <!-- Header -->
        <div class="row align-items-center mb-5">
          <div class="col">
            <h3 class="fs-6 mb-0">Reviews</h3>
          </div>
          <div class="col-auto">
            <small class="text-body-secondary">3 reviews</small>
          </div>
        </div>
    
        <!-- Reviews -->
        <div class="row gx-3 mb-4">
          <div class="col-auto">
            <!-- Avatar -->
            <div class="avatar avatar-sm">
              <img class="avatar-img" src="./assets/img/photos/photo-2.jpg" alt="..." />
            </div>
          </div>
          <div class="col">
            <!-- Card -->
            <div class="card bg-body-tertiary border-transparent mb-0">
              <div class="card-body p-4">
                <div class="row align-items-center mb-2">
                  <div class="col">
                    <h6 class="fs-sm fw-normal text-body-secondary mb-0">Michael Johnson · 1d ago</h6>
                  </div>
                  <div class="col-auto">
                    <small class="rating" aria-label="5 out of 5 stars" style="--stars: 5"></small>
                  </div>
                </div>
                <p class="mb-0">Incredible noise cancellation! Crystal-clear sound and deep bass. Perfect for any environment.</p>
              </div>
            </div>
          </div>
        </div>
        <div class="row gx-3 mb-4">
          <div class="col-auto">
            <!-- Avatar -->
            <div class="avatar avatar-sm">
              <img class="avatar-img" src="./assets/img/photos/photo-1.jpg" alt="..." />
            </div>
          </div>
          <div class="col">
            <!-- Card -->
            <div class="card bg-body-tertiary border-transparent mb-0">
              <div class="card-body p-4">
                <div class="row align-items-center mb-2">
                  <div class="col">
                    <h6 class="fs-sm fw-normal text-body-secondary mb-0">Emily Thompson · 1d ago</h6>
                  </div>
                  <div class="col-auto">
                    <small class="rating" aria-label="5 out of 5 stars" style="--stars: 5"></small>
                  </div>
                </div>
                <p class="mb-0">Super comfy and great for travel! Blocks out noise and lasts all day.</p>
              </div>
            </div>
          </div>
        </div>
        <div class="row gx-3">
          <div class="col-auto">
            <!-- Avatar -->
            <div class="avatar avatar-sm">
              <img class="avatar-img" src="./assets/img/photos/photo-3.jpg" alt="..." />
            </div>
          </div>
          <div class="col">
            <!-- Card -->
            <div class="card bg-body-tertiary border-transparent mb-0">
              <div class="card-body p-4">
                <div class="row align-items-center mb-2">
                  <div class="col">
                    <h6 class="fs-sm fw-normal text-body-secondary mb-0">Robert Garcia · 12m ago</h6>
                  </div>
                  <div class="col-auto">
                    <small class="rating" aria-label="5 out of 5 stars" style="--stars: 5"></small>
                  </div>
                </div>
                <p class="mb-0">Fantastic sound quality with long battery life. Easy to use and reliable!</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Offcanvas: Order -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="orderModal" aria-labelledby="orderModalLabel">
      <div class="offcanvas-body">
        <!-- Header -->
        <div class="row align-items-center">
          <div class="col">
            <h2 class="fs-5 mb-1">Order #3456</h2>
          </div>
          <div class="col-auto">
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
          </div>
        </div>
    
        <!-- Divider -->
        <hr class="my-6" />
    
        <!-- Header -->
        <h3 class="fs-6 mb-1">Items</h3>
    
        <!-- Products -->
        <div class="list-group list-group-flush">
          <div class="list-group-item px-0">
            <div class="row align-items-center">
              <div class="col">
                <div class="d-flex align-items-center">
                  <div class="avatar">
                    <img class="avatar-img rounded" src="./assets/img/products/vr-headset.jpg" alt="..." />
                  </div>
                  <div class="ms-4">
                    <div>VR Headset</div>
                  </div>
                </div>
              </div>
              <div class="col-auto">1 <span class="text-body-secondary mx-1">×</span> $399.99</div>
            </div>
          </div>
          <div class="list-group-item px-0">
            <div class="row align-items-center">
              <div class="col">
                <div class="d-flex align-items-center">
                  <div class="avatar">
                    <img class="avatar-img rounded" src="./assets/img/products/smart-watch.jpg" alt="..." />
                  </div>
                  <div class="ms-4">
                    <div>Smart Watch</div>
                  </div>
                </div>
              </div>
              <div class="col-auto">1 <span class="text-body-secondary mx-1">×</span> $149.99</div>
            </div>
          </div>
          <div class="list-group-item px-0">
            <div class="row">
              <div class="col">
                <strong class="fw-semibold">Total</strong>
              </div>
              <div class="col-auto">
                <strong class="fw-semibold">$549.98</strong>
              </div>
            </div>
          </div>
        </div>
    
        <!-- Divider -->
        <hr class="my-6" />
    
        <!-- Header -->
        <h3 class="fs-6 mb-5">Details</h3>
    
        <!-- Details -->
        <div class="vstack gap-3">
          <div class="row align-items-center gx-4">
            <div class="col-auto">
              <span class="text-body-secondary">Date created</span>
            </div>
            <div class="col">
              <hr class="my-0 border-style-dotted" />
            </div>
            <div class="col-auto">2021-08-12</div>
          </div>
          <div class="row align-items-center gx-4">
            <div class="col-auto">
              <span class="text-body-secondary">Customer</span>
            </div>
            <div class="col">
              <hr class="my-0 border-style-dotted" />
            </div>
            <div class="col-auto">Guest</div>
          </div>
          <div class="row align-items-center gx-4">
            <div class="col-auto">
              <span class="text-body-secondary">Status</span>
            </div>
            <div class="col">
              <hr class="my-0 border-style-dotted" />
            </div>
            <div class="col-auto">
              <span class="badge bg-success-subtle text-success">Completed</span>
            </div>
          </div>
        </div>
    
        <!-- Divider -->
        <hr class="my-6" />
    
        <!-- Header -->
        <h3 class="fs-6 mb-5">Notes</h3>
    
        <!-- Notes -->
        <div class="vstack gap-1">
          <div class="card bg-body-tertiary border-transparent mb-0">
            <div class="card-body p-4">
              <small class="text-body-secondary">10:15 AM</small>
              <p class="mb-0">Order placed successfully and is now being processed.</p>
            </div>
          </div>
          <div class="card bg-body-tertiary border-transparent mb-0">
            <div class="card-body p-4">
              <small class="text-body-secondary">2:30 PM</small>
              <p class="mb-0">Order has been shipped and is on its way to the destination.</p>
            </div>
          </div>
          <div class="card bg-body-tertiary border-transparent mb-0">
            <div class="card-body p-4">
              <small class="text-body-secondary">6:45 PM</small>
              <p class="mb-0">Order delivered successfully to the customer.</p>
            </div>
          </div>
        </div>
      </div>
    </div>