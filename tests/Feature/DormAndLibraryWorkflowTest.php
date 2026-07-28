<?php

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\BookLoan;
use App\Models\DormRoom;
use App\Models\DormStudent;
use App\Models\FinanceTransaction;
use App\Models\LibraryMember;
use App\Models\MembershipCard;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function fanousAdmin(): User
{
    return User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'status' => User::STATUS_ACTIVE,
    ]);
}

function fanousLibrarian(): User
{
    return User::factory()->create([
        'role' => User::ROLE_LIBRARIAN,
        'status' => User::STATUS_ACTIVE,
    ]);
}

function dormStudentPayload(array $overrides = []): array
{
    return array_merge([
        'full_name' => 'علی احمد',
        'father_name' => 'محمد احمد',
        'whatsapp' => '0788000001',
        'family_phone' => '0788000002',
        'tazkira_number' => 'T-1001',
        'education_place' => 'پوهنتون کابل',
        'department_or_grade' => 'انجنیری',
        'school_graduation_year' => 1402,
        'province' => 'کابل',
        'district' => 'مرکز',
        'status' => 'active',
        'application_date' => '2026-07-01',
        'joined_at' => '2026-07-01',
        'guarantee_deposit_amount' => 1000,
        'dorm_expense_fee_amount' => 1000,
        'registration_payment_status' => 'paid',
        'registration_paid_at' => '2026-07-01',
        'card_payment_status' => 'paid',
    ], $overrides);
}

function libraryMemberPayload(array $overrides = []): array
{
    return array_merge([
        'full_name' => 'امید عزیزیار',
        'father_name' => 'محمد امین',
        'phone' => '0799000001',
        'email' => 'member@example.com',
        'tazkira_number' => 'LIB-1001',
        'education_place' => 'کابل',
        'department_or_grade' => 'ادبیات',
        'membership_fee' => 300,
        'payment_status' => 'paid',
        'joined_at' => '2026-07-01',
        'status' => 'active',
    ], $overrides);
}

function bookPayload(array $overrides = []): array
{
    return array_merge([
        'isbn' => '978-1-0000-0000-1',
        'title' => 'انسان در جستجوی معنا',
        'author' => 'ویکتور فرانکل',
        'publisher' => 'فانوس',
        'language' => 'فارسی',
        'edition' => 'اول',
        'published_year' => 1400,
        'pages' => 220,
        'category' => 'روانشناسی',
        'shelf_code' => 'A-1',
        'barcode' => 'BOOK-TEST-1',
        'total_copies' => 2,
        'status' => 'available',
    ], $overrides);
}

test('dorm student registration with card creates student and redirects to card print', function () {
    $admin = fanousAdmin();
    $room = DormRoom::create([
        'room_number' => '101',
        'building' => 'A',
        'floor' => '1',
        'capacity' => 4,
        'status' => 'active',
    ]);

    $response = $this
        ->actingAs($admin)
        ->from(route('dorm.students.create'))
        ->post(route('dorm.students.store'), dormStudentPayload([
            'dorm_room_id' => $room->id,
            'bed_number' => 1,
            'issue_card' => '1',
        ]));

    $student = DormStudent::first();
    $card = MembershipCard::where('scope', 'dorm')->first();

    $response->assertRedirect(route('membership-cards.print', $card));
    expect($student)
        ->not->toBeNull()
        ->dorm_room_id->toBe($room->id)
        ->bed_number->toBe('1');
    expect($card)
        ->not->toBeNull()
        ->cardable_id->toBe($student->id)
        ->payment_status->toBe('paid');
});

test('dorm student information can be saved without issuing a card', function () {
    $admin = fanousAdmin();
    $room = DormRoom::create([
        'room_number' => '101-B',
        'building' => 'A',
        'floor' => '1',
        'capacity' => 4,
        'status' => 'active',
    ]);

    $this
        ->actingAs($admin)
        ->from(route('dorm.students.create'))
        ->post(route('dorm.students.store'), dormStudentPayload([
            'dorm_room_id' => $room->id,
            'bed_number' => 2,
            'whatsapp' => '0788111111',
            'tazkira_number' => 'T-1111',
        ]))
        ->assertRedirect(route('dorm.students.index'))
        ->assertSessionHasNoErrors();

    expect(DormStudent::count())->toBe(1);
    expect(MembershipCard::where('scope', 'dorm')->count())->toBe(0);
});

