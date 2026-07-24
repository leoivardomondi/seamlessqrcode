<?php defined('ALTUMCODE') || die() ?>


<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <nav aria-label="breadcrumb">
        <ol class="custom-breadcrumbs small">
            <li>
                <a href="<?= url('chats') ?>"><?= l('chats.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
            </li>
            <li class="active" aria-current="page"><?= l('chat_create.breadcrumb') ?></li>
        </ol>
    </nav>

    <h1 class="h4 text-truncate mb-4"><i class="fas fa-fw fa-xs fa-comments mr-1"></i> <?= l('chat_create.header') ?></h1>

    <div class="card">
        <div class="card-body">

            <form id="chat_create" action="" method="post" role="form">
                <input type="hidden" name="global_token" value="<?= \Altum\Csrf::get('global_token') ?>" />

                <div class="notification-container"></div>

                <div class="form-group">
                    <label for="name"><i class="fas fa-fw fa-signature fa-sm text-muted mr-1"></i> <?= l('global.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control" value="<?= $data->values['name'] ?>" required="required" />
                </div>

                <div class="form-group">
                    <label for="chat_assistant_id"><i class="fas fa-fw fa-id-card-alt fa-sm text-muted mr-1"></i> <?= l('chats.chat_assistant_id') ?></label>
                    <div class="row">
                        <?php foreach($data->chats_assistants as $chat_assistant_id => $chats_assistant): ?>
                            <label class="col-12 col-md-6 col-lg-4 mb-3 mb-md-4 custom-radio-box mb-3">
                                <input type="radio" name="chat_assistant_id" value="<?= $chat_assistant_id ?>" class="custom-control-input" <?= $data->values['chat_assistant_id'] == $chat_assistant_id ? 'checked="checked"' : null ?> required="required">

                                <div class="card h-100">
                                    <div class="card-body">
                                        <?php if(!empty($chats_assistant->image)): ?>
                                            <div class="mb-3 text-center">
                                                <img src="<?= \Altum\Uploads::get_full_url('chats_assistants') . $chats_assistant->image ?>" class="rounded-circle ai-chat-big-avatar" loading="lazy" />
                                            </div>
                                        <?php endif ?>

                                        <div class="h6 mb-3 text-center font-weight-bold"><?= $chats_assistant->settings->translations->{\Altum\Language::$name}->name ?></div>

                                        <p class="text-muted text-center mb-0"><?= $chats_assistant->settings->translations->{\Altum\Language::$name}->description ?></p>
                                    </div>
                                </div>
                            </label>
                        <?php endforeach ?>
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
    document.querySelector('#chat_create').addEventListener('submit', async event => {
        event.preventDefault();

        pause_submit_button(document.querySelector('#chat_create'));

        /* Notification container */
        let notification_container = event.currentTarget.querySelector('.notification-container');
        notification_container.innerHTML = '';

        /* Prepare form data */
        let form = new FormData(document.querySelector('#chat_create'));

        /* Send request to server */
        let response = await fetch(`${url}chat-create/create_ajax`, {
            method: 'post',
            body: form
        });

        let data = null;
        try {
            data = await response.json();
        } catch (error) {
            enable_submit_button(document.querySelector('#chat_create'));
            display_notifications(<?= json_encode(l('global.error_message.basic')) ?>, 'error', notification_container);
            notification_container.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        if(!response.ok) {
            enable_submit_button(document.querySelector('#chat_create'));
            display_notifications(<?= json_encode(l('global.error_message.basic')) ?>, 'error', notification_container);
            notification_container.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        if (data.status == 'error') {
            enable_submit_button(document.querySelector('#chat_create'));
            display_notifications(data.message, 'error', notification_container);
            notification_container.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else if (data.status == 'success') {
            /* Redirect */
            redirect(data.details.url, true);
        }
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

