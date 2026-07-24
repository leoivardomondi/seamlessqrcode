<?php defined('ALTUMCODE') || die() ?>

<div class="ltd-page">
    <?= \Altum\Alerts::output_alerts() ?>

    <section class="ltd-hero">
        <canvas class="ltd-hero-field" data-ltd-hero-field aria-hidden="true"></canvas>
        <div class="container text-center">
            <div class="ltd-pill mb-3"><i class="fas fa-fw fa-gift mr-1"></i> Lifetime deal</div>
            <h1 class="ltd-title mb-3">Build your complete QR marketing system for one lifetime payment.</h1>
            <p class="ltd-subtitle mx-auto mb-4">Create dynamic QR codes, bio pages, unlimited hosted flip PDFs, short links, and digital business cards from one self-contained marketing platform.</p>

            <div class="ltd-actions justify-content-center mb-4">
                <a href="#pricing" class="btn btn-primary btn-lg rounded-2x" data-ltd-scroll-pricing><i class="fas fa-fw fa-tags mr-1"></i> Claim lifetime access</a>
                <a href="#pricing" class="btn btn-light btn-lg rounded-2x" data-ltd-scroll-pricing><i class="fas fa-fw fa-arrow-down mr-1"></i> View the offer</a>
            </div>

            <div class="ltd-trust-row">
                <span><i class="fas fa-fw fa-check-circle"></i> One-time payment</span>
                <span><i class="fas fa-fw fa-check-circle"></i> Lifetime access</span>
                <span><i class="fas fa-fw fa-check-circle"></i> No monthly subscription</span>
            </div>

            <a href="#pricing" class="ltd-downlink" data-ltd-scroll-pricing aria-label="Jump to lifetime plans"><i class="fas fa-fw fa-arrow-down"></i></a>
        </div>
    </section>

    <section id="pricing" class="container ltd-pricing-section">
        <div class="text-center mb-4">
            <div class="ltd-pill mb-3"><i class="fas fa-fw fa-coins mr-1"></i> Lifetime pricing</div>
            <h2 class="ltd-section-title mb-2">Choose your lifetime plan.</h2>
            <p class="text-muted mb-0">Everything stays on this page. Pick a lifetime plan and continue directly to checkout from here.</p>
        </div>

        <?= $this->views['plans'] ?>
    </section>
    <section class="container ltd-flipbook-demo-section">
        <div class="row align-items-center">
            <div class="col-12 col-lg-4 mb-4 mb-lg-0">
                <div class="ltd-pill mb-3"><i class="fas fa-fw fa-book-open mr-1"></i> Flip PDF hosting</div>
                <h2 class="ltd-section-title mb-3">Show the real flipbook experience on desktop and mobile.</h2>
                <p class="text-muted mb-4">Your customers can open menus, brochures, and catalogs as hosted softcopy flipbooks from any screen.</p>
                <div class="ltd-flow-steps">
                    <span><i class="fas fa-desktop"></i> Computer reader</span>
                    <span><i class="fas fa-mobile-screen-button"></i> Phone reader</span>
                    <span><i class="fas fa-qrcode"></i> Share by QR code</span>
                    <span><i class="fas fa-chart-line"></i> Track views and reads</span>
                </div>
            </div>
            <div class="col-12 col-lg-8">
                <div class="ltd-screenshot-showcase" aria-label="Real desktop and phone flipbook screenshots">
                    <figure class="ltd-real-shot ltd-real-shot-desktop">
                        <img src="<?= ASSETS_FULL_URL . 'images/lifetime/flipbook-desktop.png' ?>" alt="Desktop flipbook reader screenshot" loading="lazy" />
                    </figure>
                    <figure class="ltd-real-shot ltd-real-shot-phone">
                        <img src="<?= ASSETS_FULL_URL . 'images/lifetime/flipbook-phone.png' ?>" alt="Phone flipbook reader screenshot" loading="lazy" />
                    </figure>
                </div>
            </div>
        </div>
    </section>
    <section class="container ltd-biopage-showcase-section">
        <div class="row align-items-center">
            <div class="col-12 col-lg-5 mb-4 mb-lg-0">
                <div class="ltd-pill mb-3"><i class="fas fa-fw fa-id-card mr-1"></i> Bio pages</div>
                <h2 class="ltd-section-title mb-3">Showcase people, brands, menus, and lead links from one mobile page.</h2>
                <p class="text-muted mb-4">Use real mobile-first pages for vCards, contact saves, social links, menus, PDFs, campaigns, and customer feedback.</p>
                <div class="ltd-flow-steps">
                    <span><i class="fas fa-user-check"></i> Verified profile pages</span>
                    <span><i class="fas fa-address-card"></i> Save-contact buttons</span>
                    <span><i class="fas fa-link"></i> Menus, PDFs, and links</span>
                    <span><i class="fas fa-mobile-screen-button"></i> Built for phone visitors</span>
                </div>
            </div>
            <div class="col-12 col-lg-7">
                <div class="ltd-biopage-stack" aria-label="Real biopage mobile screenshots">
                    <figure class="ltd-bio-shot ltd-bio-shot-left">
                        <img src="<?= ASSETS_FULL_URL . 'images/lifetime/biopage-world-health.png' ?>" alt="World Health Organization bio page screenshot" loading="lazy" />
                    </figure>
                    <figure class="ltd-bio-shot ltd-bio-shot-center">
                        <img src="<?= ASSETS_FULL_URL . 'images/lifetime/biopage-control-risks.png' ?>" alt="Control Risks bio page screenshot" loading="lazy" />
                    </figure>
                    <figure class="ltd-bio-shot ltd-bio-shot-right">
                        <img src="<?= ASSETS_FULL_URL . 'images/lifetime/biopage-parklands.png' ?>" alt="Parklands Sports Club bio page screenshot" loading="lazy" />
                    </figure>
                </div>
            </div>
        </div>
    </section>
    <section class="container ltd-qr-showcase-section">
        <div class="text-center mb-4">
            <div class="ltd-pill mb-3"><i class="fas fa-fw fa-qrcode mr-1"></i> Dynamic QR codes</div>
            <h2 class="ltd-section-title mb-2">Put framed QR codes anywhere people already look.</h2>
            <p class="text-muted mb-0">Use polished QR frames for menus, retail offers, events, vCards, brochures, and campaigns you can update after printing.</p>
        </div>
        <div class="ltd-qr-photo-grid" aria-label="Real framed QR code photo examples">
            <figure class="ltd-qr-photo-card">
                <img src="<?= ASSETS_FULL_URL . 'images/lifetime/qr-frame-restaurant.png' ?>" alt="Framed QR code on a restaurant table" loading="lazy" />
                <figcaption><strong>Restaurant tables</strong><span>Scan to open menus, specials, booking pages, or feedback forms.</span></figcaption>
            </figure>
            <figure class="ltd-qr-photo-card">
                <img src="<?= ASSETS_FULL_URL . 'images/lifetime/qr-frame-retail.png' ?>" alt="Framed QR code on a retail counter" loading="lazy" />
                <figcaption><strong>Retail counters</strong><span>Send shoppers to offers, catalogs, product pages, and loyalty links.</span></figcaption>
            </figure>
            <figure class="ltd-qr-photo-card">
                <img src="<?= ASSETS_FULL_URL . 'images/lifetime/qr-frame-vcard.png' ?>" alt="Framed QR code beside business cards" loading="lazy" />
                <figcaption><strong>Business cards</strong><span>Connect printed cards to vCards, bio pages, PDFs, and sales assets.</span></figcaption>
            </figure>
        </div>
    </section>
    <section id="offer" class="container ltd-section ltd-section-tight">
        <div class="row align-items-center">
            <div class="col-12 col-lg-5 mb-4 mb-lg-0">
                <div class="ltd-pill mb-3"><i class="fas fa-fw fa-bullseye mr-1"></i> What this does</div>
                <h2 class="ltd-section-title mb-3">Turn printed materials, profiles, and PDFs into trackable sales paths.</h2>
                <p class="text-muted mb-0">Instead of sending people to static links, route every scan to the right campaign, update destinations anytime, and measure what gets attention.</p>
            </div>
            <div class="col-12 col-lg-7">
                <div class="ltd-offer-grid">
                    <div><i class="fas fa-qrcode"></i><strong>Dynamic QR campaigns</strong><span>Edit destinations after printing and track scan activity.</span></div>
                    <div><i class="fas fa-mobile-screen-button"></i><strong>Bio landing pages</strong><span>Create mobile pages for offers, videos, payments, and lead capture.</span></div>
                    <div><i class="fas fa-book-open"></i><strong>Unlimited flip PDFs</strong><span>Host catalogs, menus, brochures, portfolios, and lead magnets.</span></div>
                    <div><i class="fas fa-address-card"></i><strong>Digital vCards</strong><span>Make business cards scannable and easy to save on any phone.</span></div>
                </div>
            </div>
        </div>
    </section>

    <section class="ltd-dark-band">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-12 col-lg-5 mb-4 mb-lg-0">
                    <h2 class="mb-3">Why businesses use this as a funnel.</h2>
                    <p class="mb-0">A scan should not just open a link. It should move someone from curiosity to action: view a catalog, save a contact, join a list, book, buy, or message you.</p>
                </div>
                <div class="col-12 col-lg-7">
                    <div class="ltd-check-grid">
                        <div><i class="fas fa-check"></i><span>Update campaigns without reprinting QR codes.</span></div>
                        <div><i class="fas fa-check"></i><span>Use bio pages as focused mobile landing pages.</span></div>
                        <div><i class="fas fa-check"></i><span>Host PDFs instead of sending bulky files manually.</span></div>
                        <div><i class="fas fa-check"></i><span>Give staff, sellers, and founders digital business cards.</span></div>
                        <div><i class="fas fa-check"></i><span>Track traffic sources, countries, devices, and page activity.</span></div>
                        <div><i class="fas fa-check"></i><span>Use one lifetime platform for many marketing campaigns.</span></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="container ltd-section">
        <div class="text-center mb-4">
            <div class="ltd-pill mb-3"><i class="fas fa-fw fa-store mr-1"></i> Use cases</div>
            <h2 class="ltd-section-title mb-2">Built for the work businesses already do.</h2>
            <p class="text-muted mb-0">Use the lifetime account across print, sales, events, social media, and digital document sharing.</p>
        </div>

        <div class="row">
            <div class="col-12 col-md-6 col-xl-3 mb-4">
                <div class="ltd-tool-card h-100">
                    <div class="ltd-icon qr"><i class="fas fa-fw fa-utensils"></i></div>
                    <h3>Restaurants & menus</h3>
                    <p>Publish QR menus and update PDF menus without reprinting table cards.</p>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3 mb-4">
                <div class="ltd-tool-card h-100">
                    <div class="ltd-icon bio"><i class="fas fa-fw fa-briefcase"></i></div>
                    <h3>Sales teams</h3>
                    <p>Give reps vCards, pitch PDFs, and trackable campaign links.</p>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3 mb-4">
                <div class="ltd-tool-card h-100">
                    <div class="ltd-icon pdf"><i class="fas fa-fw fa-file-lines"></i></div>
                    <h3>Catalogs & brochures</h3>
                    <p>Host unlimited flip PDFs for product catalogs, real estate packs, and portfolios.</p>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3 mb-4">
                <div class="ltd-tool-card h-100">
                    <div class="ltd-icon vcard"><i class="fas fa-fw fa-bullhorn"></i></div>
                    <h3>Campaign tracking</h3>
                    <p>Measure which flyers, posters, ads, and events drive scans and visits.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="container ltd-section pt-0">
        <div class="row">
            <div class="col-12 col-lg-4 mb-4">
                <div class="ltd-stat-card h-100">
                    <strong>One account, many funnels</strong>
                    <span>Create QR routes for product launches, PDFs for catalogs, bio pages for campaigns, and vCards for people.</span>
                </div>
            </div>
            <div class="col-12 col-lg-4 mb-4">
                <div class="ltd-stat-card h-100">
                    <strong>Change offers anytime</strong>
                    <span>Keep printed material alive by changing the destination behind each QR code whenever your business changes.</span>
                </div>
            </div>
            <div class="col-12 col-lg-4 mb-4">
                <div class="ltd-stat-card h-100">
                    <strong>Pay once, keep building</strong>
                    <span>Use the lifetime plan to avoid stacking more monthly subscriptions for basic marketing infrastructure.</span>
                </div>
            </div>
        </div>
    </section>

    <section class="container pb-5">
        <div class="ltd-faq-cta text-center">
            <div class="ltd-pill mb-3"><i class="fas fa-fw fa-rocket mr-1"></i> Final call</div>
            <h2 class="ltd-section-title mb-3">Get lifetime access and build every campaign from this toolkit.</h2>
            <p class="text-muted mb-4">Start with a lifetime plan, then create dynamic QR codes, bio pages, flip PDFs, and vCards without sending users to any separate product page.</p>
            <div class="ltd-actions justify-content-center">
                <a href="#pricing" class="btn btn-primary btn-lg rounded-2x" data-ltd-scroll-pricing>Choose lifetime plan</a>
            </div>
        </div>
    </section>
