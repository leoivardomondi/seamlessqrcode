<?php defined('ALTUMCODE') || die() ?>

<?php
$selected_display_days = array_map('intval', (array) ($data->row->settings->display_days ?? []));
$week_days = [
    1 => ['short' => 'Mon', 'full' => 'Monday'],
    2 => ['short' => 'Tue', 'full' => 'Tuesday'],
    3 => ['short' => 'Wed', 'full' => 'Wednesday'],
    4 => ['short' => 'Thu', 'full' => 'Thursday'],
    5 => ['short' => 'Fri', 'full' => 'Friday'],
    6 => ['short' => 'Sat', 'full' => 'Saturday'],
    7 => ['short' => 'Sun', 'full' => 'Sunday'],
];
?>

<div class="form-group">
    <label class="d-block"><i class="fas fa-fw fa-calendar-week fa-sm text-muted mr-1"></i> Days of the week</label>
    <div class="btn-group-toggle d-flex flex-wrap" data-toggle="buttons" style="gap: 0.35rem;">
        <?php foreach($week_days as $day_number => $day_labels): ?>
            <?php $is_checked = in_array($day_number, $selected_display_days, true); ?>
            <label class="btn btn-sm btn-outline-primary flex-fill mb-1 <?= $is_checked ? 'active' : null ?>" title="<?= $day_labels['full'] ?>">
                <input
                    type="checkbox"
                    name="display_days[]"
                    value="<?= $day_number ?>"
                    autocomplete="off"
                    <?= $is_checked ? 'checked="checked"' : null ?>
                >
                <?= $day_labels['short'] ?>
            </label>
        <?php endforeach ?>
    </div>
    <small class="form-text text-muted">Select specific days of the week when this block should appear (e.g. select Tue for Tuesday-only offers). Leave unselected to show every day.</small>
</div>
