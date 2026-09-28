let nodes = window.TOPOLOGY_CONFIG.nodes || [];
let edges = window.TOPOLOGY_CONFIG.edges || [];
let selectedNodeId = null;
let selectedEdgeId = null;
let connectFrom = null;

const canManage = Boolean(window.TOPOLOGY_CONFIG.canManage);
const allowedSplitterRatios = window.TOPOLOGY_CONFIG.allowedSplitterRatios || ['1:4', '1:8', '1:16'];

const canvas = document.getElementById('canvas');
const nodeLayer = document.getElementById('nodeLayer');
const edgeLayer = document.getElementById('edgeLayer');
const propertyContent = document.getElementById('propertyContent');

function uid(prefix) {
    return prefix + '_' + Date.now() + '_' + Math.floor(Math.random() * 9999);
}

function guardCanManage(message = 'Akses ini hanya tersedia untuk admin atau superadmin.') {
    if (canManage) return true;
    alert(message);
    return false;
}

function normalizeSplitterRatio(ratio) {
    return allowedSplitterRatios.includes(ratio) ? ratio : '1:4';
}

function getSplitterPortCount(ratio) {
    const map = {
        '1:4': 4,
        '1:8': 8,
        '1:16': 16
    };

    return map[normalizeSplitterRatio(ratio)] || 4;
}

function getDefaultSplitterLoss(ratio) {
    const defaultLosses = {
        '1:4': 7.3,
        '1:8': 10.5,
        '1:16': 13.8
    };

    const normalizedRatio = normalizeSplitterRatio(ratio);
    const savedLosses = window.TOPOLOGY_CONFIG.defaults.splitter_losses || {};

    return Number(savedLosses[normalizedRatio] ?? defaultLosses[normalizedRatio] ?? 0);
}

function addNode(type) {
    if (!guardCanManage()) return;

    const id = uid('node');
    let label = type;

    if (type === 'OLT') label = 'Main OLT';
    if (type === 'ODC') label = 'ODC Baru';
    if (type === 'ODP') label = 'ODP Baru';
    if (type === 'Splitter') label = 'Splitter 1:4';
    if (type === 'Splice') label = 'Fiber Joint';
    if (type === 'ONU') label = 'Pelanggan Baru';

    const node = {
        id,
        type,
        label,
        x: 240 + Math.floor(Math.random() * 260),
        y: 120 + Math.floor(Math.random() * 260),
        prx: null,
        total_loss: null,
        margin: null,
        status: 'belum_dihitung'
    };

    if (type === 'OLT') {
        node.ptx = window.TOPOLOGY_CONFIG.defaults.ptx;
        node.prx = window.TOPOLOGY_CONFIG.defaults.ptx;
        node.total_loss = 0;
        node.status = 'source';
    }

    if (type === 'Splitter') {
        node.ratio = '1:4';
        node.splitter_loss = getDefaultSplitterLoss('1:4');
    }

    if (type === 'ONU') {
        node.threshold_rx = window.TOPOLOGY_CONFIG.defaults.threshold_rx;
    }

    nodes.push(node);
    selectedNodeId = id;
    selectedEdgeId = null;

    render();
    renderNodeProperty(node);
}

function render() {
    renderEdges();
    renderNodes();
}

