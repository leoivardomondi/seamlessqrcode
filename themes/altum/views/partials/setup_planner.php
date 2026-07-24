<?php defined('ALTUMCODE') || die() ?>
<?php
$setup_project_model = new \Altum\Models\SetupProject();
$submitted_setup_project_id = isset($_GET['setup_project_id']) ? (int) $_GET['setup_project_id'] : null;
$setup_project = $submitted_setup_project_id ? $setup_project_model->get_by_user_id_and_project_id(user()->user_id, $submitted_setup_project_id) : null;
$saved_details = $setup_project->details ?? null;
$pricing_context = \Altum\Router::$controller == 'Lifetime' ? 'lifetime' : 'recurring';
if($setup_project && ($setup_project->recommendation->pricing_context ?? 'recurring') != $pricing_context) {
    $setup_project = null;
}
$recommended_plan_id = $setup_project->recommended_plan_id ?? null;
$recommendation = $setup_project->recommendation ?? null;
$requirements = $recommendation->requirements ?? null;
$recommended_plan_url = $recommended_plan_id && $recommended_plan_id != 'custom'
    ? (\Altum\Router::$controller == 'Lifetime' ? url('lifetime-checkout/' . $recommended_plan_id) : url('pay/' . $recommended_plan_id))
    : url('contact');
?>