test('dorm save information button can store incomplete admission without card finance', function () {
    $admin = fanousAdmin();

    $this
        ->actingAs($admin)
        ->from(route('dorm.students.create'))
        ->post(route('dorm.students.store'), dormStudentPayload([
            'save_only' => '1',
            'whatsapp' => '0788222222',
            'tazkira_number' => 'T-2222',
        ]))
        ->assertRedirect(route('dorm.students.index'))
        ->assertSessionHasNoErrors();

    $student = DormStudent::first();

    expect($student)
        ->not->toBeNull()
        ->status->toBe('on_hold')
        ->dorm_room_id->toBeNull()
        ->bed_number->toBeNull();
    expect(MembershipCard::where('scope', 'dorm')->count())->toBe(0);
});

test('dorm student document upload accepts pdf files', function () {
    Storage::fake('local');
    Storage::fake('public');

    $admin = fanousAdmin();
    $room = DormRoom::create([
        'room_number' => '101-C',
        'building' => 'A',
        'floor' => '1',
        'capacity' => 4,
        'status' => 'active',
    ]);

    $this
        ->actingAs($admin)
        ->from(route('dorm.students.create'))
        ->post(route('dorm.students.store'), dormStudentPayload([
            'dorm_room_id' => $room->id,
            'bed_number' => 3,
            'whatsapp' => '0788333333',
            'tazkira_number' => 'T-3333',
            'student_tazkira_document' => UploadedFile::fake()->create('tazkira.pdf', 64, 'application/pdf'),
        ]))
        ->assertRedirect(route('dorm.students.index'))
        ->assertSessionHasNoErrors();

    $student = DormStudent::first();
    $document = $student->document_names[0] ?? null;

    expect($document)->not->toBeNull();
    expect($document['name'])->toBe('tazkira.pdf');
    expect($document['type'])->toBe('student_tazkira');

    Storage::disk('local')->assertExists($document['path']);
});

test('dorm student search filters the visible directory', function () {
    $admin = fanousAdmin();
    $room = DormRoom::create([
        'room_number' => '103',
        'building' => 'A',
        'floor' => '1',
        'capacity' => 4,
        'status' => 'active',
    ]);

    DormStudent::create(dormStudentPayload([
        'registered_by' => $admin->id,
        'full_name' => 'Ahmad Searchable',
        'phone' => '0788444444',
        'whatsapp' => '0788444444',
        'tazkira_number' => 'T-4444',
        'dorm_room_id' => $room->id,
        'room_number' => $room->room_number,
        'bed_number' => 1,
        'active_bed_key' => $room->id.':1',
    ]));

    DormStudent::create(dormStudentPayload([
        'registered_by' => $admin->id,
        'full_name' => 'Farid Hidden',
        'phone' => '0788555555',
        'whatsapp' => '0788555555',
        'tazkira_number' => 'T-5555',
        'dorm_room_id' => $room->id,
        'room_number' => $room->room_number,
        'bed_number' => 2,
        'active_bed_key' => $room->id.':2',
    ]));

    $this
        ->actingAs($admin)
        ->get(route('dorm.students.index', ['q' => '0788444444']))
        ->assertOk()
        ->assertSee('Ahmad Searchable')
        ->assertDontSee('Farid Hidden');
});

