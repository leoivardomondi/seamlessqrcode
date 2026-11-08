let toggle_admin_sidebar = () => {
    /* Open sidebar menu */
    let body = document.querySelector('body');
    body.classList.toggle('admin-sidebar-opened');

    /* Toggle overlay */
    let admin_overlay = document.querySelector('#admin_overlay');
    admin_overlay.style.display == 'none' ? admin_overlay.style.display = 'block' : admin_overlay.style.display = 'none';

    /* Change toggle button content */
    let button = document.querySelector('#admin_menu_toggler');

    if(body.classList.contains('admin-sidebar-opened')) {
        button.innerHTML = `<i class="fas fa-fw fa-times"></i>`;
    } else {
        button.innerHTML = `<i class="fas fa-fw fa-bars"></i>`;
    }
};

/* Toggler for the sidebar */
document.querySelector('#admin_menu_toggler').addEventListener('click', event => {
    event.preventDefault();

    toggle_admin_sidebar();

    let admin_sidebar_is_opened = document.querySelector('body').classList.contains('admin-sidebar-opened');

    if(admin_sidebar_is_opened) {
        document.querySelector('#admin_overlay').removeEventListener('click', toggle_admin_sidebar);
        document.querySelector('#admin_overlay').addEventListener('click', toggle_admin_sidebar);
    } else {
        document.querySelector('#admin_overlay').removeEventListener('click', toggle_admin_sidebar);
    }
});

/* Custom select implementation */
$('select:not([multiple="multiple"]):not([class="input-group-text"]):not([class="custom-select custom-select-sm"]):not([class^="ql"]):not([data-is-not-custom-select])').each(function() {
    let $select = $(this);
    $select.select2({
        dir: document.querySelector('html').dir,
        minimumResultsForSearch: 5,
    });

    /* Make sure to trigger the select when the label is clicked as well */
    let selectId = $select.attr('id');
    if(selectId) {
        $('label[for="' + selectId + '"]').on('click', function(event) {
            event.preventDefault();
            $select.select2('open');
        });
    }
});

/* Squeeze / Collapse admin sidebar */
let init_admin_sidebar_collapse = () => {
    let update_collapse_button_icon = () => {
        let btn = document.querySelector('#admin_sidebar_collapse');
        if(!btn) return;
        if(document.body.classList.contains('admin-sidebar-collapsed')) {
            btn.innerHTML = `<i class="fas fa-fw fa-arrow-right"></i>`;
            btn.setAttribute('title', 'Expand sidebar');
        } else {
            btn.innerHTML = `<i class="fas fa-fw fa-bars"></i>`;
            btn.setAttribute('title', 'Squeeze / Collapse sidebar');
        }
    };

    update_collapse_button_icon();

    let collapse_btn = document.querySelector('#admin_sidebar_collapse');
    if(collapse_btn && !collapse_btn.dataset.initialized) {
        collapse_btn.dataset.initialized = 'true';
        collapse_btn.addEventListener('click', event => {
            event.preventDefault();
            document.body.classList.toggle('admin-sidebar-collapsed');
            let is_collapsed = document.body.classList.contains('admin-sidebar-collapsed');
            localStorage.setItem('admin_sidebar_collapsed', is_collapsed ? 'true' : 'false');
            update_collapse_button_icon();

            setTimeout(() => {
                window.dispatchEvent(new Event('resize'));
            }, 250);
        });
    }

    // Add tooltips to sidebar links for compact mode
    document.querySelectorAll('.admin-sidebar-links li > a').forEach(link => {
        let text = link.innerText.trim();
        if(text && !link.getAttribute('title')) {
            link.setAttribute('title', text);
            $(link).tooltip({
                placement: 'right',
                boundary: 'window',
                trigger: 'hover',
                template: '<div class="tooltip" role="tooltip"><div class="arrow"></div><div class="tooltip-inner text-nowrap"></div></div>'
            });
        }
    });
};

/* Top synchronized horizontal scrollbar for all .table-responsive tables */
let init_table_top_scrollbars = () => {
    document.querySelectorAll('.table-responsive').forEach(table_wrapper => {
        if(table_wrapper.dataset.hasTopScrollbar) return;

        let top_scrollbar = document.createElement('div');
        top_scrollbar.className = 'table-top-scrollbar';
        let inner_div = document.createElement('div');
        inner_div.className = 'table-top-scrollbar-inner';
        top_scrollbar.appendChild(inner_div);

        table_wrapper.parentNode.insertBefore(top_scrollbar, table_wrapper);
        table_wrapper.dataset.hasTopScrollbar = 'true';

        let sync_width = () => {
            let table = table_wrapper.querySelector('table') || table_wrapper.firstElementChild;
            let scroll_width = table ? table.offsetWidth : table_wrapper.scrollWidth;
            let client_width = table_wrapper.clientWidth;

            if(scroll_width > client_width + 2) {
                top_scrollbar.style.display = 'block';
                inner_div.style.width = scroll_width + 'px';
                top_scrollbar.scrollLeft = table_wrapper.scrollLeft;
            } else {
                top_scrollbar.style.display = 'none';
            }
        };

        sync_width();

        let is_syncing_top = false;
        let is_syncing_bottom = false;

        top_scrollbar.addEventListener('scroll', () => {
            if(!is_syncing_top) {
                is_syncing_bottom = true;
                table_wrapper.scrollLeft = top_scrollbar.scrollLeft;
            }
            is_syncing_top = false;
        }, { passive: true });

        table_wrapper.addEventListener('scroll', () => {
            if(!is_syncing_bottom) {
                is_syncing_top = true;
                top_scrollbar.scrollLeft = table_wrapper.scrollLeft;
            }
            is_syncing_bottom = false;
        }, { passive: true });

        window.addEventListener('resize', sync_width);

        if(window.ResizeObserver) {
            new ResizeObserver(sync_width).observe(table_wrapper);
            let table = table_wrapper.querySelector('table');
            if(table) {
                new ResizeObserver(sync_width).observe(table);
            }
        }
    });
};

if(document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        init_admin_sidebar_collapse();
        init_table_top_scrollbars();
    });
} else {
    init_admin_sidebar_collapse();
    init_table_top_scrollbars();
}
