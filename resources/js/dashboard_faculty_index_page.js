var allFacultySubmissions = [];
var allCalibrationSubmissions = [];
var currentDeanPreviewSubmissionId = null;
var currentDeanPreviewType = null;

var facultyIndexConfigEl = document.getElementById('faculty-index-page-config');
var facultyIndexConfig = {};

if (facultyIndexConfigEl) {
    try {
        facultyIndexConfig = JSON.parse(facultyIndexConfigEl.textContent || '{}');
    } catch (error) {
        facultyIndexConfig = {};
    }
}

window.soPerformanceData = Array.isArray(facultyIndexConfig.soPerformanceData)
    ? facultyIndexConfig.soPerformanceData
    : [];

window.directorSubmissionTrendData = Array.isArray(facultyIndexConfig.directorSubmissionTrendData)
    ? facultyIndexConfig.directorSubmissionTrendData
    : [0, 0, 0, 0, 0, 0];

var facultyIndexRoutes = facultyIndexConfig.routes || {};
var facultyIndexReturnedCalibration = facultyIndexConfig.returnedCalibration || null;
var facultyIndexCsrfToken = facultyIndexConfig.csrfToken || '';

(function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || facultyIndexCsrfToken;
    const myIpcrsUrl = facultyIndexRoutes.myIpcrs || '/faculty/my-ipcrs';
    const facultySubmissionsUrl = facultyIndexRoutes.facultySubmissions || '/dean/review/faculty-submissions';
    const deanSubmissionsUrl = facultyIndexRoutes.deanSubmissions || '/dean/review/dean-submissions';
    const PREVIEW_LIMIT = 3;

    function renderFacultyCard(sub) {
        var calBadge = '';
        var borderClass = 'border-indigo-200';
        var bgClass = 'bg-indigo-50';
        if (sub.calibration_status === 'calibrated') {
            var calibratedBy = sub.calibrated_by
                ? '<div class="text-[11px] text-green-700 font-medium">Calibrated by ' + sub.calibrated_by + '</div>'
                : '';
            calBadge = '<div class="mt-1 space-y-1"><div class="flex items-center gap-1.5"><span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded"><i class="fas fa-check-circle mr-0.5"></i>Calibrated</span><span class="text-xs font-bold text-green-700">' + (parseFloat(sub.calibration_score) || 0).toFixed(2) + '</span></div>' + calibratedBy + '</div>';
            borderClass = 'border-green-200';
            bgClass = 'bg-green-50';
        } else if (sub.calibration_status === 'draft') {
            calBadge = '<div class="mt-1"><span class="px-2 py-0.5 bg-amber-100 text-amber-700 text-xs font-semibold rounded"><i class="fas fa-pencil-alt mr-0.5"></i>Draft</span></div>';
        } else {
            calBadge = '<div class="mt-1"><span class="px-2 py-0.5 bg-gray-100 text-gray-500 text-xs font-semibold rounded">Pending</span></div>';
        }
        return '<div class="p-3 ' + bgClass + ' rounded-xl border ' + borderClass + '">' +
            '<div class="flex justify-between items-start gap-2">' +
                '<div class="flex-1 min-w-0">' +
                    '<p class="text-sm font-semibold text-gray-900 truncate">' + (sub.user_name || 'Unknown') + '</p>' +
                    '<p class="text-xs text-gray-600 truncate">' + (sub.title || 'Untitled') + '</p>' +
                    '<p class="text-xs text-gray-500">' + (sub.school_year || '') + ' &bull; ' + (sub.semester || '') + '</p>' +
                    calBadge +
                '</div>' +
                '<div class="flex flex-col gap-1 flex-shrink-0">' +
                    '<button onclick="viewFacultySubmission(' + sub.id + ')" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold py-1.5 px-3 rounded text-center">View</button>' +
                    '<span class="px-2 py-1 bg-indigo-100 text-indigo-700 text-xs font-semibold rounded text-center">' + (sub.status ? sub.status.charAt(0).toUpperCase() + sub.status.slice(1) : 'N/A') + '</span>' +
                '</div>' +
            '</div>' +
        '</div>';
    }

    function renderCalibrationCard(sub) {
        var calBadge = '';
        var borderClass = 'border-amber-200';
        var bgClass = 'bg-amber-50';
        if (sub.calibration_status === 'calibrated') {
            var calibratedBy = sub.calibrated_by
                ? '<div class="text-[11px] text-green-700 font-medium">Calibrated by ' + sub.calibrated_by + '</div>'
                : '';
            calBadge = '<div class="mt-1 space-y-1"><div class="flex items-center gap-1.5"><span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded"><i class="fas fa-check-circle mr-0.5"></i>Calibrated</span><span class="text-xs font-bold text-green-700">' + (parseFloat(sub.calibration_score) || 0).toFixed(2) + '</span></div>' + calibratedBy + '</div>';
            borderClass = 'border-green-200';
            bgClass = 'bg-green-50';
        } else if (sub.calibration_status === 'draft') {
            calBadge = '<div class="mt-1"><span class="px-2 py-0.5 bg-amber-100 text-amber-700 text-xs font-semibold rounded"><i class="fas fa-pencil-alt mr-0.5"></i>Draft</span></div>';
        } else {
            calBadge = '<div class="mt-1"><span class="px-2 py-0.5 bg-gray-100 text-gray-500 text-xs font-semibold rounded">Pending</span></div>';
        }
        return '<div class="p-3 ' + bgClass + ' rounded-xl border ' + borderClass + '">' +
            '<div class="flex justify-between items-start gap-2">' +
                '<div class="flex-1 min-w-0">' +
                    '<p class="text-sm font-semibold text-gray-900 truncate">' + (sub.user_name || 'Unknown') + '</p>' +
                    '<p class="text-xs text-amber-700 font-medium">' + (sub.department || 'N/A') + '</p>' +
                    '<p class="text-xs text-gray-600 truncate">' + (sub.title || 'Untitled') + '</p>' +
                    '<p class="text-xs text-gray-500">' + (sub.school_year || '') + ' &bull; ' + (sub.semester || '') + '</p>' +
                    calBadge +
                '</div>' +
                '<div class="flex flex-col gap-1 flex-shrink-0">' +
                    '<button onclick="viewDeanSubmission(' + sub.id + ')" class="bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold py-1.5 px-3 rounded text-center">View</button>' +
                    '<span class="px-2 py-1 bg-amber-100 text-amber-700 text-xs font-semibold rounded text-center">' + (sub.status ? sub.status.charAt(0).toUpperCase() + sub.status.slice(1) : 'N/A') + '</span>' +
                '</div>' +
            '</div>' +
        '</div>';
    }

    async function loadDeanFacultySubmissions() {
        const container = document.getElementById('deanFacultySubmissionsList');
        if (!container) return;
        try {
            const response = await fetch(facultySubmissionsUrl, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            });
            const data = await response.json();
            if (!data.success || data.submissions.length === 0) {
                container.innerHTML = '<p class="text-xs text-gray-500 text-center py-4">No faculty submissions yet</p>';
                return;
            }
            allFacultySubmissions = data.submissions;
            const preview = data.submissions.slice(0, PREVIEW_LIMIT);
            container.innerHTML = '<div class="space-y-3">' + preview.map(renderFacultyCard).join('') + '</div>';
            if (data.submissions.length > PREVIEW_LIMIT) {
                document.getElementById('expandFacultyBtn').classList.remove('hidden');
            }
        } catch (error) {
            container.innerHTML = '<p class="text-xs text-red-500 text-center py-4">Failed to load submissions</p>';
        }
    }

    async function loadDeanCalibrationSubmissions() {
        const container = document.getElementById('deanCalibrationList');
        if (!container) return;
        try {
            const response = await fetch(deanSubmissionsUrl, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            });
            const data = await response.json();
            if (!data.success || data.submissions.length === 0) {
                container.innerHTML = '<p class="text-xs text-gray-500 text-center py-4">No other deans\' submissions yet</p>';
                return;
            }
            allCalibrationSubmissions = data.submissions;
            const preview = data.submissions.slice(0, PREVIEW_LIMIT);
            container.innerHTML = '<div class="space-y-3">' + preview.map(renderCalibrationCard).join('') + '</div>';
            if (data.submissions.length > PREVIEW_LIMIT) {
                document.getElementById('expandCalibrationBtn').classList.remove('hidden');
            }
        } catch (error) {
            container.innerHTML = '<p class="text-xs text-red-500 text-center py-4">Failed to load submissions</p>';
        }
    }

    // Expose render functions for expand
    window._renderFacultyCard = renderFacultyCard;
    window._renderCalibrationCard = renderCalibrationCard;

    document.addEventListener('DOMContentLoaded', function() {
        loadDeanFacultySubmissions();
        loadDeanCalibrationSubmissions();
    });
})();

