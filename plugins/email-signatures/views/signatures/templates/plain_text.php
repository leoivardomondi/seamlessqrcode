<?php defined('ALTUMCODE') || die() ?>

<div dir="{{DIRECTION}}">
    <div id="signature_sign_off">{{SIGN_OFF}}</div>
    <div id="signature_full_name">{{FULL_NAME}}</div>

    <div id="signature_company_wrapper">
        <span id="signature_job_title">{{JOB_TITLE}}</span>
        <span id="signature_department">{{DEPARTMENT}}</span>
        <span id="signature_company">{{COMPANY}}</span>
    </div>

    <div id="signature_contact_wrapper">
        <br />
        <div id="signature_website_url">🔗 {{WEBSITE_URL}}</div>
        <div id="signature_email">✉️ {{EMAIL}}</div>
        <div id="signature_address">🗺 {{ADDRESS}}</div>
        <div id="signature_address_url">📍 {{ADDRESS_URL}}</div>
        <div id="signature_phone_number">#️⃣ {{PHONE_NUMBER}}</div>
        <div id="signature_whatsapp">📱 {{WHATSAPP}}</div>
        <div id="signature_facebook_messenger">💬 m.me/{{FACEBOOK_MESSENGER}}</div>
        <div id="signature_telegram">⚡️ t.me/{{TELEGRAM}}</div>
    </div>

    <div id="signature_socials_wrapper">
        <br />
        <?php foreach(require \Altum\Plugin::get('email-signatures')->path . 'includes/signature_socials.php' as $key => $social): ?>
            <div id="<?= 'signature_' . $key ?>"><?= sprintf(str_replace('https://', '', $social['format']), '{{' . mb_strtoupper($key) . '}}') ?></div>
        <?php endforeach ?>
    </div>

    <div id="signature_disclaimer"><br />{{DISCLAIMER}}</div>

    <div id="signature_branding"><br />{{BRANDING}}</div>
</div>
