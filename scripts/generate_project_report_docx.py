from __future__ import annotations

import html
import zipfile
from datetime import date
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "docs" / "Fanous_Project_Report.docx"


def esc(text: str) -> str:
    return html.escape(text, quote=False)


def p(text: str = "", style: str | None = None, bold: bool = False, italic: bool = False) -> str:
    style_xml = f'<w:pStyle w:val="{style}"/>' if style else ""
    b_xml = "<w:b/>" if bold else ""
    i_xml = "<w:i/>" if italic else ""
    preserve = ' xml:space="preserve"' if text.startswith(" ") or text.endswith(" ") else ""
    return (
        "<w:p><w:pPr>"
        f"{style_xml}"
        '<w:bidi/><w:jc w:val="both"/>'
        "</w:pPr><w:r><w:rPr>"
        f"{b_xml}{i_xml}"
        "</w:rPr>"
        f"<w:t{preserve}>{esc(text)}</w:t>"
        "</w:r></w:p>"
    )


def bullet(text: str) -> str:
    return (
        '<w:p><w:pPr><w:pStyle w:val="ListParagraph"/><w:numPr>'
        '<w:ilvl w:val="0"/><w:numId w:val="1"/></w:numPr><w:bidi/></w:pPr>'
        f"<w:r><w:t>{esc(text)}</w:t></w:r></w:p>"
    )


def heading(text: str, level: int = 1) -> str:
    return p(text, f"Heading{level}", bold=True)


def page_break() -> str:
    return '<w:p><w:r><w:br w:type="page"/></w:r></w:p>'


def table(headers: list[str], rows: list[list[str]]) -> str:
    def cell(text: str, header: bool = False) -> str:
        fill = '<w:shd w:fill="D9EAF7"/>' if header else ""
        bold = "<w:b/>" if header else ""
        return (
            "<w:tc><w:tcPr>"
            '<w:tcW w:w="2400" w:type="dxa"/>'
            f"{fill}"
            "</w:tcPr>"
            '<w:p><w:pPr><w:bidi/><w:jc w:val="center"/></w:pPr>'
            f"<w:r><w:rPr>{bold}</w:rPr><w:t>{esc(text)}</w:t></w:r>"
            "</w:p></w:tc>"
        )

    grid = "".join('<w:gridCol w:w="2400"/>' for _ in headers)
    out = [
        '<w:tbl><w:tblPr><w:tblStyle w:val="TableGrid"/>'
        '<w:tblW w:w="0" w:type="auto"/>'
        '<w:bidiVisual/>'
        "</w:tblPr>"
        f"<w:tblGrid>{grid}</w:tblGrid>"
    ]
    out.append("<w:tr>" + "".join(cell(h, True) for h in headers) + "</w:tr>")
    for row in rows:
        out.append("<w:tr>" + "".join(cell(c) for c in row) + "</w:tr>")
    out.append("</w:tbl>")
    return "".join(out)


def document_xml(body: str) -> str:
    return f'''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:wpc="http://schemas.microsoft.com/office/word/2010/wordprocessingCanvas"
 xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"
 xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math"
 xmlns:v="urn:schemas-microsoft-com:vml"
 xmlns:wp14="http://schemas.microsoft.com/office/word/2010/wordprocessingDrawing"
 xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing"
 xmlns:w10="urn:schemas-microsoft-com:office:word"
 xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"
 xmlns:w14="http://schemas.microsoft.com/office/word/2010/wordml"
 xmlns:wpg="http://schemas.microsoft.com/office/word/2010/wordprocessingGroup"
 xmlns:wpi="http://schemas.microsoft.com/office/word/2010/wordprocessingInk"
 xmlns:wne="http://schemas.microsoft.com/office/word/2006/wordml"
 xmlns:wps="http://schemas.microsoft.com/office/word/2010/wordprocessingShape"
 mc:Ignorable="w14 wp14">
 <w:body>{body}
  <w:sectPr>
   <w:pgSz w:w="11906" w:h="16838"/>
   <w:pgMar w:top="1134" w:right="1134" w:bottom="1134" w:left="1134" w:header="708" w:footer="708" w:gutter="0"/>
   <w:bidi/>
  </w:sectPr>
 </w:body>
</w:document>'''