var currentExpandedType = null;
var currentExpandedFilter = 'all';

function expandSection(type) {
    const mainContent = document.getElementById('dashboardMainContent');
    const expandedView = document.getElementById('expandedView');
    const titleEl = document.getElementById('expandedViewTitle');
    const subtitleEl = document.getElementById('expandedViewSubtitle');
    const iconEl = document.getElementById('expandedViewIcon');
    const filtersEl = document.getElementById('expandedViewFilters');

    currentExpandedType = type;
    currentExpandedFilter = 'all';
    mainContent.classList.add('hidden');
    expandedView.classList.remove('hidden');

    if (type === 'faculty') {
        titleEl.textContent = 'Faculty IPCRs';
        subtitleEl.textContent = allFacultySubmissions.length + ' submissions';
        iconEl.className = 'w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center';
        iconEl.innerHTML = '<svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>';

        // Build filter buttons
        var pendingCount = allFacultySubmissions.filter(function(s) { return s.calibration_status !== 'calibrated'; }).length;
        var calibratedCount = allFacultySubmissions.filter(function(s) { return s.calibration_status === 'calibrated'; }).length;
        filtersEl.innerHTML =
            '<button onclick="filterExpanded(\'all\')" id="exp-filter-all" class="exp-filter-btn px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors bg-indigo-600 text-white border-indigo-600">All (' + allFacultySubmissions.length + ')</button>' +
            '<button onclick="filterExpanded(\'pending\')" id="exp-filter-pending" class="exp-filter-btn px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors bg-white text-gray-700 border-gray-300 hover:bg-gray-50">Pending (' + pendingCount + ')</button>' +
            '<button onclick="filterExpanded(\'calibrated\')" id="exp-filter-calibrated" class="exp-filter-btn px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors bg-white text-gray-700 border-gray-300 hover:bg-gray-50">Calibrated (' + calibratedCount + ')</button>';
        filtersEl.classList.remove('hidden');

        renderExpandedList('all');
    } else {
        titleEl.textContent = 'Dean Calibration';
        subtitleEl.textContent = allCalibrationSubmissions.length + ' submissions';
        iconEl.className = 'w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center';
        iconEl.innerHTML = '<svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>';

        var dPendingCount = allCalibrationSubmissions.filter(function(s) { return s.calibration_status !== 'calibrated'; }).length;
        var dCalibratedCount = allCalibrationSubmissions.filter(function(s) { return s.calibration_status === 'calibrated'; }).length;
        filtersEl.innerHTML =
            '<button onclick="filterExpanded(\'all\')" id="exp-filter-all" class="exp-filter-btn px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors bg-amber-600 text-white border-amber-600">All (' + allCalibrationSubmissions.length + ')</button>' +
            '<button onclick="filterExpanded(\'pending\')" id="exp-filter-pending" class="exp-filter-btn px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors bg-white text-gray-700 border-gray-300 hover:bg-gray-50">Pending (' + dPendingCount + ')</button>' +
            '<button onclick="filterExpanded(\'calibrated\')" id="exp-filter-calibrated" class="exp-filter-btn px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors bg-white text-gray-700 border-gray-300 hover:bg-gray-50">Calibrated (' + dCalibratedCount + ')</button>';
        filtersEl.classList.remove('hidden');

        renderExpandedList('all');
    }

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

window.filterExpanded = function(filter) {
    currentExpandedFilter = filter;
    var activeColor = currentExpandedType === 'faculty' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-amber-600 text-white border-amber-600';
    document.querySelectorAll('.exp-filter-btn').forEach(function(btn) {
        btn.className = 'exp-filter-btn px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors bg-white text-gray-700 border-gray-300 hover:bg-gray-50';
    });
    var activeBtn = document.getElementById('exp-filter-' + filter);
    if (activeBtn) activeBtn.className = 'exp-filter-btn px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors ' + activeColor;
    renderExpandedList(filter);
};

function renderExpandedList(filter) {
    var listEl = document.getElementById('expandedListContent');
    var subtitleEl = document.getElementById('expandedViewSubtitle');
    var items, renderer;

    if (currentExpandedType === 'faculty') {
        items = allFacultySubmissions;
        renderer = window._renderFacultyCard;
    } else {
        items = allCalibrationSubmissions;
        renderer = window._renderCalibrationCard;
    }

    var filtered = items;
    if (filter === 'pending') {
        filtered = items.filter(function(s) { return s.calibration_status !== 'calibrated'; });
    } else if (filter === 'calibrated') {
        filtered = items.filter(function(s) { return s.calibration_status === 'calibrated'; });
    }

    subtitleEl.textContent = filtered.length + ' of ' + items.length + ' submissions' + (filter !== 'all' ? ' (' + filter + ')' : '');

    if (filtered.length === 0) {
        listEl.innerHTML = '<div class="text-center py-8"><p class="text-sm text-gray-400">No ' + filter + ' submissions found</p></div>';
    } else {
        listEl.innerHTML = filtered.map(renderer).join('');
    }
}

function collapseExpandedView() {
    document.getElementById('expandedView').classList.add('hidden');
    document.getElementById('dashboardMainContent').classList.remove('hidden');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// View a faculty IPCR submission in preview modal
window.viewFacultySubmission = async function(submissionId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || facultyIndexCsrfToken;
    const modal = document.getElementById('deanPreviewModal');
    const tableBody = document.getElementById('deanPreviewTableBody');
    const titleEl = document.getElementById('deanPreviewTitle');
    const yearEl = document.getElementById('deanPreviewSchoolYear');
    const semEl = document.getElementById('deanPreviewSemester');
    const rateeEl = document.getElementById('deanPreviewRatee');
    const approvedByEl = document.getElementById('deanPreviewApprovedBy');
    const notedByEl = document.getElementById('deanPreviewNotedBy');
    const statusEl = document.getElementById('deanPreviewStatus');
    const loading = document.getElementById('deanPreviewLoading');
    const content = document.getElementById('deanPreviewContent');

    currentDeanPreviewSubmissionId = submissionId;
    currentDeanPreviewType = 'faculty';
    modal.classList.remove('hidden');
    loading.classList.remove('hidden');
    content.classList.add('hidden');
    document.getElementById('deanPreviewOverallAvg').classList.add('hidden');
    resetCalibrationUI();

    try {
        const response = await fetch('/dean/review/faculty-submissions/' + submissionId, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        });
        const data = await response.json();
        if (data.success && data.submission) {
            const sub = data.submission;
            titleEl.textContent = sub.title || 'IPCR';
            if (yearEl) yearEl.textContent = sub.school_year || '';
            if (semEl) semEl.textContent = sub.semester || '';
            if (rateeEl) rateeEl.textContent = sub.user_name || 'Unknown';
            if (approvedByEl) approvedByEl.textContent = sub.approved_by || '-';
            if (notedByEl) notedByEl.textContent = sub.noted_by || '-';
            if (statusEl) {
                const st = sub.status ? sub.status.charAt(0).toUpperCase() + sub.status.slice(1) : '';
                statusEl.textContent = st;
                statusEl.className = 'font-semibold px-2 py-0.5 rounded text-xs ' +
                    (sub.status === 'submitted' ? 'bg-blue-100 text-blue-700' :
                     sub.status === 'approved' ? 'bg-green-100 text-green-700' :
                     sub.status === 'returned' ? 'bg-red-100 text-red-700' :
                     'bg-gray-100 text-gray-700');
            }
            if (tableBody && sub.table_body_html) {
                tableBody.innerHTML = sub.table_body_html;
                makeTableCalibrationEditable(tableBody);
                if (sub.calibration && sub.calibration.calibration_data) {
                    applyCalibrationData(tableBody, sub.calibration.calibration_data);
                }
                labelQetaInputsDean(tableBody);
                computeOverallAverage(tableBody);
                attachCalibrationInputListeners(tableBody);
                attachSoDocClickHandlers(tableBody, sub.user_id, submissionId, 'ipcr_submission');
                showCalibrationButtons(sub.calibration);
            }
            loading.classList.add('hidden');
            content.classList.remove('hidden');
        } else {
            modal.classList.add('hidden');
            alert('Submission not found.');
        }
    } catch (error) {
        console.error('Error:', error);
        modal.classList.add('hidden');
        alert('Failed to load submission.');
    }
};

// View a dean IPCR submission in preview modal
window.viewDeanSubmission = async function(submissionId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || facultyIndexCsrfToken;
    const modal = document.getElementById('deanPreviewModal');
    const tableBody = document.getElementById('deanPreviewTableBody');
    const titleEl = document.getElementById('deanPreviewTitle');
    const yearEl = document.getElementById('deanPreviewSchoolYear');
    const semEl = document.getElementById('deanPreviewSemester');
    const rateeEl = document.getElementById('deanPreviewRatee');
    const approvedByEl = document.getElementById('deanPreviewApprovedBy');
    const notedByEl = document.getElementById('deanPreviewNotedBy');
    const statusEl = document.getElementById('deanPreviewStatus');
    const loading = document.getElementById('deanPreviewLoading');
    const content = document.getElementById('deanPreviewContent');

    currentDeanPreviewSubmissionId = submissionId;
    currentDeanPreviewType = 'dean';
    modal.classList.remove('hidden');
    loading.classList.remove('hidden');
    content.classList.add('hidden');
    document.getElementById('deanPreviewOverallAvg').classList.add('hidden');
    resetCalibrationUI();

    try {
        const response = await fetch('/dean/review/dean-submissions/' + submissionId, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        });
        const data = await response.json();
        if (data.success && data.submission) {
            const sub = data.submission;
            const deptLabel = sub.department ? ' (' + sub.department + ')' : '';
            titleEl.textContent = (sub.title || 'IPCR') + deptLabel;
            if (yearEl) yearEl.textContent = sub.school_year || '';
            if (semEl) semEl.textContent = sub.semester || '';
            if (rateeEl) rateeEl.textContent = (sub.user_name || 'Unknown') + deptLabel;
            if (approvedByEl) approvedByEl.textContent = sub.approved_by || '-';
            if (notedByEl) notedByEl.textContent = sub.noted_by || '-';
            if (statusEl) {
                const st = sub.status ? sub.status.charAt(0).toUpperCase() + sub.status.slice(1) : '';
                statusEl.textContent = st;
                statusEl.className = 'font-semibold px-2 py-0.5 rounded text-xs ' +
                    (sub.status === 'submitted' ? 'bg-blue-100 text-blue-700' :
                     sub.status === 'approved' ? 'bg-green-100 text-green-700' :
                     sub.status === 'returned' ? 'bg-red-100 text-red-700' :
                     'bg-gray-100 text-gray-700');
            }
            if (tableBody && sub.table_body_html) {
                tableBody.innerHTML = sub.table_body_html;
                makeTableCalibrationEditable(tableBody);
                if (sub.calibration && sub.calibration.calibration_data) {
                    applyCalibrationData(tableBody, sub.calibration.calibration_data);
                }
                labelQetaInputsDean(tableBody);
                computeOverallAverage(tableBody);
                attachCalibrationInputListeners(tableBody);
                attachSoDocClickHandlers(tableBody, sub.user_id, submissionId, 'ipcr_submission');
                showCalibrationButtons(sub.calibration);
            }
            loading.classList.add('hidden');
            content.classList.remove('hidden');
        } else {
            modal.classList.add('hidden');
            alert('Submission not found.');
        }
    } catch (error) {
        console.error('Error:', error);
        modal.classList.add('hidden');
        alert('Failed to load submission.');
    }
};

