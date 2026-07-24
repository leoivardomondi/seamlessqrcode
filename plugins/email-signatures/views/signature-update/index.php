<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <nav aria-label="breadcrumb">
        <ol class="custom-breadcrumbs small">
            <li>
                <a href="<?= url('signatures') ?>"><?= l('signatures.breadcrumb') ?></a><i class="fa fa-fw fa-angle-right"></i>
            </li>
            <li class="active" aria-current="page"><?= l('signature_update.breadcrumb') ?></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 text-truncate mb-0"><?= sprintf(l('global.update_x'), $data->signature->name) ?></h1>

        <div class="d-flex align-items-center col-auto p-0">
            <?= include_view(\Altum\Plugin::get('email-signatures')->path . 'views/signatures/signature_dropdown_button.php', ['id' => $data->signature->signature_id, 'resource_name' => $data->signature->name]) ?>
        </div>
    </div>

    <form id="update" action="" method="post" role="form">
        <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

        <div class="row">
            <div class="col-xl-5 mb-5 mb-xl-0">
                <ul class="nav nav-pills" role="tablist">
                    <li class="nav-item mr-3 mb-3" role="presentation">
                        <button class="nav-link border-0 active" id="main-settings-tab" data-toggle="pill" data-target="#main-settings" type="button" role="tab" aria-controls="main-settings" aria-selected="true">
                            <i class="fa fa-tools fa-fw fa-sm mr-1"></i> <?= l('signature_update.tab.main') ?>
                        </button>
                    </li>

                    <li class="nav-item mr-3 mb-3" role="presentation">
                        <button class="nav-link border-0" id="details-settings-tab" data-toggle="pill" data-target="#details-settings" type="button" role="tab" aria-controls="details-settings" aria-selected="false">
                            <i class="fa fa-signature fa-fw fa-sm mr-1"></i> <?= l('signature_update.tab.details') ?>
                        </button>
                    </li>

                    <li class="nav-item mr-3 mb-3" role="presentation">
                        <button class="nav-link border-0" id="customizations-settings-tab" data-toggle="pill" data-target="#customizations-settings" type="button" role="tab" aria-controls="customizations-settings" aria-selected="false">
                            <i class="fa fa-paint-brush fa-fw fa-sm mr-1"></i> <?= l('signature_update.tab.customizations') ?>
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="main-settings" role="tabpanel" aria-labelledby="main-settings-tab">
                        <div class="card">
                            <div class="card-body">

                                <div class="form-group">
                                    <label for="name"><i class="fa fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('global.name') ?></label>
                                    <input type="text" id="name" name="name" class="form-control <?= \Altum\Alerts::has_field_errors('name') ? 'is-invalid' : null ?>" value="<?= $data->signature->name ?>" maxlength="256" required="required" />
                                    <?= \Altum\Alerts::output_field_error('name') ?>
                                </div>

                                <div class="form-group">
                                    <label for="template"><i class="fa fa-fw fa-paint-roller fa-sm text-muted mr-1"></i> <?= l('signatures.input.template') ?></label>
                                    <div class="row btn-group-toggle" data-toggle="buttons">
                                        <?php foreach($data->signature_templates as $key => $value): ?>
                                            <div class="col-6">
                                                <label class="btn btn-light btn-block <?= $data->signature->template == $key ? 'selected="selected""' : null?>">
                                                    <input type="radio" name="template" value="<?= $key ?>" class="custom-control-input" <?= $data->signature->template == $key ? 'checked="checked"' : null ?> data-generate-preview />
                                                    <i class="<?= $value['icon'] ?> fa-fw fa-sm mr-1"></i> <?= l('signatures.input.template.' . $key) ?>
                                                </label>
                                            </div>
                                        <?php endforeach ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="direction"><i class="fa fa-fw fa-map-signs fa-sm text-muted mr-1"></i> <?= l('signatures.input.direction') ?></label>
                                    <div class="row btn-group-toggle" data-toggle="buttons">
                                        <div class="col-6">
                                            <label class="btn btn-gray-200 btn-block <?= ($data->signature->settings->direction  ?? null) == 'ltr' ? 'active"' : null?>">
                                                <input type="radio" name="direction" value="ltr" class="custom-control-input" <?= ($data->signature->settings->direction  ?? null) == 'ltr' ? 'checked="checked"' : null?> data-generate-preview />
                                                <i class="fa fa-fw fa-long-arrow-alt-right fa-sm mr-1"></i> <?= l('signatures.input.direction.ltr') ?>
                                            </label>
                                        </div>
                                        <div class="col-6">
                                            <label class="btn btn-gray-200 btn-block <?= ($data->signature->settings->direction  ?? null) == 'rtl' ? 'active' : null?>">
                                                <input type="radio" name="direction" value="rtl" class="custom-control-input" <?= ($data->signature->settings->direction  ?? null) == 'rtl' ? 'checked="checked"' : null?> data-generate-preview />
                                                <i class="fa fa-fw fa-long-arrow-alt-left fa-sm mr-1"></i> <?= l('signatures.input.direction.rtl') ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div <?= $this->user->plan_settings->removable_branding ? null : 'data-toggle="tooltip" title="' . l('global.info_message.plan_feature_no_access') . '"' ?>>
                                    <div class="form-group custom-control custom-switch <?= $this->user->plan_settings->removable_branding ? null : 'container-disabled' ?>">
                                        <input id="is_removed_branding" name="is_removed_branding" type="checkbox" class="custom-control-input" <?= $data->signature->settings->is_removed_branding ? 'checked="checked"' : null?> <?= $this->user->plan_settings->removable_branding ? null : 'disabled="disabled"' ?> data-generate-preview>
                                        <label class="custom-control-label" for="is_removed_branding"><?= l('signatures.input.is_removed_branding') ?></label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="d-flex flex-column flex-xl-row justify-content-between">
                                        <label for="project_id"><i class="fa fa-fw fa-sm fa-project-diagram text-muted mr-1"></i> <?= l('projects.project_id') ?></label>
                                        <a href="<?= url('project-create') ?>" target="_blank" class="small mb-2"><i class="fa fa-fw fa-sm fa-plus mr-1"></i> <?= l('projects.create') ?></a>
                                    </div>
                                    <select id="project_id" name="project_id" class="custom-select">
                                        <option value=""><?= l('global.none') ?></option>
                                        <?php foreach($data->projects as $project_id => $project): ?>
                                            <option value="<?= $project_id ?>" <?= $data->signature->project_id == $project_id ? 'selected="selected"' : null ?>><?= $project->name ?></option>
                                        <?php endforeach ?>
                                    </select>
                                    <small class="form-text text-muted"><?= l('projects.project_id_help') ?></small>
                                </div>

                                <button type="submit" name="submit" class="btn btn-block btn-primary"><?= l('global.update') ?></button>

                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="details-settings" role="tabpanel" aria-labelledby="details-settings-tab">
                        <div class="card">
                            <div class="card-body">
                                <button class="btn btn-block btn-gray-200 mb-4" type="button" data-toggle="collapse" data-target="#personal_container" aria-expanded="false" aria-controls="personal_container">
                                    <i class="fa fa-user fa-fw fa-sm mr-1"></i> <?= l('signature_update.tab.personal') ?>
                                </button>

                                <div class="collapse" id="personal_container">
                                    <div class="form-group">
                                        <label for="image_url"><i class="fa fa-fw fa-sm fa-image text-muted mr-1"></i> <?= l('signatures.input.image_url') ?></label>
                                        <input type="url" id="image_url" name="image_url" class="form-control <?= \Altum\Alerts::has_field_errors('image_url') ? 'is-invalid' : null ?>" value="<?= $data->signature->settings->image_url ?>" maxlength="1024" data-generate-preview />
                                        <?= \Altum\Alerts::output_field_error('image_url') ?>
                                        <small class="form-text text-muted"><?= l('signatures.input.image_url_help') ?></small>
                                    </div>

                                    <div class="form-group">
                                        <label for="sign_off"><i class="fa fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('signatures.input.sign_off') ?></label>
                                        <input type="text" id="sign_off" name="sign_off" class="form-control <?= \Altum\Alerts::has_field_errors('sign_off') ? 'is-invalid' : null ?>" value="<?= $data->signature->settings->sign_off ?>" maxlength="64" data-generate-preview />
                                        <?= \Altum\Alerts::output_field_error('sign_off') ?>
                                    </div>

                                    <div class="form-group">
                                        <label for="full_name"><i class="fa fa-fw fa-sm fa-user text-muted mr-1"></i> <?= l('signatures.input.full_name') ?></label>
                                        <input type="text" id="full_name" name="full_name" class="form-control <?= \Altum\Alerts::has_field_errors('full_name') ? 'is-invalid' : null ?>" value="<?= $data->signature->settings->full_name ?>" maxlength="64" data-generate-preview />
                                        <?= \Altum\Alerts::output_field_error('full_name') ?>
                                    </div>

                                    <div class="form-group">
                                        <label for="job_title"><i class="fa fa-fw fa-sm fa-user-tie text-muted mr-1"></i> <?= l('signatures.input.job_title') ?></label>
                                        <input type="text" id="job_title" name="job_title" class="form-control <?= \Altum\Alerts::has_field_errors('job_title') ? 'is-invalid' : null ?>" value="<?= $data->signature->settings->job_title ?>" maxlength="64" data-generate-preview />
                                        <?= \Altum\Alerts::output_field_error('job_title') ?>
                                    </div>

                                    <div class="form-group">
                                        <label for="department"><i class="fa fa-fw fa-sm fa-code-branch text-muted mr-1"></i> <?= l('signatures.input.department') ?></label>
                                        <input type="text" id="department" name="department" class="form-control <?= \Altum\Alerts::has_field_errors('department') ? 'is-invalid' : null ?>" value="<?= $data->signature->settings->department ?>" maxlength="64" data-generate-preview />
                                        <?= \Altum\Alerts::output_field_error('department') ?>
                                    </div>

                                    <div class="form-group">
                                        <label for="company"><i class="fa fa-fw fa-sm fa-building text-muted mr-1"></i> <?= l('signatures.input.company') ?></label>
                                        <input type="text" id="company" name="company" class="form-control <?= \Altum\Alerts::has_field_errors('company') ? 'is-invalid' : null ?>" value="<?= $data->signature->settings->company ?>" maxlength="64" data-generate-preview />
                                        <?= \Altum\Alerts::output_field_error('company') ?>
                                    </div>
                                </div>

                                <button class="btn btn-block btn-gray-200 mb-4" type="button" data-toggle="collapse" data-target="#contact_container" aria-expanded="false" aria-controls="contact_container">
                                    <i class="fa fa-address-card fa-fw fa-sm mr-1"></i> <?= l('signature_update.tab.contact') ?>
                                </button>

                                <div class="collapse" id="contact_container">
                                    <div class="form-group">
                                        <label for="email"><i class="fa fa-fw fa-sm fa-envelope text-muted mr-1"></i> <?= l('global.email') ?></label>
                                        <input type="email" id="email" name="email" class="form-control <?= \Altum\Alerts::has_field_errors('email') ? 'is-invalid' : null ?>" value="<?= $data->signature->settings->email ?>" maxlength="320" data-generate-preview />
                                        <?= \Altum\Alerts::output_field_error('email') ?>
                                    </div>

                                    <div class="form-group">
                                        <label for="website_url"><i class="fa fa-fw fa-sm fa-link text-muted mr-1"></i> <?= l('signatures.input.website_url') ?></label>
                                        <input type="url" id="website_url" name="website_url" class="form-control <?= \Altum\Alerts::has_field_errors('website_url') ? 'is-invalid' : null ?>" value="<?= $data->signature->settings->website_url ?>" maxlength="256" data-generate-preview />
                                        <?= \Altum\Alerts::output_field_error('website_url') ?>
                                    </div>

                                    <div class="form-group">
                                        <label for="address"><i class="fa fa-fw fa-sm fa-map-marker-alt text-muted mr-1"></i> <?= l('signatures.input.address') ?></label>
                                        <input type="text" id="address" name="address" class="form-control <?= \Altum\Alerts::has_field_errors('address') ? 'is-invalid' : null ?>" value="<?= $data->signature->settings->address ?>" maxlength="256" data-generate-preview />
                                        <?= \Altum\Alerts::output_field_error('address') ?>
                                    </div>

                                    <div class="form-group">
                                        <label for="address_url"><i class="fa fa-fw fa-sm fa-map-pin text-muted mr-1"></i> <?= l('signatures.input.address_url') ?></label>
                                        <input type="url" id="address_url" name="address_url" class="form-control <?= \Altum\Alerts::has_field_errors('address_url') ? 'is-invalid' : null ?>" value="<?= $data->signature->settings->address_url ?>" maxlength="512" data-generate-preview />
                                        <?= \Altum\Alerts::output_field_error('address_url') ?>
                                    </div>

                                    <div class="form-group">
                                        <label for="phone_number"><i class="fa fa-fw fa-sm fa-phone-square-alt text-muted mr-1"></i> <?= l('signatures.input.phone_number') ?></label>
                                        <input type="text" id="phone_number" name="phone_number" class="form-control <?= \Altum\Alerts::has_field_errors('phone_number') ? 'is-invalid' : null ?>" value="<?= $data->signature->settings->phone_number ?>" maxlength="32" data-generate-preview />
                                        <?= \Altum\Alerts::output_field_error('phone_number') ?>
                                    </div>

                                    <div class="form-group">
                                        <label for="whatsapp"><i class="fab fa-fw fa-sm fa-whatsapp text-muted mr-1"></i> <?= l('signatures.input.whatsapp') ?></label>
                                        <input type="text" id="whatsapp" name="whatsapp" class="form-control <?= \Altum\Alerts::has_field_errors('whatsapp') ? 'is-invalid' : null ?>" value="<?= $data->signature->settings->whatsapp ?>" maxlength="32" data-generate-preview />
                                        <?= \Altum\Alerts::output_field_error('whatsapp') ?>
                                    </div>

                                    <div class="form-group">
                                        <label for="facebook_messenger"><i class="fab fa-fw fa-sm fa-facebook text-muted mr-1"></i> <?= l('signatures.input.facebook_messenger') ?></label>
                                        <input type="text" id="facebook_messenger" name="facebook_messenger" class="form-control <?= \Altum\Alerts::has_field_errors('facebook_messenger') ? 'is-invalid' : null ?>" value="<?= $data->signature->settings->facebook_messenger ?>" maxlength="64" data-generate-preview />
                                        <?= \Altum\Alerts::output_field_error('facebook_messenger') ?>
                                    </div>

                                    <div class="form-group">
                                        <label for="telegram"><i class="fab fa-fw fa-sm fa-telegram text-muted mr-1"></i> <?= l('signatures.input.telegram') ?></label>
                                        <input type="text" id="telegram" name="telegram" class="form-control <?= \Altum\Alerts::has_field_errors('telegram') ? 'is-invalid' : null ?>" value="<?= $data->signature->settings->telegram ?>" maxlength="64" data-generate-preview />
                                        <?= \Altum\Alerts::output_field_error('telegram') ?>
                                    </div>
                                </div>

                                <button class="btn btn-block btn-gray-200 mb-4" type="button" data-toggle="collapse" data-target="#social_container" aria-expanded="false" aria-controls="social_container">
                                    <i class="fa fa-share-square fa-fw fa-sm mr-1"></i> <?= l('signature_update.tab.social') ?>
                                </button>

                                <div class="collapse" id="social_container">
                                    <?php foreach($data->signature_socials as $key => $social): ?>
                                        <?php if($social['input_display_format']): ?>
                                            <div class="form-group">
                                                <label for="<?= $key ?>"><i class="<?= $social['icon'] ?> fa-fw fa-sm text-muted mr-1"></i> <?= l('signatures.input.' . $key) ?></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><?= remove_url_protocol_from_url(sprintf($social['format'], '')) ?></span>
                                                    </div>
                                                    <input type="<?= $social['value_type'] ?>" id="<?= $key ?>" name="<?= $key ?>" class="form-control <?= \Altum\Alerts::has_field_errors($key) ? 'is-invalid' : null ?>" value="<?= $data->signature->settings->{$key} ?>" maxlength="<?= $social['value_max_length'] ?>" data-generate-preview />
                                                    <?= \Altum\Alerts::output_field_error($key) ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="form-group">
                                                <label for="<?= $key ?>"><i class="<?= $social['icon'] ?> fa-fw fa-sm text-muted mr-1"></i> <?= l('signatures.input.' . $key) ?></label>
                                                <input type="<?= $social['value_type'] ?>" id="<?= $key ?>" name="<?= $key ?>" class="form-control <?= \Altum\Alerts::has_field_errors($key) ? 'is-invalid' : null ?>" value="<?= $data->signature->settings->{$key} ?>" maxlength="<?= $social['value_max_length'] ?>" data-generate-preview />
                                                <?= \Altum\Alerts::output_field_error($key) ?>
                                            </div>
                                        <?php endif ?>
                                    <?php endforeach ?>
                                </div>

                                <button class="btn btn-block btn-gray-200 mb-4" type="button" data-toggle="collapse" data-target="#disclaimer_container" aria-expanded="false" aria-controls="disclaimer_container">
                                    <i class="fa fa-exclamation-circle fa-fw fa-sm mr-1"></i> <?= l('signature_update.tab.disclaimer') ?>
                                </button>

                                <div class="collapse" id="disclaimer_container">
                                    <div class="form-group">
                                        <label for="disclaimer"><i class="fa fa-fw fa-sm fa-exclamation-circle text-muted mr-1"></i> <?= l('signatures.input.disclaimer') ?></label>
                                        <textarea id="disclaimer" name="disclaimer" class="form-control <?= \Altum\Alerts::has_field_errors('disclaimer') ? 'is-invalid' : null ?>" maxlength="1024" data-generate-preview><?= $data->signature->settings->disclaimer ?></textarea>
                                        <?= \Altum\Alerts::output_field_error('disclaimer') ?>
                                    </div>
                                </div>

                                <button type="submit" form="update" name="submit" class="btn btn-block btn-primary"><?= l('global.update') ?></button>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="customizations-settings" role="tabpanel" aria-labelledby="customizations-settings-tab">
                        <div class="card">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="font_family"><i class="fa fa-fw fa-pen-nib fa-sm text-muted mr-1"></i> <?= l('signatures.input.font_family') ?></label>
                                    <select id="font_family" name="font_family" class="custom-select" data-generate-preview>
                                        <?php foreach($data->signature_fonts as $key => $value): ?>
                                            <option value="<?= $key ?>" <?= $data->signature->settings->font_family == $key ? 'selected="selected"' : null?>><?= $value['name'] ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="font_size"><i class="fa fa-fw fa-font fa-sm text-muted mr-1"></i> <?= l('signatures.input.font_size') ?></label>
                                    <div class="input-group">
                                        <input id="font_size" type="number" min="12" max="18" name="font_size" class="form-control" value="<?= $data->signature->settings->font_size ?>" data-generate-preview />
                                        <div class="input-group-append">
                                            <span class="input-group-text">px</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="width"><i class="fa fa-fw fa-arrows-alt-h fa-sm text-muted mr-1"></i> <?= l('signatures.input.width') ?></label>
                                    <div class="input-group">
                                        <input id="width" type="number" min="300" max="600" name="width" class="form-control" value="<?= $data->signature->settings->width ?>" data-generate-preview />
                                        <div class="input-group-append">
                                            <span class="input-group-text">px</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="image_width"><i class="fa fa-fw fa-arrows-alt-h fa-sm text-muted mr-1"></i> <?= l('signatures.input.image_width') ?></label>
                                    <div class="input-group">
                                        <input id="image_width" type="number" min="45" max="150" name="image_width" class="form-control" value="<?= $data->signature->settings->image_width ?>" data-generate-preview />
                                        <div class="input-group-append">
                                            <span class="input-group-text">px</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="image_border_radius"><i class="fa fa-fw fa-border-style fa-sm text-muted mr-1"></i> <?= l('signatures.input.image_border_radius') ?></label>
                                    <div class="input-group">
                                        <input id="image_border_radius" type="number" min="0" max="100" name="image_border_radius" class="form-control" value="<?= $data->signature->settings->image_border_radius ?>" data-generate-preview />
                                        <div class="input-group-append">
                                            <span class="input-group-text">px</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="socials_width"><i class="fa fa-fw fa-arrows-alt-h fa-sm text-muted mr-1"></i> <?= l('signatures.input.socials_width') ?></label>
                                    <div class="input-group">
                                        <input id="socials_width" type="number" min="15" max="30" name="socials_width" class="form-control" value="<?= $data->signature->settings->socials_width ?>" data-generate-preview />
                                        <div class="input-group-append">
                                            <span class="input-group-text">px</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="socials_padding"><i class="fa fa-fw fa-arrows-alt-h fa-sm text-muted mr-1"></i> <?= l('signatures.input.socials_padding') ?></label>
                                    <div class="input-group">
                                        <input id="socials_padding" type="number" min="5" max="15" name="socials_padding" class="form-control" value="<?= $data->signature->settings->socials_padding ?>" data-generate-preview />
                                        <div class="input-group-append">
                                            <span class="input-group-text">px</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="separator_size"><i class="fa fa-fw fa-arrows-alt-h fa-sm text-muted mr-1"></i> <?= l('signatures.input.separator_size') ?></label>
                                    <div class="input-group">
                                        <input id="separator_size" type="number" min="0" max="5" name="separator_size" class="form-control" value="<?= $data->signature->settings->separator_size ?>" data-generate-preview />
                                        <div class="input-group-append">
                                            <span class="input-group-text">px</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label><i class="fa fa-fw fa-swatchbook fa-sm text-muted mr-1"></i> <?= l('signatures.input.theme_color') ?></label>
                                    <input id="theme_color" type="hidden" name="theme_color" class="form-control" value="<?= $data->signature->settings->theme_color ?>" required="required" data-generate-preview data-color-picker />
                                </div>

                                <div class="form-group">
                                    <label><i class="fa fa-fw fa-paint-brush fa-sm text-muted mr-1"></i> <?= l('signatures.input.full_name_color') ?></label>
                                    <input id="full_name_color" type="hidden" name="full_name_color" class="form-control" value="<?= $data->signature->settings->full_name_color ?>" required="required" data-generate-preview data-color-picker />
                                </div>

                                <div class="form-group">
                                    <label><i class="fa fa-fw fa-palette fa-sm text-muted mr-1"></i> <?= l('signatures.input.text_color') ?></label>
                                    <input id="text_color" type="hidden" name="text_color" class="form-control" value="<?= $data->signature->settings->text_color ?>" required="required" data-generate-preview data-color-picker />
                                </div>

                                <div class="form-group">
                                    <label><i class="fa fa-fw fa-tint fa-sm text-muted mr-1"></i> <?= l('signatures.input.link_color') ?></label>
                                    <input id="link_color" type="hidden" name="link_color" class="form-control" value="<?= $data->signature->settings->link_color ?>" required="required" data-generate-preview data-color-picker />
                                </div>

                                <button type="submit" form="update" name="submit" class="btn btn-block btn-primary"><?= l('global.update') ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col col-xl-7">
                <ul class="nav nav-pills" role="tablist">
                    <li class="nav-item mr-3 mb-3" role="presentation">
                        <button class="nav-link border-0 active" id="preview-settings-tab" data-toggle="pill" data-target="#preview-settings" type="button" role="tab" aria-controls="preview-settings" aria-selected="true">
                            <i class="fa fa-eye fa-fw fa-sm mr-1"></i> <?= l('signature_update.tab.preview') ?>
                        </button>
                    </li>

                    <li class="nav-item mr-3 mb-3" role="presentation">
                        <button class="nav-link border-0" id="code-settings-tab" data-toggle="pill" data-target="#code-settings" type="button" role="tab" aria-controls="code-settings" aria-selected="false">
                            <i class="fa fa-code fa-fw fa-sm mr-1"></i> <?= l('signature_update.tab.code') ?>
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="preview-settings" role="tabpanel" aria-labelledby="preview-settings-tab">
                        <button
                                id="copy"
                                type="button"
                                class="btn btn-block btn-secondary mb-4"
                                data-toggle="tooltip"
                                title="<?= l('global.clipboard_copy') ?>"
                                aria-label="<?= l('global.clipboard_copy') ?>"
                                data-copy="<?= l('global.clipboard_copy') ?>"
                                data-copied="<?= l('global.clipboard_copied') ?>"
                                data-clipboard-text
                                data-clipboard-target="#preview"
                        >
                            <i class="fa fa-fw fa-sm fa-copy"></i> <?= l('signatures.copy') ?>
                        </button>

                        <div class="card">
                            <div class="card-body">
                                <div class="mb-4">
                                    <span class="text-gray-500">
                                        <strong><?= l('signature_update.to') ?></strong>
                                        <?= l('signature_update.to_value') ?>
                                    </span>
                                    <hr class="my-3">

                                    <span class="text-gray-500">
                                        <strong><?= l('signature_update.subject') ?></strong>
                                        <?= l('signature_update.subject_value') ?>
                                    </span>
                                    <hr class="my-3">

                                    <span class="text-muted"><?= l('signature_update.body_value') ?></span>
                                </div>

                                <div id="preview"></div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="code-settings" role="tabpanel" aria-labelledby="code-settings-tab">
                        <button
                                id="copy"
                                type="button"
                                class="btn btn-block btn-secondary mb-4"
                                data-toggle="tooltip"
                                title="<?= l('global.clipboard_copy') ?>"
                                aria-label="<?= l('global.clipboard_copy') ?>"
                                data-copy="<?= l('global.clipboard_copy') ?>"
                                data-copied="<?= l('global.clipboard_copied') ?>"
                                data-clipboard-text
                                data-clipboard-target="#code"
                        >
                            <i class="fa fa-fw fa-sm fa-copy"></i> <?= l('signatures.copy_code') ?>
                        </button>

                        <div class="card">
                            <div class="card-body">
                                <div id="code" class="text-break"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<div id="templates" class="d-none">
    <?php foreach($data->signature_templates as $key => $value): ?>
        <div id="<?= 'template_' . $key ?>"><?php include \Altum\Plugin::get('email-signatures')->path . 'views/signatures/templates/' . $key . '.php' ?></div>
    <?php endforeach ?>