test('dorm blocks duplicate active bed and full rooms', function () {
    $admin = fanousAdmin();
    $room = DormRoom::create([
        'room_number' => '102',
        'building' => 'A',
        'floor' => '1',
        'capacity' => 4,
        'status' => 'active',
    ]);

    DormStudent::create(dormStudentPayload([
        'registered_by' => $admin->id,
        'dorm_room_id' => $room->id,
        'room_number' => $room->room_number,
        'bed_number' => 1,
        'active_bed_key' => $room->id.':1',
        'phone' => '0788000010',
        'whatsapp' => '0788000010',
        'tazkira_number' => 'T-1010',
    ]));

    $this
        ->actingAs($admin)
        ->post(route('dorm.students.store'), dormStudentPayload([
            'full_name' => 'شاگرد تخت تکراری',
            'whatsapp' => '0788000011',
            'tazkira_number' => 'T-1011',
            'dorm_room_id' => $room->id,
            'bed_number' => 1,
            'issue_card' => '1',
        ]))
        ->assertSessionHasErrors('bed_number');

    foreach ([2, 3, 4] as $bed) {
        DormStudent::create(dormStudentPayload([
            'registered_by' => $admin->id,
            'full_name' => 'شاگرد '.$bed,
            'phone' => '07880000'.$bed,
            'whatsapp' => '07880000'.$bed,
            'tazkira_number' => 'T-200'.$bed,
            'dorm_room_id' => $room->id,
            'room_number' => $room->room_number,
            'bed_number' => $bed,
            'active_bed_key' => $room->id.':'.$bed,
        ]));
    }

    $this
        ->actingAs($admin)
        ->post(route('dorm.students.store'), dormStudentPayload([
            'full_name' => 'شاگرد اتاق پر',
            'whatsapp' => '0788000099',
            'tazkira_number' => 'T-2099',
            'dorm_room_id' => $room->id,
            'bed_number' => 4,
            'issue_card' => '1',
        ]))
        ->assertSessionHasErrors('dorm_room_id');
});

test('library member registration with card creates member card and monthly income', function () {
    $librarian = fanousLibrarian();

    $response = $this
        ->actingAs($librarian)
        ->from(route('library.index'))
        ->post(route('library.members.store'), libraryMemberPayload([
            'issue_card' => '1',
        ]));

    $member = LibraryMember::first();
    $card = MembershipCard::where('scope', 'library')->first();

    $response->assertRedirect(route('membership-cards.print', $card));
    expect($member)
        ->not->toBeNull()
        ->member_code->toStartWith('LIB-M');
    expect($card)
        ->not->toBeNull()
        ->cardable_id->toBe($member->id)
        ->payment_status->toBe('paid');
});

test('library member information can be saved without issuing a card', function () {
    $librarian = fanousLibrarian();

    $this
        ->actingAs($librarian)
        ->from(route('library.index'))
        ->post(route('library.members.store'), libraryMemberPayload([
            'phone' => '0799111111',
            'email' => 'plain-member@example.com',
            'tazkira_number' => 'LIB-1111',
        ]))
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect(LibraryMember::count())->toBe(1);
    expect(MembershipCard::where('scope', 'library')->count())->toBe(0);
});

test('library monthly bills are tracked by billing month and are not duplicated', function () {
    $librarian = fanousLibrarian();
    $member = LibraryMember::create(array_merge(libraryMemberPayload([
        'payment_status' => 'unpaid',
        'joined_at' => '2026-07-05',
        'last_paid_at' => null,
        'next_payment_due_at' => '2026-08-05',
        'phone' => '0799000044',
        'email' => 'monthly-member@example.com',
        'tazkira_number' => 'LIB-MONTHLY-1',
    ]), [
        'registered_by' => $librarian->id,
        'member_code' => 'LIB-M-TEST-MONTH',
    ]));

    $payload = ['billing_month' => '2026-08'];

    $this
        ->actingAs($librarian)
        ->post(route('library.members.monthly-payment.store', $member), $payload)
        ->assertRedirect(route('library.members.monthly-payment.receipt', [
            'member' => $member,
            'billing_month' => '2026-08',
        ]));

    $this
        ->actingAs($librarian)
        ->post(route('library.members.monthly-payment.store', $member), $payload)
        ->assertRedirect(route('library.members.monthly-payment.receipt', [
            'member' => $member,
            'billing_month' => '2026-08',
        ]));

    expect(FinanceTransaction::where('receipt_number', 'LIB-MONTHLY-'.$member->id.'-2026-08')->count())->toBe(1);
    expect($member->fresh()->next_payment_due_at?->toDateString())->toBe('2026-09-05');
});