window.closeDeanPreviewModal = function() {
    document.getElementById('deanPreviewModal').classList.add('hidden');
};

// Make table editable only for Q, E, T, Remarks columns (dean calibration)
function makeTableCalibrationEditable(tableBody) {
    var rowIndex = 0;
    tableBody.querySelectorAll('tr').forEach(function(row) {
        var isHeaderRow = row.classList.contains('bg-green-100') ||
            row.classList.contains('bg-purple-100') ||
            row.classList.contains('bg-orange-100') ||
            row.classList.contains('bg-blue-100') ||
            row.classList.contains('bg-gray-100') ||
            row.querySelector('td[colspan]');

        var cells = row.querySelectorAll('td');
        cells.forEach(function(cell, cellIdx) {
            cell.setAttribute('contenteditable', 'false');
            cell.style.userSelect = 'none';
            cell.querySelectorAll('input, textarea').forEach(function(el) {
                el.setAttribute('readonly', 'true');
                el.setAttribute('disabled', 'true');
                el.style.pointerEvents = 'none';
            });
        });

        // For data rows (8 cells: MFO|SI|Accomplishments|Q|E|T|A|Remarks), enable Q(3), E(4), T(5), Remarks(7)
        if (!isHeaderRow && cells.length >= 8) {
            row.setAttribute('data-calibration-row', rowIndex);
            [3, 4, 5].forEach(function(idx) {
                var input = cells[idx] ? cells[idx].querySelector('input[type="number"]') : null;
                if (input) {
                    input.removeAttribute('readonly');
                    input.removeAttribute('disabled');
                    input.style.pointerEvents = 'auto';
                    input.style.backgroundColor = '#fefce8';
                    input.style.border = '1px solid #fbbf24';
                    input.style.borderRadius = '4px';
                    input.setAttribute('data-calibration-field', idx === 3 ? 'q' : idx === 4 ? 'e' : 't');
                    input.min = '1';
                    input.max = '5';
                    input.step = '0.01';
                }
            });
            // Remarks cell (index 7) - enable text input or make contenteditable
            if (cells[7]) {
                var remarksInput = cells[7].querySelector('input, textarea');
                if (remarksInput) {
                    remarksInput.removeAttribute('readonly');
                    remarksInput.removeAttribute('disabled');
                    remarksInput.style.pointerEvents = 'auto';
                    remarksInput.style.backgroundColor = '#fefce8';
                    remarksInput.style.border = '1px solid #fbbf24';
                    remarksInput.style.borderRadius = '4px';
                    remarksInput.setAttribute('data-calibration-field', 'remarks');
                } else {
                    cells[7].setAttribute('contenteditable', 'true');
                    cells[7].style.backgroundColor = '#fefce8';
                    cells[7].style.border = '1px solid #fbbf24';
                    cells[7].style.borderRadius = '4px';
                    cells[7].style.userSelect = 'text';
                    cells[7].style.cursor = 'text';
                    cells[7].setAttribute('data-calibration-field', 'remarks');
                }
            }
            rowIndex++;
        }
    });
}

