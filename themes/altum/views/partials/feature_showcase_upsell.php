<?php defined('ALTUMCODE') || die() ?>

<?php
$variant = $data->variant ?? 'teams';
$cards = $data->cards ?? [];
$primary_url = $data->primary_url ?? url('plan/upgrade');
$secondary_url = $data->secondary_url ?? url('plan');
?>

<div class="sq-feature-showcase sq-feature-showcase-<?= $variant ?>">
    <div class="sq-feature-hero">
        <div class="sq-feature-copy">
            <div class="sq-feature-kicker"><?= htmlspecialchars($data->kicker ?? '', ENT_QUOTES, 'UTF-8') ?></div>
            <h1><?= htmlspecialchars($data->title ?? '', ENT_QUOTES, 'UTF-8') ?></h1>
            <p><?= htmlspecialchars($data->subtitle ?? '', ENT_QUOTES, 'UTF-8') ?></p>

            <div class="sq-feature-actions">
                <a href="<?= htmlspecialchars($primary_url, ENT_QUOTES, 'UTF-8') ?>" class="sq-feature-primary">
                    <?= htmlspecialchars($data->primary_label ?? '', ENT_QUOTES, 'UTF-8') ?>
                </a>
                <a href="<?= htmlspecialchars($secondary_url, ENT_QUOTES, 'UTF-8') ?>" class="sq-feature-secondary">
                    <?= htmlspecialchars($data->secondary_label ?? '', ENT_QUOTES, 'UTF-8') ?>
                    <i class="fas fa-fw fa-xs fa-external-link-alt"></i>
                </a>
            </div>
        </div>

        <div class="sq-feature-art" aria-hidden="true">
            <?php if($variant == 'teams'): ?>
                <div class="sq-teams-panel">
                    <div class="sq-teams-panel-header">
                        <strong>Teams</strong>
                        <span><i class="fas fa-search"></i></span>
                        <small><i class="fas fa-user-plus"></i> Invite users</small>
                        <em>Create team</em>
                    </div>
                    <?php foreach(['New York', 'Los Angeles', 'Seattle'] as $index => $city): ?>
                        <div class="sq-team-row">
                            <span class="sq-city-avatar sq-city-avatar-<?= $index ?>"></span>
                            <b><?= $city ?></b>
                            <span class="sq-avatar-stack">
                                <i></i><i></i><i></i>
                            </span>
                            <small><?= ['Admin', 'Editor', 'Viewer'][$index] ?></small>
                        </div>
                    <?php endforeach ?>
                </div>
                <div class="sq-teams-share-card">
                    <div class="sq-mini-phone"></div>
                    <div class="sq-qr-disc"></div>
                    <strong>Share with New York?</strong>
                    <span><i class="fas fa-user-plus"></i> Share</span>
                </div>
            <?php else: ?>
                <div class="sq-domain-toggle">
                    <span>Enable white<br />labeled domain?</span>
                    <i></i>
                </div>
                <div class="sq-domain-flow">
                    <b>smqr.link <i class="fas fa-angle-right"></i></b>
                    <span><i class="fas fa-arrow-down"></i></span>
                    <b>aurora.com <i class="fas fa-angle-right"></i></b>
                </div>
                <div class="sq-domain-phone">
                    <div class="sq-phone-top"></div>
                    <div class="sq-phone-screen">
                        <div class="sq-qr-large"></div>
                        <b>aurora.com</b>
                    </div>
                </div>
            <?php endif ?>
        </div>
    </div>

    <div class="sq-feature-band">
        <div class="sq-feature-grid">
            <?php foreach($cards as $card): ?>
                <article class="sq-feature-card">
                    <div class="sq-feature-card-visual sq-card-visual-<?= htmlspecialchars($card['visual'], ENT_QUOTES, 'UTF-8') ?>">
                        <?php if($card['visual'] == 'team_share'): ?>
                            <div class="sq-share-widget">
                                <strong>Share the campaign<br />with your team</strong>
                                <span class="sq-avatar-stack sq-avatar-stack-large"><i></i><i></i><i></i></span>
                                <em>Share</em>
                            </div>
                        <?php elseif($card['visual'] == 'brand_templates'): ?>
                            <div class="sq-qr-cluster"><i></i><i></i><i></i></div>
                            <div class="sq-template-widget"><strong>Share templates</strong><span><i></i><i></i><i></i><b>+3</b></span><em>Share</em></div>
                        <?php elseif($card['visual'] == 'campaign_folders'): ?>
                            <div class="sq-folder-map"><span>Summer Campaign<br /><small>2 Folders | 0 Codes</small></span><i></i><span>Marketing<br /><small>1 Folders | 0 Codes</small></span><span>Packaging<br /><small>1 Folders | 0 Codes</small></span></div>
                        <?php elseif($card['visual'] == 'permission_list'): ?>
                            <div class="sq-permission-list">
                                <?php foreach(['Creator', 'Member', 'View only'] as $role): ?>
                                    <span><i></i><b><?= $role ?></b></span>
                                <?php endforeach ?>
                            </div>
                        <?php elseif($card['visual'] == 'domain_phone'): ?>
                            <div class="sq-domain-card-phone"><span></span><b>aurora.com</b></div>
                        <?php elseif($card['visual'] == 'domain_roi'): ?>
                            <div class="sq-domain-roi"><b>aurora.com</b><span>863<small>Scans</small></span><i></i></div>
                        <?php elseif($card['visual'] == 'brand_phone'): ?>
                            <div class="sq-brand-phone"><span></span><b>aurora.com</b></div>
                        <?php elseif($card['visual'] == 'share_domain'): ?>
                            <div class="sq-share-domain"><b>aurora.com</b><span class="sq-avatar-stack sq-avatar-stack-large"><i></i><i></i><i></i><i></i></span><em>Share domain</em></div>
                        <?php endif ?>
                    </div>
                    <h2><?= htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                    <p><?= htmlspecialchars($card['copy'], ENT_QUOTES, 'UTF-8') ?></p>
                </article>
            <?php endforeach ?>
        </div>

        <div class="sq-feature-actions sq-feature-actions-bottom">
            <a href="<?= htmlspecialchars($primary_url, ENT_QUOTES, 'UTF-8') ?>" class="sq-feature-primary">
                <?= htmlspecialchars($data->primary_label ?? '', ENT_QUOTES, 'UTF-8') ?>
            </a>
            <a href="<?= htmlspecialchars($secondary_url, ENT_QUOTES, 'UTF-8') ?>" class="sq-feature-secondary">
                <?= htmlspecialchars($data->secondary_label ?? '', ENT_QUOTES, 'UTF-8') ?>
                <i class="fas fa-fw fa-xs fa-external-link-alt"></i>
            </a>
        </div>
    </div>