function renderNodes() {
    nodeLayer.innerHTML = '';

    nodes.forEach(node => {
        const div = document.createElement('div');

        div.className = `topology-node ${node.status || ''} ${selectedNodeId === node.id ? 'selected' : ''}`;
        div.style.left = node.x + 'px';
        div.style.top = node.y + 'px';
        div.dataset.id = node.id;

        const powerText = node.prx !== null && node.prx !== undefined
            ? `${Number(node.prx).toFixed(2)} dBm`
            : '- dBm';

        let extra = '';

        if (node.type === 'Splitter') {
            const ratio = normalizeSplitterRatio(node.ratio || '1:4');
            const portCount = getSplitterPortCount(ratio);
            const usedPorts = edges
                .filter(e => e.from === node.id && e.from_port)
                .map(e => e.from_port);

            let portsHtml = '';

            for (let i = 1; i <= portCount; i++) {
                const portName = 'P' + i;
                const used = usedPorts.includes(portName);
                const clickAction = canManage
                    ? `onclick="event.stopPropagation(); startConnectFromPort('${node.id}', '${portName}')"`
                    : 'disabled';

                portsHtml += `
                    <button class="node-port ${used ? 'used' : ''}"
                            title="${portName}"
                            ${clickAction}>
                    </button>
                `;
            }

            extra = `
                <div class="node-info">Ratio: ${ratio} | Loss: ${Number(node.splitter_loss ?? getDefaultSplitterLoss(ratio)).toFixed(2)} dB</div>
                <div class="node-ports">${portsHtml}</div>
            `;
        }

        if (node.type === 'ONU') {
            extra += `<div class="node-info">Threshold: ${node.threshold_rx ?? window.TOPOLOGY_CONFIG.defaults.threshold_rx} dBm</div>`;
        }

        div.innerHTML = `
            <div class="node-type">${escapeHtml(node.type)}</div>
            <div class="node-label">${escapeHtml(node.label || '-')}</div>
            <div class="node-power">${powerText}</div>
            <div class="node-info">
                ${node.status === 'layak' ? 'Layak' : ''}
                ${node.status === 'tidak_layak' ? 'Tidak Layak' : ''}
                ${node.status === 'source' ? 'Sumber' : ''}
                ${node.status === 'belum_dihitung' ? 'Belum dihitung' : ''}
            </div>
            ${extra}
        `;

        div.addEventListener('click', function () {
            if (canManage && connectFrom && connectFrom.nodeId !== node.id) {
                createEdge(connectFrom.nodeId, node.id, connectFrom.port);
                connectFrom = null;
                return;
            }

            selectedNodeId = node.id;
            selectedEdgeId = null;
            render();
            renderNodeProperty(node);
        });

        if (canManage) {
            makeDraggable(div, node);
        }

        nodeLayer.appendChild(div);
    });
}

function renderEdges() {
    edgeLayer.innerHTML = '';

    edges.forEach(edge => {
        const fromNode = nodes.find(n => n.id === edge.from);
        const toNode = nodes.find(n => n.id === edge.to);

        if (!fromNode || !toNode) return;

        const x1 = fromNode.x + 62;
        const y1 = fromNode.y + 74;
        const x2 = toNode.x + 62;
        const y2 = toNode.y;

        const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        line.setAttribute('x1', x1);
        line.setAttribute('y1', y1);
        line.setAttribute('x2', x2);
        line.setAttribute('y2', y2);
        line.setAttribute('class', `edge-line ${selectedEdgeId === edge.id ? 'selected' : ''}`);
        edgeLayer.appendChild(line);

        const label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        label.setAttribute('x', (x1 + x2) / 2);
        label.setAttribute('y', (y1 + y2) / 2 - 8);
        label.setAttribute('class', 'edge-label');
        label.textContent = `${edge.panjang_km || 0} km`;
        edgeLayer.appendChild(label);

        const hitbox = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        hitbox.setAttribute('x1', x1);
        hitbox.setAttribute('y1', y1);
        hitbox.setAttribute('x2', x2);
        hitbox.setAttribute('y2', y2);
        hitbox.setAttribute('stroke', '#2563eb');
        hitbox.setAttribute('stroke-width', '22');
        hitbox.setAttribute('opacity', '0.001');
        hitbox.setAttribute('fill', 'none');
        hitbox.style.cursor = 'pointer';
        hitbox.style.pointerEvents = 'stroke';

        hitbox.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            selectedEdgeId = edge.id;
            selectedNodeId = null;
            connectFrom = null;

            render();
            renderEdgeProperty(edge);
        });

        edgeLayer.appendChild(hitbox);
    });
}

