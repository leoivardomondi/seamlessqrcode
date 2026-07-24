<?php defined('ALTUMCODE') || die() ?>

<div class="d-flex flex-column flex-md-row justify-content-between mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-fw fa-xs fa-clipboard-list text-primary-900 mr-2"></i> User setup projects</h1>
</div>

<?= \Altum\Alerts::output_alerts() ?>

<div class="table-responsive table-custom-container">
    <table class="table table-custom">
        <thead>
        <tr>
            <th>User</th>
            <th>Business</th>
            <th>Needs</th>
            <th>Recommended plan</th>
            <th>Status</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($data->projects as $project): ?>
            <?php $requirements = $project->recommendation->requirements ?? null; ?>
            <tr>
                <td>
                    <a href="<?= url('admin/user-view/' . $project->user_id) ?>"><?= e($project->user_name ?: 'User #' . $project->user_id) ?></a>
                    <div class="small text-muted"><?= e($project->user_email) ?></div>
                </td>
                <td>
                    <strong><?= e($project->business_name ?: 'Not provided') ?></strong>
                    <div class="small text-muted"><?= e(ucwords(str_replace('_', ' ', $project->industry))) ?></div>
                </td>
                <td class="small text-muted">
                    <?= nr($requirements->flipbooks_needed ?? 0) ?> flipbooks,
                    <?= nr($requirements->biolinks_needed ?? 0) ?> bio pages,
                    <?= nr($requirements->qr_codes_needed ?? 0) ?> QR codes,
                    <?= nr($requirements->vcards_needed ?? 0) ?> cards
                </td>
                <td><?= e($project->recommended_plan_name ?: 'Custom') ?></td>
                <td><span class="badge badge-light"><?= e($project->status) ?></span></td>
                <td class="text-right">
                    <a href="<?= url('admin/setup-projects/view/' . $project->setup_project_id) ?>" class="btn btn-sm btn-outline-primary">Open</a>
                </td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>
</div>

<?php if(!count($data->projects)): ?>
    <div class="card">
        <div class="card-body text-center text-muted">No setup projects yet.</div>
    </div>
<?php endif ?>
