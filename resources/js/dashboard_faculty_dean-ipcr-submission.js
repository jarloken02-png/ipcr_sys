const deanIpcrSubmissionConfigEl = document.getElementById('dean-ipcr-submission-config');
let deanIpcrSubmissionConfig = {};

if (deanIpcrSubmissionConfigEl) {
    try {
        deanIpcrSubmissionConfig = JSON.parse(deanIpcrSubmissionConfigEl.textContent || '{}');
    } catch (error) {
        deanIpcrSubmissionConfig = {};
    }
}

const deanIpcrFallbackUrl = deanIpcrSubmissionConfig.fallbackUrl || '/faculty/summary-reports?category=dean-ipcrs&department=all';
const supportingDocumentsBySo = deanIpcrSubmissionConfig.supportingDocumentsBySo || {};

        window.closeDeanIpcrTab = function(event, linkEl) {
            if (event) {
                event.preventDefault();
            }

            var fallbackUrl = (linkEl && linkEl.href) ? linkEl.href : deanIpcrFallbackUrl;

            if (window.opener && !window.opener.closed) {
                try {
                    window.opener.focus();
                } catch (_) {
                    // Ignore focus errors from browser policies.
                }

                window.close();

                // Fallback for browsers that block close in this context.
                setTimeout(function () {
                    if (!window.closed) {
                        window.location.href = fallbackUrl;
                    }
                }, 250);

                return false;
            }

            window.location.href = fallbackUrl;
            return false;
        };

        let activeSoDocuments = [];

        function escapeHtml(value) {
            return (value || '').toString()
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function normalizeSoLabel(label) {
            var value = (label || '').toString().toUpperCase().replace(/\s+/g, ' ').trim();
            var match = value.match(/\bSO\s*([A-Z0-9IVXLCM]+)\b/);

            if (match) {
                return 'SO ' + match[1];
            }

            if (value.indexOf(':') !== -1) {
                return value.split(':')[0].trim();
            }

            return value;
        }

        function buildSupportingDocIndex() {
            var indexed = {};

            Object.keys(supportingDocumentsBySo || {}).forEach(function (rawLabel) {
                var normalizedKey = normalizeSoLabel(rawLabel);

                if (!indexed[normalizedKey]) {
                    indexed[normalizedKey] = [];
                }

                indexed[normalizedKey] = indexed[normalizedKey].concat(supportingDocumentsBySo[rawLabel] || []);
            });

            return indexed;
        }

        const supportingDocIndex = buildSupportingDocIndex();

        function getSoRowMeta(row) {
            var firstCell = row.querySelector('td');
            var rawText = firstCell ? (firstCell.innerText || firstCell.textContent || '') : '';
            rawText = rawText.replace(/\s+/g, ' ').trim();

            var label = rawText;
            var description = '';

            if (rawText.indexOf(':') !== -1) {
                var parts = rawText.split(':');
                label = (parts.shift() || '').trim();
                description = parts.join(':').trim();
            }

            var spanLabel = row.querySelector('span.font-semibold.text-gray-800');
            if (spanLabel && spanLabel.textContent) {
                label = spanLabel.textContent.trim().replace(/:$/, '');
            }

            var soInput = row.querySelector('input[type="text"]');
            if (soInput && soInput.value) {
                description = soInput.value;
            }

            return {
                key: normalizeSoLabel(label || rawText),
                label: label || rawText || 'Supporting Documents',
                description: description,
            };
        }

        function getDocumentType(doc) {
            var mime = ((doc.mime_type || '') + '').toLowerCase();
            var name = ((doc.original_name || '') + '').toLowerCase();

            if (mime.indexOf('image/') === 0 || /\.(jpg|jpeg|png|gif|bmp|webp|svg)$/i.test(name)) {
                return 'image';
            }

            if (mime.indexOf('pdf') !== -1 || /\.pdf$/i.test(name)) {
                return 'pdf';
            }

            if (
                mime.indexOf('word') !== -1 ||
                mime.indexOf('officedocument.wordprocessingml') !== -1 ||
                /\.(doc|docx|rtf)$/i.test(name)
            ) {
                return 'word';
            }

            if (
                mime.indexOf('excel') !== -1 ||
                mime.indexOf('spreadsheetml') !== -1 ||
                /\.(xls|xlsx|csv)$/i.test(name)
            ) {
                return 'sheet';
            }

            if (
                mime.indexOf('powerpoint') !== -1 ||
                mime.indexOf('presentationml') !== -1 ||
                /\.(ppt|pptx)$/i.test(name)
            ) {
                return 'slide';
            }

            return 'document';
        }

        function getDocumentTypeUi(docType) {
            if (docType === 'image') {
                return { label: 'Image', className: 'bg-green-100 text-green-700', icon: 'IMG' };
            }

            if (docType === 'pdf') {
                return { label: 'PDF', className: 'bg-red-100 text-red-700', icon: 'PDF' };
            }

            if (docType === 'word') {
                return { label: 'DOCX', className: 'bg-blue-100 text-blue-700', icon: 'DOC' };
            }

            if (docType === 'sheet') {
                return { label: 'Sheet', className: 'bg-emerald-100 text-emerald-700', icon: 'XLS' };
            }

            if (docType === 'slide') {
                return { label: 'Slides', className: 'bg-orange-100 text-orange-700', icon: 'PPT' };
            }

            return { label: 'Document', className: 'bg-slate-100 text-slate-700', icon: 'DOC' };
        }

        function getPreviewButtonLabel(docType) {
            if (docType === 'image') {
                return 'View Image';
            }

            if (docType === 'pdf') {
                return 'View PDF';
            }

            if (docType === 'word') {
                return 'View DOCX';
            }

            if (docType === 'sheet') {
                return 'View Sheet';
            }

            if (docType === 'slide') {
                return 'View Slides';
            }

            return 'Preview';
        }

        function resolveDownloadUrl(doc) {
            var rawUrl = (doc.path || '').toString().trim();
            if (!rawUrl) {
                return '#';
            }

            // Force attachment disposition for legacy Cloudinary assets.
            if (rawUrl.indexOf('/upload/') !== -1) {
                return rawUrl.replace('/upload/', '/upload/fl_attachment/');
            }

            return rawUrl;
        }

        function resolveBrowserViewUrl(doc) {
            var rawUrl = (doc.path || '').toString().trim();
            if (!rawUrl) {
                return '#';
            }

            var type = getDocumentType(doc);

            // Use browser-friendly web viewers for Office formats.
            if (type === 'word' || type === 'sheet' || type === 'slide') {
                return 'https://docs.google.com/gview?url=' + encodeURIComponent(rawUrl) + '&embedded=true';
            }

            // PDFs and images can be shown directly by browser preview features.
            return rawUrl;
        }

        window.openDocPreviewForIndex = function(index) {
            var doc = activeSoDocuments[index];
            if (!doc) {
                return;
            }

            var docType = getDocumentType(doc);
            if (docType !== 'image') {
                return;
            }

            var previewUrl = resolveBrowserViewUrl(doc);
            var frame = document.getElementById('docPreviewFrame');
            var titleEl = document.getElementById('docPreviewTitle');
            var modal = document.getElementById('docPreviewModal');

            if (titleEl) {
                titleEl.textContent = doc.original_name || 'Document Preview';
            }

            if (frame) {
                frame.src = previewUrl;
            }

            if (modal) {
                modal.classList.remove('hidden');
            }
        };

        window.closeDocPreviewModal = function () {
            var modal = document.getElementById('docPreviewModal');
            var frame = document.getElementById('docPreviewFrame');

            if (frame) {
                frame.src = 'about:blank';
            }

            if (modal) {
                modal.classList.add('hidden');
            }
        };

        function renderSoDocuments(documents) {
            var container = document.getElementById('soDocsList');
            if (!container) {
                return;
            }

            activeSoDocuments = documents || [];

            if (!documents || documents.length === 0) {
                container.innerHTML = '<div class="text-center py-8"><p class="text-sm text-gray-400">No supporting documents for this SO.</p></div>';
                return;
            }

            container.innerHTML = documents.map(function (doc, index) {
                var safeName = escapeHtml(doc.original_name || 'Document');
                var nameDisplay = safeName.length > 45 ? safeName.substring(0, 42) + '...' : safeName;
                var meta = [];
                var type = getDocumentType(doc);
                var typeUi = getDocumentTypeUi(type);
                var previewLabel = getPreviewButtonLabel(type);
                var downloadUrl = resolveDownloadUrl(doc);
                var isImage = type === 'image';
                var isDownloadOnly = !isImage;
                var thumbOrIcon = isImage
                    ? '<img src="' + escapeHtml(doc.path || '') + '" alt="' + safeName + '" class="w-11 h-11 rounded-lg object-cover border border-slate-200 shrink-0" loading="lazy">'
                    : '<div class="w-11 h-11 rounded-lg bg-slate-100 border border-slate-200 text-[11px] font-bold text-slate-600 flex items-center justify-center shrink-0">' + typeUi.icon + '</div>';

                if (doc.file_size_human) {
                    meta.push(doc.file_size_human);
                }

                if (doc.created_at_display) {
                    meta.push(doc.created_at_display);
                }

                return '<div class="bg-white border border-slate-200 rounded-xl p-3 mb-3 shadow-sm hover:shadow-md transition-shadow">' +
                    '<div class="flex items-start gap-3">' +
                        thumbOrIcon +
                        '<div class="flex-1 min-w-0">' +
                            '<p class="text-sm font-semibold text-gray-800 truncate" title="' + safeName + '">' + nameDisplay + '</p>' +
                            '<p class="text-xs text-gray-400 mt-0.5">' + (meta.join(' - ') || 'Uploaded file') + '</p>' +
                            '<div class="mt-2 flex items-center gap-2">' +
                                '<span class="inline-flex px-2 py-0.5 text-[10px] font-semibold rounded-full ' + typeUi.className + '">' + typeUi.label + '</span>' +
                                (isDownloadOnly ? '<span class="inline-flex px-2 py-0.5 text-[10px] font-semibold rounded-full bg-amber-100 text-amber-700">Download only</span>' : '') +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="mt-3 flex items-center justify-end gap-2">' +
                        (isImage
                            ? '<button type="button" onclick="openDocPreviewForIndex(' + index + ')" class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition">' + previewLabel + '</button>'
                            : '<a href="' + downloadUrl + '" download class="px-3 py-1.5 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition">Download</a>') +
                    '</div>' +
                '</div>';
            }).join('');
        }

        function openSoDocsModal(title, description, documents) {
            var modal = document.getElementById('soDocsModal');
            var titleEl = document.getElementById('soDocsTitle');
            var descEl = document.getElementById('soDocsDesc');

            if (titleEl) {
                titleEl.textContent = title || 'Supporting Documents';
            }

            if (descEl) {
                descEl.textContent = description || '';
            }

            renderSoDocuments(documents || []);

            if (modal) {
                modal.classList.remove('hidden');
            }
        }

        function attachSoDocumentTriggers() {
            var tableBody = document.querySelector('#deanIpcrTable tbody');
            if (!tableBody) {
                return;
            }

            tableBody.querySelectorAll('tr.bg-blue-100').forEach(function (row) {
                var meta = getSoRowMeta(row);
                var docs = supportingDocIndex[meta.key] || [];

                row.style.cursor = 'pointer';
                row.title = 'Click to view supporting documents';

                var badge = row.querySelector('.so-doc-badge');
                if (!badge) {
                    var firstCell = row.querySelector('td');
                    if (firstCell) {
                        badge = document.createElement('span');
                        badge.className = 'so-doc-badge ml-2 inline-flex items-center text-[11px] font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full';
                        badge.innerHTML = '<span class="so-doc-count">0</span>';
                        var target = firstCell.querySelector('div.flex') || firstCell;
                        target.appendChild(badge);
                    }
                }

                if (badge) {
                    var countEl = badge.querySelector('.so-doc-count');
                    if (countEl) {
                        countEl.textContent = docs.length + ' doc' + (docs.length === 1 ? '' : 's');
                    }
                }

                row.addEventListener('click', function (event) {
                    if (event.target && event.target.closest('a,button,input,textarea,select')) {
                        return;
                    }

                    openSoDocsModal(meta.label, meta.description, docs);
                });
            });
        }

        window.closeSoDocsModal = function () {
            var modal = document.getElementById('soDocsModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        };

        // Make this view strictly read-only.
        document.querySelectorAll('#deanIpcrTable input, #deanIpcrTable textarea, #deanIpcrTable select, #deanIpcrTable button').forEach(function (el) {
            el.setAttribute('disabled', 'disabled');
            el.classList.add('cursor-not-allowed', 'opacity-90');
        });

        attachSoDocumentTriggers();
