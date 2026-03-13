// Mobile menu toggle
window.toggleMobileMenu = function () {
    const menu = document.querySelector('.mobile-menu');
    const overlay = document.querySelector('.mobile-menu-overlay');
    menu.classList.toggle('active');
    overlay.classList.toggle('active');
};

// Notification popup toggle
window.toggleNotificationPopup = function () {
    const popup = document.getElementById('notificationPopup');
    popup.classList.toggle('active');
};

// Close notification popup when clicking outside
document.addEventListener('click', function (e) {
    const popup = document.getElementById('notificationPopup');
    const notificationBtn = e.target.closest('button[onclick*="toggleNotificationPopup"]');

    if (!notificationBtn && popup && !popup.contains(e.target)) {
        popup.classList.remove('active');
    }
});

// Performance Chart — synced with submitted IPCR data, 0–5 rating scale
const ctx = document.getElementById('performanceChart').getContext('2d');
const soData = window.soPerformanceData || [];

function getFilteredData(section) {
    if (section === 'all' || !section) return soData;
    return soData.filter(so => so.section === section);
}

function buildChartData(filtered) {
    const labels = filtered.length > 0 ? filtered.map(so => so.label) : ['No Data'];
    const expected = filtered.length > 0 ? filtered.map(so => so.average) : [0];
    const actual = filtered.length > 0 ? filtered.map(so => so.actual_rating || 0) : [0];
    return { labels, expected, actual };
}

const initial = buildChartData(soData);

const gradientExpected = ctx.createLinearGradient(0, 0, 0, 350);
gradientExpected.addColorStop(0, 'rgba(139, 92, 246, 0.6)'); // vibrant violet
gradientExpected.addColorStop(1, 'rgba(139, 92, 246, 0.0)');

const gradientActual = ctx.createLinearGradient(0, 0, 0, 350);
gradientActual.addColorStop(0, 'rgba(14, 165, 233, 0.6)'); // energetic sky blue
gradientActual.addColorStop(1, 'rgba(14, 165, 233, 0.0)');

const performanceChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: initial.labels,
        datasets: [{
            label: 'Expected Target',
            data: initial.expected,
            borderColor: '#8B5CF6',
            backgroundColor: gradientExpected,
            borderWidth: 3,
            pointBackgroundColor: '#ffffff',
            pointBorderColor: '#8B5CF6',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6,
            fill: true,
            tension: 0.4
        }, {
            label: 'Calibrated Rating',
            data: initial.actual,
            borderColor: '#0EA5E9',
            backgroundColor: gradientActual,
            borderWidth: 3,
            pointBackgroundColor: '#ffffff',
            pointBorderColor: '#0EA5E9',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6,
            fill: true,
            tension: 0.4,
            borderDash: [5, 5]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        plugins: {
            legend: {
                display: true,
                position: 'top',
                align: 'end',
                labels: {
                    usePointStyle: true,
                    boxWidth: 8,
                    padding: 20,
                    font: {
                        family: "'Inter', sans-serif",
                        size: window.innerWidth < 640 ? 11 : 13,
                        weight: '600'
                    },
                    color: '#4B5563'
                }
            },
            tooltip: {
                backgroundColor: 'rgba(255, 255, 255, 0.95)',
                titleColor: '#1F2937',
                bodyColor: '#4B5563',
                titleFont: {
                    family: "'Inter', sans-serif",
                    size: 13,
                    weight: 'bold'
                },
                bodyFont: {
                    family: "'Inter', sans-serif",
                    size: 12,
                    weight: '500'
                },
                borderColor: '#E5E7EB',
                borderWidth: 1,
                padding: 12,
                boxPadding: 6,
                usePointStyle: true,
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + context.parsed.y.toFixed(2);
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                max: 5,
                grid: {
                    color: '#F3F4F6',
                    drawBorder: false,
                },
                border: { display: false, dash: [4, 4] },
                ticks: {
                    stepSize: 1,
                    padding: 10,
                    color: '#9CA3AF',
                    font: {
                        family: "'Inter', sans-serif",
                        size: window.innerWidth < 640 ? 10 : 12,
                        weight: '500'
                    }
                }
            },
            x: {
                grid: {
                    display: false,
                },
                border: { display: false },
                ticks: {
                    padding: 10,
                    color: '#6B7280',
                    font: {
                        family: "'Inter', sans-serif",
                        size: window.innerWidth < 640 ? 10 : 12,
                        weight: '500'
                    },
                    maxRotation: 45,
                    minRotation: 0
                }
            }
        }
    }
});

