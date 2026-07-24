<?php defined('ALTUMCODE') || die() ?>


<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <nav aria-label="breadcrumb">
        <ol class="custom-breadcrumbs small">
            <li>
                <a href="<?= url('images') ?>"><?= l('images.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
            </li>
            <li class="active" aria-current="page"><?= l('image_create.breadcrumb') ?></li>
        </ol>
    </nav>

    <h1 class="h4 text-truncate mb-4"><i class="fas fa-fw fa-xs fa-icons mr-1"></i> <?= l('image_create.header') ?></h1>

    <div class="card">
        <div class="card-body">

            <form id="image_create" action="" method="post" role="form">
                <input type="hidden" name="global_token" value="<?= \Altum\Csrf::get('global_token') ?>" />

                <div class="notification-container"></div>

                <div class="form-group">
                    <label for="name"><i class="fas fa-fw fa-signature fa-sm text-muted mr-1"></i> <?= l('global.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control" value="<?= $data->values['name'] ?>" required="required" />
                </div>

                <div class="form-group" data-character-counter="textarea">
                    <label for="input" class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-fw fa-paragraph fa-sm text-muted mr-1"></i> <?= l('images.input') ?></span>
                        <small class="text-muted" data-character-counter-wrapper></small>
                    </label>
                    <textarea id="input" name="input" class="form-control" minlength="3" maxlength="<?= $data->ai_model['max_length'] ?>" placeholder="<?= l('images.input_placeholder') ?>" required="required"><?= $data->values['input'] ?? null ?></textarea>
                    <small class="form-text text-muted"><?= l('images.input_help') ?></small>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="style"><i class="fas fa-fw fa-paint-brush fa-sm text-muted mr-1"></i> <?= l('images.style') ?></label>
                            <select id="style" name="style" class="custom-select">
                                <option value="" <?= $data->values['style'] == '' ? 'selected="selected"' : null ?>><?= l('global.none') ?></option>
                                <?php foreach($data->ai_images_styles as $key => $value): ?>
                                    <option value="<?= $key ?>" <?= $data->values['style'] == $key ? 'selected="selected"' : null ?>><?= l('images.style.' . $key) ?></option>
                                <?php endforeach ?>
                            </select>
                            <small class="form-text text-muted"><?= l('images.style_help') ?></small>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="artist"><i class="fas fa-fw fa-user fa-sm text-muted mr-1"></i> <?= l('images.artist') ?></label>
                            <select id="artist" name="artist" class="custom-select">
                                <option value="" <?= $data->values['artist'] == '' ? 'selected="selected"' : null ?>><?= l('global.none') ?></option>
                                <?php foreach(settings()->aix->images_available_artists as $artist): ?>
                                    <option value="<?= $artist ?>" <?= $data->values['artist'] == $artist ? 'selected="selected"' : null ?>><?= $artist ?></option>
                                <?php endforeach ?>
                            </select>
                            <small class="form-text text-muted"><?= l('images.artist_help') ?></small>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="lighting"><i class="fas fa-fw fa-bolt fa-sm text-muted mr-1"></i> <?= l('images.lighting') ?></label>
                            <select id="lighting" name="lighting" class="custom-select">
                                <option value="" <?= $data->values['lighting'] == '' ? 'selected="selected"' : null ?>><?= l('global.none') ?></option>
                                <?php foreach($data->ai_images_lighting as $key => $value): ?>
                                    <option value="<?= $key ?>" <?= $data->values['lighting'] == $key ? 'selected="selected"' : null ?>><?= l('images.lighting.' . $key) ?></option>
                                <?php endforeach ?>
                            </select>
                            <small class="form-text text-muted"><?= l('images.lighting_help') ?></small>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="mood"><i class="fas fa-fw fa-smile fa-sm text-muted mr-1"></i> <?= l('images.mood') ?></label>
                            <select id="mood" name="mood" class="custom-select">
                                <option value="" <?= $data->values['mood'] == '' ? 'selected="selected"' : null ?>><?= l('global.none') ?></option>
                                <?php foreach($data->ai_images_moods as $key => $value): ?>
                                    <option value="<?= $key ?>" <?= $data->values['mood'] == $key ? 'selected="selected"' : null ?>><?= l('images.mood.' . $key) ?></option>
                                <?php endforeach ?>
                            </select>
                            <small class="form-text text-muted"><?= l('images.mood_help') ?></small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="size"><i class="fas fa-fw fa-expand-arrows-alt fa-sm text-muted mr-1"></i> <?= l('images.size') ?></label>
                    <div class="row btn-group-toggle" data-toggle="buttons">
                        <?php foreach($data->ai_model['sizes'] as $key): ?>
                            <div class="col-4">
                                <label class="btn btn-light btn-block">
                                    <input type="radio" name="size" value="<?= $key ?>" class="custom-control-input" <?= $data->values['size'] == $key ? 'checked="checked"' : null ?> />
                                    <?= $key ?>
                                </label>
                            </div>
                        <?php endforeach ?>
                    </div>
                    <small class="form-text text-muted"><?= l('images.size_help') ?></small>
                </div>

                <button class="btn btn-block btn-gray-200 my-4" type="button" data-toggle="collapse" data-target="#advanced_container" aria-expanded="false" aria-controls="advanced_container">
                    <i class="fas fa-fw fa-user-tie fa-sm mr-1"></i> <?= l('images.advanced') ?>
                </button>

                <div class="collapse" id="advanced_container">
                    <div class="form-group">
                        <label for="variants"><i class="fas fa-fw fa-list-ol fa-sm text-muted mr-1"></i> <?= l('images.variants') ?></label>
                        <div class="row btn-group-toggle" data-toggle="buttons">
                            <?php foreach($data->ai_model['variants'] as $key): ?>
                                <div class="col-12 col-lg-3">
                                    <label class="btn btn-light btn-block">
                                        <input type="radio" name="variants" value="<?= $key ?>" class="custom-control-input" <?= $data->values['variants'] == $key ? 'checked="checked"' : null ?> />
                                        <?= sprintf(l('images.x_variants'), $key) ?>
                                    </label>
                                </div>
                            <?php endforeach ?>
                        </div>
                        <small class="form-text text-muted"><?= l('images.variants_help') ?></small>
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

                <button type="submit" name="submit" class="btn btn-block btn-primary" data-is-ajax><?= l('global.create') ?></button>
            </form>

        </div>
    </div>
</div>

<?php ob_start() ?>
<script>
    /* Form submission */
    document.querySelector('#image_create').addEventListener('submit', async event => {
        event.preventDefault();

        pause_submit_button(document.querySelector('#image_create'));

        /* Notification container */
        let notification_container = event.currentTarget.querySelector('.notification-container');
        notification_container.innerHTML = '';

        /* Prepare form data */
        let form = new FormData(document.querySelector('#image_create'));

        /* Send request to server */
        let response = await fetch(`${url}image-create/create_ajax`, {
            method: 'post',
            body: form
        });

        let data = null;
        try {
            data = await response.json();
        } catch (error) {
            enable_submit_button(document.querySelector('#image_create'));
            display_notifications(<?= json_encode(l('global.error_message.basic')) ?>, 'error', notification_container);
            notification_container.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        if(!response.ok) {
            enable_submit_button(document.querySelector('#image_create'));
            display_notifications(<?= json_encode(l('global.error_message.basic')) ?>, 'error', notification_container);
            notification_container.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        if (data.status == 'error') {
            enable_submit_button(document.querySelector('#image_create'));
            display_notifications(data.message, 'error', notification_container);
            notification_container.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else if (data.status == 'success') {
            /* Redirect */
            redirect(data.details.url, true);
        }
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