// Apply saved calibration data to table inputs
function applyCalibrationData(tableBody, calibrationData) {
    if (!calibrationData || !Array.isArray(calibrationData)) return;
    var dataRowIndex = 0;
    tableBody.querySelectorAll('tr').forEach(function(row) {
        var isHeaderRow = row.classList.contains('bg-green-100') ||
            row.classList.contains('bg-purple-100') ||
            row.classList.contains('bg-orange-100') ||
            row.classList.contains('bg-blue-100') ||
            row.classList.contains('bg-gray-100') ||
            row.querySelector('td[colspan]');
        var cells = row.querySelectorAll('td');
        if (!isHeaderRow && cells.length >= 8) {
            var rowData = calibrationData[dataRowIndex];
            if (rowData) {
                var qInput = cells[3] ? cells[3].querySelector('input[type="number"]') : null;
                var eInput = cells[4] ? cells[4].querySelector('input[type="number"]') : null;
                var tInput = cells[5] ? cells[5].querySelector('input[type="number"]') : null;
                if (qInput && rowData.q !== undefined && rowData.q !== null) qInput.value = rowData.q;
                if (eInput && rowData.e !== undefined && rowData.e !== null) eInput.value = rowData.e;
                if (tInput && rowData.t !== undefined && rowData.t !== null) tInput.value = rowData.t;
                if (rowData.remarks !== undefined && rowData.remarks !== null) {
                    var remarksInput = cells[7] ? cells[7].querySelector('input, textarea') : null;
                    if (remarksInput) {
                        remarksInput.value = rowData.remarks;
                    } else if (cells[7]) {
                        cells[7].textContent = rowData.remarks;
                    }
                }
            }
            dataRowIndex++;
        }
    });
}

