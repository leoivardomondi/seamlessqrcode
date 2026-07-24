<?php defined('ALTUMCODE') || die() ?>

<?php $available_plan_features = require APP_PATH . 'includes/available_plan_features.php' ?>
<?php $features = ((array) (settings()->payment->plan_features ?? [])) + array_fill_keys($available_plan_features, true) ?>
<?php $features_in_front = ((array) (settings()->payment->plan_features_in_front ?? [])) + array_fill_keys($available_plan_features, true) ?>

<?php $front_html_items = []; ?>
<?php $hidden_html_items = []; ?>
<?php $flipbooks_limit = $data->plan_settings->flipbooks_limit ?? null ?>
<?php $edit_pdf_directly_is_enabled = !empty($flipbooks_limit) && $flipbooks_limit != 0 ?>

<ul class="pricing-features">
    <?php foreach($features as $feature => $is_enabled): ?>
        <?php if(!$is_enabled) continue ?>

        <?php ob_start() ?>

        <?php if($feature == 'biolinks_limit' && settings()->links->biolinks_is_enabled): ?>
            <li>
                <div class="<?= $data->plan_settings->biolinks_limit ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.biolinks_limit'), ($data->plan_settings->biolinks_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->biolinks_limit))) ?>
                </div>
                <i class="fas fa-fw fa-sm <?= $data->plan_settings->biolinks_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'biolink_blocks_limit' && settings()->links->biolinks_is_enabled): ?>
            <li>
                <div class="<?= $data->plan_settings->biolink_blocks_limit ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.biolink_blocks_limit'), ($data->plan_settings->biolink_blocks_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->biolink_blocks_limit))) ?>
                </div>
                <i class="fas fa-fw fa-sm <?= $data->plan_settings->biolink_blocks_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'enabled_biolink_blocks' && settings()->links->biolinks_is_enabled): ?>
            <?php $enabled_biolink_blocks = array_filter((array) $data->plan_settings->enabled_biolink_blocks) ?>
            <?php $enabled_biolink_blocks_count = count($enabled_biolink_blocks) ?>
            <?php
            $enabled_biolink_blocks_string = implode(', ', array_map(function($key) {
                return l('link.biolink.blocks.' . mb_strtolower($key));
            }, array_keys($enabled_biolink_blocks)));
            ?>
            <li>
                <div class="<?= $enabled_biolink_blocks_count ? null : 'text-muted' ?>">
                    <?php if($enabled_biolink_blocks_count == count(require APP_PATH . 'includes/enabled_biolink_blocks.php')): ?>
                        <?= l('global.plan_settings.enabled_biolink_blocks_all') ?>
                        <i class="fas fa-fw fa-sm <?= $enabled_biolink_blocks_count ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                    <?php else: ?>
                        <?= sprintf(l('global.plan_settings.enabled_biolink_blocks_x'), '<strong>' . nr($enabled_biolink_blocks_count) . '</strong>') ?>
                    <?php endif ?>

                    <span class="mr-1" data-toggle="tooltip" title="<?= $enabled_biolink_blocks_string ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
                </div>
            </li>
        <?php endif ?>

        <?php if($feature == 'links_limit' && settings()->links->shortener_is_enabled): ?>
            <li>
                <div class="<?= $data->plan_settings->links_limit ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.links_limit'), ($data->plan_settings->links_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->links_limit))) ?>
                </div>
                <i class="fas fa-fw fa-sm <?= $data->plan_settings->links_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'links_bulk_limit' && settings()->links->shortener_is_enabled): ?>
            <li>
                <div class="<?= $data->plan_settings->links_bulk_limit ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.links_bulk_limit'), ($data->plan_settings->links_bulk_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->links_bulk_limit))) ?>
                </div>
                <i class="fas fa-fw fa-sm <?= $data->plan_settings->links_bulk_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'files_limit' && settings()->links->files_is_enabled): ?>
            <li>
                <div class="<?= $data->plan_settings->files_limit ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.files_limit'), ($data->plan_settings->files_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->files_limit))) ?>
                </div>
                <i class="fas fa-fw fa-sm <?= $data->plan_settings->files_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'flipbooks_limit'): ?>
            <li>
                <div class="<?= ($data->plan_settings->flipbooks_limit ?? 0) ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.flipbooks_limit'), (($data->plan_settings->flipbooks_limit ?? 0) == -1 ? l('global.unlimited') : nr($data->plan_settings->flipbooks_limit ?? 0))) ?>
                </div>
                <i class="fas fa-fw fa-sm <?= ($data->plan_settings->flipbooks_limit ?? 0) ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'vcards_limit' && settings()->links->vcards_is_enabled): ?>
            <li>
                <div class="<?= $data->plan_settings->vcards_limit ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.vcards_limit'), ($data->plan_settings->vcards_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->vcards_limit))) ?>
                </div>
                <i class="fas fa-fw fa-sm <?= $data->plan_settings->vcards_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'events_limit' && settings()->links->events_is_enabled): ?>
            <li>
                <div class="<?= $data->plan_settings->events_limit ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.events_limit'), ($data->plan_settings->events_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->events_limit))) ?>
                </div>
                <i class="fas fa-fw fa-sm <?= $data->plan_settings->events_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'static_limit' && settings()->links->static_is_enabled): ?>
            <li>
                <div class="<?= $data->plan_settings->static_limit ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.static_limit'), ($data->plan_settings->static_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->static_limit))) ?>
                </div>
                <i class="fas fa-fw fa-sm <?= $data->plan_settings->static_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'ai_static_prompts_per_month_limit' && settings()->links->static_is_enabled): ?>
            <li>
                <div class="<?= $data->plan_settings->ai_static_prompts_per_month_limit ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.ai_static_prompts_per_month_limit'), ($data->plan_settings->ai_static_prompts_per_month_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->ai_static_prompts_per_month_limit))) ?>
                </div>
                <i class="fas fa-fw fa-sm <?= $data->plan_settings->ai_static_prompts_per_month_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'qr_codes_limit' && settings()->codes->qr_codes_is_enabled): ?>
            <li>
                <div class="<?= $data->plan_settings->qr_codes_limit ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.qr_codes_limit'), ($data->plan_settings->qr_codes_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->qr_codes_limit))) ?>
                </div>
                <i class="fas fa-fw fa-sm <?= $data->plan_settings->qr_codes_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'qr_codes_bulk_limit' && settings()->codes->qr_codes_is_enabled): ?>
            <li>
                <div class="<?= $data->plan_settings->qr_codes_bulk_limit ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.qr_codes_bulk_limit'), ($data->plan_settings->qr_codes_bulk_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->qr_codes_bulk_limit))) ?>
                </div>
                <i class="fas fa-fw fa-sm <?= $data->plan_settings->qr_codes_bulk_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'signatures_limit' && \Altum\Plugin::is_active('email-signatures') && settings()->signatures->is_enabled): ?>
            <li>
                <div class="<?= $data->plan_settings->signatures_limit ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.signatures_limit'), ($data->plan_settings->signatures_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->signatures_limit))) ?>
                </div>
                <i class="fas fa-fw fa-sm <?= $data->plan_settings->signatures_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'splash_pages_limit' && settings()->links->splash_page_is_enabled): ?>
            <li>
                <div class="<?= $data->plan_settings->splash_pages_limit ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.splash_pages_limit'), ($data->plan_settings->splash_pages_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->splash_pages_limit))) ?>
                </div>
                <i class="fas fa-fw fa-sm <?= $data->plan_settings->splash_pages_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'pixels_limit' && settings()->links->pixels_is_enabled): ?>
            <li>
                <div class="<?= $data->plan_settings->pixels_limit ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.pixels_limit'), ($data->plan_settings->pixels_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->pixels_limit))) ?>
                </div>
                <i class="fas fa-fw fa-sm <?= $data->plan_settings->pixels_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'projects_limit' && settings()->links->projects_is_enabled): ?>
            <li>
                <div class="<?= $data->plan_settings->projects_limit ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.projects_limit'), ($data->plan_settings->projects_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->projects_limit))) ?>
                </div>
                <i class="fas fa-fw fa-sm <?= $data->plan_settings->projects_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'teams_limit' && \Altum\Plugin::is_active('teams')): ?>
            <li>
                <div class="<?= $data->plan_settings->teams_limit ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.teams_limit'), ($data->plan_settings->teams_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->teams_limit))) ?>
                    <span class="ml-1" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.plan_settings.team_members_limit'), '<strong>' . ($data->plan_settings->team_members_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->team_members_limit)) . '</strong>') ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
                </div>
                <i class="fas fa-fw fa-sm <?= $data->plan_settings->teams_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'affiliate_commission_percentage' && \Altum\Plugin::is_active('affiliate') && settings()->affiliate->is_enabled): ?>
            <li>
                <div class="<?= $data->plan_settings->affiliate_commission_percentage ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.affiliate_commission_percentage'), nr($data->plan_settings->affiliate_commission_percentage) . '%') ?>
                </div>
                <i class="fas fa-fw fa-sm <?= $data->plan_settings->affiliate_commission_percentage ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

        <?php if(
                $feature == 'notification_handlers_limit' &&
                (settings()->links->biolinks_is_enabled || settings()->links->shortener_is_enabled || settings()->links->files_is_enabled || settings()->links->vcards_is_enabled || settings()->links->events_is_enabled || settings()->links->static_is_enabled)
        ): ?>
            <?php ob_start() ?>
            <?php $notification_handlers_icon = 'fa-times text-muted'; ?>
            <div class='d-flex flex-column'>
                <?php foreach(array_keys(require APP_PATH . 'includes/notification_handlers.php') as $notification_handler): ?>
                    <?php if($data->plan_settings->{'notification_handlers_' . $notification_handler . '_limit'} != 0) $notification_handlers_icon = 'fa-check text-success' ?>
                    <span class='my-1'><?= sprintf(l('global.plan_settings.notification_handlers_' . $notification_handler . '_limit'), '<strong>' . ($data->plan_settings->{'notification_handlers_' . $notification_handler . '_limit'} == -1 ? l('global.unlimited') : nr($data->plan_settings->{'notification_handlers_' . $notification_handler . '_limit'})) . '</strong>') ?></span>
                <?php endforeach ?>
            </div>
            <?php $notification_handlers_html = ob_get_clean() ?>

            <li>
                <div>
                    <?= l('global.plan_settings.notification_handlers_limit') ?>
                    <span class="ml-1" data-toggle="tooltip" data-html="true" title="<?= $notification_handlers_html ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
                </div>
                <i class="fas fa-fw fa-sm <?= $notification_handlers_icon ?>"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'domains_limit' && settings()->links->domains_is_enabled): ?>
            <li>
                <div class="<?= $data->plan_settings->domains_limit ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.domains_limit'), ($data->plan_settings->domains_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->domains_limit))) ?>
                </div>
                <i class="fas fa-fw fa-sm <?= $data->plan_settings->domains_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

        <?php if(
                $feature == 'track_links_retention'
                && (settings()->links->biolinks_is_enabled || settings()->links->shortener_is_enabled || settings()->links->files_is_enabled || settings()->links->vcards_is_enabled || settings()->links->events_is_enabled || settings()->links->static_is_enabled)
        ): ?>
            <li>
                <div class="<?= $data->plan_settings->track_links_retention ? null : 'text-muted' ?>" data-toggle="tooltip" title="<?= ($data->plan_settings->track_links_retention == -1 ? '' : $data->plan_settings->track_links_retention . ' ' . l('global.date.days')) ?>">
                    <?= sprintf(l('global.plan_settings.track_links_retention'), ($data->plan_settings->track_links_retention == -1 ? l('global.unlimited') : \Altum\Date::days_format($data->plan_settings->track_links_retention))) ?>
                </div>
                <i class="fas fa-fw fa-sm <?= $data->plan_settings->track_links_retention ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'email_reports_is_enabled' && settings()->links->email_reports_is_enabled): ?>
            <li>
                <div class="<?= $data->plan_settings->email_reports_is_enabled ? null : 'text-muted' ?>">
                    <?= settings()->links->email_reports_is_enabled ? l('global.plan_settings.email_reports_is_enabled_' . settings()->links->email_reports_is_enabled) : l('global.plan_settings.email_reports_is_enabled') ?>
                </div>
                <i class="fas fa-fw fa-sm <?= $data->plan_settings->email_reports_is_enabled ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'additional_domains' && settings()->links->additional_domains_is_enabled): ?>
            <?php $additional_domains_list = (new \Altum\Models\Domain())->get_available_additional_domains(); ?>

            <li>
                <div class="<?= count($data->plan_settings->additional_domains ?? []) ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.additional_domains'), '<strong>' . nr(count($data->plan_settings->additional_domains ?? [])) . '</strong>') ?>
                    <span class="mr-1" data-toggle="tooltip" title="<?= sprintf(l('global.plan_settings.additional_domains_help'), implode(', ', array_map(function($domain_id) use($additional_domains_list) { return $additional_domains_list[$domain_id]->host ?? null; }, $data->plan_settings->additional_domains ?? []))) ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
                </div>
                <i class="fas fa-fw fa-sm <?= count($data->plan_settings->additional_domains ?? []) ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>


        <?php
        if(
                \Altum\Plugin::is_active('aix')
                && (
                        settings()->aix->documents_is_enabled || settings()->aix->images_is_enabled || settings()->aix->transcriptions_is_enabled || settings()->aix->chats_is_enabled
                )
        ):
            ?>

            <?php if($feature == 'documents_model' && settings()->aix->documents_is_enabled && isset($data->plan_settings->documents_model, $ai_text_models[$data->plan_settings->documents_model])): ?>
            <?php $ai_text_models = require \Altum\Plugin::get('aix')->path . 'includes/ai_text_models.php'; ?>

            <li>
                <div>
                    <?= $ai_text_models[$data->plan_settings->documents_model]['name'] ?>
                </div>
                <i class="fas fa-fw fa-sm fa-check text-success"></i>
            </li>
        <?php endif ?>

            <?php if($feature == 'documents_per_month_limit' && settings()->aix->documents_is_enabled): ?>
            <li>
                <div class="<?= $data->plan_settings->documents_per_month_limit ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.documents_per_month_limit'), ($data->plan_settings->documents_per_month_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->documents_per_month_limit))) ?>
                </div>
                <i class="fas fa-fw fa-sm <?= $data->plan_settings->documents_per_month_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

            <?php if($feature == 'words_per_month_limit' && settings()->aix->documents_is_enabled): ?>
            <li>
                <div class="<?= $data->plan_settings->words_per_month_limit ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.words_per_month_limit'), ($data->plan_settings->words_per_month_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->words_per_month_limit))) ?>
                </div>
                <i class="fas fa-fw fa-sm <?= $data->plan_settings->words_per_month_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

            <?php if($feature == 'images_per_month_limit' && settings()->aix->images_is_enabled): ?>
            <li>
                <div class="<?= $data->plan_settings->images_per_month_limit ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.images_per_month_limit'), ($data->plan_settings->images_per_month_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->images_per_month_limit))) ?>
                </div>
                <i class="fas fa-fw fa-sm <?= $data->plan_settings->images_per_month_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

            <?php if($feature == 'transcriptions_per_month_limit' && settings()->aix->transcriptions_is_enabled): ?>
            <li>
                <div class="<?= $data->plan_settings->transcriptions_per_month_limit ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.transcriptions_per_month_limit'), ($data->plan_settings->transcriptions_per_month_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->transcriptions_per_month_limit))) ?>
                </div>
                <i class="fas fa-fw fa-sm <?= $data->plan_settings->transcriptions_per_month_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

            <?php if($feature == 'transcriptions_file_size_limit' && settings()->aix->transcriptions_is_enabled): ?>
            <li>
                <div class="<?= $data->plan_settings->transcriptions_file_size_limit ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.transcriptions_file_size_limit'), get_formatted_bytes($data->plan_settings->transcriptions_file_size_limit * 1000 * 1000)) ?>
                </div>
                <i class="fas fa-fw fa-sm <?= $data->plan_settings->transcriptions_file_size_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

            <?php if($feature == 'chats_per_month_limit' && settings()->aix->chats_is_enabled): ?>
            <li>
                <div class="<?= $data->plan_settings->chats_per_month_limit ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.chats_per_month_limit'), '<strong>' . ($data->plan_settings->chats_per_month_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->chats_per_month_limit)) . '</strong>') ?>
                </div>
                <i class="fas fa-fw fa-sm <?= $data->plan_settings->chats_per_month_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

            <?php if($feature == 'chat_messages_per_chat_limit' && settings()->aix->chats_is_enabled): ?>
            <li>
                <div class="<?= $data->plan_settings->chat_messages_per_chat_limit ? null : 'text-muted' ?>">
                    <?= sprintf(l('global.plan_settings.chat_messages_per_chat_limit'), '<strong>' . ($data->plan_settings->chat_messages_per_chat_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->chat_messages_per_chat_limit)) . '</strong>') ?>
                </div>
                <i class="fas fa-fw fa-sm <?= $data->plan_settings->chat_messages_per_chat_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
            </li>
        <?php endif ?>

        <?php endif ?>

        <?php if($feature == 'biolinks_themes' && settings()->links->biolinks_is_enabled && settings()->links->biolinks_themes_is_enabled): ?>
            <li>
                <div class='<?= count($data->plan_settings->biolinks_themes ?? []) ? null : 'text-muted' ?>'>
                    <?= sprintf(l('global.plan_settings.biolinks_themes'), nr(count($data->plan_settings->biolinks_themes ?? []))) ?>
                </div>
                <i class='fas fa-fw fa-sm <?= count($data->plan_settings->biolinks_themes ?? []) ? 'fa-check text-success' : 'fa-times text-muted' ?>'></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'biolinks_templates' && settings()->links->biolinks_is_enabled && settings()->links->biolinks_templates_is_enabled): ?>
            <li>
                <div class='<?= count($data->plan_settings->biolinks_templates ?? []) ? null : 'text-muted' ?>'>
                    <?= sprintf(l('global.plan_settings.biolinks_templates'), nr(count($data->plan_settings->biolinks_templates ?? []))) ?>
                </div>
                <i class='fas fa-fw fa-sm <?= count($data->plan_settings->biolinks_templates ?? []) ? 'fa-check text-success' : 'fa-times text-muted' ?>'></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'payment_processors_limit' && settings()->links->biolinks_is_enabled && \Altum\Plugin::is_active('payment-blocks')): ?>
            <li>
                <div class='<?= $data->plan_settings->payment_processors_limit ? null : 'text-muted' ?>'>
                    <?= sprintf(l('global.plan_settings.payment_processors_limit'), ($data->plan_settings->payment_processors_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->payment_processors_limit))) ?>
                </div>
                <i class='fas fa-fw fa-sm <?= $data->plan_settings->payment_processors_limit ? 'fa-check text-success' : 'fa-times text-muted' ?>'></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'force_splash_page_on_link' && settings()->links->splash_page_is_enabled): ?>
            <?php
            $no_forced_splash_page = true;
            foreach(require APP_PATH . 'includes/links_types.php' as $link_type_key => $link_type_value) {
                if($data->plan_settings->{'force_splash_page_on_' . $link_type_key}) {
                    $no_forced_splash_page = false;
                    break;
                }
            }
            ?>
            <li>
                <div class='<?= $no_forced_splash_page ? null : 'text-muted' ?>'>
                    <?= l('global.plan_settings.no_forced_splash_page') ?>
                    <span class='ml-1' data-toggle='tooltip' title='<?= l('global.plan_settings.no_forced_splash_page_help') ?>'><i class='fas fa-fw fa-xs fa-circle-question text-gray-500'></i></span>
                </div>
                <i class='fas fa-fw fa-sm <?= $no_forced_splash_page ? 'fa-check text-success' : 'fa-times text-muted' ?>'></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'custom_url'): ?>
            <li>
                <div class='<?= $data->plan_settings->custom_url ? null : 'text-muted' ?>'>
                    <?= l('global.plan_settings.custom_url') ?>
                    <span class='ml-1' data-toggle='tooltip' title='<?= l('global.plan_settings.custom_url_help') ?>'><i class='fas fa-fw fa-xs fa-circle-question text-gray-500'></i></span>
                </div>
                <i class='fas fa-fw fa-sm <?= $data->plan_settings->custom_url ? 'fa-check text-success' : 'fa-times text-muted' ?>'></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'deep_links'): ?>
            <li>
                <div class='<?= $data->plan_settings->deep_links ? null : 'text-muted' ?>'>
                    <?= l('global.plan_settings.deep_links') ?>
                    <span class='ml-1' data-toggle='tooltip' title='<?= l('global.plan_settings.deep_links_help') ?>'><i class='fas fa-fw fa-xs fa-circle-question text-gray-500'></i></span>
                </div>
                <i class='fas fa-fw fa-sm <?= $data->plan_settings->deep_links ? 'fa-check text-success' : 'fa-times text-muted' ?>'></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'removable_branding'): ?>
            <li>
                <div class='<?= $data->plan_settings->removable_branding ? null : 'text-muted' ?>'>
                    <?= l('global.plan_settings.removable_branding') ?>
                    <span class='ml-1' data-toggle='tooltip' title='<?= l('global.plan_settings.removable_branding_help') ?>'><i class='fas fa-fw fa-xs fa-circle-question text-gray-500'></i></span>
                </div>
                <i class='fas fa-fw fa-sm <?= $data->plan_settings->removable_branding ? 'fa-check text-success' : 'fa-times text-muted' ?>'></i>
            </li>
        <?php endif ?>

        <?php if(settings()->links->biolinks_is_enabled): ?>

            <?php if($feature == 'custom_branding'): ?>
                <li>
                    <div class='<?= $data->plan_settings->custom_branding ? null : 'text-muted' ?>'>
                        <?= l('global.plan_settings.custom_branding') ?>
                        <span class='ml-1' data-toggle='tooltip' title='<?= l('global.plan_settings.custom_branding_help') ?>'><i class='fas fa-fw fa-xs fa-circle-question text-gray-500'></i></span>
                    </div>
                    <i class='fas fa-fw fa-sm <?= $data->plan_settings->custom_branding ? 'fa-check text-success' : 'fa-times text-muted' ?>'></i>
                </li>
            <?php endif ?>

            <?php if($feature == 'dofollow_is_enabled'): ?>
                <li>
                    <div class='<?= $data->plan_settings->dofollow_is_enabled ? null : 'text-muted' ?>'>
                        <?= l('global.plan_settings.dofollow_is_enabled') ?>
                        <span class='ml-1' data-toggle='tooltip' title='<?= l('global.plan_settings.dofollow_is_enabled_help') ?>'><i class='fas fa-fw fa-xs fa-circle-question text-gray-500'></i></span>
                    </div>
                    <i class='fas fa-fw fa-sm <?= $data->plan_settings->dofollow_is_enabled ? 'fa-check text-success' : 'fa-times text-muted' ?>'></i>
                </li>
            <?php endif ?>

            <?php if($feature == 'leap_link'): ?>
                <li>
                    <div class='<?= $data->plan_settings->leap_link ? null : 'text-muted' ?>'>
                        <?= l('global.plan_settings.leap_link') ?>
                        <span class='ml-1' data-toggle='tooltip' title='<?= l('global.plan_settings.leap_link_help') ?>'><i class='fas fa-fw fa-xs fa-circle-question text-gray-500'></i></span>
                    </div>
                    <i class='fas fa-fw fa-sm <?= $data->plan_settings->leap_link ? 'fa-check text-success' : 'fa-times text-muted' ?>'></i>
                </li>
            <?php endif ?>

            <?php if($feature == 'seo'): ?>
                <li>
                    <div class='<?= $data->plan_settings->seo ? null : 'text-muted' ?>'>
                        <?= l('global.plan_settings.seo') ?>
                        <span class='ml-1' data-toggle='tooltip' title='<?= l('global.plan_settings.seo_help') ?>'><i class='fas fa-fw fa-xs fa-circle-question text-gray-500'></i></span>
                    </div>
                    <i class='fas fa-fw fa-sm <?= $data->plan_settings->seo ? 'fa-check text-success' : 'fa-times text-muted' ?>'></i>
                </li>
            <?php endif ?>

            <?php if($feature == 'fonts'): ?>
                <li>
                    <div class='<?= $data->plan_settings->fonts ? null : 'text-muted' ?>'>
                        <?= l('global.plan_settings.fonts') ?>
                        <span class='ml-1' data-toggle='tooltip' title='<?= l('global.plan_settings.fonts_help') ?>'><i class='fas fa-fw fa-xs fa-circle-question text-gray-500'></i></span>
                    </div>
                    <i class='fas fa-fw fa-sm <?= $data->plan_settings->fonts ? 'fa-check text-success' : 'fa-times text-muted' ?>'></i>
                </li>
            <?php endif ?>

            <?php if($feature == 'custom_css_is_enabled'): ?>
                <li>
                    <div class='<?= $data->plan_settings->custom_css_is_enabled ? null : 'text-muted' ?>'>
                        <?= l('global.plan_settings.custom_css_is_enabled') ?>
                        <span class='ml-1' data-toggle='tooltip' title='<?= l('global.plan_settings.custom_css_is_enabled_help') ?>'><i class='fas fa-fw fa-xs fa-circle-question text-gray-500'></i></span>
                    </div>
                    <i class='fas fa-fw fa-sm <?= $data->plan_settings->custom_css_is_enabled ? 'fa-check text-success' : 'fa-times text-muted' ?>'></i>
                </li>
            <?php endif ?>

            <?php if($feature == 'custom_js_is_enabled'): ?>
                <li>
                    <div class='<?= $data->plan_settings->custom_js_is_enabled ? null : 'text-muted' ?>'>
                        <?= l('global.plan_settings.custom_js_is_enabled') ?>
                        <span class='ml-1' data-toggle='tooltip' title='<?= l('global.plan_settings.custom_js_is_enabled_help') ?>'><i class='fas fa-fw fa-xs fa-circle-question text-gray-500'></i></span>
                    </div>
                    <i class='fas fa-fw fa-sm <?= $data->plan_settings->custom_js_is_enabled ? 'fa-check text-success' : 'fa-times text-muted' ?>'></i>
                </li>
            <?php endif ?>

        <?php endif /* end biolinks dependent group */ ?>

        <?php if($feature == 'statistics'): ?>
            <li>
                <div class='<?= $data->plan_settings->statistics ? null : 'text-muted' ?>'>
                    <?= l('global.plan_settings.statistics') ?>
                    <span class='ml-1' data-toggle='tooltip' title='<?= l('global.plan_settings.statistics_help') ?>'><i class='fas fa-fw fa-xs fa-circle-question text-gray-500'></i></span>
                </div>
                <i class='fas fa-fw fa-sm <?= $data->plan_settings->statistics ? 'fa-check text-success' : 'fa-times text-muted' ?>'></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'temporary_url_is_enabled'): ?>
            <li>
                <div class='<?= $data->plan_settings->temporary_url_is_enabled ? null : 'text-muted' ?>'>
                    <?= l('global.plan_settings.temporary_url_is_enabled') ?>
                    <span class='ml-1' data-toggle='tooltip' title='<?= l('global.plan_settings.temporary_url_is_enabled_help') ?>'><i class='fas fa-fw fa-xs fa-circle-question text-gray-500'></i></span>
                </div>
                <i class='fas fa-fw fa-sm <?= $data->plan_settings->temporary_url_is_enabled ? 'fa-check text-success' : 'fa-times text-muted' ?>'></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'cloaking_is_enabled'): ?>
            <li>
                <div class='<?= $data->plan_settings->cloaking_is_enabled ? null : 'text-muted' ?>'>
                    <?= l('global.plan_settings.cloaking_is_enabled') ?>
                    <span class='ml-1' data-toggle='tooltip' title='<?= l('global.plan_settings.cloaking_is_enabled_help') ?>'><i class='fas fa-fw fa-xs fa-circle-question text-gray-500'></i></span>
                </div>
                <i class='fas fa-fw fa-sm <?= $data->plan_settings->cloaking_is_enabled ? 'fa-check text-success' : 'fa-times text-muted' ?>'></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'app_linking_is_enabled'): ?>
            <li>
                <div class='<?= $data->plan_settings->app_linking_is_enabled ? null : 'text-muted' ?>'>
                    <?= l('global.plan_settings.app_linking_is_enabled') ?>
                    <span class='ml-1' data-toggle='tooltip' title='<?= l('global.plan_settings.app_linking_is_enabled_help') ?>'><i class='fas fa-fw fa-xs fa-circle-question text-gray-500'></i></span>
                </div>
                <i class='fas fa-fw fa-sm <?= $data->plan_settings->app_linking_is_enabled ? 'fa-check text-success' : 'fa-times text-muted' ?>'></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'targeting_is_enabled'): ?>
            <li>
                <div class='<?= $data->plan_settings->targeting_is_enabled ? null : 'text-muted' ?>'>
                    <?= l('global.plan_settings.targeting_is_enabled') ?>
                    <span class='ml-1' data-toggle='tooltip' title='<?= l('global.plan_settings.targeting_is_enabled_help') ?>'><i class='fas fa-fw fa-xs fa-circle-question text-gray-500'></i></span>
                </div>
                <i class='fas fa-fw fa-sm <?= $data->plan_settings->targeting_is_enabled ? 'fa-check text-success' : 'fa-times text-muted' ?>'></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'utm'): ?>
            <li>
                <div class='<?= $data->plan_settings->utm ? null : 'text-muted' ?>'>
                    <?= l('global.plan_settings.utm') ?>
                    <span class='ml-1' data-toggle='tooltip' title='<?= l('global.plan_settings.utm_help') ?>'><i class='fas fa-fw fa-xs fa-circle-question text-gray-500'></i></span>
                </div>
                <i class='fas fa-fw fa-sm <?= $data->plan_settings->utm ? 'fa-check text-success' : 'fa-times text-muted' ?>'></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'password'): ?>
            <li>
                <div class='<?= $data->plan_settings->password ? null : 'text-muted' ?>'>
                    <?= l('global.plan_settings.password') ?>
                    <span class='ml-1' data-toggle='tooltip' title='<?= l('global.plan_settings.password_help') ?>'><i class='fas fa-fw fa-xs fa-circle-question text-gray-500'></i></span>
                </div>
                <i class='fas fa-fw fa-sm <?= $data->plan_settings->password ? 'fa-check text-success' : 'fa-times text-muted' ?>'></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'sensitive_content'): ?>
            <li>
                <div class='<?= $data->plan_settings->sensitive_content ? null : 'text-muted' ?>'>
                    <?= l('global.plan_settings.sensitive_content') ?>
                    <span class='ml-1' data-toggle='tooltip' title='<?= l('global.plan_settings.sensitive_content_help') ?>'><i class='fas fa-fw fa-xs fa-circle-question text-gray-500'></i></span>
                </div>
                <i class='fas fa-fw fa-sm <?= $data->plan_settings->sensitive_content ? 'fa-check text-success' : 'fa-times text-muted' ?>'></i>
            </li>
        <?php endif ?>

        <?php if($feature == 'no_ads'): ?>
            <li>
                <div class='<?= $data->plan_settings->no_ads ? null : 'text-muted' ?>'>
                    <?= l('global.plan_settings.no_ads') ?>
                    <span class='ml-1' data-toggle='tooltip' title='<?= l('global.plan_settings.no_ads_help') ?>'><i class='fas fa-fw fa-xs fa-circle-question text-gray-500'></i></span>
                </div>
                <i class='fas fa-fw fa-sm <?= $data->plan_settings->no_ads ? 'fa-check text-success' : 'fa-times text-muted' ?>'></i>
            </li>
        <?php endif ?>

        <?php if(settings()->main->api_is_enabled && $feature == 'api_is_enabled'): ?>
            <li>
                <div class='<?= $data->plan_settings->api_is_enabled ? null : 'text-muted' ?>'>
                    <?= l('global.plan_settings.api_is_enabled') ?>
                    <span class='ml-1' data-toggle='tooltip' title='<?= l('global.plan_settings.api_is_enabled_help') ?>'><i class='fas fa-fw fa-xs fa-circle-question text-gray-500'></i></span>
                </div>
                <i class='fas fa-fw fa-sm <?= $data->plan_settings->api_is_enabled ? 'fa-check text-success' : 'fa-times text-muted' ?>'></i>
            </li>
        <?php endif ?>

        <?php if(settings()->main->white_labeling_is_enabled && $feature == 'white_labeling_is_enabled'): ?>
            <li>
                <div class='<?= $data->plan_settings->white_labeling_is_enabled ? null : 'text-muted' ?>'>
                    <?= l('global.plan_settings.white_labeling_is_enabled') ?>
                    <span class='ml-1' data-toggle='tooltip' title='<?= l('global.plan_settings.white_labeling_is_enabled_help') ?>'><i class='fas fa-fw fa-xs fa-circle-question text-gray-500'></i></span>
                </div>
                <i class='fas fa-fw fa-sm <?= $data->plan_settings->white_labeling_is_enabled ? 'fa-check text-success' : 'fa-times text-muted' ?>'></i>
            </li>
        <?php endif ?>

        <?php if(\Altum\Plugin::is_active('pwa') && settings()->pwa->is_enabled && $feature == 'custom_pwa_is_enabled'): ?>
            <li>
                <div class='<?= $data->plan_settings->custom_pwa_is_enabled ? null : 'text-muted' ?>'>
                    <?= l('global.plan_settings.custom_pwa_is_enabled') ?>
                    <span class='ml-1' data-toggle='tooltip' title='<?= l('global.plan_settings.custom_pwa_is_enabled_help') ?>'><i class='fas fa-fw fa-xs fa-circle-question text-gray-500'></i></span>
                </div>
                <i class='fas fa-fw fa-sm <?= $data->plan_settings->custom_pwa_is_enabled ? 'fa-check text-success' : 'fa-times text-muted' ?>'></i>
            </li>
        <?php endif ?>

        <?php if($feature == sprintf(l('global.plan_settings.export'), '')): ?>
            <?php $enabled_exports_count = count(array_filter((array) $data->plan_settings->export)); ?>

            <?php ob_start() ?>
            <div class='d-flex flex-column'>
                <?php foreach(['csv', 'json', 'pdf'] as $export_key): ?>
                    <?php if($data->plan_settings->export->{$export_key}): ?>
                        <span class='my-1'><?= sprintf(l('global.export_to'), mb_strtoupper($export_key)) ?></span>
                    <?php else: ?>
                        <s class='my-1'><?= sprintf(l('global.export_to'), mb_strtoupper($export_key)) ?></s>
                    <?php endif ?>
                <?php endforeach ?>
            </div>
            <?php $html = ob_get_clean() ?>

            <li>
                <div class='<?= $enabled_exports_count ? null : 'text-muted' ?>'>
                    <?= sprintf(l('global.plan_settings.export'), $enabled_exports_count) ?>
                    <span class="mr-1" data-html="true" data-toggle="tooltip" title="<?= $html ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
                </div>
                <i class='fas fa-fw fa-sm <?= $enabled_exports_count ? 'fa-check text-success' : 'fa-times text-muted' ?>'></i>
            </li>
        <?php endif ?>

        <?php
        $feature_html = trim(ob_get_clean());

        if($feature_html) {
            if($features_in_front[$feature]) {
                $front_html_items[$feature] = $feature_html;
            } else {
                $hidden_html_items[$feature] = $feature_html;
            }
        }
        ?>

    <?php endforeach ?>
    <?php
    $priority_html_items = [];

    foreach(['qr_codes_limit', 'biolinks_limit'] as $priority_feature) {
        if(isset($front_html_items[$priority_feature])) {
            $priority_html_items[] = $front_html_items[$priority_feature];
            unset($front_html_items[$priority_feature]);
        } elseif(isset($hidden_html_items[$priority_feature])) {
            $priority_html_items[] = $hidden_html_items[$priority_feature];
            unset($hidden_html_items[$priority_feature]);
        }
    }

    if(isset($front_html_items['flipbooks_limit'])) {
        $priority_html_items[] = $front_html_items['flipbooks_limit'];
        unset($front_html_items['flipbooks_limit']);
    } elseif(isset($hidden_html_items['flipbooks_limit'])) {
        $priority_html_items[] = $hidden_html_items['flipbooks_limit'];
        unset($hidden_html_items['flipbooks_limit']);
    }

    if($edit_pdf_directly_is_enabled) {
        ob_start();
        ?>
        <li>
            <div>
                <?= l('global.plan_settings.edit_pdf_directly') ?>
            </div>
            <i class="fas fa-fw fa-sm fa-check text-success"></i>
        </li>
        <?php
        $priority_html_items[] = trim(ob_get_clean());
    }

    $front_html_items = array_merge($priority_html_items, array_values($front_html_items));
    $hidden_html_items = array_values($hidden_html_items);

    $visible_features_limit = 6;
    $visible_features = array_slice($front_html_items, 0, $visible_features_limit);
    $more_features = array_merge(array_slice($front_html_items, $visible_features_limit), $hidden_html_items);
    $features_collapse_id = 'plan_features_' . md5(json_encode($data->plan_settings) . microtime(true) . random_int(1, PHP_INT_MAX));
    ?>

    <?= implode('', $visible_features) ?>

    <?php if(!empty($more_features)): ?>
        <li class="pricing-features-more-toggle">
            <button type="button" class="btn btn-sm btn-outline-light btn-block text-reset text-decoration-none font-weight-bold px-4" data-toggle="collapse" data-target="#<?= $features_collapse_id ?>" aria-expanded="false" aria-controls="<?= $features_collapse_id ?>">
                <i class="fas fa-fw fa-sm fa-plus-circle mr-1"></i> + <?= count($more_features) ?> more features
            </button>
        </li>

        <div class="collapse" id="<?= $features_collapse_id ?>">
            <?= implode('', $more_features) ?>
        </div>
    <?php endif ?>
</ul>
