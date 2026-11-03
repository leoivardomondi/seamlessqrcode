<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('splash-pages') ?>"><?= l('splash_pages.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('splash_page_update.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <div class="d-flex justify-content-between mb-4">
        <h1 class="h4 text-truncate mb-0"><i class="fas fa-fw fa-xs fa-droplet mr-1"></i> <?= l('splash_page_update.header') ?></h1>

        <?= include_view(THEME_PATH . 'views/splash-pages/splash_page_dropdown_button.php', ['id' => $data->splash_page->splash_page_id, 'resource_name' => $data->splash_page->name]) ?>
    </div>

    <div class="card">
        <div class="card-body">

            <form action="" method="post" role="form" enctype="multipart/form-data">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <div class="form-group">
                    <label for="name"><i class="fas fa-fw fa-signature fa-sm text-muted mr-1"></i> <?= l('global.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control <?= \Altum\Alerts::has_field_errors('name') ? 'is-invalid' : null ?>" value="<?= $data->splash_page->name ?>" maxlength="64" required="required" />
                    <?= \Altum\Alerts::output_field_error('name') ?>
                </div>

                <div class="form-group" data-file-image-input-wrapper data-file-input-wrapper-size-limit="<?= settings()->links->avatar_size_limit ?>" data-file-input-wrapper-size-limit-error="<?= sprintf(l('global.error_message.file_size_limit'), settings()->links->avatar_size_limit) ?>">
                    <label for="logo"><i class="fas fa-fw fa-sm fa-image text-muted mr-1"></i> <?= l('splash_pages.logo') ?></label>
                    <?= include_view(THEME_PATH . 'views/partials/file_image_input.php', ['uploads_file_key' => 'splash_pages', 'file_key' => 'logo', 'already_existing_image' => $data->splash_page->settings->logo]) ?>
                    <?= \Altum\Alerts::output_field_error('logo') ?>
                    <small class="form-text text-muted"><?= sprintf(l('global.accessibility.whitelisted_file_extensions'), \Altum\Uploads::get_whitelisted_file_extensions_accept('splash_pages')) . ' ' . sprintf(l('global.accessibility.file_size_limit'), settings()->links->avatar_size_limit) ?></small>
                </div>

                <div class="form-group">
                    <label for="title"><i class="fas fa-fw fa-pen fa-sm text-muted mr-1"></i> <?= l('global.title') ?></label>
                    <input type="text" id="title" name="title" class="form-control" value="<?= $data->splash_page->title ?>" maxlength="256" />
                </div>

                <div class="form-group" data-character-counter="textarea">
                    <label for="description" class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-fw fa-sm fa-paragraph text-muted mr-1"></i> <?= l('global.description') ?></span>
                        <small class="text-muted" data-character-counter-wrapper></small>
                    </label>
                    <textarea id="description" name="description" class="form-control" maxlength="2048"><?= $data->splash_page->description ?></textarea>
                </div>

                <div class="row">
                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="secondary_button_name"><i class="fas fa-fw fa-pen-to-square fa-sm text-muted mr-1"></i> <?= l('splash_pages.secondary_button_name') ?></label>
                            <input type="text" id="secondary_button_name" name="secondary_button_name" class="form-control" value="<?= $data->splash_page->settings->secondary_button_name ?>" maxlength="256" />
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="secondary_button_url"><i class="fas fa-fw fa-link fa-sm text-muted mr-1"></i> <?= l('splash_pages.secondary_button_url') ?></label>
                            <input type="url" id="secondary_button_url" name="secondary_button_url" class="form-control" value="<?= $data->splash_page->settings->secondary_button_url ?>" placeholder="<?= l('global.url_placeholder') ?>" maxlength="1024" />
                        </div>
                    </div>
                </div>

                <div class="form-group custom-control custom-switch">
                    <input id="auto_redirect" name="auto_redirect" type="checkbox" class="custom-control-input" <?= $data->splash_page->auto_redirect ? 'checked="checked"' : null?>>
                    <label class="custom-control-label" for="auto_redirect"><i class="fas fa-fw fa-square-up-right fa-sm text-muted mr-1"></i> <?= l('splash_pages.auto_redirect') ?></label>
                    <small class="form-text text-muted"><?= l('splash_pages.auto_redirect_help') ?></small>
                </div>

                <div class="form-group">
                    <label for="link_unlock_seconds"><i class="fas fa-fw fa-stopwatch fa-sm text-muted mr-1"></i> <?= l('splash_pages.link_unlock_seconds') ?></label>
                    <div class="input-group">
                        <input id="link_unlock_seconds" type="number" min="0" step="1" name="link_unlock_seconds" class="form-control" value="<?= $data->splash_page->link_unlock_seconds ?>" />
                        <div class="input-group-append">
                            <span class="input-group-text"><?= l('global.date.seconds') ?></span>
                        </div>
                    </div>
                    <small class="form-text text-muted"><?= l('splash_pages.link_unlock_seconds_help') ?></small>
                </div>

                <!-- Customization / Colors / Background / Buttons -->
                <button class="btn btn-block btn-gray-200 my-4" type="button" data-toggle="collapse" data-target="#customization_container" aria-expanded="false" aria-controls="customization_container">
                    <i class="fas fa-fw fa-paint-brush fa-sm mr-1"></i> <?= l('splash_pages.customization') ?>
                </button>

                <div class="collapse" id="customization_container">
                    <div class="form-group">
                        <label for="background_type"><i class="fas fa-fw fa-fill fa-sm text-muted mr-1"></i> <?= l('splash_pages.background_type') ?></label>
                        <select id="background_type" name="background_type" class="custom-select">
                            <option value="default" <?= $data->splash_page->settings->background_type == 'default' ? 'selected="selected"' : null ?>><?= l('splash_pages.background_type_default') ?></option>
                            <option value="color" <?= $data->splash_page->settings->background_type == 'color' ? 'selected="selected"' : null ?>><?= l('splash_pages.background_type_color') ?></option>
                            <option value="gradient" <?= $data->splash_page->settings->background_type == 'gradient' ? 'selected="selected"' : null ?>><?= l('splash_pages.background_type_gradient') ?></option>
                            <option value="image" <?= $data->splash_page->settings->background_type == 'image' ? 'selected="selected"' : null ?>><?= l('splash_pages.background_type_image') ?></option>
                        </select>
                    </div>

                    <div class="form-group <?= $data->splash_page->settings->background_type == 'color' ? null : 'd-none' ?>" data-background-type="color">
                        <label for="background_color"><i class="fas fa-fw fa-palette fa-sm text-muted mr-1"></i> <?= l('splash_pages.background_color') ?></label>
                        <input type="hidden" id="background_color" name="background_color" class="form-control" value="<?= $data->splash_page->settings->background_color ?>" data-color-picker />
                    </div>

                    <div class="<?= $data->splash_page->settings->background_type == 'gradient' ? null : 'd-none' ?>" data-background-type="gradient">
                        <div class="row">
                            <div class="col-12 col-lg-6">
                                <div class="form-group">
                                    <label for="background_color_one"><i class="fas fa-fw fa-palette fa-sm text-muted mr-1"></i> <?= l('splash_pages.background_color_one') ?></label>
                                    <input type="hidden" id="background_color_one" name="background_color_one" class="form-control" value="<?= $data->splash_page->settings->background_color_one ?>" data-color-picker />
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <div class="form-group">
                                    <label for="background_color_two"><i class="fas fa-fw fa-palette fa-sm text-muted mr-1"></i> <?= l('splash_pages.background_color_two') ?></label>
                                    <input type="hidden" id="background_color_two" name="background_color_two" class="form-control" value="<?= $data->splash_page->settings->background_color_two ?>" data-color-picker />
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="background_gradient_direction"><i class="fas fa-fw fa-compass fa-sm text-muted mr-1"></i> <?= l('splash_pages.background_gradient_direction') ?></label>
                            <select id="background_gradient_direction" name="background_gradient_direction" class="custom-select">
                                <option value="135deg" <?= $data->splash_page->settings->background_gradient_direction == '135deg' ? 'selected="selected"' : null ?>>135deg (Diagonal)</option>
                                <option value="to bottom" <?= $data->splash_page->settings->background_gradient_direction == 'to bottom' ? 'selected="selected"' : null ?>>Top to bottom</option>
                                <option value="to right" <?= $data->splash_page->settings->background_gradient_direction == 'to right' ? 'selected="selected"' : null ?>>Left to right</option>
                                <option value="to top right" <?= $data->splash_page->settings->background_gradient_direction == 'to top right' ? 'selected="selected"' : null ?>>Bottom-left to top-right</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group <?= $data->splash_page->settings->background_type == 'image' ? null : 'd-none' ?>" data-background-type="image" data-file-image-input-wrapper data-file-input-wrapper-size-limit="<?= settings()->links->background_size_limit ?? 5000000 ?>" data-file-input-wrapper-size-limit-error="<?= sprintf(l('global.error_message.file_size_limit'), settings()->links->background_size_limit ?? 5000000) ?>">
                        <label for="background"><i class="fas fa-fw fa-image fa-sm text-muted mr-1"></i> <?= l('splash_pages.background_image') ?></label>
                        <?= include_view(THEME_PATH . 'views/partials/file_image_input.php', ['uploads_file_key' => 'splash_pages', 'file_key' => 'background', 'already_existing_image' => $data->splash_page->settings->background]) ?>
                        <small class="form-text text-muted"><?= sprintf(l('global.accessibility.whitelisted_file_extensions'), \Altum\Uploads::get_whitelisted_file_extensions_accept('splash_pages')) ?></small>
                    </div>

                    <div class="row">
                        <div class="col-12 col-lg-6">
                            <div class="form-group">
                                <label for="title_color"><i class="fas fa-fw fa-font fa-sm text-muted mr-1"></i> <?= l('splash_pages.title_color') ?></label>
                                <input type="hidden" id="title_color" name="title_color" class="form-control" value="<?= $data->splash_page->settings->title_color ?>" data-color-picker />
                            </div>
                        </div>

                        <div class="col-12 col-lg-6">
                            <div class="form-group">
                                <label for="description_color"><i class="fas fa-fw fa-font fa-sm text-muted mr-1"></i> <?= l('splash_pages.description_color') ?></label>
                                <input type="hidden" id="description_color" name="description_color" class="form-control" value="<?= $data->splash_page->settings->description_color ?>" data-color-picker />
                            </div>
                        </div>
                    </div>

                    <hr class="my-4 border-gray-100" />
                    <h2 class="h6 mb-3"><i class="fas fa-fw fa-toggle-on fa-sm text-muted mr-1"></i> <?= l('splash_pages.buttons_customization') ?></h2>

                    <div class="p-3 bg-gray-50 rounded mb-3">
                        <h3 class="h6 font-weight-bold"><?= l('splash_pages.primary_button_header') ?></h3>

                        <div class="form-group">
                            <label for="primary_button_name"><?= l('splash_pages.primary_button_name') ?></label>
                            <input type="text" id="primary_button_name" name="primary_button_name" class="form-control" value="<?= $data->splash_page->settings->primary_button_name ?>" placeholder="<?= l('splash_pages.primary_button_name_placeholder') ?>" maxlength="256" />
                        </div>

                        <div class="row">
                            <div class="col-12 col-lg-4">
                                <div class="form-group">
                                    <label for="primary_button_background_color"><?= l('splash_pages.primary_button_background_color') ?></label>
                                    <input type="hidden" id="primary_button_background_color" name="primary_button_background_color" class="form-control" value="<?= $data->splash_page->settings->primary_button_background_color ?>" data-color-picker />
                                </div>
                            </div>
                            <div class="col-12 col-lg-4">
                                <div class="form-group">
                                    <label for="primary_button_text_color"><?= l('splash_pages.primary_button_text_color') ?></label>
                                    <input type="hidden" id="primary_button_text_color" name="primary_button_text_color" class="form-control" value="<?= $data->splash_page->settings->primary_button_text_color ?>" data-color-picker />
                                </div>
                            </div>
                            <div class="col-12 col-lg-4">
                                <div class="form-group">
                                    <label for="primary_button_border_radius"><?= l('splash_pages.primary_button_border_radius') ?></label>
                                    <select id="primary_button_border_radius" name="primary_button_border_radius" class="custom-select">
                                        <option value="rounded" <?= $data->splash_page->settings->primary_button_border_radius == 'rounded' ? 'selected="selected"' : null ?>><?= l('splash_pages.border_radius_rounded') ?></option>
                                        <option value="rounded-pill" <?= $data->splash_page->settings->primary_button_border_radius == 'rounded-pill' ? 'selected="selected"' : null ?>><?= l('splash_pages.border_radius_round') ?></option>
                                        <option value="rounded-0" <?= $data->splash_page->settings->primary_button_border_radius == 'rounded-0' ? 'selected="selected"' : null ?>><?= l('splash_pages.border_radius_square') ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 bg-gray-50 rounded">
                        <h3 class="h6 font-weight-bold"><?= l('splash_pages.secondary_button_header') ?></h3>

                        <div class="row">
                            <div class="col-12 col-lg-4">
                                <div class="form-group">
                                    <label for="secondary_button_background_color"><?= l('splash_pages.secondary_button_background_color') ?></label>
                                    <input type="hidden" id="secondary_button_background_color" name="secondary_button_background_color" class="form-control" value="<?= $data->splash_page->settings->secondary_button_background_color ?>" data-color-picker />
                                </div>
                            </div>
                            <div class="col-12 col-lg-4">
                                <div class="form-group">
                                    <label for="secondary_button_text_color"><?= l('splash_pages.secondary_button_text_color') ?></label>
                                    <input type="hidden" id="secondary_button_text_color" name="secondary_button_text_color" class="form-control" value="<?= $data->splash_page->settings->secondary_button_text_color ?>" data-color-picker />
                                </div>
                            </div>
                            <div class="col-12 col-lg-4">
                                <div class="form-group">
                                    <label for="secondary_button_border_radius"><?= l('splash_pages.secondary_button_border_radius') ?></label>
                                    <select id="secondary_button_border_radius" name="secondary_button_border_radius" class="custom-select">
                                        <option value="rounded" <?= $data->splash_page->settings->secondary_button_border_radius == 'rounded' ? 'selected="selected"' : null ?>><?= l('splash_pages.border_radius_rounded') ?></option>
                                        <option value="rounded-pill" <?= $data->splash_page->settings->secondary_button_border_radius == 'rounded-pill' ? 'selected="selected"' : null ?>><?= l('splash_pages.border_radius_round') ?></option>
                                        <option value="rounded-0" <?= $data->splash_page->settings->secondary_button_border_radius == 'rounded-0' ? 'selected="selected"' : null ?>><?= l('splash_pages.border_radius_square') ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Details Capture Form (Optional) -->
                <button class="btn btn-block btn-gray-200 my-4" type="button" data-toggle="collapse" data-target="#details_form_container" aria-expanded="false" aria-controls="details_form_container">
                    <i class="fas fa-fw fa-id-card fa-sm mr-1"></i> <?= l('splash_pages.details_form_header') ?>
                </button>

                <div class="collapse" id="details_form_container">
                    <div class="form-group custom-control custom-switch">
                        <input id="details_form_is_enabled" name="details_form_is_enabled" type="checkbox" class="custom-control-input" <?= $data->splash_page->settings->details_form_is_enabled ? 'checked="checked"' : null ?>>
                        <label class="custom-control-label font-weight-bold" for="details_form_is_enabled"><i class="fas fa-fw fa-users fa-sm text-muted mr-1"></i> <?= l('splash_pages.details_form_is_enabled') ?></label>
                        <small class="form-text text-muted"><?= l('splash_pages.details_form_is_enabled_help') ?></small>
                    </div>

                    <div id="details_form_settings" class="<?= $data->splash_page->settings->details_form_is_enabled ? null : 'd-none' ?>">
                        <div class="form-group">
                            <label for="form_title"><i class="fas fa-fw fa-heading fa-sm text-muted mr-1"></i> <?= l('splash_pages.form_title') ?></label>
                            <input type="text" id="form_title" name="form_title" class="form-control" value="<?= $data->splash_page->settings->form_title ?>" maxlength="512" />
                        </div>

                        <div class="form-group">
                            <label for="form_subtitle"><i class="fas fa-fw fa-info-circle fa-sm text-muted mr-1"></i> <?= l('splash_pages.form_subtitle') ?></label>
                            <input type="text" id="form_subtitle" name="form_subtitle" class="form-control" value="<?= $data->splash_page->settings->form_subtitle ?>" maxlength="512" />
                        </div>

                        <!-- Card Colors -->
                        <div class="p-3 bg-gray-50 rounded mb-4">
                            <h3 class="h6 font-weight-bold mb-3"><i class="fas fa-fw fa-palette fa-sm text-muted mr-1"></i> Form Card Styling</h3>
                            <div class="row">
                                <div class="col-12 col-lg-4">
                                    <div class="form-group">
                                        <label for="form_card_background_color"><?= l('splash_pages.form_card_background_color') ?></label>
                                        <input type="hidden" id="form_card_background_color" name="form_card_background_color" class="form-control" value="<?= $data->splash_page->settings->form_card_background_color ?>" data-color-picker />
                                    </div>
                                </div>
                                <div class="col-12 col-lg-4">
                                    <div class="form-group">
                                        <label for="form_card_text_color"><?= l('splash_pages.form_card_text_color') ?></label>
                                        <input type="hidden" id="form_card_text_color" name="form_card_text_color" class="form-control" value="<?= $data->splash_page->settings->form_card_text_color ?>" data-color-picker />
                                    </div>
                                </div>
                                <div class="col-12 col-lg-4">
                                    <div class="form-group">
                                        <label for="form_card_border_color"><?= l('splash_pages.form_card_border_color') ?></label>
                                        <input type="hidden" id="form_card_border_color" name="form_card_border_color" class="form-control" value="<?= $data->splash_page->settings->form_card_border_color ?>" data-color-picker />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Fields Configuration -->
                        <div class="p-3 border rounded mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="custom-control custom-switch">
                                    <input id="form_show_name" name="form_show_name" type="checkbox" class="custom-control-input" <?= $data->splash_page->settings->form_show_name ? 'checked="checked"' : null ?>>
                                    <label class="custom-control-label font-weight-bold" for="form_show_name"><?= l('splash_pages.form_show_name') ?></label>
                                </div>

                                <div class="custom-control custom-checkbox">
                                    <input id="form_name_required" name="form_name_required" type="checkbox" class="custom-control-input" <?= $data->splash_page->settings->form_name_required ? 'checked="checked"' : null ?>>
                                    <label class="custom-control-label small" for="form_name_required"><?= l('splash_pages.form_name_required') ?></label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 col-lg-6">
                                    <div class="form-group mb-lg-0">
                                        <label for="form_name_label" class="small"><?= l('splash_pages.form_name_label') ?></label>
                                        <input type="text" id="form_name_label" name="form_name_label" class="form-control form-control-sm" value="<?= $data->splash_page->settings->form_name_label ?>" />
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <div class="form-group mb-0">
                                        <label for="form_name_placeholder" class="small"><?= l('splash_pages.form_name_placeholder') ?></label>
                                        <input type="text" id="form_name_placeholder" name="form_name_placeholder" class="form-control form-control-sm" value="<?= $data->splash_page->settings->form_name_placeholder ?>" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- WhatsApp Field -->
                        <div class="p-3 border rounded mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="custom-control custom-switch">
                                    <input id="form_show_phone" name="form_show_phone" type="checkbox" class="custom-control-input" <?= $data->splash_page->settings->form_show_phone ? 'checked="checked"' : null ?>>
                                    <label class="custom-control-label font-weight-bold" for="form_show_phone"><?= l('splash_pages.form_show_phone') ?></label>
                                </div>

                                <div class="custom-control custom-checkbox">
                                    <input id="form_phone_required" name="form_phone_required" type="checkbox" class="custom-control-input" <?= $data->splash_page->settings->form_phone_required ? 'checked="checked"' : null ?>>
                                    <label class="custom-control-label small" for="form_phone_required"><?= l('splash_pages.form_phone_required') ?></label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 col-lg-6">
                                    <div class="form-group mb-lg-0">
                                        <label for="form_phone_label" class="small"><?= l('splash_pages.form_phone_label') ?></label>
                                        <input type="text" id="form_phone_label" name="form_phone_label" class="form-control form-control-sm" value="<?= $data->splash_page->settings->form_phone_label ?>" />
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <div class="form-group mb-0">
                                        <label for="form_phone_placeholder" class="small"><?= l('splash_pages.form_phone_placeholder') ?></label>
                                        <input type="text" id="form_phone_placeholder" name="form_phone_placeholder" class="form-control form-control-sm" value="<?= $data->splash_page->settings->form_phone_placeholder ?>" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Email Field -->
                        <div class="p-3 border rounded mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="custom-control custom-switch">
                                    <input id="form_show_email" name="form_show_email" type="checkbox" class="custom-control-input" <?= $data->splash_page->settings->form_show_email ? 'checked="checked"' : null ?>>
                                    <label class="custom-control-label font-weight-bold" for="form_show_email"><?= l('splash_pages.form_show_email') ?></label>
                                </div>

                                <div class="custom-control custom-checkbox">
                                    <input id="form_email_required" name="form_email_required" type="checkbox" class="custom-control-input" <?= $data->splash_page->settings->form_email_required ? 'checked="checked"' : null ?>>
                                    <label class="custom-control-label small" for="form_email_required"><?= l('splash_pages.form_email_required') ?></label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 col-lg-6">
                                    <div class="form-group mb-lg-0">
                                        <label for="form_email_label" class="small"><?= l('splash_pages.form_email_label') ?></label>
                                        <input type="text" id="form_email_label" name="form_email_label" class="form-control form-control-sm" value="<?= $data->splash_page->settings->form_email_label ?>" />
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <div class="form-group mb-0">
                                        <label for="form_email_placeholder" class="small"><?= l('splash_pages.form_email_placeholder') ?></label>
                                        <input type="text" id="form_email_placeholder" name="form_email_placeholder" class="form-control form-control-sm" value="<?= $data->splash_page->settings->form_email_placeholder ?>" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Consent Checkbox -->
                        <div class="p-3 border rounded mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="custom-control custom-switch">
                                    <input id="form_show_consent" name="form_show_consent" type="checkbox" class="custom-control-input" <?= $data->splash_page->settings->form_show_consent ? 'checked="checked"' : null ?>>
                                    <label class="custom-control-label font-weight-bold" for="form_show_consent"><?= l('splash_pages.form_show_consent') ?></label>
                                </div>

                                <div class="custom-control custom-checkbox">
                                    <input id="form_consent_required" name="form_consent_required" type="checkbox" class="custom-control-input" <?= $data->splash_page->settings->form_consent_required ? 'checked="checked"' : null ?>>
                                    <label class="custom-control-label small" for="form_consent_required"><?= l('splash_pages.form_consent_required') ?></label>
                                </div>
                            </div>

                            <div class="form-group mb-0">
                                <label for="form_consent_text" class="small"><?= l('splash_pages.form_consent_text') ?></label>
                                <textarea id="form_consent_text" name="form_consent_text" class="form-control form-control-sm" rows="2" maxlength="512"><?= $data->splash_page->settings->form_consent_text ?></textarea>
                            </div>
                        </div>

                        <!-- Buttons & Messages -->
                                                <!-- Notification handlers -->
                        <div class="form-group mb-4">
                            <div class="d-flex flex-column flex-xl-row justify-content-between">
                                <label><i class="fas fa-fw fa-sm fa-bell text-muted mr-1"></i> <?= l('biolink_block.notifications') ?></label>
                                <a href="<?= url('notification-handler-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('notification_handlers.create') ?></a>
                            </div>
                            <div class="mb-2"><small class="text-muted"><?= l('biolink_block.notifications_help') ?> Form submissions will also automatically be sent to your account email / active email notifiers.</small></div>

                            <?php if(!empty($data->notification_handlers)): ?>
                                <div class="row">
                                    <?php foreach($data->notification_handlers as $notification_handler): ?>
                                        <div class="col-12 col-lg-6">
                                            <div class="custom-control custom-checkbox my-2">
                                                <input id="<?= 'splash_notifications_' . $notification_handler->notification_handler_id ?>" name="notifications[]" value="<?= $notification_handler->notification_handler_id ?>" type="checkbox" class="custom-control-input" <?= in_array($notification_handler->notification_handler_id, $data->splash_page->settings->notifications ?? []) ? 'checked="checked"' : null ?>>
                                                <label class="custom-control-label" for="<?= 'splash_notifications_' . $notification_handler->notification_handler_id ?>">
                                                    <span class="mr-1"><?= $notification_handler->name ?></span>
                                                    <small class="badge badge-light badge-pill"><?= l('notification_handlers.type_' . $notification_handler->type) ?></small>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach ?>
                                </div>
                            <?php endif ?>
                        </div>

<div class="row">
                            <div class="col-12 col-lg-6">
                                <div class="form-group">
                                    <label for="form_submit_button_text"><?= l('splash_pages.form_submit_button_text') ?></label>
                                    <input type="text" id="form_submit_button_text" name="form_submit_button_text" class="form-control" value="<?= $data->splash_page->settings->form_submit_button_text ?>" maxlength="128" />
                                </div>
                            </div>

                            <div class="col-12 col-lg-6">
                                <div class="form-group">
                                    <label for="form_skip_button_text"><?= l('splash_pages.form_skip_button_text') ?></label>
                                    <input type="text" id="form_skip_button_text" name="form_skip_button_text" class="form-control" value="<?= $data->splash_page->settings->form_skip_button_text ?>" maxlength="128" />
                                </div>
                            </div>

                            <div class="col-12 col-lg-6">
                                <div class="form-group">
                                    <label for="form_submit_button_background_color"><?= l('splash_pages.primary_button_background_color') ?></label>
                                    <input type="hidden" id="form_submit_button_background_color" name="form_submit_button_background_color" class="form-control" value="<?= $data->splash_page->settings->form_submit_button_background_color ?>" data-color-picker />
                                </div>
                            </div>

                            <div class="col-12 col-lg-6">
                                <div class="form-group">
                                    <label for="form_submit_button_text_color"><?= l('splash_pages.primary_button_text_color') ?></label>
                                    <input type="hidden" id="form_submit_button_text_color" name="form_submit_button_text_color" class="form-control" value="<?= $data->splash_page->settings->form_submit_button_text_color ?>" data-color-picker />
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    <label for="form_success_message"><?= l('splash_pages.form_success_message') ?></label>
                                    <input type="text" id="form_success_message" name="form_success_message" class="form-control" value="<?= $data->splash_page->settings->form_success_message ?>" maxlength="256" />
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Advanced settings -->
                <button class="btn btn-block btn-gray-200 my-4" type="button" data-toggle="collapse" data-target="#advanced_container" aria-expanded="false" aria-controls="advanced_container">
                    <i class="fas fa-fw fa-user-tie fa-sm mr-1"></i> <?= l('splash_pages.advanced') ?>
                </button>

                <div class="collapse" id="advanced_container">
                    <div class="form-group" data-file-image-input-wrapper data-file-input-wrapper-size-limit="<?= settings()->links->favicon_size_limit ?>" data-file-input-wrapper-size-limit-error="<?= sprintf(l('global.error_message.file_size_limit'), settings()->links->favicon_size_limit) ?>">
                        <label for="favicon"><i class="fas fa-fw fa-sm fa-image text-muted mr-1"></i> <?= l('splash_pages.favicon') ?></label>
                        <?= include_view(THEME_PATH . 'views/partials/file_image_input.php', ['uploads_file_key' => 'splash_pages', 'file_key' => 'favicon', 'already_existing_image' => $data->splash_page->settings->favicon, 'input_data' => 'data-crop data-aspect-ratio="1"']) ?>
                        <?= \Altum\Alerts::output_field_error('favicon') ?>
                        <small class="form-text text-muted"><?= sprintf(l('global.accessibility.whitelisted_file_extensions'), \Altum\Uploads::get_whitelisted_file_extensions_accept('splash_pages')) . ' ' . sprintf(l('global.accessibility.file_size_limit'), settings()->links->favicon_size_limit) ?></small>
                    </div>

                    <div class="form-group" data-file-image-input-wrapper data-file-input-wrapper-size-limit="<?= settings()->links->seo_image_size_limit ?>" data-file-input-wrapper-size-limit-error="<?= sprintf(l('global.error_message.file_size_limit'), settings()->links->seo_image_size_limit) ?>">
                        <label for="opengraph"><i class="fas fa-fw fa-sm fa-image text-muted mr-1"></i> <?= l('splash_pages.opengraph') ?></label>
                        <?= include_view(THEME_PATH . 'views/partials/file_image_input.php', ['uploads_file_key' => 'splash_pages', 'file_key' => 'opengraph', 'already_existing_image' => $data->splash_page->settings->opengraph, 'input_data' => 'data-crop data-aspect-ratio="1.91"']) ?>
                        <?= \Altum\Alerts::output_field_error('opengraph') ?>
                        <small class="form-text text-muted"><?= sprintf(l('global.accessibility.whitelisted_file_extensions'), \Altum\Uploads::get_whitelisted_file_extensions_accept('splash_pages')) . ' ' . sprintf(l('global.accessibility.file_size_limit'), settings()->links->seo_image_size_limit) ?></small>
                    </div>

                    <div <?= $this->user->plan_settings->custom_css_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                        <div class="form-group <?= $this->user->plan_settings->custom_css_is_enabled ? null : 'container-disabled' ?>" data-character-counter="textarea">
                            <label for="custom_css" class="d-flex justify-content-between align-items-center">
                                <span><i class="fab fa-fw fa-sm fa-css3 text-muted mr-1"></i> <?= l('global.custom_css') ?></span>
                                <small class="text-muted" data-character-counter-wrapper></small>
                            </label>
                            <textarea id="custom_css" class="form-control" name="custom_css" maxlength="10000" placeholder="<?= l('global.custom_css_placeholder') ?>"><?= $data->splash_page->settings->custom_css ?></textarea>
                            <small class="form-text text-muted"><?= l('global.custom_css_help') ?></small>
                        </div>
                    </div>

                    <div <?= $this->user->plan_settings->custom_js_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                        <div class="form-group <?= $this->user->plan_settings->custom_js_is_enabled ? null : 'container-disabled' ?>" data-character-counter="textarea">
                            <label for="custom_js" class="d-flex justify-content-between align-items-center">
                                <span><i class="fab fa-fw fa-sm fa-js-square text-muted mr-1"></i> <?= l('global.custom_js') ?></span>
                                <small class="text-muted" data-character-counter-wrapper></small>
                            </label>
                            <textarea id="custom_js" class="form-control" name="custom_js" maxlength="10000" placeholder="<?= l('global.custom_js_placeholder') ?>"><?= $data->splash_page->settings->custom_js ?></textarea>
                            <small class="form-text text-muted"><?= l('global.custom_js_help') ?></small>
                        </div>
                    </div>

                    <div <?= $this->user->plan_settings->no_ads ? null : get_plan_feature_disabled_info() ?>>
                        <div class="form-group <?= $this->user->plan_settings->no_ads ? null : 'container-disabled' ?>" data-character-counter="textarea">
                            <label for="ads_header" class="d-flex justify-content-between align-items-center">
                                <span><i class="fab fa-fw fa-sm fa-adversal text-muted mr-1"></i> <?= l('splash_pages.ads_header') ?></span>
                                <small class="text-muted" data-character-counter-wrapper></small>
                            </label>
                            <textarea id="ads_header" class="form-control" name="ads_header" maxlength="10000"><?= $data->splash_page->settings->ads_header ?></textarea>
                        </div>
                    </div>

                    <div <?= $this->user->plan_settings->no_ads ? null : get_plan_feature_disabled_info() ?>>
                        <div class="form-group <?= $this->user->plan_settings->no_ads ? null : 'container-disabled' ?>" data-character-counter="textarea">
                            <label for="ads_footer" class="d-flex justify-content-between align-items-center">
                                <span><i class="fab fa-fw fa-sm fa-adversal text-muted mr-1"></i> <?= l('splash_pages.ads_footer') ?></span>
                                <small class="text-muted" data-character-counter-wrapper></small>
                            </label>
                            <textarea id="ads_footer" class="form-control" name="ads_footer" maxlength="10000"><?= $data->splash_page->settings->ads_footer ?></textarea>
                        </div>
                    </div>
                </div>

                <button type="submit" name="submit" class="btn btn-block btn-primary"><?= l('global.update') ?></button>
            </form>

        </div>
    </div>
</div>

<?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
    'name' => 'splash_page',
    'resource_id' => 'splash_page_id',
    'has_dynamic_resource_name' => true,
    'path' => 'splash-pages/delete'
]), 'modals'); ?>

<?php include_view(THEME_PATH . 'views/partials/color_picker_js.php') ?>
<?php include_view(THEME_PATH . 'views/partials/js_cropper.php') ?>

<?php ob_start() ?>
<script>
    'use strict';

    /* Background type switcher */
    let background_type_handler = () => {
        let type = document.querySelector('#background_type').value;
        document.querySelectorAll('[data-background-type]').forEach(el => {
            el.classList.add('d-none');
        });
        let active = document.querySelector(`[data-background-type="${type}"]`);
        if(active) active.classList.remove('d-none');
    };
    document.querySelector('#background_type').addEventListener('change', background_type_handler);
    background_type_handler();

    /* Details form toggle */
    let details_form_handler = () => {
        let enabled = document.querySelector('#details_form_is_enabled').checked;
        let container = document.querySelector('#details_form_settings');
        if(enabled) {
            container.classList.remove('d-none');
        } else {
            container.classList.add('d-none');
        }
    };
    document.querySelector('#details_form_is_enabled').addEventListener('change', details_form_handler);
    details_form_handler();
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
