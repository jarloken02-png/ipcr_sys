<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dean IPCR Submission - {{ $submission->title }}</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/urs_logo.jpg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
    <nav class="bg-white shadow-sm border-b sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/urs_logo.jpg') }}" alt="URS Logo" class="h-10 w-auto object-contain">
                <div>
                    <h1 class="text-base sm:text-lg font-bold">Dean IPCR Submission</h1>
                    <p class="text-xs text-gray-500">HR View</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a id="deanIpcrBackLink" href="{{ route('faculty.summary-reports', ['category' => 'dean-ipcrs', 'department' => 'all']) }}" onclick="return closeDeanIpcrTab(event, this)" class="px-3 py-2 rounded-md text-xs font-semibold border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">Back</a>
                <a href="{{ route('faculty.summary-reports.dean-ipcrs.export', ['submission' => $submission->id]) }}" class="px-3 py-2 rounded-md text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-700 transition-colors">Export</a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-6">
        <section class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 sm:p-6">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">{{ $submission->title }}</h2>
                    <p class="text-sm text-gray-600 mt-1">{{ $submission->user?->name ?? 'Unknown Dean' }}{{ $submission->user?->employee_id ? ' - ' . $submission->user->employee_id : '' }}</p>
                </div>
                @if($latestCalibration)
                <div class="inline-flex items-center gap-2 px-3 py-2 rounded-md bg-green-50 border border-green-200">
                    <span class="text-xs font-semibold uppercase tracking-wider text-green-700">Latest Calibrated Score</span>
                    <span class="text-base font-bold text-green-700">{{ number_format($latestCalibration->overall_score, 2) }}</span>
                </div>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-5 text-sm">
                <div class="bg-gray-50 rounded-md p-3">
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Department</div>
                    <div class="font-semibold mt-1">{{ $submission->user?->department?->code ?? $submission->user?->department?->name ?? 'N/A' }}</div>
                </div>
                <div class="bg-gray-50 rounded-md p-3">
                    <div class="text-xs text-gray-500 uppercase tracking-wider">School Year</div>
                    <div class="font-semibold mt-1">{{ $submission->school_year }}</div>
                </div>
                <div class="bg-gray-50 rounded-md p-3">
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Semester</div>
                    <div class="font-semibold mt-1">{{ $submission->semester }}</div>
                </div>
                <div class="bg-gray-50 rounded-md p-3">
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Submitted</div>
                    <div class="font-semibold mt-1">{{ $submission->submitted_at?->format('M d, Y h:i A') ?? 'N/A' }}</div>
                </div>
            </div>
        </section>

        <section class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 sm:p-6">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800 mb-3">Calibration History</h3>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[620px] text-sm border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border border-gray-200">
                            <th class="px-3 py-2 text-left text-[11px] font-bold uppercase tracking-wider text-gray-500 border border-gray-200">Calibrated By</th>
                            <th class="px-3 py-2 text-left text-[11px] font-bold uppercase tracking-wider text-gray-500 border border-gray-200">Score</th>
                            <th class="px-3 py-2 text-left text-[11px] font-bold uppercase tracking-wider text-gray-500 border border-gray-200">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($calibrationHistory as $item)
                        <tr class="border border-gray-200">
                            <td class="px-3 py-2 border border-gray-200">{{ $item['dean_name'] }}</td>
                            <td class="px-3 py-2 border border-gray-200 font-semibold">{{ number_format($item['overall_score'], 2) }}</td>
                            <td class="px-3 py-2 border border-gray-200">{{ $item['updated_at']?->format('M d, Y h:i A') ?? 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-3 py-6 text-center text-sm text-gray-500 border border-gray-200">No calibration history found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 sm:p-6">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800 mb-3">IPCR Content</h3>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[980px] text-sm border-collapse" id="deanIpcrTable">
                    <thead>
                        <tr class="bg-gray-100 border border-gray-300">
                            <th class="border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700" rowspan="2" style="width: 15%;">MFO</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700" rowspan="2" style="width: 25%;">Success Indicators</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700" rowspan="2" style="width: 20%;">Actual Accomplishments</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700" colspan="4">Rating</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700" rowspan="2" style="width: 15%;">Remarks</th>
                        </tr>
                        <tr class="bg-gray-100 border border-gray-300">
                            <th class="border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-600">Q</th>
                            <th class="border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-600">E</th>
                            <th class="border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-600">T</th>
                            <th class="border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-600">A</th>
                        </tr>
                    </thead>
                    <tbody>
                        {!! $submission->table_body_html !!}
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <div id="soDocsModal" class="fixed inset-0 z-[70] hidden">
        <div class="absolute inset-0 bg-black/60" onclick="closeSoDocsModal()"></div>
        <div class="relative mx-auto mt-6 sm:mt-10 w-full max-w-3xl bg-white rounded-2xl shadow-xl overflow-hidden z-10">
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <h3 id="soDocsTitle" class="text-sm font-bold text-gray-900 truncate"></h3>
                    <p id="soDocsDesc" class="text-xs text-gray-500 truncate mt-0.5"></p>
                </div>
                <button onclick="closeSoDocsModal()" class="text-gray-400 hover:text-gray-600 ml-3 flex-shrink-0 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div id="soDocsList" class="px-5 py-4 max-h-[65vh] overflow-y-auto bg-slate-50/60">
                <div class="text-center py-8 text-sm text-gray-400">Loading documents...</div>
            </div>

            <div class="px-5 py-3 bg-gray-50 border-t border-gray-200 flex justify-end">
                <button onclick="closeSoDocsModal()" class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">Close</button>
            </div>
        </div>
    </div>

    <div id="docPreviewModal" class="fixed inset-0 z-[80] hidden">
        <div class="absolute inset-0 bg-black/70" onclick="closeDocPreviewModal()"></div>
        <div class="relative mx-auto mt-4 sm:mt-8 w-full max-w-5xl bg-white rounded-2xl shadow-xl overflow-hidden z-10">
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <h3 id="docPreviewTitle" class="text-sm sm:text-base font-bold text-gray-900 truncate">Document Preview</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Image preview</p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="closeDocPreviewModal()" class="text-gray-400 hover:text-gray-600 ml-1 flex-shrink-0 p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <div class="bg-slate-100">
                <iframe id="docPreviewFrame" title="Document preview" class="w-full h-[75vh] border-0"></iframe>
            </div>

            <div class="px-5 py-2.5 bg-gray-50 border-t border-gray-200 text-[11px] text-gray-500">
                Close this window to return to supporting documents.
            </div>
        </div>
    </div>

    @php
        $deanIpcrSubmissionConfig = [
            'fallbackUrl' => route('faculty.summary-reports', ['category' => 'dean-ipcrs', 'department' => 'all']),
            'supportingDocumentsBySo' => ($supportingDocuments ?? collect())->toArray(),
        ];
    @endphp
    <script type="application/json" id="dean-ipcr-submission-config">
        @json($deanIpcrSubmissionConfig)
    </script>
    <script src="{{ Vite::asset('resources/js/dashboard_faculty_dean-ipcr-submission.js') }}"></script>
</body>
</html>
