const REGION_LEVELS = [
    { key: 'province', endpoint: 'provinces', label: 'provinsi' },
    { key: 'regency', endpoint: 'regencies', label: 'kabupaten/kota' },
    { key: 'district', endpoint: 'districts', label: 'kecamatan' },
    { key: 'village', endpoint: 'villages', label: 'desa/kelurahan' },
];

const refreshSelect2 = (select) => {
    if (window.jQuery && window.jQuery.fn.select2) {
        window.jQuery(select).trigger('change.select2');
    }
};

const option = (value, label, name = '') => {
    const item = document.createElement('option');
    item.value = value;
    item.textContent = label;
    if (name) item.dataset.name = name;

    return item;
};

export const initRegionSelectors = () => {
    document.querySelectorAll('[data-region-selector]').forEach((container) => {
        if (container.dataset.regionSelectorBound) return;
        container.dataset.regionSelectorBound = 'true';

        const baseUrl = container.dataset.regionsBaseUrl?.replace(/\/$/, '');
        const status = container.querySelector('[data-region-status]');
        const retry = container.querySelector('[data-region-retry]');
        const form = container.closest('form');
        const submitButtons = [...(form?.querySelectorAll('button:not([type]), button[type="submit"], input[type="submit"]') || [])];
        const selects = Object.fromEntries(REGION_LEVELS.map(({ key }) => [
            key,
            container.querySelector(`[data-region="${key}"]`),
        ]));
        const nameInputs = Object.fromEntries(REGION_LEVELS.map(({ key }) => [
            key,
            container.querySelector(`[data-region-name="${key}"]`),
        ]));
        let generation = 0;

        if (!baseUrl || Object.values(selects).some((select) => !select)) return;

        const originalSelections = Object.fromEntries(REGION_LEVELS.map(({ key }) => [
            key,
            {
                code: selects[key].dataset.selectedCode || '',
                name: selects[key].dataset.selectedName || '',
            },
        ]));

        const setBusy = (busy) => {
            container.setAttribute('aria-busy', String(busy));
            submitButtons.forEach((button) => {
                button.disabled = busy;
            });
        };

        const showStatus = (message, error = false, loading = false) => {
            if (!status) return;
            status.classList.toggle('text-red', error);
            status.classList.toggle('text-muted', !error);
            status.replaceChildren();
            if (loading) {
                const icon = document.createElement('i');
                icon.className = 'fa fa-refresh fa-spin';
                icon.setAttribute('aria-hidden', 'true');
                status.append(icon, ' ');
            }
            status.append(message);
        };

        const rememberSelection = (key) => {
            const select = selects[key];
            const selected = select.options[select.selectedIndex];
            const name = selected?.dataset.name || '';
            select.dataset.selectedCode = select.value;
            select.dataset.selectedName = name;
            nameInputs[key].value = name;
        };

        const renderOptions = (definition, items, selectedCode = '', selectedName = '') => {
            const select = selects[definition.key];
            select.replaceChildren(option('', `-- Pilih ${definition.label} --`));

            const regions = items
                .filter((item) => item && typeof item.code === 'string' && typeof item.name === 'string')
                .map((item) => ({ code: item.code, name: item.name }));

            if (selectedCode && !regions.some((item) => item.code === selectedCode)) {
                regions.unshift({ code: selectedCode, name: selectedName || selectedCode });
            }

            regions.forEach((item) => {
                select.append(option(item.code, `${item.name} (${item.code})`, item.name));
            });

            select.value = selectedCode;
            select.disabled = false;
            refreshSelect2(select);
            rememberSelection(definition.key);
        };

        const resetFrom = (startIndex) => {
            REGION_LEVELS.slice(startIndex).forEach((definition) => {
                const select = selects[definition.key];
                select.replaceChildren(option('', `-- Pilih ${definition.label} --`));
                select.disabled = true;
                select.dataset.selectedCode = '';
                select.dataset.selectedName = '';
                nameInputs[definition.key].value = '';
                refreshSelect2(select);
            });
        };

        const restoreSavedSelections = () => {
            REGION_LEVELS.forEach((definition, index) => {
                const select = selects[definition.key];
                const code = select.dataset.selectedCode || '';
                const name = select.dataset.selectedName || '';
                renderOptions(definition, [], code, name);
                select.disabled = index > 0 && !code;
                refreshSelect2(select);
            });
        };

        const loadLevel = async (definition, parentCode, selectedCode, selectedName, requestGeneration) => {
            const select = selects[definition.key];
            const url = `${baseUrl}/${definition.endpoint}${parentCode ? `/${encodeURIComponent(parentCode)}` : ''}`;
            select.replaceChildren(option('', `Memuat ${definition.label}...`));
            select.disabled = true;
            refreshSelect2(select);

            const response = await fetch(url, {
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(payload.message || `Daftar ${definition.label} gagal dimuat.`);
            }
            if (requestGeneration !== generation) return false;
            if (!Array.isArray(payload.data)) {
                throw new Error(`Format daftar ${definition.label} tidak valid.`);
            }

            renderOptions(definition, payload.data, selectedCode, selectedName);

            return true;
        };

        const initialize = async () => {
            const requestGeneration = ++generation;
            const saved = Object.fromEntries(REGION_LEVELS.map(({ key }) => [
                key,
                {
                    code: selects[key].dataset.selectedCode || '',
                    name: selects[key].dataset.selectedName || '',
                },
            ]));

            if (retry) retry.hidden = true;
            setBusy(true);
            showStatus('Memuat daftar provinsi...', false, true);

            try {
                let parentCode = '';
                for (const [index, definition] of REGION_LEVELS.entries()) {
                    if (index > 0 && !parentCode) {
                        resetFrom(index);
                        showStatus(`Pilih ${REGION_LEVELS[index - 1].label} untuk melanjutkan.`);
                        return;
                    }

                    showStatus(`Memuat daftar ${definition.label}...`, false, true);
                    const loaded = await loadLevel(
                        definition,
                        parentCode,
                        saved[definition.key].code,
                        saved[definition.key].name,
                        requestGeneration,
                    );
                    if (!loaded) return;
                    parentCode = selects[definition.key].value;
                }

                showStatus(parentCode
                    ? 'Data wilayah tersinkron dengan wilayah.id.'
                    : 'Pilih provinsi untuk menentukan wilayah desa.');
            } catch (error) {
                if (requestGeneration !== generation) return;
                restoreSavedSelections();
                showStatus(error.message || 'Data wilayah gagal dimuat. Silakan coba lagi.', true);
                if (retry) retry.hidden = false;
            } finally {
                if (requestGeneration === generation) setBusy(false);
            }
        };

        REGION_LEVELS.forEach((definition, index) => {
            const handleChange = async () => {
                const requestGeneration = ++generation;
                setBusy(false);
                rememberSelection(definition.key);
                resetFrom(index + 1);
                if (retry) retry.hidden = true;

                if (!selects[definition.key].value) {
                    showStatus(`Pilih ${definition.label} untuk melanjutkan.`);
                    return;
                }
                if (index === REGION_LEVELS.length - 1) {
                    showStatus('Wilayah desa/kelurahan siap disimpan.');
                    return;
                }

                const next = REGION_LEVELS[index + 1];
                setBusy(true);
                showStatus(`Memuat daftar ${next.label}...`, false, true);

                try {
                    await loadLevel(next, selects[definition.key].value, '', '', requestGeneration);
                    if (requestGeneration === generation) {
                        showStatus(`Pilih ${next.label} untuk melanjutkan.`);
                    }
                } catch (error) {
                    if (requestGeneration !== generation) return;
                    showStatus(error.message || `Daftar ${next.label} gagal dimuat.`, true);
                    if (retry) retry.hidden = false;
                } finally {
                    if (requestGeneration === generation) setBusy(false);
                }
            };

            if (window.jQuery) {
                window.jQuery(selects[definition.key]).on('change.regionSelector', handleChange);
            } else {
                selects[definition.key].addEventListener('change', handleChange);
            }
        });

        retry?.addEventListener('click', initialize);
        form?.addEventListener('reset', () => {
            generation++;
            setBusy(false);
            window.setTimeout(() => {
                REGION_LEVELS.forEach(({ key }) => {
                    selects[key].dataset.selectedCode = originalSelections[key].code;
                    selects[key].dataset.selectedName = originalSelections[key].name;
                    nameInputs[key].value = originalSelections[key].name;
                });
                initialize();
            }, 0);
        });
        initialize();
    });
};
