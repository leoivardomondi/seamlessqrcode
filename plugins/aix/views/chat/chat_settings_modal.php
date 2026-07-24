<?php defined('ALTUMCODE') || die() ?>

<div class="modal fade" id="chat_settings_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="modal-title">
                        <i class="fas fa-fw fa-sm fa-wrench text-dark mr-2"></i>
                        <?= l('chat_settings_modal.header') ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" title="<?= l('global.close') ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form id="chat_settings_modal_form" name="chat_settings_modal_form" method="post" action="" role="form">
                    <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" required="required" />

                    <div class="form-group">
                        <label for="context_length"><i class="fas fa-fw fa-sm fa-arrows-alt-h text-muted mr-1"></i> <?= l('chat_settings_modal.input.context_length') ?></label>
                        <select name="context_length" id="context_length" class="custom-select">
                            <option value="0" <?= $data->chat->settings->context_length == '0' ? 'selected="selected"' : null ?>><?= l('global.all') ?></option>
                            <?php foreach([1, 3, 5, 7, 9] as $key): ?>
                                <option value="<?= $key ?>" <?= $data->chat->settings->context_length == $key ? 'selected="selected"' : null ?>><?= sprintf(l('chat_settings_modal.input.context_length_x'), $key) ?></option>
                            <?php endforeach ?>
                        </select>
                        <small class="form-text text-muted"><?= l('chat_settings_modal.input.context_length_help') ?></small>
                    </div>

                    <div class="form-group">
                        <label for="creativity_level"><i class="fas fa-fw fa-lightbulb fa-sm text-muted mr-1"></i> <?= l('documents.creativity_level') ?></label>
                        <div class="row btn-group-toggle" data-toggle="buttons">
                            <?php foreach(['none', 'low', 'optimal', 'high', 'maximum', 'custom'] as $key): ?>
                                <div class="col-12 col-lg-6">
                                    <label class="btn btn-light btn-block">
                                        <input type="radio" name="creativity_level" value="<?= $key ?>" class="custom-control-input" <?= $data->chat->settings->creativity_level == $key ? 'checked="checked"' : null ?> />
                                        <?= l('documents.creativity_level.' . $key) ?>
                                    </label>
                                </div>
                            <?php endforeach ?>
                        </div>
                        <small class="form-text text-muted"><?= l('documents.creativity_level_help') ?></small>
                    </div>

                    <div class="form-group" data-creativity-level="custom">
                        <label for="creativity_level_custom"><i class="fas fa-fw fa-hat-wizard fa-sm text-muted mr-1"></i> <?= l('documents.creativity_level_custom') ?></label>
                        <input type="number" id="creativity_level_custom" min="0" max="2" step="0.1" name="creativity_level_custom" class="form-control" value="<?= $data->chat->settings->creativity_level_custom ?? 0.8 ?>" />
                        <small class="form-text text-muted"><?= l('documents.creativity_level_custom_help') ?></small>
                    </div>

                    <div class="mt-4">
                        <button type="submit" name="submit" class="btn btn-block btn-primary"><?= l('chat_settings_modal.button') ?></button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<?php ob_start() ?>
<script>
    'use strict';

    /* On form submission */
    document.querySelector('#chat_settings_modal_form').addEventListener('submit', event => {
        event.preventDefault()

        document.querySelector('#chat_message_create input[name="context_length"]').value = document.querySelector('#context_length').value;
        document.querySelector('#chat_message_create input[name="creativity_level_custom"]').value = document.querySelector('#creativity_level_custom').value;
        document.querySelector('#chat_message_create input[name="creativity_level"]').value = document.querySelector('#chat_settings_modal_form input[name="creativity_level"]:checked').value;

        $('#chat_settings_modal').modal('hide');
    });

    type_handler('[name="creativity_level"]', 'data-creativity-level');
    document.querySelector('[name="creativity_level"]') && document.querySelectorAll('[name="creativity_level"]').forEach(element => element.addEventListener('change', () => { type_handler('[name="creativity_level"]', 'data-creativity-level'); }));
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