function makeDraggable(el, node) {
    if (!canManage) return;

    let isDragging = false;
    let startX = 0;
    let startY = 0;

    el.addEventListener('mousedown', function (e) {
        if (e.target.classList.contains('node-port')) return;

        isDragging = true;
        startX = e.clientX - node.x;
        startY = e.clientY - node.y;
        el.style.cursor = 'grabbing';
    });

    document.addEventListener('mousemove', function (e) {
        if (!isDragging) return;

        node.x = e.clientX - startX;
        node.y = e.clientY - startY;

        render();
        renderNodeProperty(node);
    });

    document.addEventListener('mouseup', function () {
        isDragging = false;
        el.style.cursor = 'grab';
    });
}

function startConnectFromPort(nodeId, port) {
    if (!guardCanManage()) return;

    connectFrom = {
        nodeId,
        port
    };

    alert(`Port ${port} dipilih. Klik node tujuan untuk membuat koneksi.`);
}

function startConnectFromNode(nodeId) {
    if (!guardCanManage()) return;

    connectFrom = {
        nodeId,
        port: null
    };

    alert('Klik node tujuan untuk membuat koneksi.');
}

function createEdge(from, to, fromPort = null) {
    if (!guardCanManage()) return;

    const fromNode = nodes.find(n => n.id === from);

    if (!fromNode) return;

    if (fromNode.type === 'Splitter' && fromPort) {
        const existingPort = edges.find(e => e.from === from && e.from_port === fromPort);

        if (existingPort) {
            alert(`${fromPort} sudah digunakan.`);
            return;
        }
    }

    const edge = {
        id: uid('edge'),
        from,
        to,
        from_port: fromPort,
        panjang_km: 0,
        jumlah_konektor: 2,
        jumlah_splice: 0,
        alpha_serat: null,
        loss_konektor: null,
        loss_splice: null
    };

    edges.push(edge);
    selectedEdgeId = edge.id;
    selectedNodeId = null;

    render();
    renderEdgeProperty(edge);
}