// Attach input listeners for live A=(Q+E+T)/3 computation during calibration
function attachCalibrationInputListeners(tableBody) {
    tableBody.querySelectorAll('tr').forEach(function(row) {
        var isHeaderRow = row.classList.contains('bg-green-100') ||
            row.classList.contains('bg-purple-100') ||
            row.classList.contains('bg-orange-100') ||
            row.classList.contains('bg-blue-100') ||
            row.classList.contains('bg-gray-100') ||
            row.querySelector('td[colspan]');
        var cells = row.querySelectorAll('td');
        if (!isHeaderRow && cells.length >= 7) {
            var qInput = cells[3] ? cells[3].querySelector('input[type="number"]') : null;
            var eInput = cells[4] ? cells[4].querySelector('input[type="number"]') : null;
            var tInput = cells[5] ? cells[5].querySelector('input[type="number"]') : null;
            var aInput = cells[6] ? cells[6].querySelector('input[type="number"]') : null;
            if (qInput && eInput && tInput && aInput) {
                var recompute = function() {
                    var q = parseFloat(qInput.value);
                    var e = parseFloat(eInput.value);
                    var t = parseFloat(tInput.value);
                    if (!isNaN(q) && !isNaN(e) && !isNaN(t)) {
                        aInput.value = ((q + e + t) / 3).toFixed(2);
                    }
                    computeOverallAverage(tableBody);
                };
                qInput.addEventListener('input', recompute);
                eInput.addEventListener('input', recompute);
                tInput.addEventListener('input', recompute);
            }
        }
    });
}

// Collect calibration data from the table
function collectCalibrationData(tableBody) {
    var data = [];
    tableBody.querySelectorAll('tr').forEach(function(row) {
        var isHeaderRow = row.classList.contains('bg-green-100') ||
            row.classList.contains('bg-purple-100') ||
            row.classList.contains('bg-orange-100') ||
            row.classList.contains('bg-blue-100') ||
            row.classList.contains('bg-gray-100') ||
            row.querySelector('td[colspan]');
        var cells = row.querySelectorAll('td');
        if (!isHeaderRow && cells.length >= 8) {
            var qInput = cells[3] ? cells[3].querySelector('input[type="number"]') : null;
            var eInput = cells[4] ? cells[4].querySelector('input[type="number"]') : null;
            var tInput = cells[5] ? cells[5].querySelector('input[type="number"]') : null;
            var aInput = cells[6] ? cells[6].querySelector('input[type="number"]') : null;
            var remarksInput = cells[7] ? cells[7].querySelector('input, textarea') : null;
            var remarksValue = remarksInput ? remarksInput.value : (cells[7] ? cells[7].textContent.trim() : '');
            data.push({
                q: qInput ? parseFloat(qInput.value) || 0 : 0,
                e: eInput ? parseFloat(eInput.value) || 0 : 0,
                t: tInput ? parseFloat(tInput.value) || 0 : 0,
                a: aInput ? parseFloat(aInput.value) || 0 : 0,
                remarks: remarksValue
            });
        }
    });
    return data;
}