<section id="setup-planner" class="setup-planner mb-5">
    <div class="setup-planner-header">
        <div>
            <span class="setup-planner-kicker">Setup planner</span>
            <h2>Find the package that fits before you pay.</h2>
            <p>Tell us what you are setting up. For hospitality, each menu booklet, such as food or drinks, counts as one flipbook.</p>
        </div>

        <?php if($setup_project): ?>
            <div class="setup-planner-recommendation">
                <span>Recommended</span>
                <strong><?= e($setup_project->recommended_plan_name) ?></strong>
                <a href="<?= $recommended_plan_url ?>" class="btn btn-sm btn-primary rounded-2x mt-2">Choose package</a>
            </div>
        <?php endif ?>
    </div>

    <?php if($setup_project && $requirements): ?>
        <div class="setup-planner-summary">
            <div><span>Flipbooks</span><strong><?= nr($requirements->flipbooks_needed ?? 0) ?></strong></div>
            <div><span>Bio pages</span><strong><?= nr($requirements->biolinks_needed ?? 0) ?></strong></div>
            <div><span>QR codes</span><strong><?= nr($requirements->qr_codes_needed ?? 0) ?></strong></div>
            <div><span>Business cards</span><strong><?= nr($requirements->vcards_needed ?? 0) ?></strong></div>
        </div>
    <?php endif ?>

    <form action="<?= url('setup-project') ?>" method="post" enctype="multipart/form-data" class="setup-planner-form" data-setup-planner-form>
        <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />
        <input type="hidden" name="return_url" value="<?= e(\Altum\Router::$original_request . (\Altum\Router::$original_request_query ? '?' . \Altum\Router::$original_request_query : null)) ?>" />
        <input type="hidden" name="pricing_context" value="<?= $pricing_context ?>" />

        <div class="setup-typeform">
            <div class="setup-typeform-progress">
                <span data-setup-step-count>Step 1 of 4</span>
                <div><i data-setup-progress-bar></i></div>
            </div>

            <div class="setup-typeform-stage">
                <div class="setup-typeform-step is-active" data-setup-step data-step-key="industry">
                    <span class="setup-typeform-number">01</span>
                    <h3>What are you setting up?</h3>
                    <p>Choose the closest match so we only ask for the details that matter.</p>

                <label for="setup_industry">Industry</label>
                <select id="setup_industry" name="industry" class="custom-select" data-setup-industry>
                    <?php $saved_industry = $saved_details->industry ?? 'restaurant'; ?>
                    <option value="restaurant" <?= $saved_industry == 'restaurant' ? 'selected="selected"' : null ?>>Restaurant / Cafe</option>
                    <option value="hotel" <?= $saved_industry == 'hotel' ? 'selected="selected"' : null ?>>Hotel with outlets</option>
                    <option value="lounge" <?= $saved_industry == 'lounge' ? 'selected="selected"' : null ?>>Lounge / Bar / Club</option>
                    <option value="catering" <?= $saved_industry == 'catering' ? 'selected="selected"' : null ?>>Catering / Food truck</option>
                    <option value="corporate" <?= $saved_industry == 'corporate' ? 'selected="selected"' : null ?>>Corporate / Business cards</option>
                    <option value="retail" <?= $saved_industry == 'retail' ? 'selected="selected"' : null ?>>Retail / Shop</option>
                    <option value="events" <?= $saved_industry == 'events' ? 'selected="selected"' : null ?>>Events</option>
                    <option value="other" <?= $saved_industry == 'other' ? 'selected="selected"' : null ?>>Other business</option>
                </select>
                </div>

                <div class="setup-typeform-step" data-setup-step data-step-key="identity">
                    <span class="setup-typeform-number">02</span>
                    <h3 data-setup-identity-title>What is the business name?</h3>
                    <p data-setup-identity-copy>This helps us prepare the setup project under the right name.</p>

                <div data-setup-name-group>
                    <label for="setup_business_name" class="mt-3" data-setup-name-label>Business name</label>
                <input id="setup_business_name" name="business_name" type="text" class="form-control" maxlength="128" placeholder="e.g. Urban Bites" value="<?= e($saved_details->business_name ?? '') ?>" data-setup-name-input />
                </div>

                <div data-setup-contact-group>
                    <label for="setup_contact" class="mt-3">Contact / WhatsApp</label>
                    <input id="setup_contact" name="contact" type="text" class="form-control" maxlength="128" placeholder="+254..." value="<?= e($saved_details->contact ?? '') ?>" />
                </div>
                </div>

                <div class="setup-typeform-step" data-setup-step data-step-key="hospitality-details" data-step-types="hospitality" data-setup-hospitality>
                    <span class="setup-typeform-number">03</span>
                    <h3>How many menu locations and booklets?</h3>
                    <p>Each menu booklet, such as food or drinks, counts as one flipbook for each branch or hotel outlet.</p>

                <div class="row">
                    <div class="col-12 col-md-6" data-setup-branches-field>
                        <label for="setup_branches_count">Branches</label>
                        <input id="setup_branches_count" name="branches_count" type="number" min="1" value="<?= e($saved_details->branches_count ?? 1) ?>" class="form-control" data-setup-number />
                    </div>
                    <div class="col-12 col-md-6" data-setup-hotel-outlets-field>
                        <label for="setup_hotel_outlets_count">Hotel outlets</label>
                        <input id="setup_hotel_outlets_count" name="hotel_outlets_count" type="number" min="1" value="<?= e($saved_details->hotel_outlets_count ?? 1) ?>" class="form-control" data-setup-number />
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="setup_menu_categories_count">Menu booklets</label>
                        <input id="setup_menu_categories_count" name="menu_categories_count" type="number" min="1" value="<?= e($saved_details->menu_categories_count ?? 2) ?>" class="form-control" data-setup-number />
                    </div>
                </div>

                <label for="setup_branch_names" class="mt-3" data-setup-location-names-label>Branch / outlet names</label>
                <textarea id="setup_branch_names" name="branch_names" class="form-control" rows="2" placeholder="Main branch, Rooftop bar, Room service"><?= e($saved_details->branch_names ?? '') ?></textarea>
                </div>

                <div class="setup-typeform-step" data-setup-step data-step-key="hospitality-assets" data-step-types="hospitality">
                    <span class="setup-typeform-number">04</span>
                    <h3>Upload what you already have.</h3>
                    <p>Logo and PDF menus are optional, but they help admin prepare your project faster.</p>

                    <div class="setup-upload-grid mt-3">
                        <div>
                            <label>Logo</label>
                            <label for="setup_logo" class="setup-upload-tile">
                                <span><i class="fas fa-image"></i></span>
                                <strong data-setup-logo-label>Upload logo</strong>
                                <small>JPG, PNG, WEBP, SVG</small>
                            </label>
                            <input id="setup_logo" name="logo_file" type="file" class="setup-upload-input" accept=".jpg,.jpeg,.png,.webp,.svg" data-setup-logo-input />
                        </div>

                        <div>
                            <label>PDF menu booklets</label>
                            <div data-setup-menu-upload-list>
                                <label class="setup-upload-tile setup-upload-menu-tile">
                                    <span><i class="fas fa-plus"></i></span>
                                    <strong>Add PDF menu</strong>
                                    <small>Max 50 MB each</small>
                                    <input name="menu_files[]" type="file" class="setup-upload-input" accept=".pdf" data-setup-menu-input />
                                </label>
                            </div>
                            <button type="button" class="setup-upload-add" data-setup-add-menu>
                                <i class="fas fa-plus mr-1"></i> Add another PDF menu
                            </button>
                            <div class="setup-upload-files" data-setup-menu-files></div>
                            <div class="small text-danger d-none mt-2" data-setup-upload-error></div>
                        </div>
                    </div>
                </div>

                <div class="setup-typeform-step" data-setup-step data-step-key="corporate-quantity" data-step-types="corporate" data-setup-corporate>
                    <span class="setup-typeform-number">03</span>
                    <h3>How many business cards do you need?</h3>
                    <p>We will contact you after this to collect the staff details.</p>

                <label for="setup_staff_cards_count">How many business cards?</label>
                <input id="setup_staff_cards_count" name="staff_cards_count" type="number" min="1" value="<?= e($saved_details->staff_cards_count ?? 5) ?>" class="form-control" data-setup-number />
                <input id="setup_department_pages_count" name="department_pages_count" type="hidden" value="0" />
                </div>

                <div class="setup-typeform-step" data-setup-step data-step-key="corporate-brochure" data-step-types="corporate">
                    <span class="setup-typeform-number">04</span>
                    <h3>Should brochures be included on the landing page?</h3>
                    <p>Tell us if the business card QR landing page should also show brochures, profiles, or company documents.</p>

                    <label for="setup_brochure_landing_page">Brochures on landing page</label>
                    <select id="setup_brochure_landing_page" name="brochure_landing_page" class="custom-select">
                        <option value="not_sure" <?= ($saved_details->brochure_landing_page ?? 'not_sure') == 'not_sure' ? 'selected="selected"' : null ?>>Not sure yet</option>
                        <option value="yes" <?= ($saved_details->brochure_landing_page ?? null) == 'yes' ? 'selected="selected"' : null ?>>Yes, include brochures</option>
                        <option value="no" <?= ($saved_details->brochure_landing_page ?? null) == 'no' ? 'selected="selected"' : null ?>>No, business card only</option>
                    </select>

                    <label for="setup_brochure_count" class="mt-3">Number of brochures</label>
                    <input id="setup_brochure_count" name="brochure_count" type="number" min="0" value="<?= e($saved_details->brochure_count ?? 0) ?>" class="form-control" />
                </div>

                <div class="setup-typeform-step" data-setup-step data-step-key="notes" data-step-types="hospitality,other" data-setup-notes>
                    <span class="setup-typeform-number">05</span>
                    <h3>Anything we should know before setup?</h3>
                    <p>Share the first priority, preferred colors, menu structure, or campaign goal.</p>

                <div data-setup-notes-fields>
                    <label for="setup_menu_notes">Notes / goals</label>
                    <textarea id="setup_menu_notes" name="menu_notes" class="form-control" rows="5" placeholder="Tell us what you want ready first, preferred colors, menu booklets, or campaign goals."><?= e($saved_details->menu_notes ?? '') ?></textarea>
                </div>
                </div>

                <div class="setup-typeform-step" data-setup-step data-step-key="review">
                    <span class="setup-typeform-number">06</span>
                    <h3>Review your estimated setup.</h3>
                    <p>Save this request and we will recommend the closest package.</p>

                <div class="setup-planner-live mt-3" data-setup-live>
                    <span>Estimated need</span>
                    <strong data-setup-live-text>2 flipbooks, 2 bio pages, 1 QR code</strong>
                </div>

                <button type="submit" class="btn btn-primary btn-block rounded-2x mt-3">
                    Save setup and recommend package
                </button>
                </div>
            </div>

            <div class="setup-typeform-controls">
                <button type="button" class="btn btn-light rounded-2x" data-setup-back>Back</button>
                <button type="button" class="btn btn-primary rounded-2x" data-setup-next>Next</button>
            </div>
        </div>
    </form>