function renderNodeProperty(node) {
    const disabledAttr = canManage ? '' : 'disabled';
    const labelChange = canManage ? `onchange="updateNode('${node.id}', 'label', this.value)"` : '';

    let html = `
        <div class="form-group">
            <label>Jenis Node</label>
            <input value="${escapeHtml(node.type)}" disabled>
        </div>

        <div class="form-group">
            <label>Label</label>
            <input value="${escapeHtml(node.label || '')}" ${disabledAttr} ${labelChange}>
        </div>
    `;

    if (node.type === 'OLT') {
        const changeAttr = canManage ? `onchange="updateNode('${node.id}', 'ptx', parseFloat(this.value))"` : '';

        html += `
            <div class="form-group">
                <label>Tx Power / Ptx (dBm)</label>
                <input type="number" step="0.001" value="${node.ptx ?? window.TOPOLOGY_CONFIG.defaults.ptx}" ${disabledAttr} ${changeAttr}>
            </div>
        `;
    }

    if (node.type === 'Splitter') {
        const ratio = normalizeSplitterRatio(node.ratio || '1:4');
        const ratioChange = canManage ? `onchange="updateSplitterRatio('${node.id}', this.value)"` : '';
        const splitterLossChange = canManage ? `onchange="updateNode('${node.id}', 'splitter_loss', parseFloat(this.value))"` : '';

        html += `
            <div class="form-group">
                <label>Rasio Splitter</label>
                <select ${disabledAttr} ${ratioChange}>
                    ${allowedSplitterRatios.map(r => `
                        <option value="${r}" ${ratio === r ? 'selected' : ''}>${r}</option>
                    `).join('')}
                </select>
            </div>

            <div class="form-group">
                <label>Loss Splitter Manual (dB)</label>
                <input type="number" step="0.001" value="${node.splitter_loss ?? getDefaultSplitterLoss(ratio)}" ${disabledAttr} ${splitterLossChange}>
                <small class="muted">Nilai redaman splitter sesuai skenario simulasi.</small>
            </div>

            <div class="form-group">
                <label>Jumlah Port Output</label>
                <input value="${getSplitterPortCount(ratio)} port" disabled>
            </div>
        `;
    }

    if (node.type === 'ONU') {
        const thresholdChange = canManage ? `onchange="updateNode('${node.id}', 'threshold_rx', parseFloat(this.value))"` : '';

        html += `
            <div class="form-group">
                <label>Threshold Rx Manual (dBm)</label>
                <input type="number" step="0.001" value="${node.threshold_rx ?? window.TOPOLOGY_CONFIG.defaults.threshold_rx}" ${disabledAttr} ${thresholdChange}>
            </div>
        `;
    }

    if (node.total_loss !== null && node.total_loss !== undefined) {
        html += `
            <div class="form-group">
                <label>Total Loss Terakhir (dB)</label>
                <input value="${Number(node.total_loss).toFixed(3)}" disabled>
            </div>
        `;
    }

    if (node.margin !== null && node.margin !== undefined) {
        html += `
            <div class="form-group">
                <label>Margin Terakhir (dB)</label>
                <input value="${Number(node.margin).toFixed(3)}" disabled>
            </div>
        `;
    }

    if (canManage) {
        html += `
            <div class="property-actions">
                <button class="btn-secondary" onclick="startConnectFromNode('${node.id}')">Mulai Koneksi</button>
                <button class="btn-secondary" onclick="duplicateNode('${node.id}')">Duplikat</button>
                <button class="btn-red" onclick="deleteNode('${node.id}')">Hapus Node</button>
            </div>
        `;
    } else {
        html += `
            <div class="hint-box">
                <strong>Mode lihat</strong>
                <p>Teknisi hanya dapat melihat detail komponen dan hasil perhitungan.</p>
            </div>
        `;
    }

    propertyContent.innerHTML = html;
}

function renderEdgeProperty(edge) {
    const fromNode = nodes.find(n => n.id === edge.from);
    const toNode = nodes.find(n => n.id === edge.to);
    const disabledAttr = canManage ? '' : 'disabled';

    const panjangChange = canManage ? `onchange="updateEdge('${edge.id}', 'panjang_km', parseFloat(this.value))"` : '';
    const konektorChange = canManage ? `onchange="updateEdge('${edge.id}', 'jumlah_konektor', parseInt(this.value))"` : '';
    const spliceChange = canManage ? `onchange="updateEdge('${edge.id}', 'jumlah_splice', parseInt(this.value))"` : '';
    const alphaChange = canManage ? `onchange="updateEdgeNullable('${edge.id}', 'alpha_serat', this.value)"` : '';
    const konektorLossChange = canManage ? `onchange="updateEdgeNullable('${edge.id}', 'loss_konektor', this.value)"` : '';
    const spliceLossChange = canManage ? `onchange="updateEdgeNullable('${edge.id}', 'loss_splice', this.value)"` : '';

    propertyContent.innerHTML = `
        <div class="form-group">
            <label>Dari</label>
            <input value="${fromNode ? escapeHtml(fromNode.label) : '-'}" disabled>
        </div>

        <div class="form-group">
            <label>Ke</label>
            <input value="${toNode ? escapeHtml(toNode.label) : '-'}" disabled>
        </div>

        <div class="form-group">
            <label>Port Output</label>
            <input value="${edge.from_port || '-'}" disabled>
        </div>

        <div class="form-group">
            <label>Panjang Kabel (km)</label>
            <input type="number" step="0.001" value="${edge.panjang_km ?? 0}" ${disabledAttr} ${panjangChange}>
        </div>

        <div class="form-group">
            <label>Jumlah Konektor</label>
            <input type="number" value="${edge.jumlah_konektor ?? 0}" ${disabledAttr} ${konektorChange}>
        </div>

        <div class="form-group">
            <label>Jumlah Splice</label>
            <input type="number" value="${edge.jumlah_splice ?? 0}" ${disabledAttr} ${spliceChange}>
        </div>

        <hr>

        <div class="form-group">
            <label>Redaman Serat Manual (dB/km)</label>
            <input type="number" step="0.001" placeholder="Kosong = pakai default" value="${edge.alpha_serat ?? ''}" ${disabledAttr} ${alphaChange}>
        </div>

        <div class="form-group">
            <label>Loss Konektor Manual (dB)</label>
            <input type="number" step="0.001" placeholder="Kosong = pakai default" value="${edge.loss_konektor ?? ''}" ${disabledAttr} ${konektorLossChange}>
        </div>

        <div class="form-group">
            <label>Loss Splice Manual (dB)</label>
            <input type="number" step="0.001" placeholder="Kosong = pakai default" value="${edge.loss_splice ?? ''}" ${disabledAttr} ${spliceLossChange}>
        </div>

        ${canManage ? `
            <div class="property-actions">
                <button class="btn-red" onclick="deleteEdge('${edge.id}')">Hapus Koneksi</button>
            </div>
        ` : `
            <div class="hint-box">
                <strong>Mode lihat</strong>
                <p>Teknisi hanya dapat melihat detail koneksi dan hasil perhitungan.</p>
            </div>
        `}
    `;
}

