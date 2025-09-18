<?php 

    // view admin profile details
    require_once ("../db_connection/conn.php");

    // check if admin is ligged in
    if (!admin_is_logged_in()) {
        admin_login_redirect();
    }

    // check if admin has permisison
    if (admin_has_permission()) {
        redirect(PROOT . 'accounts/trades');
    }

    // check if capital is given
    $capital_mover = capital_mover($admin_id);
    if ((is_array($capital_mover) && $capital_mover["msg"] != "touched") && !is_capital_given()) {
        redirect(PROOT);
    } elseif (!is_array($capital_mover) && !is_capital_given()) {
        redirect(PROOT);
    }

    include ("../includes/header.inc.php");
    include ("../includes/aside.inc.php");
    include ("../includes/left.nav.inc.php");
    include ("../includes/top.nav.inc.php");

    $errors = "";

?>

    <!-- Content -->
    <div class="container-lg">
        <!-- Page header -->
        <div class="row align-items-center mb-7">
            <div class="col-auto">
                <!-- Avatar -->
                <div class="avatar avatar-xl rounded text-warning">
                    <i class="fs-2" data-duoicon="credit-card"></i>
                </div>
            </div>
            <div class="col">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a class="text-body-secondary" href="#">Market</a></li>
                        <li class="breadcrumb-item text-danger active" aria-current="page">End trade</li>
                    </ol>
                </nav>

                <!-- Heading -->
                <h1 class="fs-4 mb-0">Trades</h1>
            </div>
            <div class="col-12 col-sm-auto mt-4 mt-sm-0">
                <!-- Action -->
                <div class="row gx-2">
                    <a class="btn btn-light d-block" href="<?= PROOT; ?>account/trades"> <span class="material-symbols-outlined me-1">close</span> Cancel </a>
                </div>

            </div>
        </div>

        <!-- Page content -->
        <div class="row">
            <div class="col-12">
                <!-- Filters -->
                <div class="card card-line bg-body-tertiary border-transparent mb-7">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-12 col-lg-auto mb-3 mb-lg-0">
                                <ul class="nav nav-pills">
                                    <li class="nav-item">
                                        <a class="btn btn-dark active" aria-current="page" href="javascript:;">Denomination</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <section class="card bg-body-tertiary border-transparent mb-5" id="general">
            <div class="card-body">
                <h2 class="fs-5 mb-1">Denomination</h2>
                <p class="text-body-secondary">Complete the form below to end trade.</p>
                <form method="POST" id="denominationForm" action="<?= PROOT; ?>auth/denomination">
                    <div class="text-danger mb-3"><?= $errors; ?></div>
                    <div class="table-responsive mb-7">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Count</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>TWO HUNDRED Ghana cedis (200 GHS) X </td>
                                    <td><input type="number" inputmode="numeric" min="0" step="1" class="form-control" name="denomination_200c" id="denomination_200c" placeholder="0" /></td>
                                    <td><input type="number" class="form-control" name="denomination_200c_amt" id="denomination_200c_amt" placeholder="0.00" readonly /></td>
                                </tr>
                                <tr>
                                    <td>HUNDRED Ghana cedis (100 GHS) X </td>
                                    <td><input type="number" inputmode="numeric" min="0" step="1" class="form-control" name="denomination_100c" id="denomination_100c" placeholder="0" /></td>
                                    <td><input type="number" class="form-control" name="denomination_100c_amt" id="denomination_100c_amt" placeholder="0.00" readonly /></td>
                                </tr>
                                <tr>
                                    <td>FIFTY Ghana cedis (50 GHS) X </td>
                                    <td><input type="number" inputmode="numeric" min="0" step="1" class="form-control" name="denomination_50c" id="denomination_50c" placeholder="0" /></td>
                                    <td><input type="number" class="form-control" name="denomination_50c_amt" id="denomination_50c_amt" placeholder="0.00" readonly /></td>
                                </tr>
                                <tr>
                                    <td>TWENTY Ghana cedis (20 GHS) X </td>
                                    <td><input type="number" inputmode="numeric" min="0" step="1" class="form-control" name="denomination_20c" id="denomination_20c" placeholder="0" /></td>
                                    <td><input type="number" class="form-control" name="denomination_20c_amt" id="denomination_20c_amt" placeholder="0.00" readonly /></td>
                                </tr>
                                <tr>
                                    <td>TEN Ghana cedis (10 GHS) X </td>
                                    <td><input type="number" inputmode="numeric" min="0" step="1" class="form-control" name="denomination_10c" id="denomination_10c" placeholder="0" /></td>
                                    <td><input type="number" class="form-control" name="denomination_10c_amt" id="denomination_10c_amt" placeholder="0.00" readonly /></td>
                                </tr>
                                <tr>
                                    <td>FIVE Ghana cedis (5 GHS) X </td>
                                    <td><input type="number" inputmode="numeric" min="0" step="1" class="form-control" name="denomination_5c" id="denomination_5c" placeholder="0" /></td>
                                    <td><input type="number" class="form-control" name="denomination_5c_amt" id="denomination_5c_amt" placeholder="0.00" readonly /></td>
                                </tr>
                                <tr>
                                    <td>TWO Ghana cedis (2 GHS) X </td>
                                    <td><input type="number" inputmode="numeric" min="0" step="1" class="form-control" name="denomination_2c" id="denomination_2c" placeholder="0" /></td>
                                    <td><input type="number" class="form-control" name="denomination_2c_amt" id="denomination_2c_amt" placeholder="0.00" readonly /></td>
                                </tr>
                                <tr>
                                    <td>ONE Ghana cedis (1 GHS) X </td>
                                    <td><input type="number" inputmode="numeric" min="0" step="1" class="form-control" name="denomination_1c" id="denomination_1c" placeholder="0" /></td>
                                    <td><input type="number" class="form-control" name="denomination_1c_amt" id="denomination_1c_amt" placeholder="0.00" readonly /></td>
                                </tr>
                                <tr>
                                    <td>FIFTY Ghana pesewas (0.50 P) X </td>
                                    <td><input type="number" inputmode="numeric" min="0" step="1" class="form-control" name="denomination_50p" id="denomination_50p" placeholder="0" /></td>
                                    <td><input type="number" class="form-control" name="denomination_50p_amt" id="denomination_50p_amt" placeholder="0.00" readonly /></td>
                                </tr>
                                <tr>
                                    <td>TWENTY Ghana pesewas (0.20 P) X </td>
                                    <td><input type="number" inputmode="numeric" min="0" step="1" class="form-control" name="denomination_20p" id="denomination_20p" placeholder="0" /></td>
                                    <td><input type="number" class="form-control" name="denomination_20p_amt" id="denomination_20p_amt" placeholder="0.00" readonly /></td>
                                </tr>
                                <tr>
                                    <td>TEN Ghana pesewas (0.10 P) X </td>
                                    <td><input type="number" inputmode="numeric" min="0" step="1" class="form-control" name="denomination_10p" id="denomination_10p" placeholder="0" /></td>
                                    <td><input type="number" class="form-control" name="denomination_10p_amt" id="denomination_10p_amt" placeholder="0.00" readonly /></td>
                                </tr>
                                <tr>
                                    <td>FIVE Ghana pesewas (0.05 P) X </td>
                                    <td><input type="number" inputmode="numeric" min="0" step="1" class="form-control" name="denomination_5p" id="denomination_5p" placeholder="0" /></td>
                                    <td><input type="number" class="form-control" name="denomination_5p_amt" id="denomination_5p_amt" placeholder="0.00" readonly /></td>
                                </tr>
                                <tr>
                                    <td>ONE Ghana pesewas (0.01 P) X </td>
                                    <td><input type="number" inputmode="numeric" min="0" step="1" class="form-control" name="denomination_1p" id="denomination_1p" placeholder="0" /></td>
                                    <td><input type="number" class="form-control" name="denomination_1p_amt" id="denomination_1p_amt" placeholder="0.00" readonly /></td>
                                </tr>
                                <tr>
                                    <td colspan="2">Total</td>
                                    <td><input name="denomination_total" id="denomination-total" class="form-control" placeholder="0.00" readonly /></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="form-check mb-4">
                        <input class="form-check-input" name="no-cash" type="checkbox" id="no-cash" value="no-cash">
                        <label class="form-check-label" for="no-cash">
                            No cash
                        </label>
                    </div>
                    <button type="button" class="btn btn-secondary w-100" data-bs-target="#endModal" data-bs-toggle="modal">
                        <span class="material-symbols-outlined me-1">money_off</span> Save and End trade 
                    </button>
                    <a href="javascript:;" id="reset-form" class="btn btn-link w-100 mt-3">
                        Reset form
                    </a>
                    <div class="modal fade" id="endModal" tabindex="-1" aria-labelledby="endModalLabel" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true" style="backdrop-filter: blur(5px);">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content overflow-hidden">
                                <div class="modal-header pb-0 border-0">
                                    <h1 class="modal-title h4" id="endModalLabel">Confirm end trade capital!</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="inputpin mb-3">
                                        <div>
                                            <label class="form-label">Enter pin</label>
                                            <div class="d-flex justify-content-between p-4 bg-body-tertiary rounded">
                                                <style>
                                                    #pin:focus {
                                                        box-shadow: none;
                                                    }
                                                </style>
                                                <input type="password" class="form-control form-control-flush text-xl fw-bold w-rem-40 bg-transparent" placeholder="0000" name="pin" id="pin" autocomplete="off" inputmode="numeric" data-maxlength="4" oninput="this.value=this.value.slice(0,this.dataset.maxlength)" required>
                                                <button type="button" class="btn btn-sm btn-light rounded-pill shadow-none flex-none d-flex align-items-center gap-2 p-2" style="border: 1px solid #cbd5e1;">
                                                    <img src="<?= PROOT; ?>assets/media/pin.jpg" class="w-rem-6 h-rem-6 rounded-circle" alt="..."> <span>PIN</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" id="submitDenomination" class="btn btn-warning mt-4">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>

    </div>


