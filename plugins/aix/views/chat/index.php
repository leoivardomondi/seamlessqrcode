<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <nav aria-label="breadcrumb">
        <ol class="custom-breadcrumbs small">
            <li>
                <a href="<?= url('chats') ?>"><?= l('chats.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
            </li>
            <li class="active" aria-current="page"><?= l('chat.breadcrumb') ?></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex">
            <?php if(!empty($data->chat_assistant->image)): ?>
                <img src="<?= \Altum\Uploads::get_full_url('chats_assistants') . $data->chat_assistant->image ?>" class="rounded-circle ai-chat-avatar mr-3" loading="lazy" data-toggle="tooltip" title="<?= $data->chat_assistant->settings->translations->{\Altum\Language::$name}->name ?>" />
            <?php endif ?>

            <div>
                <h1 class="h4 text-truncate mb-0"><?= sprintf(l('chat.header'), $data->chat->name) ?></h1>
                <small><?= sprintf(l('chat.total_messages'), '<span class="font-weight-bold" id="total_messages" data-total-messages="' . $data->chat->total_messages . '">' . nr($data->chat->total_messages) . '</span>') ?></small>
            </div>
        </div>

        <div class="d-flex align-items-center col-auto p-0">
            <div class="mr-1">
                <a href="<?= url('chat-create') ?>" class="btn btn-primary">
                    <i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('chats.create') ?>
                </a>
            </div>

            <button type="button" class="btn btn-light text-secondary dropdown-toggle dropdown-toggle-simple" data-toggle="modal" data-target="#chat_settings_modal">
                <i class="fas fa-fw fa-wrench"></i>
            </button>

            <?= include_view(\Altum\Plugin::get('aix')->path . 'views/chats/chat_dropdown_button.php', ['id' => $data->chat->chat_id, 'resource_name' => $data->chat->name]) ?>
        </div>
    </div>

    <div id="chat_messages_wrapper" class="card mb-4 <?= count($data->chat_messages) ? null : 'd-none' ?>">
        <div class="card-body">
            <div id="chat_messages" class="chat-messages custom-scrollbar">
                <?php foreach($data->chat_messages as $chat_message): ?>
                    <div class="<?= $chat_message->role == 'user' ? '' : 'bg-gray-100' ?> p-3 rounded d-flex mb-3" data-chat-message-wrapper>
                        <div class="mr-3">
                            <img src="<?= $chat_message->role == 'user' ? get_gravatar($this->user->email) : ($data->chat_assistant->image ? \Altum\Uploads::get_full_url('chats_assistants') . $data->chat_assistant->image : get_gravatar('')) ?>" class="ai-chat-avatar rounded" loading="lazy" />
                        </div>

                        <div class="flex-fill">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="font-weight-bold small <?= $chat_message->role == 'user' ? 'text-primary' : 'text-muted' ?>"><?= $chat_message->role == 'user' ? $this->user->name : $data->chat_assistant->settings->translations->{\Altum\Language::$name}->name ?></div>
                                <div>
                                    <small class="text-muted chat-datetime" data-toggle="tooltip" title="<?= \Altum\Date::get($chat_message->datetime, 1) ?>"><?= \Altum\Date::get($chat_message->datetime, 3) ?></small>
                                </div>
                            </div>

                            <div class="d-flex">
                                <div class="flex-fill">
                                    <div class="chat-content"><?= $chat_message->role == 'user' ? input_clean(nl2br($chat_message->content)) : $data->parsedown->text($chat_message->content) ?></div>
                                </div>

                                <?php if($chat_message->image): ?>
                                    <div class="chat-image ml-3"><img src="<?= \Altum\Uploads::get_full_url('chats_images') . $chat_message->image ?>" class="img-fluid rounded" /></div>
                                <?php endif ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">

            <form id="chat_message_create" action="" method="post" role="form" enctype="multipart/form-data">
                <input type="hidden" name="global_token" value="<?= \Altum\Csrf::get('global_token') ?>" />
                <input type="hidden" name="chat_id" value="<?= $data->chat->chat_id ?>" />
                <input type="hidden" name="context_length" value="<?= $data->chat->settings->context_length ?>" />
                <input type="hidden" name="creativity_level" value="<?= $data->chat->settings->creativity_level ?>" />
                <input type="hidden" name="creativity_level_custom" value="<?= $data->chat->settings->creativity_level_custom ?>" />

                <div class="notification-container"></div>

                <div class="row">
                    <div class="col-lg">
                        <div class="form-group" data-character-counter="textarea">
                            <textarea name="content" class="form-control" placeholder="<?= l('chats.content_placeholder') ?>" maxlength="2048" autofocus="autofocus" required="required"><?= $data->content ?></textarea>

                            <label for="input" class="d-flex justify-content-end align-items-center mt-1 mb-0">
                                <small class="text-muted" data-character-counter-wrapper></small>
                            </label>
                        </div>
                    </div>

                    <?php if($this->user->plan_settings->chats_model == 'gpt-4o'): ?>
                        <div class="col-lg-2">
                            <div class="form-group h-100">
                                <label for="image" type="button" class="btn btn-lg btn-block btn-light" data-toggle="tooltip" title="<?= sprintf(l('global.accessibility.whitelisted_file_extensions'), \Altum\Uploads::get_whitelisted_file_extensions_accept('chats_images')) . ' ' . sprintf(l('global.accessibility.file_size_limit'), $this->user->plan_settings->replaced_background_images_file_size_limit) ?>">
                                    <i class="fas fa-fw fa-sm fa-image text-muted"></i>
                                </label>
                                <input id="image" type="file" name="image" accept="<?= \Altum\Uploads::get_whitelisted_file_extensions_accept('chats_images') ?>" class="form-control-file altum-file-input d-none" />
                            </div>
                        </div>
                    <?php endif ?>
                </div>

                <button
                        type="submit"
                        name="form-submit"
                        class="btn btn-block btn-primary"
                    <?= $this->user->plan_settings->chat_messages_per_chat_limit != -1 && $data->sent_chat_messages >= $this->user->plan_settings->chat_messages_per_chat_limit ? 'disabled="disabled" data-toggle="tooltip" title="' . l('global.info_message.plan_feature_limit') . '"' : null ?>
                        data-is-ajax
                >
                    <i class="fas fa-fw fa-sm fa-paper-plane mr-1"></i> <?= l('global.submit') ?>
                </button>
            </form>

        </div>
    </div>
</div>

<template id="template_chat_message_user">
    <div class="p-3 rounded d-flex mb-3 altum-animate altum-animate-fill-none altum-animate-fade-in" data-chat-message-wrapper>
        <div class="mr-3">
            <img src="<?= get_gravatar($this->user->email) ?>" class="ai-chat-avatar rounded" loading="lazy" />
        </div>

        <div class="flex-fill">
            <div class="d-flex justify-content-between align-items-center">
                <div class="font-weight-bold small text-primary"><?= $this->user->name ?></div>
                <div>
                    <small class="text-muted chat-datetime" data-toggle="tooltip" title=""></small>
                </div>
            </div>

            <div class="d-flex">
                <div class="flex-fill">
                    <div class="chat-content"></div>
                </div>

                <div class="d-none chat-image ml-3"><img class="img-fluid rounded" /></div>
            </div>
        </div>
    </div>
</template>

<template id="template_chat_message_assistant">
    <div class="bg-gray-100 p-3 rounded d-flex mb-3 altum-animate altum-animate-fill-none altum-animate-fade-in" data-chat-message-wrapper>
        <div class="mr-3">
            <img src="<?= ($data->chat_assistant->image ? \Altum\Uploads::get_full_url('chats_assistants') . $data->chat_assistant->image : get_gravatar('')) ?>" class="ai-chat-avatar rounded" loading="lazy" />
        </div>

        <div class="flex-fill">
            <div class="d-flex justify-content-between align-items-center">
                <div class="font-weight-bold small text-muted"><?= $data->chat_assistant->settings->translations->{\Altum\Language::$name}->name ?></div>
                <div>
                    <small class="text-muted chat-datetime" data-toggle="tooltip" title=""></small>
                </div>
            </div>
            <div class="chat-content"></div>
        </div>
    </div>
</template>

<?php ob_start() ?>
<script>
    /* Form submission */
    let form_submit = async form_element => {
        if(form_element.querySelector('[type="submit"][name="form-submit"]').hasAttribute('disabled')) {
            return
        }

        pause_submit_button(form_element.querySelector('[type="submit"][name="form-submit"]'));

        /* Notification container */
        let notification_container = form_element.querySelector('.notification-container');
        notification_container.innerHTML = '';

        /* Make sure the chat messages wrapper is shown */
        document.querySelector('#chat_messages_wrapper').classList.remove('d-none');

        /* Prepare form data */
        let form = new FormData(document.querySelector('#chat_message_create'));

        /* Add user message */
        let clone = document.querySelector(`#template_chat_message_user`).content.cloneNode(true);
        clone.querySelector('.chat-content').innerText = form.get('content');
        document.querySelector(`#chat_messages`).appendChild(clone);
        document.querySelector('#chat_messages [data-chat-message-wrapper]:last-child').scrollIntoView({ behavior: 'smooth', block: 'end' });

        /* Send request to server */
        let response = await fetch(`${url}chat/create_ajax`, {
            method: 'post',
            body: form
        });

        let data = null;
        try {
            data = await response.json();
        } catch (error) {
            enable_submit_button(form_element.querySelector('[type="submit"][name="form-submit"]'));
            display_notifications(<?= json_encode(l('global.error_message.basic')) ?>, 'error', notification_container);
            notification_container.scrollIntoView({ behavior: 'smooth', block: 'center' });
            document.querySelector(`#chat_messages`).lastElementChild.remove();
        }

        if(!response.ok) {
            enable_submit_button(form_element.querySelector('[type="submit"][name="form-submit"]'));
            display_notifications(<?= json_encode(l('global.error_message.basic')) ?>, 'error', notification_container);
            notification_container.scrollIntoView({ behavior: 'smooth', block: 'center' });
            document.querySelector(`#chat_messages`).lastElementChild.remove();
        }

        if (data.status == 'error') {
            enable_submit_button(form_element.querySelector('[type="submit"][name="form-submit"]'));
            document.querySelector(`#chat_messages`).lastElementChild.remove();

            /* Custom notification */
            if(data.message == 'context_length_exceeded') {
                notification_container.innerHTML = `
                <div class="alert alert-info altum-animate altum-animate-fill-none altum-animate-fade-in">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <strong><?= l('chat.error_message.context_length') ?></strong> <br />
                    <?= l('chat.error_message.context_length_help') ?>
                </div>
                `;

                document.querySelector('#chat_message_create input[name="context_length"]').value = 5;
                document.querySelector('#context_length').value = 5;
            } else {
                display_notifications(data.message, 'error', notification_container);
            }

            notification_container.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else if (data.status == 'success') {

            /* Datetime for user message */
            document.querySelector(`#chat_messages`).lastElementChild.querySelector('.chat-datetime').innerHTML = data.details.datetime_his;
            document.querySelector(`#chat_messages`).lastElementChild.querySelector('.chat-datetime').setAttribute('data-tooltip', data.details.datetime_full);

            <?php if($this->user->plan_settings->chats_model == 'gpt-4o'): ?>
            document.querySelector(`#chat_messages`).lastElementChild.querySelector('.chat-image').classList.remove('d-none');

            if(data.details.image_url) {
                document.querySelector(`#chat_messages`).lastElementChild.querySelector('.chat-image').classList.remove('d-none');
                document.querySelector(`#chat_messages`).lastElementChild.querySelector('.chat-image img').src = data.details.image_url;
            }

            /* Remove selected image from input */
            document.querySelector('#image').value = '';
            document.querySelector('label[for="image"]').innerHTML = `<i class="fas fa-fw fa-sm fa-image text-muted"></i>`;
            <?php endif ?>

            /* Add assistant message */
            clone = document.querySelector(`#template_chat_message_assistant`).content.cloneNode(true);
            clone.querySelector('.chat-content').innerHTML = data.details.content;
            clone.querySelector('.chat-datetime').innerHTML = data.details.datetime_his;
            clone.querySelector('.chat-datetime').setAttribute('data-tooltip', data.details.datetime_full);
            document.querySelector(`#chat_messages`).appendChild(clone);

            /* Reset tooltips */
            $('[data-toggle="tooltip"],[data-tooltip]').tooltip();

            /* Clear message input */
            let content = document.querySelector('textarea[name="content"]');
            content.value = '';

            /* Increment total messages display */
            let new_total_messages = parseInt(document.querySelector('#total_messages').getAttribute('data-total-messages')) + 2;
            document.querySelector('#total_messages').innerText = nr(new_total_messages);
            document.querySelector('#total_messages').setAttribute('data-total-messages', new_total_messages);

            /* Trigger change event */
            content.dispatchEvent(new Event('change'));

            enable_submit_button(form_element.querySelector('[type="submit"][name="form-submit"]'));

            setTimeout(() => {
                document.querySelector('#chat_messages [data-chat-message-wrapper]:last-child').scrollIntoView({ behavior: 'smooth', block: 'end' });
            }, 500);
        }
    };

    document.querySelector('#chat_message_create').addEventListener('submit', event => {
        event.preventDefault()

        form_submit(event.target);
    });

    /* Make the form submission work with enter, new line with shift+enter */
    document.querySelector('textarea[name="content"]').addEventListener('keydown', async event => {
        if (event.keyCode == 13 && !event.shiftKey) {
            event.preventDefault();
            form_submit(document.querySelector('#chat_message_create'));
        }
    });

    /* Scroll to the last message */
    document.querySelector('#chat_messages').scrollTop = document.querySelector('#chat_messages').scrollHeight;

    <?php if($this->user->plan_settings->chats_model == 'gpt-4o'): ?>
    /* Process current selected image */
    let process_image = () => {
        let file_reader = new FileReader();

        file_reader.onload = event => {
            document.querySelector('label[for="image"]').innerHTML = `<img src="${file_reader.result || event.target.result}" class="img-fluid rounded" />`;
            document.querySelector('label[for="image"]').style.maxHeight = `${document.querySelector('textarea[name="content"]').offsetHeight}px`;
            document.querySelector('label[for="image"] img').style.maxHeight = `${document.querySelector('textarea[name="content"]').offsetHeight * 0.75}px`;
        }

        let on_image_selected = event => {
            if(event.currentTarget.files.length) {
                file_reader.readAsDataURL(event.currentTarget.files[0]);
            } else {
                document.querySelector('label[for="image"]').innerHTML = `<i class="fas fa-fw fa-sm fa-image text-muted"></i>`;
                document.querySelector('label[for="image"]').style.maxHeight = null;
            }
        }

        document.querySelector('#image').addEventListener('change', on_image_selected);
        //document.querySelector('#image').removeEventListener('change', on_image_selected);
    }

    process_image();
    <?php endif ?>
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>


<?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
    'name' => 'chat',
    'resource_id' => 'chat_id',
    'has_dynamic_resource_name' => true,
    'path' => 'chats/delete'
]), 'modals'); ?>

<?php \Altum\Event::add_content(include_view(\Altum\Plugin::get('aix')->path . 'views/chat/chat_settings_modal.php', ['chat' => $data->chat]), 'modals'); ?>