function updateNode(id, key, value) {
    if (!guardCanManage()) return;

    const node = nodes.find(n => n.id === id);
    if (!node) return;

    node[key] = value;
    render();

    if (selectedNodeId === id) {
        renderNodeProperty(node);
    }
}

function updateSplitterRatio(id, ratio) {
    if (!guardCanManage()) return;

    const node = nodes.find(n => n.id === id);
    if (!node) return;

    const normalizedRatio = normalizeSplitterRatio(ratio);
    const oldPortCount = getSplitterPortCount(node.ratio || '1:4');
    const newPortCount = getSplitterPortCount(normalizedRatio);

    if (newPortCount < oldPortCount) {
        const invalidEdges = edges.filter(e => {
            if (e.from !== id || !e.from_port) return false;

            const portNumber = parseInt(e.from_port.replace('P', ''));
            return portNumber > newPortCount;
        });

        if (invalidEdges.length > 0) {
            const ok = confirm('Beberapa koneksi port akan dilepas karena rasio lebih kecil. Lanjutkan?');

            if (!ok) return;

            edges = edges.filter(e => !invalidEdges.includes(e));
        }
    }

    node.ratio = normalizedRatio;
    node.label = `Splitter ${normalizedRatio}`;
    node.splitter_loss = getDefaultSplitterLoss(normalizedRatio);

    render();
    renderNodeProperty(node);
}

function updateEdge(id, key, value) {
    if (!guardCanManage()) return;

    const edge = edges.find(e => e.id === id);
    if (!edge) return;

    edge[key] = Number.isNaN(value) ? 0 : value;
    render();

    if (selectedEdgeId === id) {
        renderEdgeProperty(edge);
    }
}

function updateEdgeNullable(id, key, value) {
    if (!guardCanManage()) return;

    const edge = edges.find(e => e.id === id);
    if (!edge) return;

    edge[key] = value === '' ? null : parseFloat(value);
    render();

    if (selectedEdgeId === id) {
        renderEdgeProperty(edge);
    }
}

function deleteNode(id) {
    if (!guardCanManage()) return;

    const ok = confirm('Hapus node ini beserta semua koneksinya?');
    if (!ok) return;

    nodes = nodes.filter(n => n.id !== id);
    edges = edges.filter(e => e.from !== id && e.to !== id);

    selectedNodeId = null;
    selectedEdgeId = null;

    render();
    propertyContent.innerHTML = '<p class="muted">Klik node atau garis untuk mengatur parameter.</p>';
}