test('library creates copies and blocks duplicate ISBN or duplicate book identity', function () {
    $librarian = fanousLibrarian();

    $this
        ->actingAs($librarian)
        ->post(route('library.books.store'), bookPayload())
        ->assertSessionHasNoErrors();

    $book = Book::first();
    expect($book)
        ->not->toBeNull()
        ->available_copies->toBe(2);
    expect($book->identity_key)->toHaveLength(40);
    expect(BookCopy::where('book_id', $book->id)->count())->toBe(2);

    $this
        ->actingAs($librarian)
        ->post(route('library.books.store'), bookPayload([
            'title' => 'کتاب دیگر',
            'barcode' => 'BOOK-TEST-2',
        ]))
        ->assertSessionHasErrors('isbn');

    $this
        ->actingAs($librarian)
        ->post(route('library.books.store'), bookPayload([
            'isbn' => '978-1-0000-0000-2',
            'barcode' => 'BOOK-TEST-3',
        ]))
        ->assertSessionHasErrors('title');

    expect(fn () => Book::create(array_merge(bookPayload([
        'isbn' => '978-1-0000-0000-9',
        'barcode' => 'BOOK-TEST-9',
    ]), [
        'registered_by' => $librarian->id,
        'identity_key' => $book->identity_key,
    ])))->toThrow(QueryException::class);
});

test('library loan and return protect copy availability', function () {
    $librarian = fanousLibrarian();
    $member = LibraryMember::create(array_merge(libraryMemberPayload(), [
        'registered_by' => $librarian->id,
        'member_code' => 'LIB-M-TEST',
        'phone' => '0799000090',
        'email' => 'loan-member@example.com',
        'tazkira_number' => 'LIB-9000',
    ]));
    $book = Book::create(array_merge(bookPayload(), [
        'registered_by' => $librarian->id,
        'available_copies' => 1,
        'total_copies' => 1,
    ]));
    $copy = BookCopy::create([
        'book_id' => $book->id,
        'copy_code' => 'COPY-1',
        'barcode' => 'COPY-1',
        'shelf_code' => 'A-1',
        'status' => 'available',
    ]);

    $loanPayload = [
        'library_member_id' => $member->id,
        'book_id' => $book->id,
        'copy_code' => $copy->copy_code,
        'borrowed_at' => '2026-07-01',
        'due_at' => '2026-07-08',
    ];

    $response = $this
        ->actingAs($librarian)
        ->post(route('library.loans.store'), $loanPayload);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $loan = BookLoan::first();
    expect($loan)
        ->not->toBeNull()
        ->book_copy_id->toBe($copy->id)
        ->status->toBe('borrowed');
    expect($copy->fresh()->status)->toBe('on_loan');
    expect($book->fresh()->available_copies)->toBe(0);

    $this
        ->actingAs($librarian)
        ->post(route('library.loans.store'), $loanPayload)
        ->assertSessionHasErrors('copy_code');

    $this
        ->actingAs($librarian)
        ->put(route('library.loans.return', $loan), [
            'returned_at' => '2026-07-04',
            'fine_amount' => 0,
            'return_status' => 'available',
        ])
        ->assertSessionHasNoErrors();

    expect($loan->fresh()->status)->toBe('returned');
    expect($copy->fresh()->status)->toBe('available');
    expect($book->fresh()->available_copies)->toBe(1);

    $this
        ->actingAs($librarian)
        ->put(route('library.loans.return', $loan), [
            'returned_at' => '2026-07-05',
            'fine_amount' => 0,
            'return_status' => 'available',
        ])
        ->assertSessionHasErrors('copy_code');
});
