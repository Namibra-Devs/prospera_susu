<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="A fully featured admin theme which can be used to build CRM, CMS, etc." />
    
    <!-- Settings -->
    <script>
      /*!
       * Color mode toggler for Bootstrap's docs (https://getbootstrap.com/)
       * Copyright 2011-2024 The Bootstrap Authors
       * Licensed under the Creative Commons Attribution 3.0 Unported License.
       * Modified by Simpleqode
       */
    
      (() => {
        'use strict';
    
        const getStoredTheme = () => localStorage.getItem('theme');
        const setStoredTheme = (theme) => localStorage.setItem('theme', theme);
    
        const getStoredNavigationPosition = () => localStorage.getItem('navigationPosition');
        const setStoredNavigationPosition = (navigationPosition) => localStorage.setItem('navigationPosition', navigationPosition);
    
        const getStoredSidenavSizing = () => localStorage.getItem('sidenavSizing');
        const setStoredSidenavSizing = (sidenavSizing) => localStorage.setItem('sidenavSizing', sidenavSizing);
    
        const getPreferredTheme = () => {
          const storedTheme = getStoredTheme();
          if (storedTheme) {
            return storedTheme;
          }
          return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        };
    
        const getPreferredNavigationPosition = () => {
          const storedNavigationPosition = getStoredNavigationPosition();
          if (storedNavigationPosition) {
            return storedNavigationPosition;
          }
          return 'sidenav';
        };
    
        const getPreferredSidenavSizing = () => {
          const storedSidenavSizing = getStoredSidenavSizing();
          if (storedSidenavSizing) {
            return storedSidenavSizing;
          }
          return 'base';
        };
    
        const setTheme = (theme) => {
          if (theme === 'auto') {
            document.documentElement.setAttribute('data-bs-theme', window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
          } else {
            document.documentElement.setAttribute('data-bs-theme', theme);
          }
        };
    
        const setNavigationPosition = (navigationPosition) => {
          document.documentElement.setAttribute('data-bs-navigation-position', navigationPosition);
        };
    
        const setSidenavSizing = (sidenavSizing) => {
          document.documentElement.setAttribute('data-bs-sidenav-sizing', sidenavSizing);
        };
    
        setTheme(getPreferredTheme());
        setNavigationPosition(getPreferredNavigationPosition());
        setSidenavSizing(getPreferredSidenavSizing());
    
        const showActiveTheme = (theme, settingsSwitcher) => {
          document.querySelectorAll('[data-bs-theme-value]').forEach((element) => {
            element.classList.remove('active');
            element.setAttribute('aria-pressed', 'false');
    
            if (element.getAttribute('data-bs-theme-value') === theme) {
              element.classList.add('active');
              element.setAttribute('aria-pressed', 'true');
            }
          });
    
          if (settingsSwitcher) {
            settingsSwitcher.focus();
          }
        };
    
        const showActiveNavigationPosition = (navigationPosition, settingsSwitcher) => {
          document.querySelectorAll('[data-bs-navigation-position-value]').forEach((element) => {
            element.classList.remove('active');
            element.setAttribute('aria-pressed', 'false');
    
            if (element.getAttribute('data-bs-navigation-position-value') === navigationPosition) {
              element.classList.add('active');
              element.setAttribute('aria-pressed', 'true');
            }
          });
    
          if (settingsSwitcher) {
            settingsSwitcher.focus();
          }
        };
    
        const showActiveSidenavSizing = (sidenavSizing, settingsSwitcher) => {
          document.querySelectorAll('[data-bs-sidenav-sizing-value]').forEach((element) => {
            element.classList.remove('active');
            element.setAttribute('aria-pressed', 'false');
    
            if (element.getAttribute('data-bs-sidenav-sizing-value') === sidenavSizing) {
              element.classList.add('active');
              element.setAttribute('aria-pressed', 'true');
            }
          });
    
          if (settingsSwitcher) {
            settingsSwitcher.focus();
          }
        };
    
        const refreshCharts = () => {
          const charts = document.querySelectorAll('.chart-canvas');
    
          charts.forEach((chart) => {
            const chartId = chart.getAttribute('id');
            const instance = Chart.getChart(chartId);
    
            if (!instance) {
              return;
            }
    
            if (instance.options.scales.y) {
              instance.options.scales.y.grid.color = getComputedStyle(document.documentElement).getPropertyValue('--bs-border-color');
              instance.options.scales.y.ticks.color = getComputedStyle(document.documentElement).getPropertyValue('--bs-secondary-color');
            }
    
            if (instance.options.scales.x) {
              instance.options.scales.x.ticks.color = getComputedStyle(document.documentElement).getPropertyValue('--bs-secondary-color');
            }
    
            if (instance.options.elements.arc) {
              instance.options.elements.arc.borderColor = getComputedStyle(document.documentElement).getPropertyValue('--bs-body-bg');
              instance.options.elements.arc.hoverBorderColor = getComputedStyle(document.documentElement).getPropertyValue('--bs-body-bg');
            }
    
            instance.update();
          });
        };
    
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
          const storedTheme = getStoredTheme();
          if (storedTheme !== 'light' && storedTheme !== 'dark') {
            setTheme(getPreferredTheme());
          }
        });
    
        window.addEventListener('DOMContentLoaded', () => {
          showActiveTheme(getPreferredTheme());
          showActiveNavigationPosition(getPreferredNavigationPosition());
          showActiveSidenavSizing(getPreferredSidenavSizing());
    
          document.querySelectorAll('[data-bs-theme-value]').forEach((toggle) => {
            toggle.addEventListener('click', (e) => {
              e.preventDefault();
              const theme = toggle.getAttribute('data-bs-theme-value');
              const settingsSwitcher = toggle.closest('.nav-item').querySelector('[data-bs-settings-switcher]');
              setStoredTheme(theme);
              setTheme(theme);
              showActiveTheme(theme, settingsSwitcher);
              refreshCharts();
            });
          });
    
          document.querySelectorAll('[data-bs-navigation-position-value]').forEach((toggle) => {
            toggle.addEventListener('click', (e) => {
              e.preventDefault();
              const navigationPosition = toggle.getAttribute('data-bs-navigation-position-value');
              const settingsSwitcher = toggle.closest('.nav-item').querySelector('[data-bs-settings-switcher]');
              setStoredNavigationPosition(navigationPosition);
              setNavigationPosition(navigationPosition);
              showActiveNavigationPosition(navigationPosition, settingsSwitcher);
            });
          });
    
          document.querySelectorAll('[data-bs-sidenav-sizing-value]').forEach((toggle) => {
            toggle.addEventListener('click', (e) => {
              e.preventDefault();
              const sidenavSizing = toggle.getAttribute('data-bs-sidenav-sizing-value');
              const settingsSwitcher = toggle.closest('.nav-item').querySelector('[data-bs-settings-switcher]');
              setStoredSidenavSizing(sidenavSizing);
              setSidenavSizing(sidenavSizing);
              showActiveSidenavSizing(sidenavSizing, settingsSwitcher);
            });
          });
        });
      })();
    </script>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="./assets/favicon/favicon.ico" type="image/x-icon" />
    
    <!-- Fonts and icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,1,0" />
    
    <!-- Libs CSS -->
    <link rel="stylesheet" href="./assets/css/libs.bundle.css" />
    
    <!-- Theme CSS -->
    <link rel="stylesheet" href="./assets/css/theme.bundle.css" />
    
    <!-- Title -->
    <title>Dashbrd</title>
  </head>

  <body>
    <!-- Modals -->
    <!-- Modal: Event -->
    <div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header border-bottom-0 pb-0">
            <h1 class="modal-title fs-5" id="eventModalLabel">New event</h1>
            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form>
              <div class="mb-4">
                <label class="form-label" for="eventTitle">Title</label>
                <input class="form-control" id="eventTitle" type="text" />
              </div>
              <div class="mb-4">
                <label class="form-label" for="eventDescription">Description</label>
                <textarea class="form-control" id="eventDescription" rows="3" data-autosize></textarea>
              </div>
              <div class="row">
                <div class="col">
                  <div class="mb-4">
                    <label class="form-label" for="eventStart">Start</label>
                    <input class="form-control" id="eventStart" type="text" data-flatpickr />
                  </div>
                </div>
                <div class="col">
                  <div class="mb-4">
                    <label class="form-label" for="eventEnd">End</label>
                    <input class="form-control" id="eventEnd" type="text" data-flatpickr />
                  </div>
                </div>
              </div>
              <button type="submit" class="btn btn-secondary w-100 mt-4">Create event</button>
            </form>
          </div>
        </div>
      </div>
    </div>
    
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