<?php include ("../includes/footer.inc.php"); ?>

<script>

    const price = 14340;

    // Format the price above to USD using the locale, style, and currency.
    let USDollar = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    });

    console.log(`The formated version of ${price} is ${USDollar.format(price)}`);
    // The formated version of 14340 is $14,340.00

    function delay(callback, ms) {
        var timer = 0;
        return function() {
            var context = this, args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
            callback.apply(context, args);
            }, ms || 0);
        };
    }

    // denomination_200c
    $('#denomination_200c').keyup(delay(function() {
        var a = (200 * +$('#denomination_200c').val())
        $('#denomination_200c_amt').val(a);

        denomination_total()
    }, 500));

    // denomination_100c
    $('#denomination_100c').keyup(delay(function() {
        var a = (100 * +$('#denomination_100c').val())
        $('#denomination_100c_amt').val(a);

        denomination_total()
    }, 500));

    // denomination_50c
    $('#denomination_50c').keyup(delay(function() {
        var a = (50 * +$('#denomination_50c').val())
        $('#denomination_50c_amt').val(a);

        denomination_total()
    }, 500));

    // denomination_20c
    $('#denomination_20c').keyup(delay(function() {
        var a = (20 * +$('#denomination_20c').val())
        $('#denomination_20c_amt').val(a)

        denomination_total()
    }, 500));

    // denomination_10c
    $('#denomination_10c').keyup(delay(function() {
        var a = (10 * +$('#denomination_10c').val())
        $('#denomination_10c_amt').val(a);

        denomination_total()
    }, 500));

    // denomination_5c
    $('#denomination_5c').keyup(delay(function() {
        var a = (5 * +$('#denomination_5c').val())
        $('#denomination_5c_amt').val(a);

        denomination_total()
    }, 500));

    // denomination_2c
    $('#denomination_2c').keyup(delay(function() {
        var a = (2 * +$('#denomination_2c').val())
        $('#denomination_2c_amt').val(a);

        denomination_total()
    }, 500));

    // denomination_1c
    $('#denomination_1c').keyup(delay(function() {
        var a = (1 * +$('#denomination_1c').val())
        $('#denomination_1c_amt').val(a);

        denomination_total()
    }, 500));

    // denomination_50p
    $('#denomination_50p').keyup(delay(function() {
        var a = (parseFloat(+0.50) * +$('#denomination_50p').val())
        a = a.toFixed(2)
        $('#denomination_50p_amt').val(a);

        denomination_total()
    }, 500));

    // denomination_20p
    $('#denomination_20p').keyup(delay(function() {
        var a = (parseFloat(0.20) * $('#denomination_20p').val())
        a = a.toFixed(2)
        $('#denomination_20p_amt').val(a);

        denomination_total()
    }, 500));

    // denomination_10p
    $('#denomination_10p').keyup(delay(function() {
        var a = (parseFloat(0.10) * $('#denomination_10p').val())
        a = a.toFixed(2)
        $('#denomination_10p_amt').val(a);

        denomination_total()
    }, 500));

    // denomination_5p
    $('#denomination_5p').keyup(delay(function() {
        var a = (parseFloat(0.05) * $('#denomination_5p').val())
        a = a.toFixed(2)
        $('#denomination_5p_amt').val(a);

        denomination_total()
    }, 500));

    // denomination_1p
    $('#denomination_1p').keyup(delay(function() {
        var a = (parseFloat(0.01) * $('#denomination_1p').val())
        a = a.toFixed(2)
        $('#denomination_1p_amt').val(a);

        denomination_total()
    }, 500));

    function denomination_total() {
        var sum = (
            parseFloat(
                +$('#denomination_200c_amt').val() + 
                +$('#denomination_100c_amt').val() + 
                +$('#denomination_50c_amt').val() + 
                +$('#denomination_20c_amt').val() + 
                +$('#denomination_10c_amt').val() + 
                +$('#denomination_5c_amt').val() + 
                +$('#denomination_2c_amt').val() + 
                +$('#denomination_1c_amt').val() + 
                +$('#denomination_50p_amt').val() + 
                +$('#denomination_20p_amt').val() + 
                +$('#denomination_10p_amt').val() + 
                +$('#denomination_5p_amt').val() + 
                +$('#denomination_1p_amt').val()
            )
        ).toFixed(2)

        $('#denomination-total').val(sum);
    }

    // no cash checkbox
    $("#no-cash").change(function() {
        if (this.checked) {
            // Do stuff
            $('#denominationForm')[0].reset();

            // re-check checkbox
            $( this ).prop( "checked", true );
        }
    });

    // reset form
    $("#reset-form").on('click', function() {
        $('#denominationForm')[0].reset();
        $('#denomination_200c').focus() 
    });

    $('#submitDenomination').on('click', function() {
        if ($('#pin').val() == '' || $('#pin').val() != <?= $admin_data['admin_pin']; ?>) {
            alert("Invalid PIN provided!");
            $('#pin').val('');
            $('#pin').focus()
            return false;
        } else {
            if (confirm("By clicking on 'OK' button, today's trade will be ended!")) {
                $('#submitDenomination').attr('disabled', true);
                $('#submitDenomination').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span> Processing ...</span>');

                setTimeout(function () {
                    $('#denominationForm').submit()
                }, 2000)
            } else {
                $('#denominationForm')[0].reset()
                $('#endModal').modal('hide');
                return false
            }
        } 
    });
</script>
