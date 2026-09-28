document.addEventListener('DOMContentLoaded', function () {
    initRedamanValidation();
});

function initRedamanValidation() {
    const redamanInputs = document.querySelectorAll('input[name="redaman_manual"]');

    redamanInputs.forEach(function (input) {
        input.addEventListener('input', function () {
            const value = Number(input.value);

            if (input.value === '') {
                input.setCustomValidity('');
                return;
            }

            if (Number.isNaN(value)) {
                input.setCustomValidity('Redaman manual harus berupa angka.');
                return;
            }

            if (value < 0) {
                input.setCustomValidity('Redaman manual tidak boleh kurang dari 0.');
                return;
            }

            if (value > 100) {
                input.setCustomValidity('Redaman manual terlalu besar. Maksimal 100 dB.');
                return;
            }

            input.setCustomValidity('');
        });
    });
}

function parseJsonDataset(raw) {
    if (!raw) {
        return null;
    }

    try {
        return JSON.parse(raw);
    } catch (error) {
        console.error('Data tombol tidak valid:', error);
        return null;
    }
}

function toggleRedamanPopover(id) {
    const target = document.getElementById(id);

    if (!target) {
        return;
    }

    document.querySelectorAll('.redaman-popover.open').forEach(function (popover) {
        if (popover.id !== id) {
            popover.classList.remove('open');
        }
    });

    target.classList.toggle('open');
}

function closeRedamanPopovers() {
    document.querySelectorAll('.redaman-popover.open').forEach(function (popover) {
        popover.classList.remove('open');
    });
}

function getPelangganModalElements() {
    return {
        modal: document.getElementById('pelangganModal'),
        form: document.getElementById('pelangganForm'),
        method: document.getElementById('pelangganFormMethod'),
        title: document.getElementById('pelangganModalTitle'),
        subtitle: document.getElementById('pelangganModalSubtitle'),
        odpId: document.getElementById('pelanggan_odp_id'),
        portId: document.getElementById('pelanggan_port_id'),
        portLabel: document.getElementById('pelanggan_port_label'),
        nama: document.getElementById('pelanggan_nama'),
        noLayanan: document.getElementById('pelanggan_no_layanan'),
        alamat: document.getElementById('pelanggan_alamat'),
        lat: document.getElementById('pelanggan_lat'),
        lng: document.getElementById('pelanggan_lng'),
        keterangan: document.getElementById('pelanggan_keterangan')
    };
}

function resetPelangganForm(elements) {
    if (!elements.form) {
        return;
    }

    elements.form.reset();

    const storeUrl = elements.form.dataset.storeUrl || '/pelanggan';

    elements.method.value = 'POST';
    elements.form.action = storeUrl;

    elements.nama.value = '';
    elements.noLayanan.value = '';
    elements.alamat.value = '';
    elements.lat.value = '';
    elements.lng.value = '';
    elements.keterangan.value = '';
    elements.portId.value = '';
}

function openTambahPelangganModal(button) {
    const data = parseJsonDataset(button.getAttribute('data-port'));

    if (!data) {
        return;
    }

    const elements = getPelangganModalElements();

    if (!elements.modal || !elements.form) {
        return;
    }

    resetPelangganForm(elements);

    elements.title.textContent = 'Tambah Pelanggan';
    elements.subtitle.textContent = 'Tambahkan pelanggan ke port kosong pada ODP ini.';
    elements.method.value = 'POST';
    elements.form.action = elements.form.dataset.storeUrl || '/pelanggan';

    elements.odpId.value = data.odp_id;
    elements.portId.value = data.port_id;
    elements.portLabel.textContent = `${data.odp_nama} - OUT ${data.nomor_port}`;

    openPelangganModal(elements);
}

function openEditPelangganModal(button) {
    const data = parseJsonDataset(button.getAttribute('data-pelanggan'));

    if (!data) {
        return;
    }

    const elements = getPelangganModalElements();

    if (!elements.modal || !elements.form) {
        return;
    }

    resetPelangganForm(elements);

    const updateBaseUrl = elements.form.dataset.updateBaseUrl || '/pelanggan';

    elements.title.textContent = 'Edit Pelanggan';
    elements.subtitle.textContent = 'Ubah data pelanggan pada port ini.';
    elements.method.value = 'PUT';
    elements.form.action = `${updateBaseUrl}/${data.id}`;

    elements.odpId.value = data.odp_id;
    elements.portId.value = data.port_id;
    elements.portLabel.textContent = 'Port OUT terpakai';

    elements.nama.value = data.nama ?? '';
    elements.noLayanan.value = data.no_layanan ?? '';
    elements.alamat.value = data.alamat ?? '';
    elements.lat.value = data.lat ?? '';
    elements.lng.value = data.lng ?? '';
    elements.keterangan.value = data.keterangan ?? '';

    openPelangganModal(elements);
}

function openPelangganModal(elements) {
    closeRedamanPopovers();

    elements.modal.classList.add('open');
    elements.modal.setAttribute('aria-hidden', 'false');

    setTimeout(function () {
        elements.nama.focus();
    }, 100);
}

function closePelangganModal() {
    const elements = getPelangganModalElements();

    if (!elements.modal) {
        return;
    }

    elements.modal.classList.remove('open');
    elements.modal.setAttribute('aria-hidden', 'true');
}

document.addEventListener('click', function (event) {
    const isRedamanButton = event.target.closest('.btn-redaman-icon');
    const isRedamanPopover = event.target.closest('.redaman-popover');

    if (!isRedamanButton && !isRedamanPopover) {
        closeRedamanPopovers();
    }
});

document.addEventListener('click', function (event) {
    const modal = document.getElementById('pelangganModal');

    if (!modal || !modal.classList.contains('open')) {
        return;
    }

    if (event.target === modal) {
        closePelangganModal();
    }
});

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeRedamanPopovers();
        closePelangganModal();
    }
});