function deleteEdge(id) {
    if (!guardCanManage()) return;

    edges = edges.filter(e => e.id !== id);
    selectedEdgeId = null;

    render();
    propertyContent.innerHTML = '<p class="muted">Klik node atau garis untuk mengatur parameter.</p>';
}

function duplicateNode(id) {
    if (!guardCanManage()) return;

    const node = nodes.find(n => n.id === id);
    if (!node) return;

    const copy = JSON.parse(JSON.stringify(node));
    copy.id = uid('node');
    copy.label = node.label + ' Copy';
    copy.x = node.x + 220;
    copy.y = node.y + 40;
    copy.prx = null;
    copy.total_loss = null;
    copy.margin = null;
    copy.status = 'belum_dihitung';

    nodes.push(copy);
    selectedNodeId = copy.id;
    selectedEdgeId = null;

    render();
    renderNodeProperty(copy);
}

async function saveTopology(showAlert = true) {
    if (!guardCanManage()) return { status: false };

    if (!window.TOPOLOGY_CONFIG.saveUrl) {
        alert('URL penyimpanan tidak tersedia untuk role ini.');
        return { status: false };
    }

    try {
        const response = await fetch(window.TOPOLOGY_CONFIG.saveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.TOPOLOGY_CONFIG.csrf
            },
            body: JSON.stringify({
                nodes,
                edges
            })
        });

        const data = await response.json();

        if (showAlert) {
            alert(data.message || 'Topologi disimpan.');
        }

        return data;
    } catch (error) {
        alert('Gagal menyimpan topologi. Periksa koneksi atau konfigurasi route.');
        return { status: false, error };
    }
}

async function calculateTopology() {
    if (!guardCanManage()) return;

    const saveResult = await saveTopology(false);

    if (!saveResult || saveResult.status === false) {
        alert(saveResult.message || 'Topologi belum berhasil disimpan, perhitungan dibatalkan.');
        return;
    }

    if (!window.TOPOLOGY_CONFIG.hitungUrl) {
        alert('URL perhitungan tidak tersedia untuk role ini.');
        return;
    }

    try {
        const response = await fetch(window.TOPOLOGY_CONFIG.hitungUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.TOPOLOGY_CONFIG.csrf
            },
            body: JSON.stringify({
                nodes,
                edges
            })
        });

        const data = await response.json();

        if (!response.ok || !data.status) {
            alert(data.message || 'Gagal menghitung topologi.');
            return;
        }

        nodes = data.nodes;
        edges = data.edges;

        render();
        renderResultTable(data.hasil || []);

        if (!data.hasil || data.hasil.length === 0) {
            alert('Perhitungan selesai, tetapi belum ada endpoint ONU/Pelanggan yang menghasilkan data.');
            return;
        }

        alert(`Perhitungan PLB berhasil. ${data.hasil.length} endpoint berhasil dihitung.`);
    } catch (error) {
        alert('Gagal menghitung topologi. Periksa koneksi atau konfigurasi route.');
    }
}

function renderResultTable(results) {
    const tbody = document.getElementById('resultTable');

    if (!results.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="muted">Belum ada endpoint yang dihitung.</td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = results.map(row => `
        <tr>
            <td>${escapeHtml(row.endpoint)}</td>
            <td>${escapeHtml(row.jalur)}</td>
            <td>${row.total_panjang_km} km</td>
            <td>${row.total_loss} dB</td>
            <td>${row.safety_margin ?? window.TOPOLOGY_CONFIG.defaults.safety_margin} dB</td>
            <td>${row.prx} dBm</td>
            <td>${row.margin} dB</td>
            <td>
                <span class="${row.status === 'layak' ? 'badge-ok' : 'badge-bad'}">
                    ${row.status === 'layak' ? 'Layak' : 'Tidak Layak'}
                </span>
            </td>
        </tr>
    `).join('');
}

function escapeHtml(text) {
    if (text === null || text === undefined) return '';

    return String(text)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

render();