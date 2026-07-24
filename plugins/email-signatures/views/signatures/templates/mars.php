<?php defined('ALTUMCODE') || die() ?>
<style>
    .altum a {
        text-decoration: none !important;
        color: {{LINK_COLOR}} !important;
    }
</style>

<div dir="{{DIRECTION}}" class="altum" style="margin: 0 !important; padding: 0 !important; width: {{WIDTH}}px !important; max-width: 450px !important;">
    <div style="font-family: {{FONT_FAMILY}}; font-size: {{FONT_SIZE}}px; color: {{TEXT_COLOR}};">
        <table cellpadding="0" cellspacing="0" border="0" role="presentation" style="border-collapse: collapse !important; font-size: inherit;">
            <tr>
                <td id="signature_image_url" style="padding: 0 25px 0 0; line-height: 0; vertical-align: top;">
                    <img src="{{IMAGE_URL}}" alt="{{FULL_NAME}}" style="width: {{IMAGE_WIDTH}}px; height: auto; border-radius: {{IMAGE_BORDER_RADIUS}}px;" />
                </td>
                <td>
                    <div>
                        <div id="signature_sign_off">{{SIGN_OFF}}</div>
                        <div id="signature_full_name" style="color: {{FULL_NAME_COLOR}};">{{FULL_NAME}}</div>

                        <div id="signature_company_wrapper">
                            <span id="signature_job_title">{{JOB_TITLE}}</span>
                            <span id="signature_department">{{DEPARTMENT}}</span>
                            <span id="signature_company">{{COMPANY}}</span>
                        </div>

                        <div id="signature_contact_wrapper">
                            <hr style="border-top: {{SEPARATOR_SIZE}}px solid {{THEME_COLOR}}; margin: 16px 0;" />
                            <div id="signature_website_url"><small>🔗 <a href="{{WEBSITE_URL}}" target="_blank">{{WEBSITE_URL}}</a></small></div>
                            <div id="signature_email"><small>✉️ <a href="mailto:{{EMAIL}}" target="_blank">{{EMAIL}}</a></small></div>
                            <div id="signature_address"><small>🗺 <a href="{{ADDRESS_URL}}" target="_blank">{{ADDRESS}}</a></small></div>
                            <div>
                                <span id="signature_phone_number" style="padding-right: 7.5px"><small>#️⃣ <a href="tel:{{PHONE_NUMBER}}" target="_blank">{{PHONE_NUMBER}}</a></small></span>
                                <span id="signature_whatsapp"><small>📱 <a href="https://wa.me/{{WHATSAPP}}" target="_blank">{{WHATSAPP}}</a></small></span>
                            </div>
                            <div>
                                <span id="signature_facebook_messenger" style="padding-right: 7.5px"><small>💬 <a href="https://m.me/{{FACEBOOK_MESSENGER}}" target="_blank">{{FACEBOOK_MESSENGER}}</a></small></span>
                                <span id="signature_telegram"><small>⚡️ <a href="https://t.me/{{TELEGRAM}}" target="_blank">{{TELEGRAM}}</a></small></span>
                            </div>
                        </div>

                        <div id="signature_socials_wrapper">
                            <br />
                            <?php foreach(require \Altum\Plugin::get('email-signatures')->path . 'includes/signature_socials.php' as $key => $social): ?>
                                <span id="<?= 'signature_' . $key ?>" style="padding-right: {{SOCIALS_PADDING}}px; line-height: 30px;">
                                    <a href="<?= sprintf($social['format'], '{{' . mb_strtoupper($key) . '}}') ?>" target="_blank">
                                        <img src="<?= ASSETS_FULL_URL . 'images/signatures/socials/' . $key . '.png' ?>" style="width: {{SOCIALS_WIDTH}}px; height: auto;" alt="<?= $social['name'] ?>" />
                                    </a>
                                </span>
                            <?php endforeach ?>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>

<small id="signature_disclaimer" style="color: gray;"><br />{{DISCLAIMER}}</small>

<div id="signature_branding"><br />{{BRANDING}}</div>