</div>

<style>
    .sq-feature-showcase { color: #080808; margin: 0 calc(50% - 50vw) 0; background: #fff; }
    .sq-feature-showcase, .sq-feature-showcase * { letter-spacing: 0; }
    .sq-feature-hero { display: grid; grid-template-columns: minmax(0, 1fr) 582px; max-width: 1164px; min-height: 432px; margin: 0 auto; background: #fff; }
    .sq-feature-copy { display: flex; flex-direction: column; justify-content: center; padding: 3.65rem 3rem 3.65rem 2.35rem; max-width: 560px; }
    .sq-feature-kicker { margin-bottom: 1.65rem; font-size: .75rem; line-height: 1; font-weight: 800; text-transform: uppercase; }
    .sq-feature-copy h1 { max-width: 30rem; margin: 0; color: #050505; font-size: clamp(1.65rem, 2.1vw, 1.9rem); line-height: 1.16; font-weight: 500; }
    .sq-feature-copy p { max-width: 31rem; margin: 1.35rem 0 0; color: #3d3d3d; font-size: .86rem; line-height: 1.5; }
    .sq-feature-actions { display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap; margin-top: 1.35rem; }
    .sq-feature-primary { display: inline-flex; align-items: center; justify-content: center; min-height: 2rem; padding: .55rem .85rem; border: 1px solid #e8b75c; background: #f5bf68; color: #060606; font-size: .72rem; line-height: 1; font-weight: 800; text-decoration: none; }
    .sq-feature-primary:hover { color: #060606; background: #efb354; text-decoration: none; }
    .sq-feature-secondary { display: inline-flex; align-items: center; gap: .35rem; color: #080808; font-size: .72rem; font-weight: 800; text-decoration: none; }
    .sq-feature-secondary:hover { color: #080808; text-decoration: underline; }
    .sq-feature-art { position: relative; min-height: 432px; overflow: hidden; background: #88a34d; }
    .sq-feature-showcase-domains .sq-feature-art { background: #f9d233; }
    .sq-feature-band { padding: 2.35rem 1rem 1.9rem; background: #f3f2f0; }
    .sq-feature-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .78rem; max-width: 1164px; margin: 0 auto; }
    .sq-feature-card-visual { position: relative; height: 190px; overflow: hidden; border: 1px solid rgba(0,0,0,.06); background: #bd9798; }
    .sq-card-visual-brand_templates, .sq-card-visual-domain_phone { background: #adc4f1; }
    .sq-card-visual-campaign_folders { background: #ca8ed2; }
    .sq-card-visual-permission_list, .sq-card-visual-share_domain { background: #d0b374; }
    .sq-card-visual-domain_roi { background: #a75e60; }
    .sq-card-visual-brand_phone { background: #afc4f1; }
    .sq-feature-card h2 { margin: 1.05rem 0 .55rem; font-size: 1.08rem; line-height: 1.2; color: #050505; font-weight: 500; }
    .sq-feature-card p { margin: 0; color: #323232; font-size: .74rem; line-height: 1.45; }
    .sq-feature-actions-bottom { justify-content: center; margin-top: 2.6rem; }
    .sq-teams-panel { position: absolute; left: 34px; top: 61px; width: 353px; min-width: 0; padding: 1.34rem 1rem .98rem; background: #fff; box-shadow: 0 14px 30px rgba(30,43,16,.18); }
    .sq-teams-panel-header { display: grid; grid-template-columns: 1fr auto auto auto; align-items: center; gap: .65rem; margin-bottom: 1.2rem; }
    .sq-teams-panel-header strong { font-size: 1.55rem; font-weight: 500; }
    .sq-teams-panel-header small, .sq-teams-panel-header em { padding: .55rem .65rem; background: #f4f2ef; color: #050505; font-size: .62rem; font-style: normal; font-weight: 800; white-space: nowrap; }
    .sq-teams-panel-header em { background: #078f3e; color: #fff; }
    .sq-team-row { display: grid; grid-template-columns: 42px 1fr 106px 70px; align-items: center; gap: .7rem; padding: .7rem 0; border-top: 1px solid #e6e0d8; }
    .sq-team-row b { font-size: .78rem; }
    .sq-team-row small { padding: .45rem .55rem; border: 1px solid #d5d0c8; text-align: center; color: #111; }
    .sq-city-avatar, .sq-avatar-stack i, .sq-permission-list i { display: inline-block; width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #d8b59d, #7c8da1); border: 2px solid #fff; }
    .sq-city-avatar-1 { background: linear-gradient(135deg, #b76d48, #e4b56b); }
    .sq-city-avatar-2 { background: linear-gradient(135deg, #8fb1e2, #735b42); }
    .sq-avatar-stack { display: inline-flex; align-items: center; }
    .sq-avatar-stack i { width: 28px; height: 28px; margin-left: -8px; }
    .sq-avatar-stack i:first-child { margin-left: 0; background: linear-gradient(135deg, #4a78a6, #eeaa62); }
    .sq-avatar-stack i:nth-child(2) { background: linear-gradient(135deg, #5ab1a8, #78483b); }
    .sq-avatar-stack i:nth-child(3) { background: linear-gradient(135deg, #f7d46f, #212121); }
    .sq-avatar-stack i:nth-child(4) { background: linear-gradient(135deg, #7ea66a, #3c5675); }
    .sq-teams-share-card { position: absolute; right: 36px; top: 138px; width: 196px; padding: .88rem 1rem 1rem; background: #fff; box-shadow: 0 14px 28px rgba(33,35,25,.22); }
    .sq-mini-phone { width: 72px; height: 118px; background: linear-gradient(#8fb65d, #253915); border: 4px solid #20312e; border-radius: 8px; }
    .sq-qr-disc, .sq-qr-large, .sq-qr-cluster i { background-color: #8db34f; background-image: linear-gradient(90deg, rgba(0,0,0,.55) 10%, transparent 10%), linear-gradient(rgba(0,0,0,.55) 10%, transparent 10%); background-size: 9px 9px; border: 4px solid #1d2519; }
    .sq-qr-disc { position: absolute; top: 28px; right: 20px; width: 86px; height: 86px; border-radius: 50%; }
    .sq-teams-share-card strong, .sq-teams-share-card span { display: block; margin-top: .9rem; font-size: .95rem; font-weight: 500; }
    .sq-teams-share-card span, .sq-share-widget em, .sq-template-widget em, .sq-share-domain em { padding: .55rem; background: #1f1f1f; color: #fff; text-align: center; font-size: .75rem; font-style: normal; font-weight: 800; }
    .sq-domain-toggle { position: absolute; left: 22px; top: 98px; display: flex; align-items: center; gap: 2rem; padding: 1.1rem 1.5rem; border-radius: 8px; background: rgba(255,255,255,.7); color: #111; font-size: .98rem; font-weight: 600; }
    .sq-domain-toggle i { width: 52px; height: 30px; border-radius: 999px; background: #1f1c1b; }
    .sq-domain-toggle i:after { content: ""; display: block; width: 22px; height: 22px; margin: 4px 4px 4px auto; border-radius: 50%; background: #fff; }
    .sq-domain-flow { position: absolute; left: 100px; top: 196px; width: 216px; padding: 2rem 1.6rem; background: #fff; border-radius: 8px; text-align: center; }
    .sq-domain-flow b, .sq-domain-phone b, .sq-share-domain b, .sq-domain-card-phone b, .sq-brand-phone b, .sq-domain-roi b { display: inline-flex; align-items: center; gap: .35rem; padding: .45rem .75rem; border-radius: 999px; background: #f7d51f; color: #141414; font-size: .82rem; }
    .sq-domain-flow span { display: block; width: 2px; height: 56px; margin: .45rem auto; background: #50471f; }
    .sq-domain-flow span i { transform: translate(-50%, 20px); width: 26px; height: 26px; border-radius: 50%; background: #222; color: #fff; line-height: 26px; }
    .sq-domain-phone { position: absolute; right: 48px; top: 60px; width: 178px; height: 350px; padding: 22px 12px; border: 8px solid #1c1b1b; border-radius: 28px; background: #050505; box-shadow: 0 22px 36px rgba(77,56,0,.24); transform: rotate(7deg); }
    .sq-phone-top { position: absolute; left: 50%; top: 8px; width: 58px; height: 8px; transform: translateX(-50%); border-radius: 999px; background: #191919; }
    .sq-phone-screen { display: flex; height: 100%; flex-direction: column; align-items: center; justify-content: center; gap: .75rem; border-radius: 18px; background: #f2efe2; }
    .sq-qr-large { width: 92px; height: 92px; border-radius: 50%; border-color: #d6ba28; background-color: #f2f0da; }
    .sq-share-widget, .sq-template-widget, .sq-share-domain { position: absolute; inset: 18px 34px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .75rem; background: #fff; border-radius: 5px; text-align: center; box-shadow: 0 12px 26px rgba(76,38,38,.16); }
    .sq-card-visual-team_share .sq-share-widget { inset: 17px 34px 16px; }
    .sq-avatar-stack-large i { width: 48px; height: 48px; margin-left: -12px; }
    .sq-share-widget strong, .sq-template-widget strong { font-size: .9rem; line-height: 1.2; font-weight: 600; }
    .sq-share-widget em, .sq-template-widget em, .sq-share-domain em { width: 110px; border-radius: 999px; }
    .sq-qr-cluster { position: absolute; left: 18px; top: 16px; display: flex; gap: 8px; }
    .sq-qr-cluster i { width: 66px; height: 66px; border-radius: 50%; border-width: 2px; }
    .sq-template-widget { inset: 68px 18px 15px 108px; }
    .sq-template-widget span { display: flex; align-items: center; gap: 0; }
    .sq-template-widget span i { width: 28px; height: 28px; margin-left: -7px; border-radius: 50%; background: #c58b80; border: 2px solid #fff; }
    .sq-template-widget span i:first-child { margin-left: 0; }
    .sq-template-widget span b { margin-left: 5px; font-size: .86rem; font-weight: 500; }
    .sq-folder-map { position: absolute; inset: 28px 22px; display: grid; grid-template-columns: 1fr 1fr; gap: 16px 36px; align-items: center; }
    .sq-folder-map span { min-height: 78px; padding: 48px .65rem .5rem; border-radius: 8px; background: #fff; box-shadow: 0 12px 20px rgba(80,29,90,.12); font-size: .72rem; }
    .sq-folder-map span:before { content: ""; position: absolute; width: 48px; height: 34px; margin-top: -38px; border-radius: 4px; background: #bbcaf7; }
    .sq-folder-map i { position: absolute; left: 50%; top: 30px; bottom: 30px; width: 2px; background: #1e1e1e; opacity: .65; }
    .sq-permission-list { position: absolute; inset: 12px 42px; display: grid; gap: 12px; }
    .sq-permission-list span { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .65rem; background: #fff; box-shadow: 0 6px 16px rgba(71,48,15,.14); }
    .sq-permission-list i { width: 42px; height: 42px; }
    .sq-permission-list b { padding: .55rem .8rem; background: #24211e; color: #fff; font-size: .68rem; font-weight: 600; }
    .sq-domain-card-phone, .sq-brand-phone { position: absolute; left: 44px; top: 16px; width: 138px; height: 160px; border: 6px solid #111; border-radius: 18px; background: #f6f3e4; transform: rotate(10deg); }
    .sq-domain-card-phone span, .sq-brand-phone span { display: block; width: 76px; height: 76px; margin: 38px auto 10px; border-radius: 50%; background: #f1efdf; border: 5px solid #d7bf2c; background-image: linear-gradient(90deg, rgba(0,0,0,.4) 12%, transparent 12%), linear-gradient(rgba(0,0,0,.4) 12%, transparent 12%); background-size: 8px 8px; }
    .sq-domain-card-phone b, .sq-brand-phone b { margin-left: 18px; }
    .sq-domain-roi b { position: absolute; left: 22px; top: 54px; }
    .sq-domain-roi span { position: absolute; left: 52px; top: 100px; padding: .75rem 1.15rem; border-radius: 4px; background: #222; color: #fff; font-size: 1.55rem; }
    .sq-domain-roi span small { display: block; font-size: .58rem; text-align: center; }
    .sq-domain-roi i { position: absolute; right: 48px; bottom: 18px; width: 82px; height: 136px; background: #d8c7a6; box-shadow: inset 0 0 0 1px rgba(0,0,0,.08); }
    .sq-brand-phone { left: 76px; height: 190px; transform: rotate(-7deg); }
    .sq-share-domain { inset: 42px 24px 32px; gap: .8rem; }
    .sq-share-domain b { position: absolute; top: -20px; }

    @media (max-width: 991.98px) {
        .sq-feature-showcase { margin-left: calc(50% - 50vw); margin-right: calc(50% - 50vw); }
        .sq-feature-hero { grid-template-columns: 1fr; }
        .sq-feature-copy { padding: 3rem 1.25rem; max-width: none; }
        .sq-feature-art { min-height: 380px; }
        .sq-feature-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 575.98px) {
        .sq-feature-copy h1 { font-size: 2rem; }
        .sq-feature-actions { align-items: stretch; flex-direction: column; gap: .9rem; }
        .sq-feature-primary, .sq-feature-secondary { width: 100%; justify-content: center; }
        .sq-feature-grid { grid-template-columns: 1fr; }
        .sq-teams-panel { left: 4%; width: 92%; min-width: 0; }
        .sq-teams-panel-header { grid-template-columns: 1fr auto; }
        .sq-teams-panel-header small, .sq-teams-panel-header em { display: none; }
        .sq-team-row { grid-template-columns: 38px 1fr 62px; }
        .sq-team-row .sq-avatar-stack { display: none; }
        .sq-teams-share-card { right: 4%; top: 58%; }
        .sq-domain-flow { left: 5%; width: 190px; }
        .sq-domain-toggle { left: 5%; gap: 1rem; }
        .sq-domain-phone { right: -18px; transform: rotate(6deg) scale(.86); }
    }
</style>
