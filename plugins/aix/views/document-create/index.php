<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <nav aria-label="breadcrumb">
        <ol class="custom-breadcrumbs small">
            <li>
                <a href="<?= url('documents') ?>"><?= l('documents.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
            </li>
            <li class="active" aria-current="page"><?= l('document_create.breadcrumb') ?></li>
        </ol>
    </nav>

    <h1 class="h4 text-truncate mb-4"><i class="fas fa-fw fa-xs fa-robot mr-1"></i> <?= l('document_create.header') ?></h1>

    <div class="card">
        <div class="card-body">

            <form id="document_create" action="" method="post" role="form">
                <input type="hidden" name="global_token" value="<?= \Altum\Csrf::get('global_token') ?>" />

                <div class="notification-container"></div>

                <div class="form-group">
                    <label for="name"><i class="fas fa-fw fa-signature fa-sm text-muted mr-1"></i> <?= l('global.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control" value="<?= $data->values['name'] ?>" required="required" />
                </div>

                <div class="form-group">
                    <label for="type"><i class="fas fa-fw fa-moon fa-sm text-muted mr-1"></i> <?= l('global.type') ?></label>
                    <select id="type" name="type" class="custom-select">
                        <?php foreach($data->templates_categories as $category_id => $category): ?>
                            <optgroup label="<?= $category->emoji ?> <?= $category->settings->translations->{\Altum\Language::$name}->name ?>">
                                <?php foreach($data->templates as $template_id => $template): ?>
                                    <?php if($template->template_category_id != $category_id) continue ?>

                                    <option value="<?= $template_id ?>" <?= $data->values['type'] == $template_id ? 'selected="selected"' : null ?>><?= $template->settings->translations->{\Altum\Language::$name}->name ?></option>
                                <?php endforeach ?>
                            </optgroup>
                        <?php endforeach ?>
                    </select>
                </div>

                <?php foreach($data->templates as $template_id => $template): ?>
                    <?php foreach($template->settings->inputs as $input_key => $input): ?>
                        <div class="form-group" data-type="<?= $template_id ?>">
                            <label for="<?= $template_id . '_' . $input_key ?>"><i class="<?= $input->icon ?> fa-sm text-muted mr-1"></i> <?= $input->translations->{\Altum\Language::$name}->name ?></label>

                            <?php if($input->type == 'input'): ?>
                                <input id="<?= $template_id . '_' . $input_key ?>" name="<?= $template_id . '_' . $input_key ?>" class="form-control" value="<?= $data->values[$template_id . '_' . $input_key] ?? null ?>" placeholder="<?= $input->translations->{\Altum\Language::$name}->placeholder ?>" required="required" />
                            <?php elseif($input->type == 'textarea'): ?>
                                <textarea id="<?= $template_id . '_' . $input_key ?>" name="<?= $template_id . '_' . $input_key ?>" class="form-control" rows="5" placeholder="<?= $input->translations->{\Altum\Language::$name}->placeholder ?>" required="required"><?= $data->values[$template_id . '_' . $input_key] ?? null ?></textarea>
                            <?php endif ?>

                            <?php if($input->translations->{\Altum\Language::$name}->help): ?>
                                <small class="form-text text-muted"><?= $input->translations->{\Altum\Language::$name}->help ?></small>
                            <?php endif ?>
                        </div>
                    <?php endforeach ?>
                <?php endforeach ?>

                <div class="form-group">
                    <label for="language"><i class="fas fa-fw fa-language fa-sm text-muted mr-1"></i> <?= l('global.language') ?></label>
                    <select id="language" name="language" class="custom-select">
                        <?php foreach(settings()->aix->documents_available_languages as $key): ?>
                            <option value="<?= $key ?>" <?= $data->values['language'] == $key ? 'selected="selected"' : null ?>><?= $key ?></option>
                        <?php endforeach ?>
                    </select>
                    <small class="form-text text-muted"><?= l('documents.language_help') ?></small>
                </div>

                <button class="btn btn-block btn-gray-200 my-4" type="button" data-toggle="collapse" data-target="#advanced_container" aria-expanded="false" aria-controls="advanced_container">
                    <i class="fas fa-fw fa-user-tie fa-sm mr-1"></i> <?= l('documents.advanced') ?>
                </button>

                <div class="collapse" id="advanced_container">
                    <div class="form-group">
                        <label for="creativity_level"><i class="fas fa-fw fa-lightbulb fa-sm text-muted mr-1"></i> <?= l('documents.creativity_level') ?></label>
                        <div class="row btn-group-toggle" data-toggle="buttons">
                            <?php foreach(['none', 'low', 'optimal', 'high', 'maximum', 'custom'] as $key): ?>
                                <div class="col-12 col-lg-4">
                                    <label class="btn btn-light btn-block">
                                        <input type="radio" name="creativity_level" value="<?= $key ?>" class="custom-control-input" <?= $data->values['creativity_level'] == $key ? 'checked="checked"' : null ?> />
                                        <?= l('documents.creativity_level.' . $key) ?>
                                    </label>
                                </div>
                            <?php endforeach ?>
                        </div>
                        <small class="form-text text-muted"><?= l('documents.creativity_level_help') ?></small>
                    </div>

                    <div class="form-group" data-creativity-level="custom">
                        <label for="creativity_level_custom"><i class="fas fa-fw fa-hat-wizard fa-sm text-muted mr-1"></i> <?= l('documents.creativity_level_custom') ?></label>
                        <input type="number" id="creativity_level_custom" min="0" max="2" step="0.1" name="creativity_level_custom" class="form-control" value="<?= $data->values['creativity_level_custom'] ?? 0.8 ?>" />
                        <small class="form-text text-muted"><?= l('documents.creativity_level_custom_help') ?></small>
                    </div>

                    <div class="form-group">
                        <label for="variants"><i class="fas fa-fw fa-list-ol fa-sm text-muted mr-1"></i> <?= l('documents.variants') ?></label>
                        <div class="row btn-group-toggle" data-toggle="buttons">
                            <?php foreach([1,2,3] as $key): ?>
                                <div class="col-12 col-lg-4">
                                    <label class="btn btn-light btn-block">
                                        <input type="radio" name="variants" value="<?= $key ?>" class="custom-control-input" <?= $data->values['variants'] == $key ? 'checked="checked"' : null ?> />
                                        <?= sprintf(l('documents.x_variants'), $key) ?>
                                    </label>
                                </div>
                            <?php endforeach ?>
                        </div>
                        <small class="form-text text-muted"><?= l('documents.variants_help') ?></small>
                    </div>

                    <div class="form-group">
                        <label for="max_words_per_variant"><i class="fas fa-fw fa-keyboard fa-sm text-muted mr-1"></i> <?= l('documents.max_words_per_variant') ?></label>
                        <div class="input-group">
                            <input type="number" min="1" <?= $this->user->plan_settings->words_per_month_limit == -1 ? null : 'max="' . $data->available_words . '"' ?> id="max_words_per_variant" name="max_words_per_variant" class="form-control" value="<?= $data->values['max_words_per_variant'] ?>" />
                            <div class="input-group-append">
                                <span class="input-group-text"><?= sprintf(l('documents.x_words_available'), ($this->user->plan_settings->words_per_month_limit == -1 ? l('global.unlimited') : $data->available_words)) ?></span>
                            </div>
                        </div>
                        <small class="form-text text-muted"><?= l('documents.max_words_per_variant_help') ?></small>
                    </div>

                    <div class="form-group">
                        <div class="d-flex flex-column flex-xl-row justify-content-between">
                            <label for="project_id"><i class="fas fa-fw fa-sm fa-project-diagram text-muted mr-1"></i> <?= l('projects.project_id') ?></label>
                            <a href="<?= url('project-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('projects.create') ?></a>
                        </div>
                        <select id="project_id" name="project_id" class="custom-select">
                            <option value=""><?= l('global.none') ?></option>
                            <?php foreach($data->projects as $project_id => $project): ?>
                                <option value="<?= $project_id ?>" <?= $data->values['project_id'] == $project_id ? 'selected="selected"' : null ?>><?= $project->name ?></option>
                            <?php endforeach ?>
                        </select>
                        <small class="form-text text-muted"><?= l('projects.project_id_help') ?></small>
                    </div>
                </div>

                <button type="submit" name="submit" class="btn btn-block btn-primary" data-is-ajax data-is-ajax-full><?= l('global.create') ?></button>
            </form>

        </div>
    </div>
</div>

<?php ob_start() ?>
<script>
    /* Form submission */
    document.querySelector('#document_create').addEventListener('submit', async event => {
        event.preventDefault();

        pause_submit_button(document.querySelector('#document_create'));

        /* Notification container */
        let notification_container = event.currentTarget.querySelector('.notification-container');
        notification_container.innerHTML = '';

        /* Prepare form data */
        let form = new FormData(document.querySelector('#document_create'));

        /* Send request to server */
        let response = await fetch(`${url}document-create/create_ajax`, {
            method: 'post',
            body: form
        });

        let data = null;
        try {
            data = await response.json();
        } catch (error) {
            enable_submit_button(document.querySelector('#document_create'));
            display_notifications(<?= json_encode(l('global.error_message.basic')) ?>, 'error', notification_container);
            notification_container.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        if(!response.ok) {
            enable_submit_button(document.querySelector('#document_create'));
            display_notifications(<?= json_encode(l('global.error_message.basic')) ?>, 'error', notification_container);
            notification_container.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        if (data.status == 'error') {
            enable_submit_button(document.querySelector('#document_create'));
            display_notifications(data.message, 'error', notification_container);
            notification_container.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else if (data.status == 'success') {
            /* Redirect */
            redirect(data.details.url, true);
        }
    });
</script>

<script>
    'use strict';

    type_handler('[name="type"]', 'data-type');
    document.querySelector('[name="type"]') && document.querySelectorAll('[name="type"]').forEach(element => element.addEventListener('change', () => { type_handler('[name="type"]', 'data-type'); }));

    type_handler('[name="creativity_level"]', 'data-creativity-level');
    document.querySelector('[name="creativity_level"]') && document.querySelectorAll('[name="creativity_level"]').forEach(element => element.addEventListener('change', () => { type_handler('[name="creativity_level"]', 'data-creativity-level'); }));

</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

