<?php defined('ALTUMCODE') || die() ?>


<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <nav aria-label="breadcrumb">
        <ol class="custom-breadcrumbs small">
            <li>
                <a href="<?= url('transcriptions') ?>"><?= l('transcriptions.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
            </li>
            <li class="active" aria-current="page"><?= l('transcription_create.breadcrumb') ?></li>
        </ol>
    </nav>

    <h1 class="h4 text-truncate mb-4"><i class="fas fa-fw fa-xs fa-microphone-lines mr-1"></i> <?= l('transcription_create.header') ?></h1>

    <div class="card">
        <div class="card-body">

            <form id="transcription_create" action="" method="post" role="form" enctype="multipart/form-data">
                <input type="hidden" name="global_token" value="<?= \Altum\Csrf::get('global_token') ?>" />

                <div class="notification-container"></div>

                <div class="form-group">
                    <label for="name"><i class="fas fa-fw fa-signature fa-sm text-muted mr-1"></i> <?= l('global.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control" value="<?= $data->values['name'] ?>" required="required" />
                </div>

                <div class="form-group">
                    <label for="file"><i class="fas fa-fw fa-sm fa-file-audio text-muted mr-1"></i> <?= l('transcriptions.file') ?></label>
                    <input id="file" type="file" name="file" accept="<?= \Altum\Uploads::get_whitelisted_file_extensions_accept('transcriptions') ?>" class="form-control-file altum-file-input" required="required" />
                    <small class="form-text text-muted"><?= sprintf(l('global.accessibility.whitelisted_file_extensions'), \Altum\Uploads::get_whitelisted_file_extensions_accept('transcriptions')) . ' ' . sprintf(l('global.accessibility.file_size_limit'), $this->user->plan_settings->transcriptions_file_size_limit) ?></small>
                </div>

                <button class="btn btn-block btn-gray-200 my-4" type="button" data-toggle="collapse" data-target="#advanced_container" aria-expanded="false" aria-controls="advanced_container">
                    <i class="fas fa-fw fa-user-tie fa-sm mr-1"></i> <?= l('transcriptions.advanced') ?>
                </button>

                <div class="collapse" id="advanced_container">
                    <div class="form-group">
                        <label for="input"><i class="fas fa-fw fa-paragraph fa-sm text-muted mr-1"></i> <?= l('transcriptions.input') ?></label>
                        <textarea id="input" name="input" class="form-control" maxlength="1000" placeholder="<?= l('transcriptions.input_placeholder') ?>"><?= $data->values['input'] ?? null ?></textarea>
                        <small class="form-text text-muted"><?= l('transcriptions.input_help') ?></small>
                    </div>

                    <div class="form-group">
                        <label for="language"><i class="fas fa-fw fa-language fa-sm text-muted mr-1"></i> <?= l('global.language') ?></label>
                        <select id="language" name="language" class="custom-select">
                            <option value="" <?= $data->values['language'] == '' ? 'selected="selected"' : null ?>><?= l('transcriptions.language.auto') ?></option>
                            <?php foreach($data->ai_transcriptions_languages as $key => $value): ?>
                                <option value="<?= $key ?>" <?= $data->values['language'] == $key ? 'selected="selected"' : null ?>><?= $value ?></option>
                            <?php endforeach ?>
                        </select>
                        <small class="form-text text-muted"><?= l('transcriptions.language_help') ?></small>
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
    document.querySelector('#transcription_create').addEventListener('submit', async event => {
        event.preventDefault();

        pause_submit_button(document.querySelector('#transcription_create'));

        /* Notification container */
        let notification_container = event.currentTarget.querySelector('.notification-container');
        notification_container.innerHTML = '';

        /* Prepare form data */
        let form = new FormData(document.querySelector('#transcription_create'));

        /* Send request to server */
        let response = await fetch(`${url}transcription-create/create_ajax`, {
            method: 'post',
            body: form
        });

        let data = null;
        try {
            data = await response.json();
        } catch (error) {
            enable_submit_button(document.querySelector('#transcription_create'));
            display_notifications(<?= json_encode(l('global.error_message.basic')) ?>, 'error', notification_container);
            notification_container.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        if(!response.ok) {
            enable_submit_button(document.querySelector('#transcription_create'));
            display_notifications(<?= json_encode(l('global.error_message.basic')) ?>, 'error', notification_container);
            notification_container.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        if (data.status == 'error') {
            enable_submit_button(document.querySelector('#transcription_create'));
            display_notifications(data.message, 'error', notification_container);
            notification_container.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else if (data.status == 'success') {
            /* Redirect */
            redirect(data.details.url, true);
        }
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