// Show/hide calibration buttons and status
function showCalibrationButtons(calibration) {
    var draftBtn = document.getElementById('deanSaveDraftBtn');
    var calibrateBtn = document.getElementById('deanCalibrateBtn');
    var statusEl = document.getElementById('deanCalibrationStatus');

    draftBtn.classList.remove('hidden');
    calibrateBtn.classList.remove('hidden');

    if (calibration) {
        statusEl.classList.remove('hidden');
        if (calibration.status === 'calibrated') {
            var calibratedBy = calibration.dean_name ? (' by ' + calibration.dean_name) : '';
            statusEl.textContent = 'Calibrated' + calibratedBy;
            statusEl.className = 'text-xs font-semibold px-2 py-1 rounded bg-green-100 text-green-700';
            calibrateBtn.innerHTML = '<i class="fas fa-sync-alt mr-1"></i>Recalibrate';
        } else {
            statusEl.textContent = 'Draft';
            statusEl.className = 'text-xs font-semibold px-2 py-1 rounded bg-amber-100 text-amber-700';
            calibrateBtn.innerHTML = '<i class="fas fa-check-circle mr-1"></i>Calibrate';
        }
    } else {
        statusEl.classList.add('hidden');
        calibrateBtn.innerHTML = '<i class="fas fa-check-circle mr-1"></i>Calibrate';
    }
}

// Reset calibration UI elements
function resetCalibrationUI() {
    document.getElementById('deanSaveDraftBtn').classList.add('hidden');
    document.getElementById('deanCalibrateBtn').classList.add('hidden');
    document.getElementById('deanCalibrationStatus').classList.add('hidden');
}

// Save calibration (draft or finalize)
window.saveDeanCalibration = async function(status) {
    if (!currentDeanPreviewSubmissionId) return;

    var tableBody = document.getElementById('deanPreviewTableBody');
    var calibrationData = collectCalibrationData(tableBody);
    var avgEl = document.getElementById('deanPreviewAvgValue');
    var overallScore = avgEl ? parseFloat(avgEl.textContent) || 0 : 0;

    var actionLabel = status === 'calibrated' ? 'Calibrate' : 'Save Draft';
    if (status === 'calibrated') {
        const confirmed = await new Promise((resolve) => {
            const modal = document.getElementById('calibrationConfirmModal');
            document.getElementById('confirmCalibrationBtn').onclick = function() {
                modal.classList.add('hidden');
                resolve(true);
            };
            window.closeCalibrationConfirmModal = function() {
                modal.classList.add('hidden');
                resolve(false);
            };
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
        if (!confirmed) return;
    }

    var btn = status === 'calibrated' ? document.getElementById('deanCalibrateBtn') : document.getElementById('deanSaveDraftBtn');
    var originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>' + actionLabel + '...';

    try {
        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        var response = await fetch('/dean/review/calibrations', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                ipcr_submission_id: currentDeanPreviewSubmissionId,
                calibration_data: calibrationData,
                overall_score: overallScore,
                status: status
            })
        });
        var data = await response.json();
        if (data.success) {
            showCalibrationButtons(data.calibration);
            // Update the submission in the local array so cards reflect new status
            var arr = currentDeanPreviewType === 'faculty' ? allFacultySubmissions : allCalibrationSubmissions;
            for (var i = 0; i < arr.length; i++) {
                if (arr[i].id === currentDeanPreviewSubmissionId) {
                    arr[i].calibration_status = data.calibration.display_status || data.calibration.status;
                    arr[i].calibration_score = data.calibration.display_score || data.calibration.overall_score;
                    arr[i].calibrated_by = data.calibration.display_calibrated_by || arr[i].calibrated_by;
                    break;
                }
            }
            // Refresh expanded view if visible
            if (currentExpandedType && !document.getElementById('expandedView').classList.contains('hidden')) {
                expandSection(currentExpandedType);
                filterExpanded(currentExpandedFilter);
            }
            // Show success toast
            var toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 z-[2000] bg-green-600 text-white px-4 py-3 rounded-lg shadow-lg text-sm font-semibold flex items-center gap-2 animate-pulse';
            toast.innerHTML = '<i class="fas fa-check-circle"></i> ' + (status === 'calibrated' ? 'Calibration saved successfully!' : 'Draft saved successfully!');
            document.body.appendChild(toast);
            setTimeout(function() { toast.remove(); }, 3000);
        } else {
            alert(data.message || 'Failed to save calibration.');
        }
    } catch (error) {
        console.error('Error saving calibration:', error);
        alert('Failed to save calibration. Please try again.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
};

// Label QETA inputs and compute A = (Q + E + T) / 3 for each data row
function labelQetaInputsDean(tableBody) {
    if (!tableBody) return;
    tableBody.querySelectorAll('tr').forEach(function(row) {
        if (row.classList.contains('bg-green-100') ||
            row.classList.contains('bg-purple-100') ||
            row.classList.contains('bg-orange-100') ||
            row.classList.contains('bg-blue-100') ||
            row.classList.contains('bg-gray-100') ||
            row.querySelector('td[colspan]')) {
            return;
        }
        var cells = row.querySelectorAll('td');
        // Data rows: MFO | SI | Accomplishments | Q | E | T | A | Remarks
        if (cells.length >= 7) {
            var qInput = cells[3] ? cells[3].querySelector('input[type="number"]') : null;
            var eInput = cells[4] ? cells[4].querySelector('input[type="number"]') : null;
            var tInput = cells[5] ? cells[5].querySelector('input[type="number"]') : null;
            var aInput = cells[6] ? cells[6].querySelector('input[type="number"]') : null;
            if (qInput && eInput && tInput && aInput) {
                var q = parseFloat(qInput.value);
                var e = parseFloat(eInput.value);
                var t = parseFloat(tInput.value);
                if (!isNaN(q) && !isNaN(e) && !isNaN(t)) {
                    aInput.value = ((q + e + t) / 3).toFixed(2);
                }
                aInput.readOnly = true;
                aInput.style.backgroundColor = '#f3f4f6';
            }
        }
    });
}

// Compute and display overall average of all A values
function computeOverallAverage(tableBody) {
    var allA = [];
    tableBody.querySelectorAll('tr').forEach(function(row) {
        if (row.classList.contains('bg-green-100') ||
            row.classList.contains('bg-purple-100') ||
            row.classList.contains('bg-orange-100') ||
            row.classList.contains('bg-blue-100') ||
            row.classList.contains('bg-gray-100') ||
            row.querySelector('td[colspan]')) {
            return;
        }
        var cells = row.querySelectorAll('td');
        if (cells.length >= 7) {
            var aInput = cells[6] ? cells[6].querySelector('input[type="number"]') : null;
            if (aInput && aInput.value) {
                var val = parseFloat(aInput.value);
                if (!isNaN(val)) allA.push(val);
            }
        }
    });
    var container = document.getElementById('deanPreviewOverallAvg');
    var valueEl = document.getElementById('deanPreviewAvgValue');
    if (allA.length > 0) {
        var avg = allA.reduce(function(s, v) { return s + v; }, 0) / allA.length;
        valueEl.textContent = avg.toFixed(2);
        container.classList.remove('hidden');
    } else {
        container.classList.add('hidden');
    }
}

// Attach click handlers to SO rows (bg-blue-100) to view supporting documents
function attachSoDocClickHandlers(tableBody, ownerId, docId, docType) {
    var rows = tableBody.querySelectorAll('tr.bg-blue-100');
    rows.forEach(function(row) {
        var soSpan = row.querySelector('span.font-semibold.text-gray-800');
        var soInput = row.querySelector('input[type="text"]');
        var soLabel = '';
        var soDescription = '';
        if (soSpan) soLabel = soSpan.textContent.trim().replace(/:$/, '');
        if (soInput) soDescription = soInput.value || soInput.getAttribute('value') || '';
        if (!soLabel) return;

        // Re-enable pointer events on the row for clicking
        row.style.cursor = 'pointer';
        row.title = 'Click to view supporting documents';
        row.style.pointerEvents = 'auto';
        row.addEventListener('click', function(ev) {
            ev.stopPropagation();
            openDeanSoDocsModal(soLabel, soDescription, ownerId, docId, docType);
        });

        // Add/refresh doc count badge
        var badge = row.querySelector('.so-doc-badge');
        if (!badge) {
            var td = row.querySelector('td');
            if (td) {
                badge = document.createElement('span');
                badge.className = 'so-doc-badge ml-2 inline-flex items-center gap-1 text-xs text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full';
                badge.innerHTML = '<i class="fas fa-paperclip text-[10px]"></i> <span class="so-doc-count">...</span>';
                badge.style.fontSize = '11px';
                var innerDiv = td.querySelector('div.flex') || td;
                innerDiv.appendChild(badge);
            }
        }
        if (badge) {
            var countEl = badge.querySelector('.so-doc-count');
            if (countEl) fetchDeanSoDocCount(soLabel, ownerId, docId, docType, countEl);
        }
    });
}

function fetchDeanSoDocCount(soLabel, ownerId, docId, docType, countElement) {
    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    var url = '/faculty/supporting-documents?documentable_type=' + encodeURIComponent(docType) +
        '&documentable_id=' + docId +
        '&so_label=' + encodeURIComponent(soLabel) +
        '&owner_id=' + ownerId;
    fetch(url, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken } })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success && countElement) countElement.textContent = data.documents.length;
        })
        .catch(function() { if (countElement) countElement.textContent = '0'; });
}