</section>

<?php ob_start() ?>
<style>
    .setup-planner { border: 1px solid rgba(15,23,42,.08); border-radius: 1rem; background: #fff; box-shadow: 0 1rem 2.5rem rgba(15,23,42,.06); padding: 1.35rem; }
    .setup-planner-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1.25rem; margin-bottom: 1rem; }
    .setup-planner-kicker { display: inline-flex; color: var(--primary); font-size: .78rem; font-weight: 800; text-transform: uppercase; }
    .setup-planner h2 { margin: .35rem 0 .45rem; color: #0f172a; font-size: 1.45rem; font-weight: 800; line-height: 1.2; }
    .setup-planner p { max-width: 54rem; margin: 0; color: #64748b; line-height: 1.65; }
    .setup-planner-recommendation { min-width: 12rem; border-radius: .85rem; background: #eff6ff; padding: .9rem; text-align: center; }
    .setup-planner-recommendation span, .setup-planner-summary span, .setup-planner-live span { display: block; color: #64748b; font-size: .78rem; font-weight: 800; text-transform: uppercase; }
    .setup-planner-recommendation strong { display: block; color: #1d4ed8; font-size: 1.1rem; }
    .setup-planner-summary { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .75rem; margin-bottom: 1rem; }
    .setup-planner-summary div, .setup-planner-card { border: 1px solid rgba(15,23,42,.08); border-radius: .85rem; background: #f8fafc; padding: 1rem; }
    .setup-planner-summary strong { color: #0f172a; font-size: 1.35rem; }
    .setup-typeform { border: 1px solid rgba(15,23,42,.08); border-radius: .95rem; background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); overflow: hidden; }
    .setup-typeform-progress { display: flex; align-items: center; gap: 1rem; border-bottom: 1px solid rgba(15,23,42,.08); padding: .85rem 1rem; }
    .setup-typeform-progress span { color: #64748b; font-size: .8rem; font-weight: 800; text-transform: uppercase; white-space: nowrap; }
    .setup-typeform-progress div { flex: 1; height: .45rem; border-radius: 999px; background: #e2e8f0; overflow: hidden; }
    .setup-typeform-progress i { display: block; width: 20%; height: 100%; border-radius: inherit; background: var(--primary); transition: width .24s ease; }
    .setup-typeform-stage { min-height: 22rem; display: grid; align-items: center; padding: clamp(1.25rem, 3vw, 2.4rem); }
    .setup-typeform-step { display: none; max-width: 42rem; width: 100%; margin: 0 auto; animation: setupStepIn .22s ease both; }
    .setup-typeform-step.is-active { display: block; }
    .setup-typeform-number { display: inline-flex; align-items: center; justify-content: center; width: 2.35rem; height: 2.35rem; border-radius: 50%; background: #eef2ff; color: var(--primary); font-weight: 800; margin-bottom: 1rem; }
    .setup-typeform-step h3 { margin: 0 0 .65rem; color: #0f172a; font-size: clamp(1.55rem, 3vw, 2.3rem); line-height: 1.08; font-weight: 850; letter-spacing: 0; }
    .setup-typeform-step p { margin: 0 0 1.2rem; color: #64748b; font-size: 1rem; line-height: 1.65; }
    .setup-typeform-step label { color: #334155; font-size: .9rem; font-weight: 800; }
    .setup-typeform-step .form-control, .setup-typeform-step .custom-select { min-height: 3.1rem; border-radius: .85rem; border-color: #cbd5e1; font-size: 1rem; }
    .setup-typeform-step textarea.form-control { min-height: 8rem; }
    .setup-typeform-controls { display: flex; justify-content: space-between; gap: .75rem; border-top: 1px solid rgba(15,23,42,.08); padding: 1rem; background: #fff; }
    .setup-typeform-controls .btn { min-width: 7rem; }
    .setup-planner-live { border-radius: .75rem; background: #111827; color: #fff; padding: .85rem; }
    .setup-planner-live strong { display: block; color: #fff; margin-top: .25rem; }
    .setup-upload-grid { display: grid; grid-template-columns: minmax(0, .85fr) minmax(0, 1.15fr); gap: 1rem; }
    .setup-upload-tile { display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 9.25rem; margin: 0; border: 1px dashed #94a3b8; border-radius: 1rem; background: #fff; color: #334155; text-align: center; cursor: pointer; transition: border-color .18s ease, background .18s ease, transform .18s ease; }
    .setup-upload-tile:hover { border-color: var(--primary); background: #f8fbff; transform: translateY(-1px); }
    .setup-upload-tile span { display: inline-flex; align-items: center; justify-content: center; width: 2.8rem; height: 2.8rem; border-radius: 50%; background: #eef2ff; color: var(--primary); font-size: 1.05rem; margin-bottom: .65rem; }
    .setup-upload-tile strong { display: block; color: #0f172a; font-size: .98rem; }
    .setup-upload-tile small { color: #64748b; margin-top: .25rem; }
    .setup-upload-input { position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none; }
    .setup-upload-menu-tile { min-height: 6.6rem; margin-bottom: .65rem; }
    .setup-upload-add { display: inline-flex; align-items: center; border: 0; border-radius: .75rem; background: #eef2ff; color: var(--primary); font-weight: 800; padding: .6rem .85rem; }
    .setup-upload-files { display: grid; gap: .45rem; margin-top: .75rem; }
    .setup-upload-file { display: flex; align-items: center; justify-content: space-between; gap: .75rem; border-radius: .75rem; background: #f1f5f9; color: #334155; padding: .55rem .7rem; font-size: .88rem; }
    .setup-upload-file i { color: #dc2626; margin-right: .35rem; }
    @keyframes setupStepIn { from { opacity: 0; transform: translateY(.5rem); } to { opacity: 1; transform: translateY(0); } }
    @media (max-width: 991.98px) { .setup-planner-header { flex-direction: column; } .setup-planner-summary, .setup-upload-grid { grid-template-columns: 1fr; } .setup-planner-recommendation { width: 100%; } .setup-typeform-stage { min-height: 24rem; } }
</style>
<?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>

<?php ob_start() ?>
<script>
    'use strict';

    document.querySelectorAll('[data-setup-planner-form]').forEach(form => {
        const industry = form.querySelector('[data-setup-industry]');
        const steps = Array.from(form.querySelectorAll('[data-setup-step]'));
        const back = form.querySelector('[data-setup-back]');
        const next = form.querySelector('[data-setup-next]');
        const count = form.querySelector('[data-setup-step-count]');
        const progress = form.querySelector('[data-setup-progress-bar]');
        const contactGroup = form.querySelector('[data-setup-contact-group]');
        const nameLabel = form.querySelector('[data-setup-name-label]');
        const nameInput = form.querySelector('[data-setup-name-input]');
        const identityTitle = form.querySelector('[data-setup-identity-title]');
        const identityCopy = form.querySelector('[data-setup-identity-copy]');
        const branchesField = form.querySelector('[data-setup-branches-field]');
        const hotelOutletsField = form.querySelector('[data-setup-hotel-outlets-field]');
        const locationNamesLabel = form.querySelector('[data-setup-location-names-label]');
        const live = form.querySelector('[data-setup-live-text]');
        const logoInput = form.querySelector('[data-setup-logo-input]');
        const logoLabel = form.querySelector('[data-setup-logo-label]');
        const menuUploadList = form.querySelector('[data-setup-menu-upload-list]');
        const addMenuButton = form.querySelector('[data-setup-add-menu]');
        const menuFiles = form.querySelector('[data-setup-menu-files]');
        const uploadError = form.querySelector('[data-setup-upload-error]');
        const maxPdfSize = 50 * 1024 * 1024;
        let currentIndex = 0;

        const getPath = () => {
            if(['restaurant', 'hotel', 'lounge', 'catering'].includes(industry.value)) return 'hospitality';
            if(industry.value === 'corporate') return 'corporate';
            return 'other';
        };

        const getVisibleSteps = () => {
            const path = getPath();

            return steps.filter(step => {
                const types = step.dataset.stepTypes;
                if(!types) return true;
                return types.split(',').includes(path);
            });
        };

        const update = () => {
            const value = industry.value;
            const branches = Math.max(1, parseInt(form.querySelector('[name="branches_count"]').value || 1, 10));
            const outlets = Math.max(1, parseInt(form.querySelector('[name="hotel_outlets_count"]').value || 1, 10));
            const categories = Math.max(1, parseInt(form.querySelector('[name="menu_categories_count"]').value || 1, 10));
            const staff = Math.max(1, parseInt(form.querySelector('[name="staff_cards_count"]').value || 1, 10));
            const visibleSteps = getVisibleSteps();

            if(currentIndex > visibleSteps.length - 1) currentIndex = visibleSteps.length - 1;

            steps.forEach(step => step.classList.remove('is-active'));
            visibleSteps.forEach((step, index) => {
                const number = step.querySelector('.setup-typeform-number');
                if(number) number.textContent = String(index + 1).padStart(2, '0');
            });
            visibleSteps[currentIndex].classList.add('is-active');

            count.textContent = `Step ${currentIndex + 1} of ${visibleSteps.length}`;
            progress.style.width = `${((currentIndex + 1) / visibleSteps.length) * 100}%`;
            back.disabled = currentIndex === 0;
            next.classList.toggle('d-none', currentIndex === visibleSteps.length - 1);

            if(value === 'corporate') {
                nameLabel.textContent = 'Company / contact name';
                nameInput.placeholder = 'e.g. Acme Ltd or Jane from HR';
                identityTitle.textContent = 'Who should we prepare this for?';
                identityCopy.textContent = 'Use the company name or the person we should reference when contacting you.';
                contactGroup.classList.add('d-none');
            } else {
                nameLabel.textContent = 'Business name';
                nameInput.placeholder = 'e.g. Urban Bites';
                identityTitle.textContent = 'What is the business name?';
                identityCopy.textContent = 'This helps us prepare the setup project under the right name.';
                contactGroup.classList.remove('d-none');
            }

            hotelOutletsField.classList.toggle('d-none', value !== 'hotel');
            branchesField.classList.toggle('d-none', value === 'hotel');
            locationNamesLabel.textContent = value === 'hotel' ? 'Outlet names' : 'Branch names';

            if(getPath() === 'hospitality') {
                const locations = value === 'hotel' ? outlets : branches;
                live.textContent = `${locations * categories} flipbooks, ${locations * 2} bio pages, ${locations} QR codes`;
            } else if(value === 'corporate') {
                live.textContent = `${staff} business cards. We will contact you for staff details.`;
            } else {
                live.textContent = '1 bio page, 1 QR code, interest tags saved';
            }
        };

        industry.addEventListener('change', update);
        form.querySelectorAll('[data-setup-number]').forEach(input => input.addEventListener('input', update));
        logoInput.addEventListener('change', () => {
            logoLabel.textContent = logoInput.files.length ? logoInput.files[0].name : 'Upload logo';
        });

        const renderMenuFiles = () => {
            const selectedFiles = Array.from(form.querySelectorAll('[data-setup-menu-input]'))
                .flatMap(input => Array.from(input.files || []));
            const escapeHtml = string => string.replace(/[&<>"']/g, match => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            })[match]);

            menuFiles.innerHTML = selectedFiles.map(file => `
                <div class="setup-upload-file">
                    <span><i class="fas fa-file-pdf"></i>${escapeHtml(file.name)}</span>
                    <small>${(file.size / 1024 / 1024).toFixed(1)} MB</small>
                </div>
            `).join('');
        };

        const validateMenuInput = input => {
            uploadError.classList.add('d-none');
            uploadError.textContent = '';

            for(const file of Array.from(input.files || [])) {
                if(file.size > maxPdfSize) {
                    input.value = '';
                    uploadError.textContent = `${file.name} is larger than 50 MB.`;
                    uploadError.classList.remove('d-none');
                    renderMenuFiles();
                    return false;
                }
            }

            renderMenuFiles();
            return true;
        };

        const bindMenuInput = input => {
            input.addEventListener('change', () => validateMenuInput(input));
        };

        form.querySelectorAll('[data-setup-menu-input]').forEach(bindMenuInput);
        addMenuButton.addEventListener('click', () => {
            const label = document.createElement('label');
            label.className = 'setup-upload-tile setup-upload-menu-tile';
            label.innerHTML = `
                <span><i class="fas fa-plus"></i></span>
                <strong>Add PDF menu</strong>
                <small>Max 50 MB each</small>
                <input name="menu_files[]" type="file" class="setup-upload-input" accept=".pdf" data-setup-menu-input />
            `;
            menuUploadList.appendChild(label);
            const input = label.querySelector('[data-setup-menu-input]');
            bindMenuInput(input);
            input.click();
        });

        form.addEventListener('submit', event => {
            for(const input of Array.from(form.querySelectorAll('[data-setup-menu-input]'))) {
                if(!validateMenuInput(input)) {
                    event.preventDefault();
                    return;
                }
            }
        });
        next.addEventListener('click', () => {
            const visibleSteps = getVisibleSteps();
            currentIndex = Math.min(currentIndex + 1, visibleSteps.length - 1);
            update();
        });
        back.addEventListener('click', () => {
            currentIndex = Math.max(currentIndex - 1, 0);
            update();
        });
        update();
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