</div>

<?php ob_start() ?>
<script>
    'use strict';

    let generate_preview = () => {
        /* Get current template */
        let template = document.querySelector('input[name="template"]:checked').value;

        /* Create new element for the preview */
        let preview = document.createElement('div');
        preview.innerHTML = document.querySelector(`#template_${template}`).innerHTML.trim();

        /* Get direction */
        let direction = document.querySelector('input[name="direction"]:checked').value;
        preview.innerHTML = preview.innerHTML.replaceAll(`{{DIRECTION}}`, direction);

        /* Variable to store current values */
        let current_values = {};

        /* Go through all keys automatically */
        ['image_url', 'sign_off', 'full_name', 'job_title', 'department', 'company', 'email', 'website_url', 'address', 'address_url', 'phone_number', 'whatsapp', 'facebook_messenger', 'telegram', <?= '\'' . implode('\', \'', array_keys($data->signature_socials)) . '\'' ?>, 'disclaimer', 'font_family', 'font_size', 'width', 'image_width', 'image_border_radius', 'socials_width', 'socials_padding', 'separator_size', 'theme_color', 'full_name_color', 'text_color', 'link_color'].forEach(key => {
            current_values[key] = document.querySelector(`#${key}`).value;
            if(current_values[key]) {
                preview.innerHTML = preview.innerHTML.replaceAll(`{{${key.toUpperCase()}}}`, current_values[key]);
            } else {
                preview.querySelector(`#signature_${key}`) && preview.querySelector(`#signature_${key}`).remove();
            }
        });

        /* Company wrapper */
        if(!current_values.job_title && !current_values.department && !current_values.company) {
            preview.querySelector('#signature_company_wrapper') && preview.querySelector('#signature_company_wrapper').remove();
        }

        /* Socials wrappers */
        let socials_wrapper_should_display = false;
        [<?= '\'' . implode('\', \'', array_keys($data->signature_socials)) . '\'' ?>].forEach(key => {
            if(current_values[key]) socials_wrapper_should_display = true;
        });

        if(!socials_wrapper_should_display) {
            preview.querySelector('#signature_socials_wrapper') && preview.querySelector('#signature_socials_wrapper').remove();
        }

        /* Branding */
        let is_removed_branding = document.querySelector('#is_removed_branding').checked;
        if(is_removed_branding) {
            preview.querySelector('#signature_branding') && preview.querySelector('#signature_branding').remove();
        } else {
            preview.innerHTML = preview.innerHTML.replaceAll(`{{BRANDING}}`, <?= json_encode(settings()->signatures->branding) ?>);
        }

        /* Display */
        document.querySelector('#preview').innerHTML = preview.innerHTML;

        /* Code */
        if(template == 'plain_text') {
            /* Only display text for this template */
            document.querySelector('#code').innerHTML = document.querySelector('#preview').innerHTML.trim();
        } else {
            /* Display HTML code */
            document.querySelector('#code').innerText = preview.innerHTML.trim();
        }

        /* Remove preview element */
        preview.remove();
    }

    generate_preview();

    let apply_generate_preview_event_listeners = () => {
        document.querySelectorAll('[data-generate-preview]').forEach(element => {
            ['change', 'paste', 'keyup'].forEach(event_type => {
                element.removeEventListener(event_type, generate_preview);
                element.addEventListener(event_type, generate_preview);
            })
        });
    }

    apply_generate_preview_event_listeners();
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>


<?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
    'name' => 'signature',
    'resource_id' => 'signature_id',
    'has_dynamic_resource_name' => true,
    'path' => 'signatures/delete'
]), 'modals'); ?>

<?php include_view(THEME_PATH . 'views/partials/clipboard_js.php') ?>

<?php include_view(THEME_PATH . 'views/partials/color_picker_js.php') ?>