function openDeanSoDocsModal(soLabel, soDescription, ownerId, docId, docType) {
    document.getElementById('deanSoDocsTitle').textContent = soLabel;
    document.getElementById('deanSoDocsDesc').textContent = soDescription || '';
    document.getElementById('deanSoDocsModal').classList.remove('hidden');

    var container = document.getElementById('deanSoDocsList');
    container.innerHTML = '<div class="flex items-center justify-center py-8"><svg class="animate-spin h-5 w-5 text-gray-300 mr-2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg><span class="text-sm text-gray-400">Loading documents...</span></div>';

    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    var url = '/faculty/supporting-documents?documentable_type=' + encodeURIComponent(docType) +
        '&documentable_id=' + docId +
        '&so_label=' + encodeURIComponent(soLabel) +
        '&owner_id=' + ownerId;
    fetch(url, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken } })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                renderDeanSoDocs(data.documents);
            } else {
                container.innerHTML = '<p class="text-sm text-red-500 text-center py-4">Failed to load documents</p>';
            }
        })
        .catch(function() {
            container.innerHTML = '<p class="text-sm text-red-500 text-center py-4">Error loading documents</p>';
        });
}

function renderDeanSoDocs(documents) {
    var container = document.getElementById('deanSoDocsList');
    if (!documents || documents.length === 0) {
        container.innerHTML = '<div class="text-center py-8"><i class="fas fa-folder-open text-gray-200 text-3xl mb-3"></i><p class="text-sm text-gray-400">No supporting documents</p></div>';
        return;
    }
    container.innerHTML = documents.map(function(doc) {
        var isImage = (doc.mime_type || '').match(/jpg|jpeg|png|gif|webp|image/i);
        var isPdf = (doc.mime_type || '').match(/pdf/i) || (doc.original_name || '').endsWith('.pdf');
        var icon = 'fas fa-file text-gray-400';
        if (isImage) icon = 'fas fa-image text-green-500';
        else if (isPdf) icon = 'fas fa-file-pdf text-red-500';
        else if ((doc.original_name || '').match(/\.(doc|docx)$/i)) icon = 'fas fa-file-word text-blue-500';
        else if ((doc.original_name || '').match(/\.(xls|xlsx)$/i)) icon = 'fas fa-file-excel text-green-600';
        else if ((doc.original_name || '').match(/\.(ppt|pptx)$/i)) icon = 'fas fa-file-powerpoint text-orange-500';

        var nameDisplay = doc.original_name.length > 35 ? doc.original_name.substring(0, 32) + '...' : doc.original_name;
        var previewHtml = isImage
            ? '<div class="w-10 h-10 flex-shrink-0 rounded overflow-hidden bg-gray-200"><img src="' + doc.path + '" alt="" class="w-full h-full object-cover" /></div>'
            : '<i class="' + icon + ' text-lg flex-shrink-0"></i>';

        return '<div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg mb-2 hover:bg-gray-100 transition">' +
            previewHtml +
            '<div class="flex-1 min-w-0">' +
                '<p class="text-sm font-medium text-gray-800 truncate" title="' + doc.original_name + '">' + nameDisplay + '</p>' +
                '<p class="text-xs text-gray-400">' + (doc.file_size_human || '') + ' &bull; ' + (doc.created_at || '') + '</p>' +
            '</div>' +
            '<a href="/faculty/supporting-documents/' + doc.id + '/download" class="p-2 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition" title="Download"><i class="fas fa-download"></i></a>' +
        '</div>';
    }).join('');
}