// Section filter for chart + expected target list
window.filterSection = function (section) {
    // Update active button styling
    document.querySelectorAll('.section-filter-btn').forEach(btn => btn.classList.remove('active'));
    const activeBtn = document.getElementById('filter-' + section);
    if (activeBtn) activeBtn.classList.add('active');

    // Filter chart data
    const filtered = buildChartData(getFilteredData(section));
    performanceChart.data.labels = filtered.labels;
    performanceChart.data.datasets[0].data = filtered.expected;
    performanceChart.data.datasets[1].data = filtered.actual;
    performanceChart.update();

    // Filter section groups in the expected target list
    document.querySelectorAll('.section-group').forEach(group => {
        if (section === 'all') {
            group.style.display = '';
        } else {
            group.style.display = group.dataset.section === section ? '' : 'none';
        }
    });
};

// SO Detail Modal
const sectionLabels = {
    strategic_objectives: 'Strategic Objectives',
    core_functions: 'Core Functions',
    support_functions: 'Support Functions',
};

function getRatingHtml(value) {
    const num = parseFloat(value);
    if (isNaN(num) || num <= 0) return `<span class="text-gray-400 font-medium">—</span>`;
    return `<span class="text-gray-900 font-semibold">${num.toFixed(1)}</span>`;
}

function getDocIcon(mimeType) {
    if (!mimeType) return { cls: 'other', emoji: '📎' };
    if (mimeType.startsWith('image/')) return { cls: 'image', emoji: '🖼️' };
    if (mimeType === 'application/pdf') return { cls: 'pdf', emoji: '📄' };
    if (mimeType.includes('word') || mimeType.includes('document')) return { cls: 'doc', emoji: '📝' };
    return { cls: 'other', emoji: '📎' };
}