STYLES = '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
 <w:style w:type="paragraph" w:default="1" w:styleId="Normal">
  <w:name w:val="Normal"/>
  <w:pPr><w:bidi/><w:jc w:val="both"/></w:pPr>
  <w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr>
 </w:style>
 <w:style w:type="paragraph" w:styleId="Title">
  <w:name w:val="Title"/><w:basedOn w:val="Normal"/>
  <w:pPr><w:bidi/><w:jc w:val="center"/></w:pPr>
  <w:rPr><w:b/><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial"/><w:sz w:val="44"/><w:szCs w:val="44"/></w:rPr>
 </w:style>
 <w:style w:type="paragraph" w:styleId="Heading1">
  <w:name w:val="heading 1"/><w:basedOn w:val="Normal"/><w:next w:val="Normal"/>
  <w:pPr><w:bidi/><w:spacing w:before="360" w:after="160"/></w:pPr>
  <w:rPr><w:b/><w:color w:val="0B5E78"/><w:sz w:val="32"/><w:szCs w:val="32"/></w:rPr>
 </w:style>
 <w:style w:type="paragraph" w:styleId="Heading2">
  <w:name w:val="heading 2"/><w:basedOn w:val="Normal"/><w:next w:val="Normal"/>
  <w:pPr><w:bidi/><w:spacing w:before="240" w:after="120"/></w:pPr>
  <w:rPr><w:b/><w:color w:val="1B335F"/><w:sz w:val="27"/><w:szCs w:val="27"/></w:rPr>
 </w:style>
 <w:style w:type="paragraph" w:styleId="ListParagraph">
  <w:name w:val="List Paragraph"/><w:basedOn w:val="Normal"/>
  <w:pPr><w:ind w:left="720" w:hanging="360"/><w:bidi/></w:pPr>
 </w:style>
 <w:style w:type="table" w:styleId="TableGrid">
  <w:name w:val="Table Grid"/>
  <w:tblPr><w:tblBorders>
   <w:top w:val="single" w:sz="4" w:space="0" w:color="9DB4C0"/>
   <w:left w:val="single" w:sz="4" w:space="0" w:color="9DB4C0"/>
   <w:bottom w:val="single" w:sz="4" w:space="0" w:color="9DB4C0"/>
   <w:right w:val="single" w:sz="4" w:space="0" w:color="9DB4C0"/>
   <w:insideH w:val="single" w:sz="4" w:space="0" w:color="9DB4C0"/>
   <w:insideV w:val="single" w:sz="4" w:space="0" w:color="9DB4C0"/>
  </w:tblBorders></w:tblPr>
 </w:style>
</w:styles>'''


NUMBERING = '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:numbering xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
 <w:abstractNum w:abstractNumId="0">
  <w:multiLevelType w:val="hybridMultilevel"/>
  <w:lvl w:ilvl="0">
   <w:start w:val="1"/><w:numFmt w:val="bullet"/><w:lvlText w:val="•"/>
   <w:lvlJc w:val="right"/><w:pPr><w:ind w:left="720" w:hanging="360"/></w:pPr>
  </w:lvl>
 </w:abstractNum>
 <w:num w:numId="1"><w:abstractNumId w:val="0"/></w:num>
</w:numbering>'''


CONTENT_TYPES = '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
 <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
 <Default Extension="xml" ContentType="application/xml"/>
 <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
 <Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>
 <Override PartName="/word/numbering.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.numbering+xml"/>
 <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
 <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
</Types>'''


RELS = '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
 <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
 <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
 <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>'''


DOC_RELS = '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
 <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
 <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/numbering" Target="numbering.xml"/>