window.closeDeanSoDocsModal = function() {
    document.getElementById('deanSoDocsModal').classList.add('hidden');
};


window.openReturnedCalibrationModal = function() {
    var modal = document.getElementById('returnedCalibrationModal');
    if (!modal) return;

    if (!facultyIndexReturnedCalibration) return;

    modal.classList.remove('hidden');

    var tableBody = document.getElementById('returnedCalibrationTableBody');
    if (!tableBody) return;

    var calData = facultyIndexReturnedCalibration && Array.isArray(facultyIndexReturnedCalibration.calibrationData)
        ? facultyIndexReturnedCalibration.calibrationData
        : [];
    var tableHtml = facultyIndexReturnedCalibration && typeof facultyIndexReturnedCalibration.tableHtml === 'string'
        ? facultyIndexReturnedCalibration.tableHtml
        : '';

    // Load original table HTML
    tableBody.innerHTML = tableHtml;

    // Apply calibration values and make everything read-only
    var dataRowIndex = 0;
    tableBody.querySelectorAll('tr').forEach(function(row) {
        var isHeaderRow = row.classList.contains('bg-green-100') ||
            row.classList.contains('bg-purple-100') ||
            row.classList.contains('bg-orange-100') ||
            row.classList.contains('bg-blue-100') ||
            row.classList.contains('bg-gray-100') ||
            row.querySelector('td[colspan]');

        var cells = row.querySelectorAll('td');

        // Make all cells read-only
        cells.forEach(function(cell) {
            cell.setAttribute('contenteditable', 'false');
            cell.style.userSelect = 'none';
            cell.querySelectorAll('input, textarea').forEach(function(el) {
                el.setAttribute('readonly', 'true');
                el.setAttribute('disabled', 'true');
                el.style.pointerEvents = 'none';
            });
        });

        // For data rows, overlay calibration values
        if (!isHeaderRow && cells.length >= 8) {
            var rowData = calData[dataRowIndex];
            if (rowData) {
                var qInput = cells[3] ? cells[3].querySelector('input[type="number"]') : null;
                var eInput = cells[4] ? cells[4].querySelector('input[type="number"]') : null;
                var tInput = cells[5] ? cells[5].querySelector('input[type="number"]') : null;
                var aInput = cells[6] ? cells[6].querySelector('input[type="number"]') : null;

                if (qInput && rowData.q !== undefined && rowData.q !== null) {
                    qInput.value = rowData.q;
                    qInput.style.backgroundColor = '#ecfdf5';
                }
                if (eInput && rowData.e !== undefined && rowData.e !== null) {
                    eInput.value = rowData.e;
                    eInput.style.backgroundColor = '#ecfdf5';
                }
                if (tInput && rowData.t !== undefined && rowData.t !== null) {
                    tInput.value = rowData.t;
                    tInput.style.backgroundColor = '#ecfdf5';
                }
                if (aInput && rowData.a !== undefined && rowData.a !== null) {
                    aInput.value = rowData.a;
                    aInput.style.backgroundColor = '#ecfdf5';
                    aInput.style.fontWeight = 'bold';
                } else if (aInput && qInput && eInput && tInput) {
                    var q = parseFloat(qInput.value) || 0;
                    var e = parseFloat(eInput.value) || 0;
                    var t = parseFloat(tInput.value) || 0;
                    aInput.value = ((q + e + t) / 3).toFixed(2);
                    aInput.style.backgroundColor = '#ecfdf5';
                    aInput.style.fontWeight = 'bold';
                }
                if (rowData.remarks !== undefined && rowData.remarks !== null && rowData.remarks !== '') {
                    var remarksInput = cells[7] ? cells[7].querySelector('input, textarea') : null;
                    if (remarksInput) {
                        remarksInput.value = rowData.remarks;
                        remarksInput.style.backgroundColor = '#ecfdf5';
                    } else if (cells[7]) {
                        cells[7].textContent = rowData.remarks;
                        cells[7].style.backgroundColor = '#ecfdf5';
                    }
                }
            }
            dataRowIndex++;
        }
    });
};

window.closeReturnedCalibrationModal = function() {
    document.getElementById('returnedCalibrationModal').classList.add('hidden');
};

window.exportReturnedCalibration = function() {
    var submissionId = facultyIndexReturnedCalibration ? facultyIndexReturnedCalibration.submissionId : null;
    if (!submissionId) return;
    window.location.href = '/faculty/ipcr/submissions/' + submissionId + '/export';
};

document.addEventListener('DOMContentLoaded', function () {
    if (document.body) {
        document.body.style.visibility = 'visible';
    }
});
