<?php

namespace App\Http\Controllers;

use App\Models\DormRoom;
use App\Models\DormStudent;
use App\Models\FoodFinance;
use App\Models\MembershipCard;
use App\Models\StudentCollection;
use App\Models\User;
use App\Support\Audit;
use App\Support\DormStudentDirectory;
use App\Support\SecurityRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DormStudentController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(array_keys($this->statusLabels()))],
            'room' => ['nullable', 'string', 'max:40'],
            'date' => ['nullable', 'date'],
        ]);

        $students = DormStudentDirectory::visibleQuery($filters)
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $waitingApplicants = DormStudent::query()
            ->whereIn('status', ['waiting', 'on_hold'])
            ->orderByRaw('COALESCE(eligibility_score, 0) DESC')
            ->orderByRaw('COALESCE(education_score, 0) DESC')
            ->orderByRaw('COALESCE(application_date, created_at) ASC')
            ->with('room')
            ->limit(6)
            ->get();

        $visibleStatusQuery = DormStudent::query()->whereNotIn('status', ['waiting', 'on_hold', 'rejected']);
        $rooms = DormRoom::query()
            ->orderBy('room_number')
            ->pluck('room_number')
            ->merge(DormStudent::query()->whereNotNull('room_number')->distinct()->pluck('room_number'))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('dorm.students.index', [
            'students' => $students,
            'waitingApplicants' => $waitingApplicants,
            'admissionRooms' => $this->roomsForAdmission(),
            'filters' => $filters,
            'statusLabels' => $this->statusLabels(),
            'rooms' => $rooms,
            'studentSummary' => [
                'active' => (clone $visibleStatusQuery)->where('status', 'active')->count(),
                'waiting' => DormStudent::query()->where('status', 'waiting')->count(),
                'on_hold' => DormStudent::query()->where('status', 'on_hold')->count(),
                'missing_documents' => (clone $visibleStatusQuery)
                    ->where(fn ($query) => $query->whereNull('document_names')->orWhere('document_names', '[]'))
                    ->count(),
                'recent' => (clone $visibleStatusQuery)->where('created_at', '>=', now()->subDays(30))->count(),
            ],
        ]);
    }

    public function show(DormStudent $student): View
    {
        $student->load([
            'room',
            'registeredBy',
            'admissionDecisionBy',
            'membershipCards' => fn ($query) => $query->where('scope', 'dorm')->latest('expires_at'),
            'collections.recordedBy',
            'foodFinances.recordedBy',
        ]);

        return view('dorm.students.show', [
            'student' => $student,
            'statusLabels' => $this->statusLabels(),
            'collectionLabels' => $this->collectionLabels(),
            'foodFinanceLabels' => $this->foodFinanceLabels(),
            'collectionTotals' => StudentCollection::query()
                ->where('dorm_student_id', $student->id)
                ->selectRaw('type, sum(amount) as total')
                ->groupBy('type')
                ->pluck('total', 'type'),
            'foodIncomeTotal' => (int) FoodFinance::query()
                ->where('dorm_student_id', $student->id)
                ->whereIn('type', ['contribution', 'weekly_food', 'monthly_fee', 'electricity', 'water'])
                ->sum('amount'),
            'canEditStudent' => request()->user()->canAccessAdmin(),
            'canRecordPurchaser' => request()->user()->role === User::ROLE_PURCHASER,
            'canRecordRepresentative' => request()->user()->role === User::ROLE_STUDENT_REPRESENTATIVE,
        ]);
    }

    public function document(DormStudent $student, int $index)
    {
        $document = $student->document_names[$index] ?? null;
        $path = $document['path'] ?? null;

        abort_if(
            ! $path
            || ! str_starts_with($path, 'dorm-student-documents/')
            || (! Storage::disk('local')->exists($path) && ! Storage::disk('public')->exists($path)),
            404
        );

        $disk = Storage::disk('local')->exists($path) ? 'local' : 'public';

        return Storage::disk($disk)->response($path, $document['name'] ?? null, [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function create(): View
    {
        return view('dorm.students.form', [
            'student' => new DormStudent([
                'status' => 'active',
                'joined_at' => now(),
                'application_date' => now(),
                'guarantee_deposit_amount' => 1000,
                'dorm_expense_fee_amount' => 1000,
                'registration_payment_status' => 'paid',
                'registration_paid_at' => now(),
            ]),
            'statusLabels' => $this->statusLabels(),
            'rooms' => $this->roomsForForm(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateStudent($request);
        $issueCard = $request->boolean('issue_card');
        $saveOnly = $request->boolean('save_only') && ! $issueCard;

        $this->normalizeAdmissionState($validated, null, $saveOnly);
        $this->ensureRoomCanAccept($validated);
        $this->syncRoomNumber($validated);
        $this->normalizeRegistrationPayment($validated);
        $validated['document_names'] = $this->storeDocuments($request);
        $validated['profile_photo_path'] = $this->storeProfilePhoto($request);
        $this->unsetUploadInputs($validated);

        $student = DormStudent::create(array_merge($validated, [
            'registered_by' => $request->user()->id,
        ]));
        Audit::record('dorm_student_created', $student, [], $student->only(['full_name', 'phone', 'status', 'dorm_room_id', 'room_number', 'bed_number']), $request);

        if ($issueCard && $student->status === 'active') {
            $card = $this->issueDormCard($student, $request);

            return redirect()->route('membership-cards.print', $card);
        }

        return redirect()
            ->route('dorm.students.index')
            ->with('status', 'شاگرد ذخیره شد. تا زمان صدور کارت، رسید و محاسبه مالی ساخته نمی‌شود.');
    }

    public function issueCard(Request $request, DormStudent $student): RedirectResponse
    {
        if ($student->status !== 'active') {
            throw ValidationException::withMessages(['status' => 'فقط شاگردان فعال می‌توانند کارت لیلیه دریافت کنند.']);
        }

        $card = $this->issueDormCard($student, $request);

        return redirect()->route('membership-cards.print', $card);
    }

    public function registrationReceipt(DormStudent $student): View
    {
        $student->load([
            'room',
            'registeredBy',
            'membershipCards' => fn ($query) => $query->where('scope', 'dorm')->latest('issued_at'),
        ]);

        $latestCard = $student->membershipCards->first();
        $guaranteeDeposit = (int) ($student->guarantee_deposit_amount ?? 1000);
        $dormExpenseFee = (int) ($student->dorm_expense_fee_amount ?? 1000);

        return view('dorm.receipts.registration', [
            'student' => $student,
            'latestCard' => $latestCard,
            'receiptNumber' => 'ADM-'.str_pad((string) $student->id, 6, '0', STR_PAD_LEFT),
            'lineItems' => [
                ['label' => 'پول ضمانت', 'amount' => $guaranteeDeposit],
                ['label' => 'مصارف ابتدایی لیلیه', 'amount' => $dormExpenseFee],
            ],
            'totalAmount' => $guaranteeDeposit + $dormExpenseFee,
            'paymentStatus' => $student->registration_payment_status ?? 'paid',
            'paidAt' => $student->registration_paid_at,
            'backRoute' => route('dorm.students.show', $student),
        ]);
    }

    public function edit(DormStudent $student): View
    {
        $dormCard = $student->membershipCards()
            ->where('scope', 'dorm')
            ->latest('expires_at')
            ->first();

        return view('dorm.students.form', [
            'student' => $student,
            'dormCard' => $dormCard,
            'statusLabels' => $this->statusLabels(),
            'rooms' => $this->roomsForForm($student),
        ]);
    }

    public function update(Request $request, DormStudent $student): RedirectResponse
    {
        $validated = $this->validateStudent($request, $student);
        $this->normalizeAdmissionState($validated, $student);
        $this->ensureRoomCanAccept($validated, $student);
        $this->syncRoomNumber($validated);
        $this->normalizeRegistrationPayment($validated, $student);
        $validated['document_names'] = $this->mergeDocuments($request, $student);
        $validated['profile_photo_path'] = $this->syncProfilePhoto($request, $student);
        $this->unsetUploadInputs($validated);

        $oldValues = $student->only(['full_name', 'phone', 'status', 'dorm_room_id', 'room_number', 'bed_number', 'profile_photo_path', 'document_names']);
        $student->update($validated);
        Audit::record('dorm_student_updated', $student, $oldValues, $student->fresh()->only(['full_name', 'phone', 'status', 'dorm_room_id', 'room_number', 'bed_number', 'profile_photo_path', 'document_names']), $request);

        return redirect()
            ->route('dorm.students.index')
            ->with('status', 'پروفایل شاگرد موفقانه به‌روزرسانی شد.');
    }

    public function admit(Request $request, DormStudent $student): RedirectResponse
    {
        if (! in_array($student->status, ['waiting', 'on_hold'], true)) {
            throw ValidationException::withMessages(['status' => 'فقط شاگردان لیست انتظار از این بخش قابل پذیرش هستند.']);
        }

        $validated = $request->validate([
            'dorm_room_id' => ['required', 'exists:dorm_rooms,id'],
            'bed_number' => ['required', 'integer', 'min:1'],
            'admission_note' => ['nullable', 'string', 'max:700'],
        ]);

        $validated['status'] = 'active';
        $this->ensureRoomCanAccept($validated, $student);
        $this->syncRoomNumber($validated);

        $notes = trim((string) ($student->eligibility_notes ?? ''));
        if (! empty($validated['admission_note'])) {
            $notes = trim($notes."\nیادداشت پذیرش: ".$validated['admission_note']);
        }

        $student->update([
            'status' => 'active',
            'dorm_room_id' => $validated['dorm_room_id'],
            'room_number' => $validated['room_number'] ?? null,
            'bed_number' => $validated['bed_number'] ?? null,
            'active_bed_key' => $validated['active_bed_key'] ?? null,
            'left_at' => null,
            'joined_at' => $student->joined_at ?: now()->toDateString(),
            'admitted_at' => now(),
            'admission_decision_by' => $request->user()->id,
            'eligibility_notes' => $notes ?: $student->eligibility_notes,
        ]);

        return redirect()
            ->route('dorm.students.show', $student)
            ->with('status', 'شاگرد از لیست انتظار به لیست اصلی لیلیه منتقل شد.');
    }

    private function validateStudent(Request $request, ?DormStudent $student = null): array
    {
        $saveOnly = $request->boolean('save_only') && ! $request->boolean('issue_card');

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:120'],
            'father_name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp' => [...SecurityRules::phone(), Rule::unique('dorm_students', 'phone')->ignore($student)],
            'family_phone' => SecurityRules::phone(false),
            'email' => ['nullable', 'email', 'max:120', Rule::unique('dorm_students', 'email')->ignore($student)],
            'tazkira_number' => ['required', 'string', 'max:80', Rule::unique('dorm_students', 'tazkira_number')->ignore($student)],
            'education_place' => ['required', 'string', 'max:160'],
            'department_or_grade' => ['nullable', 'string', 'max:160'],
            'school_graduation_year' => ['nullable', 'integer', 'min:1300', 'max:1500'],
            'province' => ['nullable', 'string', 'max:80'],
            'district' => ['nullable', 'string', 'max:100'],
            'dorm_room_id' => [Rule::requiredIf(fn () => $request->input('status') === 'active' && ! $saveOnly), 'nullable', 'exists:dorm_rooms,id'],
            'room_number' => ['nullable', 'string', 'max:40'],
            'bed_number' => [Rule::requiredIf(fn () => $request->input('status') === 'active' && ! $saveOnly), 'nullable', 'integer', 'min:1'],
            'guarantor_name' => ['nullable', 'string', 'max:120'],
            'guarantor_relation' => ['nullable', 'string', 'max:80'],
            'guarantor_phone' => SecurityRules::phone(false),
            'guarantor_tazkira_number' => ['nullable', 'string', 'max:80'],
            'guarantor_job' => ['nullable', 'string', 'max:120'],
            'guarantor_permanent_address' => ['nullable', 'string', 'max:255'],
            'guarantor_current_address' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(array_keys($this->statusLabels()))],
            'application_date' => ['nullable', 'date'],
            'education_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'eligibility_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'eligibility_notes' => ['nullable', 'string', 'max:1000'],
            'guarantee_deposit_amount' => ['nullable', 'integer', 'min:0'],
            'dorm_expense_fee_amount' => ['nullable', 'integer', 'min:0'],
            'registration_payment_status' => ['nullable', Rule::in(['paid', 'unpaid', 'partial'])],
            'registration_paid_at' => ['nullable', 'date'],
            'joined_at' => ['nullable', 'date'],
            'left_at' => ['nullable', 'date', 'after_or_equal:joined_at'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'profile_photo' => SecurityRules::profileImage(),
            'remove_profile_photo' => ['nullable', 'boolean'],
            'documents' => ['nullable', 'array'],
            'documents.*' => SecurityRules::safeDocument(),
            'student_tazkira_document' => ['nullable', ...SecurityRules::safeDocument()],
            'student_documents' => ['nullable', 'array'],
            'student_documents.*' => SecurityRules::safeDocument(),
            'guarantor_tazkira_document' => ['nullable', ...SecurityRules::safeDocument()],
            'guarantor_documents' => ['nullable', 'array'],
            'guarantor_documents.*' => SecurityRules::safeDocument(),
            'remove_documents' => ['nullable', 'array'],
            'remove_documents.*' => ['integer', 'min:0'],
            'save_only' => ['nullable', 'boolean'],
            'issue_card' => ['nullable', 'boolean'],
            'card_payment_status' => ['nullable', Rule::in(['paid', 'unpaid'])],
        ]);

        $validated['phone'] = $validated['whatsapp'];

        return $validated;
    }

    private function roomsForForm(?DormStudent $student = null)
    {
        return DormRoom::query()
            ->withCount(['activeStudents as occupied_beds'])
            ->where(function ($query) use ($student) {
                $query->where('status', 'active');

                if ($student?->dorm_room_id) {
                    $query->orWhere('id', $student->dorm_room_id);
                }
            })
            ->orderBy('room_number')
            ->get();
    }

    private function ensureRoomCanAccept(array $validated, ?DormStudent $student = null): void
    {
        if (($validated['status'] ?? null) !== 'active') {
            return;
        }

        if (empty($validated['dorm_room_id'])) {
            throw ValidationException::withMessages(['dorm_room_id' => 'انتخاب اتاق برای شاگرد فعال ضروری است.']);
        }

        if (empty($validated['bed_number'])) {
            throw ValidationException::withMessages(['bed_number' => 'نمبر تخت برای شاگرد فعال ضروری است.']);
        }

        $room = DormRoom::withCount(['activeStudents as occupied_beds'])->findOrFail($validated['dorm_room_id']);
        $isSameRoom = $student && (int) $student->dorm_room_id === (int) $room->id && $student->status === 'active';
        $occupiedBeds = $isSameRoom ? $room->occupied_beds - 1 : $room->occupied_beds;

        if ($room->status !== 'active') {
            throw ValidationException::withMessages(['dorm_room_id' => 'این اتاق فعلاً فعال نیست.']);
        }

        if ($occupiedBeds >= $room->capacity) {
            throw ValidationException::withMessages(['dorm_room_id' => 'ظرفیت این اتاق تکمیل است.']);
        }

        if ((int) $validated['bed_number'] < 1 || (int) $validated['bed_number'] > (int) $room->capacity) {
            throw ValidationException::withMessages([
                'bed_number' => 'نمبر تخت باید بین ۱ و ظرفیت اتاق باشد.',
            ]);
        }

        if (! empty($validated['bed_number'])) {
            $bedIsTaken = DormStudent::query()
                ->where('dorm_room_id', $room->id)
                ->where('bed_number', $validated['bed_number'])
                ->where('status', 'active')
                ->when($student, fn ($query) => $query->whereKeyNot($student->id))
                ->exists();

            if ($bedIsTaken) {
                throw ValidationException::withMessages(['bed_number' => 'این تخت در اتاق انتخاب‌شده قبلاً گرفته شده است.']);
            }
        }
    }

    private function syncRoomNumber(array &$validated): void
    {
        if (! empty($validated['dorm_room_id'])) {
            $validated['room_number'] = DormRoom::find($validated['dorm_room_id'])?->room_number;
            $validated['active_bed_key'] = (($validated['status'] ?? null) === 'active' && ! empty($validated['bed_number']))
                ? $validated['dorm_room_id'].':'.$validated['bed_number']
                : null;
        }
    }

    private function normalizeAdmissionState(array &$validated, ?DormStudent $student = null, bool $saveOnly = false): void
    {
        $validated['application_date'] = $validated['application_date'] ?? $student?->application_date?->toDateString() ?? now()->toDateString();

        if ($saveOnly && ($validated['status'] ?? null) === 'active' && (empty($validated['dorm_room_id']) || empty($validated['bed_number']))) {
            $validated['status'] = 'on_hold';
        }

        if (($validated['status'] ?? null) !== 'active') {
            $validated['dorm_room_id'] = null;
            $validated['room_number'] = null;
            $validated['bed_number'] = null;
            $validated['active_bed_key'] = null;
        }

        if (($validated['status'] ?? null) === 'active' && empty($validated['joined_at'])) {
            $validated['joined_at'] = now()->toDateString();
        }

        if (($validated['status'] ?? null) === 'active') {
            $validated['left_at'] = null;
        }
    }

    private function normalizeRegistrationPayment(array &$validated, ?DormStudent $student = null): void
    {
        $validated['guarantee_deposit_amount'] = (int) ($validated['guarantee_deposit_amount'] ?? $student?->guarantee_deposit_amount ?? 1000);
        $validated['dorm_expense_fee_amount'] = (int) ($validated['dorm_expense_fee_amount'] ?? $student?->dorm_expense_fee_amount ?? 1000);
        $validated['registration_payment_status'] = $validated['registration_payment_status'] ?? $student?->registration_payment_status ?? 'paid';

        if ($validated['registration_payment_status'] === 'paid') {
            $validated['registration_paid_at'] = $validated['registration_paid_at'] ?? $student?->registration_paid_at?->toDateString() ?? now()->toDateString();
        } elseif (($validated['registration_payment_status'] ?? null) === 'unpaid') {
            $validated['registration_paid_at'] = null;
        }
    }

    private function roomsForAdmission()
    {
        return DormRoom::query()
            ->withCount(['activeStudents as occupied_beds'])
            ->where('status', 'active')
            ->orderBy('room_number')
            ->get();
    }

    private function mergeDocuments(Request $request, DormStudent $student): array
    {
        $currentDocuments = collect($student->document_names ?? [])
            ->reject(function ($document, $index) use ($request) {
                $shouldRemove = in_array((string) $index, $request->input('remove_documents', []), true);

                if ($shouldRemove && isset($document['path'])) {
                    Storage::disk('local')->delete($document['path']);
                    Storage::disk('public')->delete($document['path']);
                }

                return $shouldRemove;
            })
            ->values()
            ->all();

        return array_merge($currentDocuments, $this->storeDocuments($request));
    }

    private function storeDocuments(Request $request): array
    {
        $documents = [];

        foreach ($request->file('documents', []) as $document) {
            $documents[] = $this->documentPayload($document, 'student_document', 'سند شاگرد');
        }

        foreach ($request->file('student_documents', []) as $document) {
            $documents[] = $this->documentPayload($document, 'student_document', 'سند شاگرد');
        }

        if ($request->hasFile('student_tazkira_document')) {
            $documents[] = $this->documentPayload($request->file('student_tazkira_document'), 'student_tazkira', 'تذکره شاگرد');
        }

        if ($request->hasFile('guarantor_tazkira_document')) {
            $documents[] = $this->documentPayload($request->file('guarantor_tazkira_document'), 'guarantor_tazkira', 'تذکره ضامن');
        }

        foreach ($request->file('guarantor_documents', []) as $document) {
            $documents[] = $this->documentPayload($document, 'guarantor_document', 'سند ضامن');
        }

        return $documents;
    }

    private function documentPayload($document, string $type, string $label): array
    {
        return [
            'name' => $document->getClientOriginalName(),
            'path' => $document->store('dorm-student-documents', 'local'),
            'type' => $type,
            'label' => $label,
            'uploaded_at' => now()->toDateTimeString(),
        ];
    }

    private function unsetUploadInputs(array &$validated): void
    {
        unset(
            $validated['documents'],
            $validated['student_tazkira_document'],
            $validated['student_documents'],
            $validated['guarantor_tazkira_document'],
            $validated['guarantor_documents'],
            $validated['remove_documents'],
            $validated['profile_photo'],
            $validated['remove_profile_photo'],
            $validated['save_only'],
            $validated['issue_card'],
            $validated['card_fee'],
            $validated['card_payment_status']
        );
    }

    private function storeProfilePhoto(Request $request): ?string
    {
        return $request->file('profile_photo')?->store('profile-photos/dorm-students', 'public');
    }

    private function syncProfilePhoto(Request $request, DormStudent $student): ?string
    {
        if ($request->boolean('remove_profile_photo')) {
            if ($student->profile_photo_path) {
                Storage::disk('public')->delete($student->profile_photo_path);
            }

            return null;
        }

        if ($request->hasFile('profile_photo')) {
            if ($student->profile_photo_path) {
                Storage::disk('public')->delete($student->profile_photo_path);
            }

            return $this->storeProfilePhoto($request);
        }

        return $student->profile_photo_path;
    }

    private function statusLabels(): array
    {
        return [
            'active' => 'فعال',
            'waiting' => 'لیست انتظار',
            'on_hold' => 'ناقص',
            'rejected' => 'رد شده',
            'suspended' => 'تعلیق شده',
            'graduated' => 'فارغ شده',
            'left' => 'خارج شده',
        ];
    }

    private function collectionLabels(): array
    {
        return [
            'monthly_fee' => 'فیس ماهانه',
            'electricity' => 'برق',
            'fine' => 'جریمه',
            'water' => 'آب',
            'expense' => 'مصرف نماینده',
        ];
    }

    private function foodFinanceLabels(): array
    {
        return [
            'contribution' => 'سهم غذا',
            'weekly_food' => 'غذای هفته‌وار',
            'monthly_fee' => 'فیس ماهانه',
            'electricity' => 'برق',
            'water' => 'آب',
            'expense' => 'مصرف و خریداری',
        ];
    }

    private function issueDormCard(DormStudent $student, Request $request): MembershipCard
    {
        $issuedAt = now();
        $paymentStatus = $request->input('card_payment_status', 'paid');

        return $student->membershipCards()->create([
            'scope' => 'dorm',
            'card_number' => $this->nextCardNumber('DORM'),
            'holder_name' => $student->full_name,
            'father_name' => $student->father_name,
            'issued_at' => $issuedAt,
            'expires_at' => $issuedAt->copy()->addMonths(6),
            'payment_status' => $paymentStatus,
            'paid_at' => $paymentStatus === 'paid' ? $issuedAt : null,
            'created_by' => $request->user()->id,
            'notes' => 'کارت لیلیه بدون فیس صادر شد.',
        ]);
    }

    private function nextCardNumber(string $prefix): string
    {
        return $prefix.'-'.now()->format('Ymd').'-'.str_pad((string) (MembershipCard::count() + 1), 5, '0', STR_PAD_LEFT);
    }
}
