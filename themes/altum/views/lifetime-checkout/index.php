<?php defined('ALTUMCODE') || die() ?>

<div class="container py-5 lifetime-checkout-page">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-7 col-xl-6">
            <?= \Altum\Alerts::output_alerts() ?>

            <div class="card border-0 shadow-sm rounded-2x">
                <div class="card-body p-4 p-md-5">
                    <div class="mb-4 text-center">
                        <div class="mb-3"><i class="fas fa-fw fa-2x fa-bolt text-primary"></i></div>
                        <h1 class="h3 mb-2">Continue to lifetime checkout</h1>
                        <p class="text-muted mb-0">Enter your details once. We will prepare checkout for <strong><?= $data->plan->name ?></strong> without sending you to a separate signup page.</p>
                    </div>

                    <div class="bg-gray-50 rounded p-3 mb-4 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="font-weight-bold"><?= $data->plan->name ?></div>
                            <small class="text-muted">Lifetime access</small>
                        </div>
                        <div class="h5 mb-0"><?= nr($data->plan->prices->lifetime->{currency()}, settings()->payment->currencies->{currency()}->currency_decimals ?? 2) ?> <?= currency() ?></div>
                    </div>

                    <form action="" method="post" role="form">
                        <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                        <div class="form-group">
                            <label for="name"><?= l('global.name') ?></label>
                            <input id="name" type="text" name="name" class="form-control <?= \Altum\Alerts::has_field_errors('name') ? 'is-invalid' : null ?>" value="<?= $data->values['name'] ?>" maxlength="64" required="required" autofocus="autofocus" />
                            <?= \Altum\Alerts::output_field_error('name') ?>
                        </div>

                        <div class="form-group">
                            <label for="email"><?= l('global.email') ?></label>
                            <input id="email" type="email" name="email" class="form-control <?= \Altum\Alerts::has_field_errors('email') ? 'is-invalid' : null ?>" value="<?= $data->values['email'] ?>" maxlength="128" required="required" />
                            <?= \Altum\Alerts::output_field_error('email') ?>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg btn-block rounded-2x mt-4">
                            Continue to payment
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <a href="<?= url('lifetime#pricing') ?>" class="text-muted"><i class="fas fa-fw fa-arrow-left mr-1"></i> Back to lifetime plans</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php ob_start() ?>
<style>
    #navbar, footer { display: none !important; }
</style>
<?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>