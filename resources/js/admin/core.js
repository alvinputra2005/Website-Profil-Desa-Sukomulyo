const showSuccessDialog = (root) => {
    const dialog = root.querySelector('[data-success-dialog]');
    if (!dialog) return;

    const message = dialog.dataset.message || 'Perubahan berhasil disimpan.';
    dialog.remove();

    if (window.Swal) {
        window.Swal.fire({
            title: 'Berhasil!',
            text: message,
            icon: 'success',
            confirmButtonText: 'Oke',
            confirmButtonColor: '#526b42',
            allowOutsideClick: false,
            returnFocus: false,
        });
        return;
    }

    window.alert(message);
};

const initLegacyPlugins = (root) => {
    if (!window.jQuery) return;

    const $ = window.jQuery;
    const $root = $(root);
    const savedSidebarState = localStorage.getItem('sidebar');

    if (savedSidebarState === 'collapsed') {
        document.body.classList.add('sidebar-collapse');
    }

    $root.find('.sidebar-toggle').on('click.adminShell', () => {
        window.setTimeout(() => {
            const state = document.body.classList.contains('sidebar-collapse') ? 'collapsed' : 'expanded';
            localStorage.setItem('sidebar', state);
        }, 0);
    });

    $root.find('#cari-menu').on('input.adminShell', function filterSidebar() {
        const term = this.value.toLowerCase().trim();

        $root.find('.sidebar-menu > li:not(.header)').each(function toggleMenuItem() {
            const $item = $(this);
            const matches = $item.text().toLowerCase().includes(term);
            $item.toggle(matches);

            if (term && matches) {
                $item.addClass('menu-open').children('.treeview-menu').show();
            } else if (!term && !$item.hasClass('active')) {
                $item.removeClass('menu-open').children('.treeview-menu').hide();
            }
        });
    });

    $root.find('.select2:not(.select2-hidden-accessible):not([data-select2-disabled])').select2({ width: '100%' });
    $root.find('[data-toggle="tooltip"]').tooltip();

    if ($.fn.tree) $root.find('[data-widget="tree"]').tree();
    if ($.fn.boxWidget) $root.find('[data-widget="collapse"]').closest('.box').boxWidget();
    if ($.fn.layout && $('body').data('lte.layout')) $('body').layout('fix');

    window.setTimeout(() => $root.find('#notifikasi').fadeTo(500, 0).slideUp(500), 5000);
};

export const initAdminShell = (root = document) => {
    showSuccessDialog(root);
    initLegacyPlugins(root);
};
