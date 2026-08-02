const getAdminDialog = () => window.AdminDialog || window.Swal;

const initAdminDialog = () => {
    if (!window.Swal || window.AdminDialog) return getAdminDialog();

    window.AdminDialog = window.Swal.mixin({
        buttonsStyling: false,
        customClass: {
            popup: 'admin-dialog',
            title: 'admin-dialog__title',
            htmlContainer: 'admin-dialog__text',
            actions: 'admin-dialog__actions',
            confirmButton: 'admin-dialog__button admin-dialog__button--confirm',
            cancelButton: 'admin-dialog__button admin-dialog__button--cancel',
            denyButton: 'admin-dialog__button admin-dialog__button--deny',
        },
        confirmButtonColor: '#526b42',
        cancelButtonColor: '#6f7870',
        reverseButtons: true,
    });

    return window.AdminDialog;
};

const showSuccessDialog = (root) => {
    const dialog = root.querySelector('[data-success-dialog]');
    if (!dialog) return;

    const message = dialog.dataset.message || 'Perubahan berhasil disimpan.';
    dialog.remove();

    const dialogApi = initAdminDialog();
    if (dialogApi) {
        dialogApi.fire({
            title: 'Berhasil!',
            text: message,
            icon: 'success',
            confirmButtonText: 'Oke',
            allowOutsideClick: false,
            returnFocus: false,
        });
        return;
    }

    window.alert(message);
};

const showErrorDialog = (root) => {
    const dialog = root.querySelector('[data-error-dialog]');
    if (!dialog) return;

    const message = dialog.dataset.message || 'Data belum dapat diproses.';
    const title = dialog.dataset.title || 'Data belum dapat diproses';
    dialog.remove();

    const dialogApi = initAdminDialog();
    if (dialogApi) {
        dialogApi.fire({
            title,
            text: message,
            icon: 'error',
            confirmButtonText: 'Oke',
            allowOutsideClick: false,
            returnFocus: false,
        });
        return;
    }

    window.alert(`${title}\n\n${message}`);
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
    initAdminDialog();
    showSuccessDialog(root);
    showErrorDialog(root);
    initLegacyPlugins(root);
};