</div>

<?php ob_start() ?>
<style>
    #navbar, footer { display: none !important; }
    .ltd-page { overflow: hidden; }
    .ltd-hero { position: relative; overflow: hidden; padding: 5rem 0 4rem; background: #fff; }
    .ltd-hero:before { content: ''; position: absolute; inset: auto 0 0; height: 42%; background: linear-gradient(180deg, rgba(255,255,255,0), #f8fafc); pointer-events: none; }
    .ltd-hero-field { position: absolute; inset: 0; width: 100%; height: 100%; pointer-events: none; }
    .ltd-hero .container { position: relative; z-index: 1; }
    .ltd-pill { display: inline-flex; align-items: center; border-radius: 999px; padding: .45rem .85rem; background: rgba(37, 99, 235, .1); color: #2563eb; font-size: .78rem; font-weight: 800; text-transform: uppercase; }
    .ltd-title { font-size: clamp(2.35rem, 6vw, 5rem); line-height: 1; font-weight: 900; color: #0f172a; letter-spacing: 0; }
    .ltd-subtitle { max-width: 780px; color: #475569; font-size: 1.15rem; line-height: 1.75; }
    .ltd-actions { display: flex; flex-wrap: wrap; gap: .85rem; }
    .ltd-actions .btn { min-height: 3rem; display: inline-flex; justify-content: center; align-items: center; width: auto; min-width: 13rem; max-width: 19rem; padding-left: 1.25rem; padding-right: 1.25rem; white-space: normal; }
    .ltd-trust-row { display: flex; justify-content: center; flex-wrap: wrap; gap: .75rem; }
    .ltd-trust-row span { padding: .65rem .9rem; border: 1px solid rgba(15,23,42,.08); border-radius: 999px; background: rgba(255,255,255,.78); color: #334155; font-weight: 700; font-size: .9rem; }
    .ltd-trust-row i { color: #059669; }
    .ltd-section { padding-top: 4.5rem; padding-bottom: 4.5rem; }
    .ltd-section-tight { padding-bottom: 2rem; }
    .ltd-pricing-section { padding-top: 3.5rem; padding-bottom: 4.5rem; scroll-margin-top: 1rem; }
    .ltd-downlink { display: inline-flex; align-items: center; justify-content: center; width: 2.75rem; height: 2.75rem; margin-top: 1.5rem; border-radius: 999px; color: #2563eb; background: rgba(255,255,255,.92); box-shadow: 0 .75rem 1.8rem rgba(15,23,42,.12); text-decoration: none; }
    .ltd-pricing-focus { animation: ltd-pricing-focus 1.25s ease; }
    @keyframes ltd-pricing-focus { 0% { box-shadow: 0 0 0 0 rgba(37,99,235,.35); } 100% { box-shadow: 0 0 0 1.5rem rgba(37,99,235,0); } }
    .ltd-section-title { font-size: clamp(1.75rem, 3vw, 2.8rem); line-height: 1.1; font-weight: 850; color: #0f172a; }
    .ltd-flipbook-demo-section { padding: 4.5rem 0; }
    .ltd-flow-steps { display: grid; gap: .75rem; }
    .ltd-flow-steps span { display: flex; align-items: center; gap: .7rem; min-height: 3rem; padding: .75rem 1rem; border: 1px solid rgba(15,23,42,.08); border-radius: .85rem; background: #fff; color: #334155; font-weight: 750; }
    .ltd-flow-steps i { color: #2563eb; }
    .ltd-biopage-showcase-section { padding: 4.5rem 0; }
    .ltd-biopage-stack { position: relative; min-height: 610px; display: flex; align-items: center; justify-content: center; }
    .ltd-bio-shot { position: absolute; width: min(34vw, 225px); margin: 0; border-radius: 1.35rem; overflow: hidden; border: 1px solid rgba(226,232,240,.88); background: #fff; box-shadow: 0 1.35rem 2.7rem rgba(15,23,42,.2); }
    .ltd-bio-shot img { display: block; width: 100%; height: auto; }
    .ltd-bio-shot-left { left: 4%; transform: rotate(-6deg) translateY(1.25rem); z-index: 1; }
    .ltd-bio-shot-center { left: 50%; transform: translateX(-50%) scale(1.06); z-index: 3; }
    .ltd-bio-shot-right { right: 4%; transform: rotate(5deg) translateY(1.1rem); z-index: 2; }
    .ltd-qr-showcase-section { padding: 4.5rem 0; }
    .ltd-qr-photo-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1.1rem; }
    .ltd-qr-photo-card { margin: 0; overflow: hidden; border: 1px solid rgba(15,23,42,.08); border-radius: 1rem; background: #fff; box-shadow: 0 .85rem 2.2rem rgba(15,23,42,.08); }
    .ltd-qr-photo-card img { display: block; width: 100%; aspect-ratio: 4 / 5; object-fit: cover; }
    .ltd-qr-photo-card figcaption { display: block; padding: 1.05rem; }
    .ltd-qr-photo-card strong, .ltd-qr-photo-card span { display: block; }
    .ltd-qr-photo-card strong { color: #0f172a; font-size: 1.02rem; margin-bottom: .35rem; }
    .ltd-qr-photo-card span { color: #64748b; line-height: 1.55; }
    .ltd-screenshot-showcase { display: grid; grid-template-columns: minmax(0, 1fr) minmax(150px, 220px); align-items: center; gap: 1.25rem; }
    .ltd-real-shot { margin: 0; border: 1px solid #dbe3ef; border-radius: 1.15rem; background: #fff; box-shadow: 0 1.25rem 2.6rem rgba(15,23,42,.16); overflow: hidden; }
    .ltd-real-shot img { display: block; width: 100%; height: auto; }
    .ltd-real-shot-desktop { align-self: center; }
    .ltd-real-shot-phone { max-width: 220px; justify-self: end; border-radius: 1.35rem; }
    .ltd-device-showcase { position: relative; display: grid; grid-template-columns: minmax(0, 1fr) 14.5rem; align-items: end; gap: 1.25rem; }
    .ltd-computer-shot, .ltd-phone-shot { overflow: hidden; border: 1px solid #dbe3ef; background: #f8fafc; box-shadow: 0 1.5rem 3rem rgba(15,23,42,.16); }
    .ltd-computer-shot { border-radius: 1.15rem; min-width: 0; }
    .ltd-phone-shot { border-radius: 1.45rem; max-width: 14.5rem; justify-self: end; }
    .ltd-shot-topbar, .ltd-phone-topbar { display: flex; align-items: center; justify-content: space-between; background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #0f172a; }
    .ltd-shot-topbar { height: 3rem; padding: 0 1rem; }
    .ltd-phone-topbar { height: 2.55rem; padding: 0 .75rem; }
    .ltd-shot-topbar > div, .ltd-phone-topbar { gap: .65rem; }
    .ltd-shot-topbar > div { display: flex; align-items: center; }
    .ltd-shot-topbar span, .ltd-phone-topbar span { padding: .32rem .62rem; border-radius: 999px; background: #111827; color: #fff; font-size: .72rem; font-weight: 850; box-shadow: 0 .35rem .75rem rgba(15,23,42,.24); }
    .ltd-shot-topbar b, .ltd-phone-topbar b { display: inline-flex; align-items: center; gap: .45rem; margin-left: .7rem; padding: .35rem .65rem; border-radius: 999px; background: #eef2ff; font-size: .72rem; }
    .ltd-computer-canvas { position: relative; min-height: 360px; display: flex; align-items: center; justify-content: center; padding: 1.6rem 3.2rem; background: #111827; }
    .ltd-spread-shot { display: grid; grid-template-columns: 1fr 1fr; width: min(100%, 540px); aspect-ratio: 1.42 / 1; background: #fff; box-shadow: 0 1.2rem 2rem rgba(0,0,0,.34); }
    .ltd-spread-page { position: relative; padding: 1.25rem; background: #fff; border-right: 1px solid #e2e8f0; }
    .ltd-spread-page:last-child { border-right: 0; }
    .ltd-qa-title { font-weight: 900; color: #111827; margin-bottom: .65rem; }
    .ltd-photo-block { border-radius: .15rem; background: linear-gradient(135deg, #93c5fd, #dbeafe); }
    .ltd-photo-block.sky { height: 5.5rem; margin-bottom: .55rem; background: linear-gradient(180deg, #38bdf8, #dbeafe 45%, #a3a3a3 46%, #475569); }
    .ltd-photo-block.field { height: 6.3rem; margin-bottom: .7rem; background: linear-gradient(180deg, #fde68a, #a16207 54%, #334155 55%, #111827); }
    .ltd-photo-block.building { height: 12.6rem; margin-bottom: .7rem; background: linear-gradient(135deg, #e2e8f0, #64748b 45%, #f59e0b 46%, #334155); }
    .ltd-text-lines span { display: block; height: .32rem; margin-bottom: .34rem; border-radius: 999px; background: #cbd5e1; }
    .ltd-text-lines span:nth-child(2n) { width: 78%; } .ltd-text-lines span:nth-child(3n) { width: 58%; }
    .ltd-spread-page em { position: absolute; right: 1rem; bottom: .75rem; color: #94a3b8; font-style: normal; font-size: 1.35rem; }
    .ltd-shot-arrow { position: absolute; top: 50%; transform: translateY(-50%); width: 2.65rem; height: 2.65rem; border: 0; border-radius: 999px; background: #f8fafc; color: #0f172a; box-shadow: 0 .55rem 1.25rem rgba(15,23,42,.26); }
    .ltd-shot-arrow.left { left: .85rem; } .ltd-shot-arrow.right { right: .85rem; }
    .ltd-phone-canvas { position: relative; min-height: 360px; display: flex; align-items: center; justify-content: center; padding: 1.25rem .9rem; background: #f8fafc; }
    .ltd-phone-page { position: relative; width: 100%; aspect-ratio: 9 / 14; padding: 1.05rem; color: #fff; background: radial-gradient(circle at 55% 76%, rgba(132,204,22,.85), transparent 23%), linear-gradient(135deg, #050505, #18181b); box-shadow: 0 1rem 1.7rem rgba(15,23,42,.22); }
    .ltd-phone-page .ltd-page-brand-row { font-size: .54rem; }
    .ltd-phone-page h4 { margin: 2.2rem 0 1rem; text-transform: uppercase; text-align: center; color: #fff; font-size: .76rem; }
    .ltd-phone-page p { display: flex; justify-content: space-between; gap: .5rem; margin-bottom: .45rem; color: rgba(255,255,255,.76); font-size: .58rem; }
    .ltd-lime-preview { position: absolute; left: 1.1rem; right: 1.1rem; bottom: 2.3rem; height: 5rem; border-radius: 3rem 3rem .8rem .8rem; background: radial-gradient(circle at 30% 35%, rgba(255,255,255,.75), transparent 14%), linear-gradient(135deg, #84cc16, #365314); opacity: .9; }
    .ltd-phone-page em { position: absolute; right: .75rem; bottom: .65rem; color: rgba(255,255,255,.72); font-style: normal; }
    .ltd-phone-arrow { position: absolute; top: 50%; transform: translateY(-50%); width: 2.15rem; height: 2.15rem; border: 0; border-radius: 999px; background: #fff; color: #0f172a; box-shadow: 0 .45rem 1rem rgba(15,23,42,.2); }
    .ltd-phone-arrow.left { left: .1rem; } .ltd-phone-arrow.right { right: .1rem; }
    .ltd-phone-footer { height: 3.45rem; display: grid; grid-template-columns: 1fr auto 1fr; align-items: center; padding: 0 .8rem; border-top: 1px solid #e2e8f0; background: #fff; color: #0f172a; }
    .ltd-phone-footer span { display: flex; flex-direction: column; align-items: center; gap: .18rem; color: #1d4ed8; text-transform: uppercase; font-size: .62rem; font-weight: 850; }
    .ltd-phone-footer span:last-child { justify-self: end; } .ltd-phone-footer strong { font-size: .76rem; position: relative; padding-bottom: .35rem; }
    .ltd-phone-footer strong:after { content: ''; position: absolute; left: 50%; bottom: 0; width: 2.1rem; height: .18rem; transform: translateX(-50%); border-radius: 999px; background: linear-gradient(90deg, #2563eb 55%, #e2e8f0 55%); }    .ltd-offer-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
    .ltd-offer-grid div, .ltd-tool-card, .ltd-stat-card, .ltd-faq-cta { border: 1px solid rgba(15,23,42,.08); border-radius: 1rem; background: #fff; box-shadow: 0 .85rem 2.2rem rgba(15,23,42,.07); }
    .ltd-offer-grid div { padding: 1.1rem; }
    .ltd-offer-grid i { color: #2563eb; font-size: 1.35rem; margin-bottom: .75rem; }
    .ltd-offer-grid strong, .ltd-offer-grid span { display: block; }
    .ltd-offer-grid strong { color: #0f172a; margin-bottom: .35rem; }
    .ltd-offer-grid span { color: #64748b; line-height: 1.55; }
    .ltd-tool-card { padding: 1.35rem; }
    .ltd-tool-card h3 { font-size: 1.05rem; font-weight: 850; margin: 1rem 0 .55rem; color: #0f172a; }
    .ltd-tool-card p { margin: 0; color: #64748b; line-height: 1.65; }
    .ltd-icon { width: 3.5rem; height: 3.5rem; border-radius: .9rem; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; }
    .ltd-icon.qr { color: #2563eb; background: #eff6ff; } .ltd-icon.bio { color: #059669; background: #ecfdf5; } .ltd-icon.pdf { color: #dc2626; background: #fef2f2; } .ltd-icon.vcard { color: #7c3aed; background: #f5f3ff; }
    .ltd-dark-band { padding: 4.5rem 0; color: #fff; background: #0f172a; }
    .ltd-dark-band h2 { font-size: clamp(1.75rem, 3vw, 2.8rem); font-weight: 850; }
    .ltd-dark-band p { color: rgba(255,255,255,.72); line-height: 1.75; }
    .ltd-check-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
    .ltd-check-grid div { display: flex; gap: .75rem; min-height: 100%; border-radius: .9rem; padding: 1rem; background: rgba(255,255,255,.08); }
    .ltd-check-grid i { color: #34d399; margin-top: .2rem; }
    .ltd-stat-card { padding: 1.35rem; }
    .ltd-stat-card strong, .ltd-stat-card span { display: block; }
    .ltd-stat-card strong { color: #0f172a; font-size: 1.05rem; margin-bottom: .45rem; }
    .ltd-stat-card span { color: #64748b; line-height: 1.65; }
    .ltd-faq-cta { padding: 3rem 1.5rem; }
    @media (max-width: 767.98px) { .ltd-hero { padding: 3.5rem 0 2.5rem; } .ltd-actions { flex-direction: column; } .ltd-actions .btn { width: 100%; max-width: none; } .ltd-offer-grid, .ltd-check-grid, .ltd-qr-photo-grid { grid-template-columns: 1fr; } .ltd-section, .ltd-pricing-section, .ltd-flipbook-demo-section, .ltd-biopage-showcase-section, .ltd-qr-showcase-section { padding-top: 3rem; padding-bottom: 3rem; } .ltd-screenshot-showcase { grid-template-columns: 1fr; } .ltd-real-shot-phone { max-width: 240px; justify-self: center; } .ltd-biopage-stack { min-height: 520px; } .ltd-bio-shot { width: 42vw; max-width: 185px; } .ltd-bio-shot-left { left: 0; } .ltd-bio-shot-right { right: 0; } }
</style>
<?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>
<?php ob_start() ?>
<script>
    'use strict';

    document.querySelectorAll('[data-ltd-scroll-pricing]').forEach(element => {
        element.addEventListener('click', event => {
            const pricing = document.querySelector('#pricing');
            if(!pricing) return;

            event.preventDefault();
            pricing.scrollIntoView({ behavior: 'smooth', block: 'start' });
            pricing.classList.remove('ltd-pricing-focus');
            window.setTimeout(() => pricing.classList.add('ltd-pricing-focus'), 120);
        });
    });

    (() => {
        const canvas = document.querySelector('[data-ltd-hero-field]');
        if(!canvas || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        const context = canvas.getContext('2d');
        const colors = ['#2563eb', '#4f46e5', '#7c3aed', '#db2777', '#f97316'];
        const particles = [];
        const pointer = { x: 0, y: 0, active: false };
        let width = 0;
        let height = 0;
        let pixel_ratio = 1;

        const resize = () => {
            const rect = canvas.getBoundingClientRect();
            width = rect.width;
            height = rect.height;
            pixel_ratio = Math.min(window.devicePixelRatio || 1, 2);
            canvas.width = width * pixel_ratio;
            canvas.height = height * pixel_ratio;
            context.setTransform(pixel_ratio, 0, 0, pixel_ratio, 0, 0);

            particles.length = 0;
            const count = Math.min(190, Math.max(90, Math.floor(width * height / 6500)));

            for(let index = 0; index < count; index++) {
                particles.push({
                    x: Math.random() * width,
                    y: Math.random() * height,
                    base_x: Math.random() * width,
                    base_y: Math.random() * height,
                    size: Math.random() * 2.4 + .6,
                    angle: Math.random() * Math.PI,
                    speed: Math.random() * .45 + .15,
                    color: colors[index % colors.length],
                });
            }
        };

        const set_pointer = event => {
            const source = event.touches ? event.touches[0] : event;
            const rect = canvas.getBoundingClientRect();
            pointer.x = source.clientX - rect.left;
            pointer.y = source.clientY - rect.top;
            pointer.active = true;
        };

        const draw = () => {
            context.clearRect(0, 0, width, height);

            particles.forEach(particle => {
                particle.angle += particle.speed * .02;
                const wave_x = Math.cos(particle.angle) * 7;
                const wave_y = Math.sin(particle.angle * 1.4) * 7;
                let target_x = particle.base_x + wave_x;
                let target_y = particle.base_y + wave_y;

                if(pointer.active) {
                    const dx = target_x - pointer.x;
                    const dy = target_y - pointer.y;
                    const distance = Math.max(1, Math.sqrt(dx * dx + dy * dy));
                    const force = Math.max(0, 1 - distance / 260);
                    target_x += (dx / distance) * force * 85;
                    target_y += (dy / distance) * force * 85;
                }

                particle.x += (target_x - particle.x) * .06;
                particle.y += (target_y - particle.y) * .06;

                context.save();
                context.translate(particle.x, particle.y);
                context.rotate(particle.angle);
                context.fillStyle = particle.color;
                context.globalAlpha = .72;
                context.fillRect(-particle.size * 1.8, -particle.size * .45, particle.size * 3.6, particle.size * .9);
                context.restore();
            });

            requestAnimationFrame(draw);
        };

        window.addEventListener('resize', resize, { passive: true });
        canvas.closest('.ltd-hero').addEventListener('mousemove', set_pointer, { passive: true });
        canvas.closest('.ltd-hero').addEventListener('touchmove', set_pointer, { passive: true });
        canvas.closest('.ltd-hero').addEventListener('mouseleave', () => pointer.active = false);

        resize();
        draw();
    })();
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
