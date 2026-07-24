<?php defined('ALTUMCODE') || die() ?>

<?php $project = $data->project ?>
<?php $requirements = $project->recommendation->requirements ?? null ?>
<?php $uploads_url = \Altum\Uploads::get_full_url('setup_projects') ?>

<div class="d-flex flex-column flex-md-row justify-content-between mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-fw fa-xs fa-clipboard-list text-primary-900 mr-2"></i> Setup project #<?= $project->setup_project_id ?></h1>
    <a href="<?= url('admin/setup-projects') ?>" class="btn btn-gray-300">Back</a>
</div>

<?= \Altum\Alerts::output_alerts() ?>

<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="h5 mb-3">Submission</h2>

                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label class="font-weight-bold">User</label>
                        <div><a href="<?= url('admin/user-view/' . $project->user_id) ?>"><?= e($project->user_name ?: 'User #' . $project->user_id) ?></a></div>
                        <div class="small text-muted"><?= e($project->user_email) ?></div>
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label class="font-weight-bold">Business</label>
                        <div><?= e($project->business_name ?: 'Not provided') ?></div>
                        <div class="small text-muted"><?= e(ucwords(str_replace('_', ' ', $project->industry))) ?></div>
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label class="font-weight-bold">Contact</label>
                        <div><?= e($project->details->contact ?? 'Not provided') ?></div>
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label class="font-weight-bold">Recommended plan</label>
                        <div><?= e($project->recommended_plan_name ?: 'Custom') ?></div>
                    </div>
                </div>

                <div class="row text-center">
                    <div class="col-6 col-md-3 mb-3"><div class="p-3 bg-gray-50 rounded"><strong><?= nr($requirements->flipbooks_needed ?? 0) ?></strong><div class="small text-muted">Flipbooks</div></div></div>
                    <div class="col-6 col-md-3 mb-3"><div class="p-3 bg-gray-50 rounded"><strong><?= nr($requirements->biolinks_needed ?? 0) ?></strong><div class="small text-muted">Bio pages</div></div></div>
                    <div class="col-6 col-md-3 mb-3"><div class="p-3 bg-gray-50 rounded"><strong><?= nr($requirements->qr_codes_needed ?? 0) ?></strong><div class="small text-muted">QR codes</div></div></div>
                    <div class="col-6 col-md-3 mb-3"><div class="p-3 bg-gray-50 rounded"><strong><?= nr($requirements->vcards_needed ?? 0) ?></strong><div class="small text-muted">Cards</div></div></div>
                </div>

                <?php if(in_array($project->industry ?? null, ['restaurant', 'hotel', 'lounge', 'catering'])): ?>
                    <div class="row">
                        <div class="col-12 col-md-4 mb-3">
                            <label class="font-weight-bold"><?= ($project->industry ?? null) == 'hotel' ? 'Hotel outlets' : 'Branches' ?></label>
                            <div><?= nr(($project->industry ?? null) == 'hotel' ? ($project->details->hotel_outlets_count ?? 1) : ($project->details->branches_count ?? 1)) ?></div>
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label class="font-weight-bold">Menu booklets</label>
                            <div><?= nr($project->details->menu_categories_count ?? 1) ?></div>
                        </div>
                    </div>
                <?php endif ?>

                <label class="font-weight-bold"><?= ($project->industry ?? null) == 'hotel' ? 'Outlet names' : 'Branch names' ?></label>
                <pre class="bg-gray-50 rounded p-3"><?= e($project->details->branch_names ?? '') ?></pre>

                <label class="font-weight-bold">Notes / goals</label>
                <pre class="bg-gray-50 rounded p-3"><?= e($project->details->menu_notes ?? '') ?></pre>

                <?php if(($project->industry ?? null) == 'corporate'): ?>
                    <label class="font-weight-bold">Brochures on landing page</label>
                    <div class="bg-gray-50 rounded p-3 mb-3"><?= e(ucwords(str_replace('_', ' ', $project->details->brochure_landing_page ?? 'Not sure'))) ?></div>

                    <label class="font-weight-bold">Number of brochures</label>
                    <div class="bg-gray-50 rounded p-3"><?= nr($project->details->brochure_count ?? 0) ?></div>
                <?php endif ?>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h2 class="h5 mb-3">Uploaded assets</h2>

                <?php if($project->assets->logo ?? null): ?>
                    <div class="mb-3">
                        <label class="font-weight-bold">Logo</label>
                        <div><a href="<?= $uploads_url . $project->assets->logo ?>" target="_blank"><?= e($project->assets->logo) ?></a></div>
                    </div>
                <?php endif ?>

                <label class="font-weight-bold">PDF menus</label>
                <?php if(!empty($project->assets->menus)): ?>
                    <?php foreach($project->assets->menus as $menu): ?>
                        <div class="mb-2">
                            <a href="<?= $uploads_url . $menu->file ?>" target="_blank"><i class="fas fa-fw fa-file-pdf mr-1"></i> <?= e($menu->original_name ?? $menu->file) ?></a>
                        </div>
                    <?php endforeach ?>
                <?php else: ?>
                    <div class="text-muted">No PDFs uploaded.</div>
                <?php endif ?>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-body">
                <form action="" method="post" role="form">
                    <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="custom-select">
                            <?php foreach(['new', 'reviewing', 'waiting_for_user', 'in_setup', 'ready', 'completed'] as $status): ?>
                                <option value="<?= $status ?>" <?= $project->status == $status ? 'selected="selected"' : null ?>><?= ucwords(str_replace('_', ' ', $status)) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="admin_notes">Admin notes</label>
                        <textarea id="admin_notes" name="admin_notes" class="form-control" rows="8"><?= e($project->admin_notes) ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Update project</button>
                </form>
            </div>
        </div>
    </div>
</div>