window.openSoModal = function (index) {
    const so = (window.soPerformanceData || [])[index];
    if (!so) return;

    // Header
    const secLabel = sectionLabels[so.section] || so.section || '';

    const sectionBadgeEl = document.getElementById('soModalSectionBadge');
    if (sectionBadgeEl) sectionBadgeEl.textContent = secLabel;

    const titleEl = document.getElementById('soModalTitle');
    if (titleEl) titleEl.textContent = so.name || so.label || '';

    const avgEl = document.getElementById('soModalAvgValue');
    if (avgEl) {
        const avg = parseFloat(so.average) || 0;
        avgEl.textContent = avg.toFixed(2);
    }

    // Rows table
    const tbody = document.getElementById('soModalTableBody');
    if (tbody) {
        tbody.innerHTML = '';
        const rows = so.rows || [];
        if (rows.length === 0) {
            tbody.innerHTML = `
                <tr class="so-modal-row-empty">
                    <td colspan="7">
                        <div class="empty-state-icon">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </div>
                        <p class="text-sm font-medium text-gray-900 mb-1">No Performance Data</p>
                        <p class="text-xs text-gray-500">There are no rows recorded for this Strategic Objective.</p>
                    </td>
                </tr>`;
        } else {
            rows.forEach(row => {
                // If this row has a sub_header label, insert a divider row first
                if (row.sub_header) {
                    const dividerTr = document.createElement('tr');
                    dividerTr.className = 'bg-gray-100';
                    dividerTr.innerHTML = `
                        <td colspan="7" class="py-2 px-4 text-xs font-semibold text-gray-600 tracking-wide">
                            ${escHtml(row.sub_header)}
                        </td>
                    `;
                    tbody.appendChild(dividerTr);
                }

                const avg = [row.q, row.e, row.t, row.a]
                    .map(v => parseFloat(v))
                    .filter(v => !isNaN(v) && v > 0);
                const rowAvg = avg.length > 0 ? (avg.reduce((a, b) => a + b, 0) / avg.length).toFixed(2) : 0;
                const accomplishmentVal = (row.accomplishment || '').trim();
                const filled = accomplishmentVal !== '';

                const tr = document.createElement('tr');
                tr.className = 'so-modal-table-row border-b border-gray-100 last:border-0';
                tr.innerHTML = `
                    <td class="py-3 px-4 text-xs text-gray-700 align-top leading-relaxed">${escHtml(row.mfo || '—')}</td>
                    <td class="py-3 px-4 text-xs text-gray-700 align-top leading-relaxed">${escHtml(row.success_indicator || '—')}</td>
                    <td class="py-3 px-4 text-xs align-top leading-relaxed">
                        ${filled
                        ? `<span class="text-gray-900">${escHtml(accomplishmentVal)}</span>`
                        : `<span class="inline-flex items-center px-2 py-0.5 rounded textxs font-medium bg-gray-100 text-gray-600">No Entry</span>`}
                    </td>
                    <td class="py-3 px-2 text-center align-top">${getRatingHtml(row.q)}</td>
                    <td class="py-3 px-2 text-center align-top">${getRatingHtml(row.e)}</td>
                    <td class="py-3 px-2 text-center align-top">${getRatingHtml(row.t)}</td>
                    <td class="py-3 px-3 text-center align-top bg-indigo-50/30">
                        ${getRatingHtml(rowAvg)}
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }
    }

    // Documents grid
    const docsGrid = document.getElementById('soModalDocsGrid');
    const docCount = document.getElementById('soModalDocCount');
    if (docsGrid) {
        docsGrid.innerHTML = '';
        const docs = so.documents || [];
        if (docCount) docCount.textContent = docs.length;

        if (docs.length === 0) {
            docsGrid.innerHTML = `
                <div class="col-span-1 sm:col-span-2 py-8 text-center border-2 border-dashed border-gray-200 rounded-xl bg-gray-50/50">
                    <svg class="mx-auto h-10 w-10 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-sm font-medium text-gray-900">No Documents attached</p>
                    <p class="text-xs text-gray-500 mt-1">Supporting files for this objective will appear here.</p>
                </div>
            `;
        } else {
            docs.forEach(doc => {
                const { cls, emoji } = getDocIcon(doc.mime_type || '');
                const imageExts = /\.(jpe?g|png|gif|webp|bmp|svg)$/i;
                const isImage = (doc.mime_type || '').startsWith('image/') ||
                    imageExts.test(doc.original_name || '') ||
                    imageExts.test(doc.path || '');

                if (isImage) {
                    const card = document.createElement('div');
                    card.className = 'so-doc-card group flex items-center justify-between';
                    card.innerHTML = `
                        <div class="flex items-center gap-3 flex-1 min-w-0 cursor-pointer" onclick="openImageViewer('${escHtml(doc.path || '')}')">
                            <div class="relative w-10 h-10 flex-shrink-0 rounded-lg bg-gray-100 overflow-hidden shadow-inner group/img">
                                <img src="${escHtml(doc.path || '')}" class="w-full h-full object-cover transition-transform duration-300 group-hover/img:scale-110" onerror="this.onerror=null; this.src=''">
                                <div class="absolute inset-0 bg-black/30 flex items-center justify-center opacity-0 group-hover/img:opacity-100 transition-opacity">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-medium text-gray-800 truncate group-hover:text-indigo-600 transition-colors">${escHtml(doc.original_name || 'Document')}</p>
                                <p class="text-xs text-gray-400 mt-0.5">${escHtml(doc.file_size_human || '')}</p>
                            </div>
                        </div>
                        <a href="${escHtml(doc.download_url || doc.path || '#')}" target="_blank" rel="noopener noreferrer" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-full transition-colors flex-shrink-0" title="Download">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                        </a>
                    `;
                    docsGrid.appendChild(card);
                } else {
                    const card = document.createElement('a');
                    card.href = doc.download_url || doc.path || '#';
                    card.target = '_blank';
                    card.rel = 'noopener noreferrer';
                    card.className = 'so-doc-card';
                    card.innerHTML = `
                        <div class="so-doc-icon ${cls}">${emoji}</div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-medium text-gray-800 truncate">${escHtml(doc.original_name || 'Document')}</p>
                            <p class="text-xs text-gray-400">${escHtml(doc.file_size_human || '')}</p>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                    `;
                    docsGrid.appendChild(card);
                }
            });
        }
    }

    // Show modal
    document.getElementById('soDetailModal').classList.add('active');
    document.body.style.overflow = 'hidden';
};

window.closeSoModal = function () {
    const modal = document.getElementById('soDetailModal');
    if (!modal) return;
    modal.classList.remove('active');
    document.body.style.overflow = '';
};

window.openImageViewer = function (url) {
    const overlay = document.getElementById('imageViewerOverlay');
    const img = document.getElementById('imageViewerContent');
    if (!overlay || !img) return;

    img.src = url;
    overlay.classList.remove('hidden');
    overlay.style.display = 'flex';
    // Slightly delay for transition effect
    setTimeout(() => {
        overlay.classList.remove('opacity-0');
        img.classList.remove('scale-95');
    }, 10);
};

window.closeImageViewer = function () {
    const overlay = document.getElementById('imageViewerOverlay');
    const img = document.getElementById('imageViewerContent');
    if (!overlay || !img) return;

    overlay.classList.add('opacity-0');
    img.classList.add('scale-95');
    setTimeout(() => {
        overlay.classList.add('hidden');
        overlay.style.display = 'none';
        img.src = '';
    }, 300);
};

function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

// Compact mode for notifications — synced between popup and sidebar
let compactMode = localStorage.getItem('notif_compact') === '1';

function applyCompactMode() {
    document.querySelectorAll('.notif-card').forEach(card => {
        if (compactMode) {
            card.classList.add('compact-notif');
            card.querySelectorAll('.notif-message').forEach(m => m.style.display = 'none');
            card.querySelectorAll('.notif-time').forEach(t => t.style.display = 'none');
        } else {
            card.classList.remove('compact-notif');
            card.querySelectorAll('.notif-message').forEach(m => m.style.display = '');
            card.querySelectorAll('.notif-time').forEach(t => t.style.display = '');
        }
    });
    document.querySelectorAll('.compact-toggle-btn').forEach(btn => {
        if (compactMode) {
            btn.classList.add('bg-indigo-100', 'border-indigo-300', 'text-indigo-700');
            btn.classList.remove('bg-gray-50', 'border-gray-200', 'text-gray-500');
        } else {
            btn.classList.remove('bg-indigo-100', 'border-indigo-300', 'text-indigo-700');
            btn.classList.add('bg-gray-50', 'border-gray-200', 'text-gray-500');
        }
    });
}

window.toggleCompactMode = function () {
    compactMode = !compactMode;
    localStorage.setItem('notif_compact', compactMode ? '1' : '0');
    applyCompactMode();
};

// Apply on page load
document.addEventListener('DOMContentLoaded', applyCompactMode);

// Mark all notifications as read
window.markAllNotificationsRead = function () {
    fetch('/faculty/notifications/mark-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Hide all badges
            document.querySelectorAll('#notifBadge, #sidebarNotifBadge').forEach(el => el.classList.add('hidden'));
            // Remove unread dots from notification cards
            document.querySelectorAll('.notif-unread-dot').forEach(dot => dot.remove());
            // Remove unread styling from cards
            document.querySelectorAll('.notif-card.notif-unread').forEach(card => card.classList.remove('notif-unread'));
        }
    })
    .catch(() => {});
};
