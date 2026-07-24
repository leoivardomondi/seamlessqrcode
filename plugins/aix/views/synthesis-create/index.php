<?php defined('ALTUMCODE') || die() ?>


<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <nav aria-label="breadcrumb">
        <ol class="custom-breadcrumbs small">
            <li>
                <a href="<?= url('syntheses') ?>"><?= l('syntheses.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
            </li>
            <li class="active" aria-current="page"><?= l('synthesis_create.breadcrumb') ?></li>
        </ol>
    </nav>

    <h1 class="h4 text-truncate mb-4"><i class="fas fa-fw fa-xs fa-voicemail mr-1"></i> <?= l('synthesis_create.header') ?></h1>

    <div class="card">
        <div class="card-body">

            <form id="synthesis_create" action="" method="post" role="form">
                <input type="hidden" name="global_token" value="<?= \Altum\Csrf::get('global_token') ?>" />

                <div class="notification-container"></div>

                <div class="form-group">
                    <label for="name"><i class="fas fa-fw fa-signature fa-sm text-muted mr-1"></i> <?= l('global.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control" value="<?= $data->values['name'] ?>" required="required" />
                </div>

                <div class="form-group" data-character-counter="textarea">
                    <label for="input" class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-fw fa-paragraph fa-sm text-muted mr-1"></i> <?= l('syntheses.input') ?></span>
                        <small class="text-muted" data-character-counter-wrapper></small>
                    </label>
                    <textarea id="input" name="input" class="form-control" minlength="3" maxlength="<?= $data->ai_api['max_length'] ?>" placeholder="<?= l('syntheses.input_placeholder') ?>" required="required"><?= $data->values['input'] ?? null ?></textarea>
                    <small class="form-text text-muted"><?= l('syntheses.input_help') ?></small>
                </div>

                <div class="form-group">
                    <label for="language"><i class="fas fa-fw fa-language fa-sm text-muted mr-1"></i> <?= l('syntheses.language') ?></label>
                    <select id="language" name="language" class="custom-select">
                        <?php foreach($data->ai_languages as $key => $value): ?>
                            <option value="<?= $key ?>" <?= $data->values['language'] == $key ? 'selected="selected"' : null ?>><?= $value ?></option>
                        <?php endforeach ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="voice_id"><i class="fas fa-fw fa-volume-up fa-sm text-muted mr-1"></i> <?= l('syntheses.voice_id') ?></label>
                            <select id="voice_id" name="voice_id" class="custom-select">
                                <?php foreach($data->ai_voices as $voice_id => $voice): ?>
                                    <option value="<?= $voice_id ?>" <?= $data->values['voice_id'] == $voice_id ? 'selected="selected"' : null ?> data-language="<?= $voice['language_code'] ?? null ?>" data-voice-type-standard="<?= isset($voice['standard']) && $voice['standard'] ? 'true' : 'false' ?>" data-voice-type-neural="<?= isset($voice['neural']) && $voice['neural'] ? 'true' : 'false' ?>"><?= ($voice['name'] ?? $voice_id) . ' (' . l('syntheses.voice_gender.' . $voice['gender']) . ')' ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="voice_engine"><i class="fas fa-fw fa-podcast fa-sm text-muted mr-1"></i> <?= l('syntheses.voice_engine') ?></label>
                            <select id="voice_engine" name="voice_engine" class="custom-select">
                                <?php foreach($data->ai_engines as $engine): ?>
                                <option value="<?= $engine ?>" <?= $data->values['voice_engine'] == $engine ? 'selected="selected"' : null ?>><?= l('syntheses.voice_engine.' . str_replace('-', '_', $engine)) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                </div>

                <button class="btn btn-block btn-gray-200 my-4" type="button" data-toggle="collapse" data-target="#advanced_container" aria-expanded="false" aria-controls="advanced_container">
                    <i class="fas fa-fw fa-user-tie fa-sm mr-1"></i> <?= l('syntheses.advanced') ?>
                </button>

                <div class="collapse" id="advanced_container">
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
    <?php if($this->user->plan_settings->syntheses_api == 'aws_polly'): ?>
    /* Languages & voices */
    let reselect_voice_id = () => {
        let voice_id = document.querySelector('#voice_id').value;

        /* Reselect another option which is not disabled */
        let selected_voice_id = document.querySelector(`#voice_id option[value="${voice_id}"]`);
        if(selected_voice_id.hasAttribute('hidden')) {
            selected_voice_id.removeAttribute('selected');
            document.querySelector('#voice_id option:enabled').setAttribute('selected', 'selected');
        }
    }

    let process_language = () => {
        let language = document.querySelector('#language').value;

        document.querySelectorAll('#voice_id option').forEach(element => {
            let voice_language = element.getAttribute('data-language');

            if(language == voice_language) {
                element.removeAttribute('hidden');
            } else {
                element.setAttribute('hidden', 'hidden');
            }
        });

        reselect_voice_id();
        process_voice_id();
    }

    /* Process voice type based on voice id */
    let reselect_voice_engine = () => {
        let voice_engine = document.querySelector('#voice_engine').value;

        /* Reselect another option which is not disabled */
        let selected_voice_engine = document.querySelector(`#voice_engine option[value="${voice_engine}"]`);
        if(selected_voice_engine.hasAttribute('disabled')) {
            selected_voice_engine.removeAttribute('selected');
            document.querySelector('#voice_engine option:enabled').setAttribute('selected', 'selected');
        }
    }

    let process_voice_id = () => {
        let voice_id = document.querySelector('#voice_id').value;

        let voice_id_types = {
            standard: document.querySelector(`#voice_id option[value="${voice_id}"]`).getAttribute('data-voice-type-standard') == 'true',
            neural: document.querySelector(`#voice_id option[value="${voice_id}"]`).getAttribute('data-voice-type-neural') == 'true',
        };

        document.querySelectorAll('#voice_engine option').forEach(element => {
            let voice_engine = element.value;

            if(voice_id_types[voice_engine]) {
                element.removeAttribute('disabled');
            } else {
                element.setAttribute('disabled', 'disabled');
            }
        });

        reselect_voice_engine();
    }

    /* Trigger language & voice id listeners */
    document.querySelector('#language').addEventListener('change', process_language);
    process_language();

    document.querySelector('#voice_id').addEventListener('change', process_voice_id);
    process_voice_id();
    <?php endif ?>

    /* Form submission */
    document.querySelector('#synthesis_create').addEventListener('submit', async event => {
        event.preventDefault();

        pause_submit_button(document.querySelector('#synthesis_create'));

        /* Notification container */
        let notification_container = event.currentTarget.querySelector('.notification-container');
        notification_container.innerHTML = '';

        /* Prepare form data */
        let form = new FormData(document.querySelector('#synthesis_create'));

        /* Send request to server */
        let response = await fetch(`${url}synthesis-create/create_ajax`, {
            method: 'post',
            body: form
        });

        let data = null;
        try {
            data = await response.json();
        } catch (error) {
            enable_submit_button(document.querySelector('#synthesis_create'));
            display_notifications(<?= json_encode(l('global.error_message.basic')) ?>, 'error', notification_container);
            notification_container.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        if(!response.ok) {
            enable_submit_button(document.querySelector('#synthesis_create'));
            display_notifications(<?= json_encode(l('global.error_message.basic')) ?>, 'error', notification_container);
            notification_container.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        if (data.status == 'error') {
            enable_submit_button(document.querySelector('#synthesis_create'));
            display_notifications(data.message, 'error', notification_container);
            notification_container.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else if (data.status == 'success') {
            /* Redirect */
            redirect(data.details.url, true);
        }
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

