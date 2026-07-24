<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <div class="row mb-4">
        <div class="col-12 col-xl d-flex align-items-center mb-3 mb-xl-0">
            <h1 class="h4 m-0"><?= l('templates.header') ?></h1>

            <div class="ml-2">
                <span data-toggle="tooltip" title="<?= l('templates.subheader') ?>">
                    <i class="fas fa-fw fa-info-circle text-muted"></i>
                </span>
            </div>
        </div>
    </div>

    <form id="search" action="" method="get" role="form">
        <div class="form-group">
            <input type="search" name="search" class="form-control form-control-lg" value="" placeholder="<?= l('global.filters.search') ?>" aria-label="<?= l('global.filters.search') ?>" />
        </div>
    </form>

    <?php foreach($data->templates_categories as $category_id => $category): ?>
        <div class="card mt-5 mb-4" style="background: <?= $category->color ?>; color: white;" data-category="<?= $category_id ?>">
            <div class="card-body d-flex justify-content-between">
                <div>
                    <i class="<?= $category->icon ?> fa-fw mr-1"></i>
                    <strong><?= $category->settings->translations->{\Altum\Language::$name}->name ?></strong>
                </div>
                <span data-original-count-string="<?= l('templates.templates_per_category') ?>"></span>
            </div>
        </div>

        <div class="row">
            <?php foreach($data->templates as $template_id => $template): ?>
                <?php if($template->template_category_id != $category_id) continue ?>
                <div class="col-12 col-lg-4 mb-4 icon-zoom-animation" data-template-id="<?= $template_id ?>" data-template-name="<?= $template->settings->translations->{\Altum\Language::$name}->name ?>" data-template-category="<?= $template->template_category_id ?>">
                    <div class="card d-flex flex-column justify-content-between h-100">
                        <div class="card-body" data-toggle="tooltip" title="<?= l('documents.create') ?>">
                            <div class="mb-3 p-3 rounded w-fit-content" style="background: <?= $category->background ?>">
                                <i class="<?= $template->icon ?> fa-fw fa-lg" style="color: <?= $category->color ?>"></i>
                            </div>

                            <div class="mb-2">
                                <a href="<?= url('document-create?type=' . $template_id) ?>" class="stretched-link text-decoration-none text-body">
                                    <span class="h5"><?= $template->settings->translations->{\Altum\Language::$name}->name ?></span>
                                </a>
                            </div>
                            <span class="text-muted"><?= $template->settings->translations->{\Altum\Language::$name}->description ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    <?php endforeach ?>
</div>


<?php ob_start() ?>
<script>
    'use strict';

    document.querySelector('#search').addEventListener('submit', event => {
        event.preventDefault();
    });

    let templates = [];

    document.querySelectorAll('[data-template-id]').forEach(element => {
        let category = element.getAttribute('data-template-category').toLowerCase();

        templates.push({
            id: element.getAttribute('data-template-id'),
            name: element.getAttribute('data-template-name').toLowerCase(),
            category,
        })
    });

    ['change', 'paste', 'keyup', 'search'].forEach(event_type => {
        document.querySelector('input[name="search"]').addEventListener(event_type, event => {
            let string = event.currentTarget.value.toLowerCase();

            /* Hide header sections */
            document.querySelectorAll('[data-category]').forEach(element => {
                if(string.length) {
                    element.classList.add('d-none');
                } else {
                    element.classList.remove('d-none');
                }
            });

            /* Go through each template and decide which to show */
            for(let template of templates) {
                if(template.name.includes(string)) {
                    document.querySelector(`[data-template-id="${template.id}"]`).classList.remove('d-none');
                    document.querySelector(`[data-category="${template.category}"]`).classList.remove('d-none');
                } else {
                    document.querySelector(`[data-template-id="${template.id}"]`).classList.add('d-none');
                }
            }

            process_templates_counts();
        });
    });

    let process_templates_counts = () => {
        document.querySelectorAll('[data-category]').forEach(element => {
            let category = element.getAttribute('data-category');
            let count = document.querySelectorAll(`[data-template-category="${category}"]:not(.d-none)`).length;
            let original_count_string = element.querySelector('[data-original-count-string]').getAttribute('data-original-count-string');
            element.querySelector('[data-original-count-string]').innerText = original_count_string.replace('%s', count);
        })
    }

    process_templates_counts();
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