</Relationships>'''


def build_body() -> str:
    today = date.today().strftime("%B %d, %Y")
    parts: list[str] = []
    parts.append(p("Fanous Dormitory and Library Management System", "Title", bold=True))
    parts.append(p("Complete Project Report", "Title", bold=True))
    parts.append(p(""))
    parts.append(p("Prepared for: Teacher / Instructor"))
    parts.append(p("Prepared by: Student Name: ____________________"))
    parts.append(p(f"Report date: {today}"))
    parts.append(p("Technology stack: Laravel 12, PHP 8.2+, MySQL, Blade, HTML, CSS, JavaScript"))
    parts.append(page_break())

    parts.append(heading("Abstract"))
    parts.append(p(
        "Fanous is a web-based management system developed for a dormitory and library environment. "
        "The project centralizes student registration, room allocation, dormitory finance, food collection, "
        "purchase records, library members, book inventory, book loans, membership cards, receipts, reports, "
        "and user administration. The system is designed for practical daily use by administrators, librarians, "
        "student representatives, guards, and purchasers. It improves data accuracy, reduces duplicate records, "
        "adds financial transparency, and applies security controls such as role-based access, rate limiting, "
        "strong password validation, request filtering, and secure production settings."
    ))

    parts.append(heading("Introduction"))
    parts.append(p(
        "Dormitories and libraries usually manage many repeated tasks: registering people, issuing cards, "
        "tracking payments, assigning rooms, collecting food money, buying supplies, managing books, and preparing reports. "
        "Manual management through notebooks or separate spreadsheets can cause duplicate records, wrong calculations, "
        "slow searching, and weak accountability. This project solves those problems by creating one integrated Laravel "
        "application for the Fanous environment."
    ))

    parts.append(heading("Project Objectives"))
    for item in [
        "Create a centralized system for dormitory and library operations.",
        "Register dorm students and library members with clear validation and unique identity checks.",
        "Manage rooms, room capacity, allocations, student status, and dormitory cards.",
        "Record dormitory financial income, expenses, student collections, food finance, and purchasing records.",
        "Manage library members, books, book copies, loans, returns, monthly fees, card fees, and receipts.",
        "Provide dashboards, searchable lists, reports, CSV/Excel-style exports, and print-ready receipts/cards.",
        "Protect the application with authentication, role permissions, strong passwords, secure uploads, and security headers.",
        "Prepare the application for real hosting with production configuration, cache optimization, and HTTPS settings.",
    ]:
        parts.append(bullet(item))

    parts.append(heading("Scope of the System"))
    parts.append(p(
        "The current scope covers the daily workflow of a dormitory with an attached library. "
        "It includes staff login, role-based menus, dorm student management, room management, library management, "
        "finance tracking, receipt printing, reporting, and settings. The project currently focuses on internal staff "
        "operations and does not yet include a public self-service portal for students or library members."
    ))

    parts.append(heading("Technology Stack"))
    parts.append(table(
        ["Layer", "Technology", "Purpose"],
        [
            ["Backend", "Laravel 12 / PHP 8.2+", "Routing, controllers, validation, models, middleware, authentication, migrations"],
            ["Database", "MySQL / MariaDB", "Stores users, students, rooms, finance, library, cards, books, and audit records"],
            ["Frontend", "Blade, HTML, CSS, JavaScript", "Server-rendered user interface, RTL screens, forms, dashboards, responsive layouts"],
            ["Styling", "Custom CSS and component views", "Admin layout, cards, tables, dark/light themes, mobile responsiveness"],
            ["Testing/Quality", "PHP Artisan test, Laravel Pint", "Automated tests and code style checks"],
            ["Deployment", "Production web host / XAMPP local development", "Local development and live server hosting"],
        ],
    ))

    parts.append(heading("User Roles and Access Control"))
    parts.append(table(
        ["Role", "Main Permissions"],
        [
            ["Admin", "Access admin dashboard, manage users, dorm records, rooms, finance, and reports"],
            ["Librarian", "Manage library members, books, book copies, loans, returns, library fees, and receipts"],
            ["Student Representative", "Record and view student collection/food finance workflows"],
            ["Purchaser", "Record purchase and expense-related records and prepare purchase reports"],
            ["Guard", "View dorm student records and student details for identification/security"],
            ["Owner/Finance Admin", "Reserved finance authority in the model layer for finance-level access"],
        ],
    ))
    parts.append(p(
        "Access is controlled through Laravel authentication and custom role middleware. Important routes use throttling, "
        "so repeated login attempts and repeated form submissions are limited."
    ))

    parts.append(heading("Main Modules"))
    modules = [
        ("Authentication and Staff Setup", "Login, logout, staff setup, active/suspended statuses, strong password rules, and rate-limited authentication routes."),
        ("Dashboard", "Central overview for staff with navigation to dormitory, finance, library, rooms, and settings depending on the user role."),
        ("Dorm Student Management", "Student registration, profile photo/document uploads, required field validation, view/edit screens, admission/card workflow, and registration receipts."),
        ("Dorm Room Management", "Room creation, room details, allocation of students to rooms, movement between rooms, and capacity/occupancy tracking."),
        ("Dorm Finance", "Monthly income, expenses, balances, student collection records, food finance records, purchase records, receipts, reports, and exports."),
        ("Admin Finance Summary", "Administrative finance page showing dormitory financial totals and a separate card for library income, while library financial operations remain inside the library finance area."),
        ("Student Representative Module", "Collection registration, collection receipts, and reports for money collected from students."),
        ("Purchaser Module", "Purchase/expense registration, receipt generation, and purchase reports."),
        ("Library Members", "Member registration, duplicate prevention, card fee handling, monthly fee handling, member status, search, filters, and exports."),
        ("Library Books and Inventory", "Book registration, multiple physical copies, copy labels, inventory report, and inventory export."),
        ("Library Loans", "Book lending, return workflow, return by copy code, loan editing, and loan status tracking."),
        ("Library Finance", "Library monthly fees, card fee income, manual library income/expense records, receipts, finance filters, totals, and export."),
        ("Membership Cards and Receipts", "Printable membership cards, six-month library card validity, dorm cards, monthly payment receipts, and registration receipts."),
        ("Settings", "Profile update, password update, theme preference, and profile photo management."),
        ("Transparency Page", "Public or shared transparency view for financial communication and accountability."),
    ]
    parts.append(table(["Module", "Description"], [[m, d] for m, d in modules]))

    parts.append(heading("Library Finance Improvements"))
    for item in [
        "Library registration fee and monthly fee were simplified so duplicate fee categories do not create wrong totals.",
        "Library card fee is stored separately from the monthly fee and defaults to 50 AFN.",
        "Library membership cards are valid for six months; monthly payments generate receipts without forcing a new card print every month.",
        "The member list no longer shows the print-card action beside view/edit because card printing is only needed in the proper card workflow.",
        "Monthly payment duplication is prevented so the same member cannot accidentally be charged twice for the same period.",
        "Library income is visible to the admin in finance summaries, but detailed library finance management remains in the library module.",
    ]:
        parts.append(bullet(item))

    parts.append(heading("Database Design Overview"))
    parts.append(p(
        "The database is managed through Laravel migrations. The design separates users, dormitory records, room records, "
        "finance records, library records, and audit logs. This makes the system easier to maintain and improves data integrity."
    ))
    parts.append(table(
        ["Table / Model Area", "Purpose"],
        [
            ["users", "Staff accounts, roles, status, theme, phone, profile photo, and hashed passwords"],
            ["dorm_students", "Dorm student profile, contact, identity, fees, guarantor details, documents, card and admission data"],
            ["dorm_rooms", "Room names/numbers, capacity, and room status"],
            ["student_collections", "Food and student collection records handled by representatives"],
            ["food_finances", "Food-related purchase/income/expense records"],
            ["dorm_expenses", "Dormitory expense and purchase records"],
            ["library_members", "Library member identity, contact, fee status, card fee, monthly fee, and member code"],
            ["membership_cards", "Printable membership card information and validity dates"],
            ["books", "Book catalog data such as title, author, category, and shelf information"],
            ["book_copies", "Physical book copy records for copy-level inventory and lending"],
            ["book_loans", "Borrowing and return records for library books"],
            ["finance_transactions", "Admin finance income and expense transactions"],
            ["finance_categories", "Finance categories for classification and reporting"],
            ["finance_donors / finance_projects", "Optional donor and project tracking for finance records"],
            ["finance_audit_logs / audit_logs", "Tracking important changes for accountability"],
        ],
    ))

    parts.append(heading("Important Business Rules"))
    for item in [
        "A dorm student should not generate financial receipt/calculation until required fields are complete and a card is issued.",
        "A library member should not generate financial calculation until required fields are complete and membership card rules are satisfied.",
        "Library monthly fee and card fee are separate values.",
        "Library card validity is six months, while monthly fee payment is handled every month.",
        "Library members and dorm students use unique identifiers such as phone, email, tazkira/member code where available to reduce duplicate records.",
        "Book loans are connected to physical book copies, not only book titles, so inventory remains more accurate.",
    ]:
        parts.append(bullet(item))

    parts.append(heading("Security Features"))
    parts.append(table(
        ["Security Control", "Implementation / Benefit"],
        [
            ["Authentication", "All internal modules require login except public login/contact/transparency pages"],
            ["Role-based authorization", "Custom role middleware separates admin, librarian, purchaser, representative, and guard access"],
            ["Strong passwords", "Passwords require minimum length, letters, uppercase/lowercase, numbers, and symbols"],
            ["Password hashing", "Laravel model casts store passwords as hashed values"],
            ["Rate limiting", "Login, setup, finance, user, library, room, and student actions are throttled"],
            ["Suspicious request blocking", "Middleware blocks suspicious payloads and reduces common attack attempts"],
            ["Security headers", "Middleware adds browser-level protection headers"],
            ["Upload validation", "Profile images and documents are restricted by type and size"],
            ["Private storage response", "Storage routes prevent path traversal and check file existence"],
            ["Production safety", "Recommended APP_DEBUG=false, HTTPS, secure cookies, encrypted sessions, and private .env"],
        ],
    ))

    parts.append(heading("User Interface and Responsiveness"))
    parts.append(p(
        "The application uses RTL-friendly Blade views for Persian/Dari usage. Recent work improved the responsive behavior "
        "of tables, cards, forms, navigation, and action buttons so the system is more usable on mobile phones. The UI uses "
        "clear dashboard cards, filters, search inputs, status badges, and print/export buttons for practical office use."
    ))

    parts.append(heading("Core Workflows"))
    parts.append(heading("Dorm Student Registration Workflow", 2))
    for item in [
        "Admin opens the dorm student registration form.",
        "Required personal, contact, fee, room, and document fields are entered.",
        "Validation checks required fields and identity/contact uniqueness.",
        "Student card is issued after the profile is complete.",
        "Financial receipt/calculation becomes available only after the required card and registration conditions are met.",
    ]:
        parts.append(bullet(item))
    parts.append(heading("Library Member Workflow", 2))
    for item in [
        "Librarian registers the member with identity and contact details.",
        "The system prevents duplicate member records where unique identity information already exists.",
        "Monthly fee and card fee are entered separately.",
        "A card is issued with six-month validity and default card fee of 50 AFN.",
        "Monthly payments generate monthly receipts without reprinting the card every month.",
    ]:
        parts.append(bullet(item))
    parts.append(heading("Book Loan Workflow", 2))
    for item in [
        "Librarian selects the member and the available book copy.",
        "The system stores loan date, due date, and status.",
        "On return, the loan is marked returned and the copy becomes available again.",
        "Return can also be handled by physical copy code for faster daily work.",
    ]:
        parts.append(bullet(item))
    parts.append(heading("Finance Workflow", 2))
    for item in [
        "Authorized staff record income, expense, student collection, food finance, purchase, or library finance entries.",
        "Transactions are categorized and can be filtered by date/type/search terms.",
        "The system prepares summaries, balances, reports, receipts, and export files.",
        "Library income is included in the admin finance overview so management can see total organization income.",
    ]:
        parts.append(bullet(item))

    parts.append(heading("Reports and Exports"))
    parts.append(p(
        "The project includes several reporting screens: admin finance report, purchaser report, student representative report, "
        "library inventory report, library finance report, fee reminders, member exports, finance exports, and printable receipts. "
        "These reports support fast searching and filtering and help staff present accurate information to management."
    ))

    parts.append(heading("Testing and Quality Assurance"))
    parts.append(p(
        "The project uses Laravel's testing tools and Laravel Pint for formatting. During development, important changes were checked "
        "with PHP syntax validation, Laravel tests, and style formatting. Manual testing was also performed through the browser for forms, "
        "navigation, financial totals, responsive UI, and receipt/card behavior."
    ))
    parts.append(table(
        ["Check", "Purpose"],
        [
            ["php artisan test", "Runs automated Laravel/Pest tests"],
            ["vendor\\bin\\pint --dirty", "Formats changed PHP files according to Laravel style"],
            ["php -l file.php", "Checks PHP syntax for edited files"],
            ["Manual browser testing", "Verifies forms, navigation, calculations, print pages, mobile layout, and reports"],
        ],
    ))

    parts.append(heading("Deployment and Hosting"))
    parts.append(p(
        "The application is prepared for real hosting. The web server should point to the Laravel public directory, and the production "
        "environment should disable debugging, use HTTPS, secure sessions, correct file permissions, and cached configuration/routes/views."
    ))
    for item in [
        "Server document root must point to public/.",
        "PHP version should be 8.2 or newer.",
        ".env must stay private and must not be committed to GitHub.",
        "storage/ and bootstrap/cache/ must be writable by the web server.",
        "APP_ENV=production and APP_DEBUG=false must be set on the live server.",
        "APP_URL should use the real HTTPS domain.",
        "After pulling updates from GitHub, run composer install --no-dev, npm run build, php artisan migrate --force, and Laravel cache commands.",
    ]:
        parts.append(bullet(item))

    parts.append(heading("Recommended Production Commands"))
    parts.append(table(
        ["Command", "Purpose"],
        [
            ["git pull origin main", "Bring latest GitHub changes to the server"],
            ["composer install --no-dev --optimize-autoloader", "Install optimized PHP dependencies"],
            ["npm install && npm run build", "Build frontend assets"],
            ["php artisan migrate --force", "Apply database migrations safely in production"],
            ["php artisan storage:link", "Expose public storage files"],
            ["php artisan config:cache", "Cache configuration"],
            ["php artisan route:cache", "Cache routes"],
            ["php artisan view:cache", "Cache Blade views"],
            ["php artisan optimize", "Run Laravel optimization"],
        ],
    ))

    parts.append(heading("Current Limitations"))
    for item in [
        "The system is mainly an internal staff application and does not yet provide a full public portal for students or library members.",
        "Some advanced analytics, charts, and forecasting reports can be added later.",
        "A full backup/restore dashboard is not yet included inside the application.",
        "SMS/WhatsApp/email notifications for fee reminders are not yet automated.",
        "More automated tests can be added for every module and financial edge case.",
    ]:
        parts.append(bullet(item))

    parts.append(heading("Future Improvements"))
    for item in [
        "Add a student/member self-service portal for viewing dues, receipts, room details, and library loans.",
        "Add automated monthly fee reminders by SMS, WhatsApp, or email.",
        "Add charts for income, expenses, library income, room occupancy, and overdue books.",
        "Add barcode/QR scanning for cards, books, and physical book copies.",
        "Add database backup scheduling and admin download for backups.",
        "Add multilingual UI switching with cleaner translation files.",
        "Add more complete audit logs for every financial and identity-related change.",
    ]:
        parts.append(bullet(item))

    parts.append(heading("Conclusion"))
    parts.append(p(
        "Fanous Dormitory and Library Management System is a practical, integrated Laravel application that replaces scattered manual "
        "records with one organized digital platform. It supports dormitory management, library management, finance, cards, receipts, "
        "reports, exports, and role-based staff operations. The project has also been improved with stronger security, better mobile "
        "responsiveness, stricter validation, duplicate prevention, cleaner library finance rules, and deployment documentation. "
        "With future improvements such as notifications, charts, QR scanning, and a student portal, it can grow into a more complete "
        "institution management system."
    ))

    parts.append(heading("Appendix A: Main Laravel Routes"))
    parts.append(table(
        ["Route Area", "Examples"],
        [
            ["Authentication", "/login, /logout, /staff/setup"],
            ["Dashboard", "/dashboard"],
            ["Dorm Students", "/dorm/students, /dorm/students/create, /dorm/students/{student}/edit"],
            ["Dorm Rooms", "/dorm/rooms, /dorm/rooms/create, /dorm/rooms/{room}"],
            ["Representative", "/representative, /representative/report"],
            ["Purchaser", "/purchaser, /purchaser/report"],
            ["Library", "/library, /library/members/{member}, /library/inventory, /library/fee-reminders"],
            ["Library Finance", "/library/finance, /library/finance/export, /library/finance/transactions/{transaction}/receipt"],
            ["Admin Finance", "/admin/finance, /admin/finance/report, /admin/finance/export"],
            ["Users", "/admin/users"],
            ["Settings", "/settings"],
        ],
    ))

    parts.append(heading("Appendix B: Project Files"))
    parts.append(table(
        ["Path", "Description"],
        [
            ["app/Models", "Eloquent models for users, dorm, library, finance, and audit data"],
            ["app/Http/Controllers", "Application controllers for workflows and pages"],
            ["app/Http/Middleware", "Security, locale, and role middleware"],
            ["app/Support", "Reusable helpers for security rules, locale, and audit logic"],
            ["database/migrations", "Database schema history"],
            ["resources/views", "Blade templates for UI pages, forms, cards, receipts, and reports"],
            ["routes/web.php", "Main web route definitions"],
            ["public/css", "Custom responsive/admin UI styling"],
            ["docs", "Project documentation, hosting checklist, schema documents, and this report"],
        ],
    ))

    return "".join(parts)


def create_docx() -> None:
    OUT.parent.mkdir(parents=True, exist_ok=True)
    core = f'''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties"
 xmlns:dc="http://purl.org/dc/elements/1.1/"
 xmlns:dcterms="http://purl.org/dc/terms/"
 xmlns:dcmitype="http://purl.org/dc/dcmitype/"
 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
 <dc:title>Fanous Dormitory and Library Management System Report</dc:title>
 <dc:subject>Complete project report</dc:subject>
 <dc:creator>Fanous Project</dc:creator>
 <cp:lastModifiedBy>Fanous Project</cp:lastModifiedBy>
 <dcterms:created xsi:type="dcterms:W3CDTF">{date.today().isoformat()}T00:00:00Z</dcterms:created>
 <dcterms:modified xsi:type="dcterms:W3CDTF">{date.today().isoformat()}T00:00:00Z</dcterms:modified>
</cp:coreProperties>'''
    app = '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties"
 xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">
 <Application>Microsoft Word</Application>
</Properties>'''
    with zipfile.ZipFile(OUT, "w", compression=zipfile.ZIP_DEFLATED) as docx:
        docx.writestr("[Content_Types].xml", CONTENT_TYPES)
        docx.writestr("_rels/.rels", RELS)
        docx.writestr("word/_rels/document.xml.rels", DOC_RELS)
        docx.writestr("word/document.xml", document_xml(build_body()))
        docx.writestr("word/styles.xml", STYLES)
        docx.writestr("word/numbering.xml", NUMBERING)
        docx.writestr("docProps/core.xml", core)
        docx.writestr("docProps/app.xml", app)


if __name__ == "__main__":
    create_docx()
    print(OUT)
