<!DOCTYPE html>
<html lang="fa" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="@yield('description', 'سیستم مدیریت لیلیه و کتاب‌خانه فانوس برای مدیریت محصلین، اتاق‌ها، مالی و کتاب‌خانه.')">

        <title>@yield('title', 'فانوس')</title>
        <script>
            (function () {
                const theme = localStorage.getItem('fanous.theme') || '{{ auth()->user()?->theme === 'dark' ? 'dark' : 'light' }}';
                localStorage.setItem('fanous.lang', 'fa');
                document.documentElement.lang = 'fa';
                document.documentElement.dir = 'rtl';
                document.documentElement.dataset.theme = theme === 'dark' ? 'dark' : 'light';
            })();
        </script>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        <style>
            @font-face {
                font-family: 'Vazir';
                src: url('{{ asset('font/vazir-font-v16.1.0/Vazir.woff2') }}') format('woff2');
                font-weight: 400;
                font-style: normal;
                font-display: swap;
            }

            @font-face {
                font-family: 'Vazir';
                src: url('{{ asset('font/vazir-font-v16.1.0/Vazir-Medium.woff2') }}') format('woff2');
                font-weight: 500;
                font-style: normal;
                font-display: swap;
            }

            @font-face {
                font-family: 'Vazir';
                src: url('{{ asset('font/vazir-font-v16.1.0/Vazir-Bold.woff2') }}') format('woff2');
                font-weight: 700;
                font-style: normal;
                font-display: swap;
            }

            :root {
                --green: #123f32;
                --gold: #f2c94c;
                --white: #fffdf7;
                --soft: #f4f0df;
                --muted: #5a7067;
            }

            * {
                box-sizing: border-box;
            }

            html {
                scroll-behavior: smooth;
            }

            body {
                margin: 0;
                background: var(--white);
                color: var(--green);
                font-family: 'Vazir', Tahoma, Arial, sans-serif;
                line-height: 1.8;
            }

            a {
                color: inherit;
                text-decoration: none;
            }

            button,
            input,
            select,
            textarea {
                font: inherit;
            }

            .page {
                min-height: 100vh;
                overflow: hidden;
            }

            .site-header {
                position: relative;
                z-index: 10;
                background: var(--white);
                border-bottom: 1px solid color-mix(in srgb, var(--green) 14%, transparent);
            }

            .container {
                width: min(100% - 40px, 1180px);
                margin-inline: auto;
            }

            .header-inner {
                min-height: 88px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 24px;
            }

            .brand {
                display: flex;
                align-items: center;
                gap: 12px;
                font-size: 21px;
                font-weight: 700;
            }

            .brand-mark {
                width: 48px;
                height: 48px;
                display: grid;
                place-items: center;
                border-radius: 8px;
                background: var(--green);
                color: var(--white);
                font-weight: 700;
            }

            .nav {
                display: flex;
                align-items: center;
                gap: 28px;
                font-size: 15px;
                font-weight: 700;
            }

            .nav a {
                transition: color 160ms ease;
            }

            .nav a:hover {
                color: var(--gold);
            }

            .header-actions {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .login-button,
            .primary-button,
            .secondary-button {
                min-height: 48px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 8px;
                padding: 12px 22px;
                font-weight: 700;
                transition: background 160ms ease, color 160ms ease, filter 160ms ease;
            }

            .login-button {
                border: 1px solid var(--green);
                background: transparent;
                color: var(--green);
            }

            .login-button:hover {
                background: var(--green);
                color: var(--white);
            }

            .hero {
                background: var(--green);
                color: var(--white);
            }

            .hero-inner {
                min-height: calc(100vh - 89px);
                display: grid;
                grid-template-columns: 1fr 0.9fr;
                align-items: center;
                gap: 64px;
                padding-block: 72px;
            }

            .eyebrow,
            .section-label {
                width: fit-content;
                margin: 0 0 22px;
                border-radius: 8px;
                background: var(--gold);
                color: var(--green);
                padding: 10px 16px;
                font-weight: 700;
            }

            h1,
            h2,
            h3,
            p {
                margin-block: 0;
            }

            h1 {
                font-size: clamp(42px, 7vw, 78px);
                line-height: 1.08;
                font-weight: 700;
                letter-spacing: 0;
            }

            .hero-copy {
                max-width: 680px;
                margin-top: 24px;
                color: color-mix(in srgb, var(--white) 86%, transparent);
                font-size: 19px;
                line-height: 2;
            }

            .hero-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 16px;
                margin-top: 36px;
            }

            .primary-button {
                border: 0;
                background: var(--gold);
                color: var(--green);
                cursor: pointer;
            }

            .primary-button:hover {
                filter: brightness(0.96);
            }

            .secondary-button {
                border: 1px solid var(--white);
                color: var(--white);
            }

            .secondary-button:hover {
                background: var(--white);
                color: var(--green);
            }

            .program-card {
                border-radius: 8px;
                border: 1px solid color-mix(in srgb, var(--white) 26%, transparent);
                background: var(--white);
                color: var(--green);
                padding: 24px;
                box-shadow: 0 24px 70px color-mix(in srgb, #000 28%, transparent);
            }

            .program-visual {
                min-height: 420px;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                border-radius: 8px;
                background: var(--gold);
                padding: 28px;
            }

            .program-panel {
                min-height: 100%;
                display: flex;
                flex: 1;
                flex-direction: column;
                justify-content: space-between;
                border-radius: 8px;
                background: var(--white);
                padding: 30px;
            }

            .program-title {
                margin-top: 18px;
                font-size: clamp(28px, 4vw, 42px);
                line-height: 1.45;
                font-weight: 700;
            }

            .program-tags {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 12px;
                margin-top: 28px;
            }

            .program-tags span {
                border-radius: 8px;
                background: var(--green);
                color: var(--white);
                padding: 16px 10px;
                text-align: center;
                font-weight: 700;
            }

            .stats,
            .info-section,
            .process-section,
            .roles-section {
                padding-block: 70px;
                background: var(--white);
            }

            .stats {
                background: var(--soft);
            }

            .stat-row,
            .info-grid,
            .roles-grid {
                display: grid;
                gap: 24px;
            }

            .stat-row {
                grid-template-columns: repeat(4, 1fr);
            }

            .info-grid {
                grid-template-columns: repeat(3, 1fr);
            }

            .roles-grid {
                grid-template-columns: repeat(4, 1fr);
            }

            .section-head {
                display: flex;
                align-items: end;
                justify-content: space-between;
                gap: 28px;
                margin-bottom: 28px;
            }

            .section-head h2 {
                max-width: 720px;
                font-size: clamp(30px, 4vw, 46px);
                line-height: 1.45;
                font-weight: 700;
            }

            .section-head p {
                max-width: 410px;
                color: var(--muted);
                line-height: 2;
            }

            .stat-card,
            .info-card,
            .process-card,
            .role-card {
                border: 1px solid color-mix(in srgb, var(--green) 16%, transparent);
                border-radius: 8px;
                background: var(--white);
                padding: 28px;
            }

            .stat-card strong {
                display: block;
                font-size: 32px;
                line-height: 1.2;
            }

            .stat-card span,
            .info-card p,
            .process-card p,
            .role-card p {
                color: var(--muted);
                line-height: 2;
            }

            .info-card h3,
            .process-card h3,
            .role-card h3 {
                margin-bottom: 12px;
                font-size: 24px;
                font-weight: 700;
            }

            .feature-icon,
            .step-number {
                width: 48px;
                height: 48px;
                display: grid;
                place-items: center;
                margin-bottom: 18px;
                border-radius: 8px;
                background: var(--gold);
                color: var(--green);
                font-weight: 700;
            }

            .process-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 24px;
            }

            .process-card {
                display: grid;
                grid-template-columns: auto 1fr;
                gap: 18px;
            }

            .process-card .step-number {
                margin-bottom: 0;
            }

            .consultation {
                background: var(--green);
                color: var(--white);
                padding-block: 58px;
            }

            .consultation-inner {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 28px;
            }

            .consultation-label {
                color: var(--gold);
                font-weight: 700;
            }

            .consultation h2 {
                margin-top: 12px;
                font-size: clamp(28px, 4vw, 42px);
                line-height: 1.45;
                font-weight: 700;
            }

            .form-page {
                min-height: calc(100vh - 89px);
                display: grid;
                align-items: start;
                background: var(--green);
                color: var(--white);
                padding-block: 54px;
            }

            .form-shell {
                display: grid;
                grid-template-columns: 0.78fr 1.22fr;
                gap: 28px;
                align-items: start;
            }

            .form-intro,
            .form-card {
                border-radius: 8px;
                background: var(--white);
                color: var(--green);
                padding: 30px;
            }

            .form-intro {
                position: sticky;
                top: 24px;
                background: var(--gold);
            }

            .form-intro h1,
            .form-card h1 {
                font-size: clamp(30px, 4vw, 44px);
                line-height: 1.35;
            }

            .form-intro p,
            .form-card p {
                margin-top: 16px;
                line-height: 2;
            }

            .form-note-list {
                display: grid;
                gap: 12px;
                margin-top: 24px;
                padding: 0;
                list-style: none;
            }

            .form-note-list li {
                border-radius: 8px;
                background: color-mix(in srgb, var(--green) 9%, transparent);
                padding: 12px 14px;
                font-weight: 700;
            }

            .form-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 18px;
                margin-top: 28px;
            }

            .field {
                display: grid;
                gap: 8px;
            }

            .full,
            .field.full {
                grid-column: 1 / -1;
            }

            label {
                font-weight: 700;
            }

            input,
            select,
            textarea {
                width: 100%;
                border: 1px solid color-mix(in srgb, var(--green) 20%, transparent);
                border-radius: 8px;
                background: var(--white);
                color: var(--green);
                padding: 13px 14px;
                outline: none;
            }

            input[type="file"] {
                background: color-mix(in srgb, var(--gold) 10%, var(--white));
            }

            input:focus,
            select:focus,
            textarea:focus {
                border-color: var(--gold);
                box-shadow: 0 0 0 3px color-mix(in srgb, var(--gold) 28%, transparent);
            }

            textarea {
                min-height: 130px;
                resize: vertical;
            }

            .error {
                color: #8a1f11;
                font-size: 13px;
                line-height: 1.7;
            }

            .status {
                margin-top: 22px;
                border-radius: 8px;
                background: color-mix(in srgb, var(--gold) 45%, var(--white));
                padding: 14px 16px;
                font-weight: 700;
            }

            .form-footer {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
                margin-top: 24px;
            }

            .request-list {
                display: grid;
                gap: 18px;
                margin-top: 28px;
            }

            .request-card {
                border: 1px solid color-mix(in srgb, var(--green) 16%, transparent);
                border-radius: 8px;
                padding: 22px;
            }

            .request-card-header {
                display: flex;
                align-items: start;
                justify-content: space-between;
                gap: 16px;
            }

            .request-card h2 {
                font-size: 22px;
                line-height: 1.5;
            }

            .request-meta {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 12px 18px;
                margin-top: 18px;
                line-height: 1.8;
            }

            .request-meta strong {
                display: block;
                font-size: 13px;
            }

            .status-badge {
                min-width: 112px;
                border-radius: 8px;
                background: var(--gold);
                color: var(--green);
                padding: 9px 13px;
                text-align: center;
                font-weight: 700;
                white-space: nowrap;
            }

            .empty-state {
                margin-top: 28px;
                border: 1px dashed color-mix(in srgb, var(--green) 28%, transparent);
                border-radius: 8px;
                padding: 28px;
                text-align: center;
                line-height: 2;
            }

            .site-footer {
                padding-block: 28px;
                border-top: 1px solid color-mix(in srgb, var(--green) 14%, transparent);
                background: var(--white);
                color: var(--muted);
            }

            @media (max-width: 980px) {
                .nav {
                    display: none;
                }

                .hero-inner,
                .info-grid,
                .process-grid,
                .form-shell {
                    grid-template-columns: 1fr;
                }

                .hero-inner {
                    min-height: auto;
                }

                .stat-row,
                .roles-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .section-head {
                    align-items: stretch;
                    flex-direction: column;
                }

                .form-intro {
                    position: static;
                }
            }

            body.theme-dark {
                background: #0f172a;
                color: #e5e7eb;
            }

            body.theme-dark .gui-role-shell,
            body.theme-dark .gui-role-top,
            body.theme-dark .gui-main,
            body.theme-dark .gui-card,
            body.theme-dark .gui-stat,
            body.theme-dark .gui-panel,
            body.theme-dark .room-card,
            body.theme-dark .dorm-table-wrap {
                background: #111827;
                color: #e5e7eb;
            }

            body.theme-dark .gui-role-top,
            body.theme-dark .gui-card,
            body.theme-dark .gui-stat,
            body.theme-dark .gui-panel,
            body.theme-dark .room-card,
            body.theme-dark .dorm-table-wrap,
            body.theme-dark .gui-item {
                border-color: rgba(255, 255, 255, .12);
            }

            body.theme-dark .gui-brand strong,
            body.theme-dark .gui-top-title,
            body.theme-dark .gui-page-head h1,
            body.theme-dark .gui-panel h2,
            body.theme-dark .gui-panel h3,
            body.theme-dark .gui-card h2,
            body.theme-dark .room-card h2,
            body.theme-dark .gui-field label {
                color: #f9fafb;
            }

            body.theme-dark .gui-brand span span,
            body.theme-dark .gui-page-head p,
            body.theme-dark .gui-panel p,
            body.theme-dark .gui-card p,
            body.theme-dark .gui-stat span,
            body.theme-dark .gui-stat p,
            body.theme-dark .gui-item span,
            body.theme-dark .dorm-inline-meta {
                color: #a7b0c0;
            }

            body.theme-dark .gui-field input,
            body.theme-dark .gui-field select,
            body.theme-dark .gui-field textarea,
            body.theme-dark input,
            body.theme-dark select,
            body.theme-dark textarea {
                border-color: rgba(255, 255, 255, .16);
                background: #0f172a;
                color: #f9fafb;
            }

            body.theme-dark .gui-item {
                background: #0f172a;
            }

            body.theme-dark .dorm-table td {
                border-color: rgba(255, 255, 255, .12);
                background: #162033;
                color: #f9fafb;
            }

            body.theme-dark .dorm-table tr:nth-child(even) td {
                background: #1f2937;
            }

            body.theme-dark .dorm-table tr:hover td {
                background: #26405a;
            }

            @media (max-width: 620px) {
                .container {
                    width: min(100% - 28px, 1180px);
                }

                .header-inner,
                .consultation-inner,
                .request-card-header,
                .form-footer {
                    align-items: stretch;
                    flex-direction: column;
                }

                .login-button,
                .primary-button,
                .secondary-button,
                .header-actions {
                    width: 100%;
                }

                .header-actions {
                    flex-direction: column;
                }

                .program-visual {
                    min-height: 360px;
                    padding: 18px;
                }

                .program-panel {
                    padding: 20px;
                }

                .program-tags,
                .form-grid,
                .request-meta,
                .stat-row,
                .roles-grid {
                    grid-template-columns: 1fr;
                }
            }

            .site-header {
                position: sticky;
                top: 0;
                background: rgba(255, 253, 247, .88);
                backdrop-filter: blur(18px);
            }

            .premium-hero {
                min-height: calc(100vh - 150px);
                display: grid;
                align-items: end;
                position: relative;
                color: #fffdf7;
                background:
                    linear-gradient(90deg, rgba(9, 31, 25, .92), rgba(9, 31, 25, .58) 52%, rgba(9, 31, 25, .22)),
                    url('https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=1800&q=82') center/cover;
            }

            .premium-hero-content {
                padding-block: 96px 68px;
            }

            .hero-kicker {
                width: fit-content;
                margin-bottom: 22px;
                border: 1px solid rgba(255, 253, 247, .28);
                border-radius: 8px;
                background: rgba(255, 253, 247, .12);
                padding: 10px 14px;
                color: #f2c94c;
                font-weight: 700;
            }

            .premium-hero h1 {
                max-width: 880px;
                font-size: clamp(44px, 8vw, 92px);
                line-height: 1.08;
            }

            .premium-hero .hero-copy {
                max-width: 760px;
                color: rgba(255, 253, 247, .84);
            }

            .premium-hero .secondary-button {
                border-color: rgba(255, 253, 247, .8);
                color: #fffdf7;
                background: rgba(255, 253, 247, .08);
            }

            .premium-stats {
                background: #fffdf7;
                border-bottom: 1px solid rgba(18, 63, 50, .1);
            }

            .premium-stat-row {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
            }

            .premium-stat-row article {
                padding: 28px 24px;
                border-inline-start: 1px solid rgba(18, 63, 50, .1);
            }

            .premium-stat-row article:last-child {
                border-inline-start: 0;
            }

            .premium-stat-row strong {
                display: block;
                color: #123f32;
                font-size: 36px;
                line-height: 1.1;
            }

            .premium-stat-row span {
                color: #5a7067;
                font-weight: 700;
            }

            .premium-section {
                padding-block: 86px;
                background: #fffdf7;
            }

            .premium-head {
                display: grid;
                grid-template-columns: 1fr .62fr;
                align-items: end;
                gap: 34px;
                margin-bottom: 32px;
            }

            .premium-head.compact {
                display: block;
                max-width: 780px;
            }

            .premium-head h2,
            .showcase-copy h2,
            .roles-lux h2,
            .final-cta h2 {
                font-size: clamp(32px, 4.8vw, 58px);
                line-height: 1.35;
            }

            .premium-head p:last-child,
            .showcase-copy p,
            .timeline-grid p,
            .premium-card p {
                color: #5a7067;
                line-height: 2;
            }

            .premium-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 18px;
            }

            .premium-card {
                min-height: 285px;
                border: 1px solid rgba(18, 63, 50, .12);
                border-radius: 8px;
                background: #fffdf7;
                padding: 28px;
                box-shadow: 0 24px 70px rgba(18, 63, 50, .08);
            }

            .premium-card span {
                display: grid;
                width: 48px;
                height: 48px;
                place-items: center;
                margin-bottom: 28px;
                border-radius: 8px;
                background: #123f32;
                color: #f2c94c;
                font-weight: 700;
            }

            .premium-card h3,
            .timeline-grid h3 {
                margin-bottom: 12px;
                font-size: 24px;
            }

            .showcase {
                padding-block: 86px;
                background: #eef4f1;
            }

            .showcase-inner {
                display: grid;
                grid-template-columns: .92fr 1fr;
                align-items: center;
                gap: 44px;
            }

            .showcase-image {
                min-height: 530px;
                border-radius: 8px;
                background:
                    linear-gradient(rgba(18, 63, 50, .1), rgba(18, 63, 50, .1)),
                    url('https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=1200&q=82') center/cover;
                box-shadow: 0 30px 80px rgba(18, 63, 50, .16);
            }

            .showcase-points,
            .role-pills {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                margin-top: 28px;
            }

            .showcase-points span,
            .role-pills span {
                border-radius: 8px;
                background: #fffdf7;
                color: #123f32;
                padding: 10px 14px;
                font-weight: 700;
            }

            .timeline-grid {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 16px;
            }

            .timeline-grid article {
                border-top: 3px solid #f2c94c;
                padding: 22px 0 0;
            }

            .timeline-grid b {
                display: inline-grid;
                width: 38px;
                height: 38px;
                place-items: center;
                margin-bottom: 18px;
                border-radius: 8px;
                background: #123f32;
                color: #fffdf7;
            }

            .roles-lux {
                padding-block: 72px;
                background: #123f32;
                color: #fffdf7;
            }

            .roles-lux-inner {
                display: grid;
                grid-template-columns: .8fr 1fr;
                align-items: center;
                gap: 36px;
            }

            .roles-lux .section-label {
                background: #f2c94c;
            }

            .role-pills span {
                background: rgba(255, 253, 247, .1);
                color: #fffdf7;
                border: 1px solid rgba(255, 253, 247, .18);
            }

            .final-cta {
                padding-block: 72px;
                background: #fffdf7;
            }

            .final-cta-inner {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 28px;
                border-block: 1px solid rgba(18, 63, 50, .12);
                padding-block: 36px;
            }

            @media (max-width: 980px) {
                .header-inner {
                    align-items: flex-start;
                    flex-direction: column;
                    padding-block: 16px;
                }

                .nav {
                    display: flex;
                    width: 100%;
                    overflow-x: auto;
                    padding-bottom: 6px;
                    white-space: nowrap;
                }

                .premium-head,
                .showcase-inner,
                .roles-lux-inner,
                .premium-grid,
                .timeline-grid {
                    grid-template-columns: 1fr;
                }

                .premium-stat-row {
                    grid-template-columns: repeat(2, 1fr);
                }

                .showcase-image {
                    min-height: 360px;
                }
            }

            @media (max-width: 620px) {
                .premium-hero {
                    min-height: auto;
                }

                .premium-hero-content {
                    padding-block: 64px;
                }

                .premium-stat-row {
                    grid-template-columns: 1fr;
                }

                .premium-stat-row article {
                    border-inline-start: 0;
                    border-bottom: 1px solid rgba(18, 63, 50, .1);
                }

                .final-cta-inner {
                    align-items: stretch;
                    flex-direction: column;
                }
            }

            .calm-hero {
                padding-block: 64px 78px;
                background: #fffdf7;
            }

            .calm-hero-inner {
                display: grid;
                grid-template-columns: .9fr 1.1fr;
                align-items: center;
                gap: 52px;
            }

            .calm-hero h1 {
                max-width: 760px;
                font-size: clamp(42px, 6.8vw, 82px);
                line-height: 1.16;
            }

            .calm-hero .hero-copy {
                color: #5a7067;
            }

            .calm-hero .secondary-button {
                border-color: rgba(18, 63, 50, .25);
                color: #123f32;
                background: transparent;
            }

            .calm-hero-photo,
            .place-photo {
                min-height: 560px;
                border-radius: 8px;
                background:
                    linear-gradient(rgba(18, 63, 50, .06), rgba(18, 63, 50, .06)),
                    url('https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=1200&q=82') center/cover;
                box-shadow: 0 26px 70px rgba(18, 63, 50, .14);
            }

            .hero-kicker {
                width: fit-content;
                margin-bottom: 18px;
                border-radius: 8px;
                background: #f2c94c;
                color: #123f32;
                padding: 10px 14px;
                font-weight: 700;
            }

            .quiet-stats {
                background: #123f32;
                color: #fffdf7;
            }

            .quiet-stats-row {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 1px;
                background: rgba(255, 253, 247, .16);
            }

            .quiet-stats article {
                background: #123f32;
                padding: 28px;
            }

            .quiet-stats strong {
                display: block;
                font-size: 26px;
            }

            .quiet-stats span {
                color: rgba(255, 253, 247, .72);
            }

            .service-band,
            .simple-process,
            .place-section {
                padding-block: 78px;
                background: #fffdf7;
            }

            .service-band-inner,
            .simple-process-inner {
                display: grid;
                grid-template-columns: .72fr 1fr;
                gap: 42px;
                align-items: start;
            }

            .section-intro h2,
            .simple-process h2,
            .place-section h2 {
                font-size: clamp(30px, 4.6vw, 56px);
                line-height: 1.42;
            }

            .service-list {
                display: grid;
                gap: 16px;
            }

            .service-list article,
            .process-steps article {
                border: 1px solid rgba(18, 63, 50, .12);
                border-radius: 8px;
                background: #fffdf7;
                padding: 24px;
            }

            .service-list h3 {
                margin-bottom: 8px;
                font-size: 24px;
            }

            .service-list p,
            .process-steps p,
            .place-section p {
                color: #5a7067;
                line-height: 2;
            }

            .simple-process {
                background: #f4f0df;
            }

            .process-steps {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 14px;
            }

            .process-steps span {
                display: grid;
                width: 42px;
                height: 42px;
                place-items: center;
                margin-bottom: 18px;
                border-radius: 8px;
                background: #123f32;
                color: #fffdf7;
                font-weight: 700;
            }

            .place-grid {
                display: grid;
                grid-template-columns: 1fr .86fr;
                align-items: center;
                gap: 46px;
            }

            .place-photo {
                min-height: 460px;
                background:
                    linear-gradient(rgba(18, 63, 50, .08), rgba(18, 63, 50, .08)),
                    url('https://images.unsplash.com/photo-1481627834876-b7833e8f5570?auto=format&fit=crop&w=1200&q=82') center/cover;
            }

            .place-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
                margin-top: 28px;
            }

            @media (max-width: 980px) {
                .calm-hero-inner,
                .service-band-inner,
                .simple-process-inner,
                .place-grid {
                    grid-template-columns: 1fr;
                }

                .process-steps,
                .quiet-stats-row {
                    grid-template-columns: 1fr;
                }

                .calm-hero-photo,
                .place-photo {
                    min-height: 340px;
                }
            }

            .login-screen {
                min-height: calc(100vh - 145px);
                display: grid;
                place-items: center;
                background:
                    radial-gradient(circle at 50% 20%, rgba(18, 63, 50, .06), transparent 32%),
                    linear-gradient(180deg, #f8fafc, #eaf0f4);
                padding: 52px 20px;
            }

            .login-card {
                width: min(100%, 390px);
                border: 1px solid rgba(18, 63, 50, .1);
                border-radius: 8px;
                background: rgba(255, 255, 255, .94);
                padding: 34px;
                box-shadow: 0 22px 60px rgba(18, 63, 50, .14);
            }

            .login-mark {
                width: 42px;
                height: 42px;
                display: grid;
                place-items: center;
                margin-inline: auto;
                border-radius: 8px;
                background: #123f32;
                color: #f2c94c;
                font-size: 22px;
                font-weight: 700;
            }

            .login-card h1 {
                margin-top: 16px;
                text-align: center;
                font-size: 30px;
                line-height: 1.2;
            }

            .login-subtitle {
                margin-top: 8px;
                color: #718096;
                text-align: center;
                font-size: 14px;
                font-weight: 700;
            }

            .secure-login-form {
                display: grid;
                gap: 18px;
                margin-top: 28px;
            }

            .login-field {
                display: grid;
                gap: 8px;
            }

            .login-field label {
                color: #233047;
                font-size: 13px;
                font-weight: 700;
            }

            .login-field input {
                min-height: 48px;
                border: 1px solid #d9e2ec;
                border-radius: 8px;
                background: #fff;
                color: #123f32;
                padding: 12px 14px;
                box-shadow: inset 0 1px 1px rgba(18, 63, 50, .03);
            }

            .login-field input:focus {
                border-color: #123f32;
                box-shadow: 0 0 0 3px rgba(18, 63, 50, .12);
            }

            .login-field span,
            .login-alert {
                color: #8a1f11;
                font-size: 13px;
                line-height: 1.7;
            }

            .login-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                color: #718096;
                font-size: 13px;
                font-weight: 700;
            }

            .login-row label {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                white-space: nowrap;
            }

            .login-row input {
                width: auto;
            }

            .login-submit,
            .login-secondary {
                width: 100%;
                min-height: 50px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border: 0;
                border-radius: 8px;
                background: #123f32;
                color: #fffdf7;
                padding: 12px 18px;
                font-weight: 700;
                cursor: pointer;
            }

            .login-secondary {
                border: 1px solid #123f32;
                background: transparent;
                color: #123f32;
            }

            .login-actions {
                display: grid;
                gap: 12px;
                margin-top: 24px;
            }

            .login-alert {
                margin-top: 18px;
                border-radius: 8px;
                background: rgba(242, 201, 76, .28);
                padding: 12px 14px;
                color: #123f32;
                font-weight: 700;
            }

            .login-alert a {
                margin-inline-start: 8px;
                text-decoration: underline;
            }

            .login-security {
                display: grid;
                grid-template-columns: auto 1fr;
                gap: 8px 12px;
                margin-top: 18px;
                border: 1px solid rgba(32, 171, 104, .2);
                border-radius: 8px;
                background: rgba(32, 171, 104, .08);
                color: #16643f;
                padding: 14px;
                font-size: 13px;
                line-height: 1.7;
            }

            .login-security::before {
                content: "✓";
                grid-row: span 2;
                width: 22px;
                height: 22px;
                display: grid;
                place-items: center;
                border-radius: 8px;
                background: rgba(32, 171, 104, .14);
                font-weight: 700;
            }

            .login-security strong,
            .login-security span {
                display: block;
            }

            .login-transparency {
                display: block;
                margin-top: 18px;
                color: #123f32;
                text-align: center;
                font-size: 13px;
                font-weight: 700;
            }

            @media (max-width: 620px) {
                .login-screen {
                    min-height: auto;
                    padding: 30px 14px;
                }

                .login-card {
                    padding: 24px;
                }

                .login-row {
                    align-items: flex-start;
                    flex-direction: column;
                }
            }

            .fanous-auth {
                min-height: calc(100vh - 145px);
                display: flex;
                align-items: center;
                justify-content: center;
                background:
                    radial-gradient(circle at 50% 18%, rgba(242, 201, 76, .18), transparent 30%),
                    linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
                padding: 54px 20px;
            }

            .fanous-auth .login-container {
                width: 100%;
                max-width: 410px;
            }

            .fanous-auth .login-card {
                position: relative;
                width: 100%;
                border: 1px solid #eef2f7;
                border-radius: 16px;
                background: #ffffff;
                padding: 40px 32px 32px;
                box-shadow: 0 22px 64px rgba(18, 63, 50, .14), 0 4px 8px rgba(15, 23, 42, .06);
            }

            .fanous-auth .login-header {
                margin-bottom: 30px;
                text-align: center;
            }

            .fanous-auth .logo {
                display: flex;
                justify-content: center;
                margin-bottom: 16px;
            }

            .fanous-auth .login-header h1 {
                color: #1e293b;
                font-size: 30px;
                font-weight: 700;
                line-height: 1.25;
            }

            .fanous-auth .login-header p {
                margin-top: 8px;
                color: #64748b;
                font-size: 13px;
                font-weight: 700;
                line-height: 1.8;
            }

            .fanous-auth .form-group {
                position: relative;
                margin-bottom: 20px;
            }

            .fanous-auth .form-group label {
                display: block;
                margin-bottom: 6px;
                color: #334155;
                font-size: 13px;
                font-weight: 700;
            }

            .fanous-auth .form-group input {
                width: 100%;
                min-height: 48px;
                border: 1.5px solid #e2e8f0;
                border-radius: 8px;
                background: #ffffff;
                color: #123f32;
                padding: 12px 14px;
                font-size: 15px;
                outline: none;
                transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
            }

            .fanous-auth .form-group input:focus {
                border-color: #123f32;
                box-shadow: 0 0 0 3px rgba(18, 63, 50, .11);
            }

            .fanous-auth .password-wrapper {
                position: relative;
            }

            .fanous-auth .password-wrapper input {
                padding-left: 46px;
            }

            .fanous-auth .password-toggle {
                position: absolute;
                top: 50%;
                left: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 0;
                border-radius: 6px;
                background: transparent;
                color: #64748b;
                padding: 6px;
                cursor: pointer;
                transform: translateY(-50%);
                transition: color .2s ease, background .2s ease;
            }

            .fanous-auth .password-toggle:hover {
                background: #f8fafc;
                color: #123f32;
            }

            .fanous-auth .eye-closed {
                display: none;
            }

            .fanous-auth .password-toggle.show-password .eye-open {
                display: none;
            }

            .fanous-auth .password-toggle.show-password .eye-closed {
                display: block;
            }

            .fanous-auth .form-options {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                margin-bottom: 24px;
            }

            .fanous-auth .checkbox-wrapper {
                display: inline-flex;
                align-items: center;
                color: #374151;
                font-size: 13px;
                font-weight: 700;
                cursor: pointer;
                white-space: nowrap;
            }

            .fanous-auth .checkbox-wrapper input[type="checkbox"] {
                display: none;
            }

            .fanous-auth .checkmark {
                position: relative;
                width: 16px;
                height: 16px;
                flex: 0 0 auto;
                border: 1.5px solid #d1d5db;
                border-radius: 4px;
                background: #ffffff;
                margin-left: 8px;
                transition: background .2s ease, border-color .2s ease;
            }

            .fanous-auth .checkbox-wrapper input[type="checkbox"]:checked + .checkmark {
                border-color: #123f32;
                background: #123f32;
            }

            .fanous-auth .checkmark::after {
                content: '';
                position: absolute;
                left: 4px;
                top: 1px;
                width: 4px;
                height: 8px;
                border: solid #ffffff;
                border-width: 0 1.5px 1.5px 0;
                opacity: 0;
                transform: rotate(45deg);
                transition: opacity .2s ease;
            }

            .fanous-auth .checkbox-wrapper input[type="checkbox"]:checked + .checkmark::after {
                opacity: 1;
            }

            .fanous-auth .forgot-link {
                color: #64748b;
                font-size: 12px;
                font-weight: 700;
                line-height: 1.7;
                text-align: left;
            }

            .fanous-auth .login-btn,
            .fanous-auth .secondary-login-btn {
                width: 100%;
                min-height: 46px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border: 0;
                border-radius: 8px;
                background: #123f32;
                color: #fffdf7;
                padding: 12px 20px;
                font-size: 15px;
                font-weight: 700;
                cursor: pointer;
                transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
            }

            .fanous-auth .login-btn:hover {
                background: #0d3429;
                box-shadow: 0 10px 22px rgba(18, 63, 50, .26);
                transform: translateY(-1px);
            }

            .fanous-auth .secondary-login-btn {
                border: 1px solid #123f32;
                background: transparent;
                color: #123f32;
            }

            .fanous-auth .secondary-login-btn:hover {
                background: #f8fafc;
            }

            .fanous-auth .login-actions {
                display: grid;
                gap: 12px;
                margin-top: 20px;
            }

            .fanous-auth .login-actions form {
                margin: 0;
            }

            .fanous-auth .security-notice {
                display: flex;
                align-items: center;
                gap: 8px;
                margin-top: 16px;
                border: 1px solid #bbf7d0;
                border-radius: 8px;
                background: #f0fdf4;
                padding: 12px 14px;
            }

            .fanous-auth .security-notice span {
                color: #166534;
                font-size: 12px;
                font-weight: 700;
                line-height: 1.7;
            }

            .fanous-auth .form-alert {
                margin-bottom: 18px;
                border-radius: 8px;
                background: rgba(242, 201, 76, .26);
                color: #123f32;
                padding: 12px 14px;
                font-size: 13px;
                font-weight: 700;
                line-height: 1.8;
            }

            .fanous-auth .form-alert a {
                margin-inline-start: 6px;
                text-decoration: underline;
            }

            .fanous-auth .error-message {
                display: block;
                margin-top: 5px;
                color: #dc2626;
                font-size: 12px;
                font-weight: 700;
                line-height: 1.7;
                opacity: 0;
                transform: translateY(-2px);
                transition: opacity .2s ease, transform .2s ease;
            }

            .fanous-auth .error-message.show {
                opacity: 1;
                transform: translateY(0);
            }

            .fanous-auth .form-group.error input {
                border-color: #dc2626;
                background: #fef2f2;
            }

            .fanous-auth .form-group.error input:focus {
                box-shadow: 0 0 0 3px rgba(220, 38, 38, .1);
            }

            .fanous-auth .public-link {
                display: block;
                margin-top: 16px;
                color: #123f32;
                text-align: center;
                font-size: 13px;
                font-weight: 700;
            }

            .fanous-auth :focus-visible {
                outline: 2px solid currentColor;
                outline-offset: 2px;
            }

            @media (max-width: 620px) {
                .fanous-auth {
                    min-height: auto;
                    padding: 32px 14px;
                }

                .fanous-auth .login-card {
                    border-radius: 12px;
                    padding: 32px 24px 24px;
                }

                .fanous-auth .form-options {
                    align-items: flex-start;
                    flex-direction: column;
                }

                .fanous-auth .forgot-link {
                    text-align: right;
                }

                .fanous-auth .security-notice {
                    align-items: flex-start;
                }
            }

            .mabolo-login-shell {
                min-height: 100vh;
                overflow: hidden;
                background: #f9f9f9;
                color: #111111;
                font-family: 'Century Gothic', 'Segoe UI', Arial, sans-serif;
                line-height: 1.4;
            }

            .mabolo-titlebar {
                position: relative;
                height: 69px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #f9f9f9;
            }

            .mabolo-title {
                margin-left: 330px;
                font-size: 18px;
            }

            .mabolo-window-actions {
                position: absolute;
                top: 10px;
                right: 22px;
                display: flex;
                gap: 6px;
            }

            .mabolo-window-button {
                width: 61px;
                height: 45px;
                display: grid;
                place-items: center;
                border-radius: 5px;
                background: #f9f9f9;
                color: #111111;
                font-size: 20px;
                font-weight: 700;
            }

            .mabolo-window-button.close:hover {
                background: #ff8080;
                color: #ffffff;
            }

            .mabolo-main {
                min-height: calc(100vh - 69px);
                display: grid;
                grid-template-columns: 689px 1fr;
            }

            .mabolo-panel {
                position: relative;
                min-height: calc(100vh - 69px);
                background: #f9f9f9;
            }

            .mabolo-logo-wrap {
                position: relative;
                width: 170px;
                height: 150px;
                margin: 78px auto 0;
            }

            .mabolo-logo-back {
                position: absolute;
                left: 22px;
                top: 10px;
                width: 115px;
                height: 133px;
                border-radius: 28px 28px 10px 10px;
                background:
                    linear-gradient(#24963e, #24963e) center 76px/74px 8px no-repeat,
                    linear-gradient(#24963e, #24963e) center 96px/74px 8px no-repeat,
                    #abedb5;
                transform: rotate(-4deg);
            }

            .mabolo-logo-front {
                position: absolute;
                right: 4px;
                top: 12px;
                width: 144px;
                height: 111px;
                display: grid;
                place-items: center;
                border: 7px solid #24963e;
                border-radius: 18px;
                background: #ffffff;
                color: #24963e;
                font-size: 62px;
                font-weight: 700;
                box-shadow: 0 12px 24px rgba(36, 150, 62, .16);
            }

            .mabolo-panel h1 {
                margin-top: 8px;
                color: #111111;
                text-align: center;
                font-size: 19px;
                font-weight: 700;
                line-height: 1.35;
            }

            .mabolo-school {
                margin-top: 2px;
                color: #111111;
                text-align: center;
                font-size: 18px;
            }

            .mabolo-panel h2 {
                margin-top: 45px;
                color: #111111;
                text-align: center;
                font-size: 44px;
                font-weight: 700;
                line-height: 1.1;
            }

            .mabolo-subtitle {
                margin-top: 4px;
                color: #111111;
                text-align: center;
                font-size: 21px;
            }

            .mabolo-form {
                width: 408px;
                margin: 30px auto 0;
            }

            .mabolo-input-row {
                position: relative;
                height: 48px;
                margin-bottom: 34px;
            }

            .mabolo-input-row input {
                width: 100%;
                height: 44px;
                border: 0;
                border-bottom: 2px solid #2ecc71;
                border-radius: 0;
                background: transparent;
                color: #111111;
                padding: 0 48px 0 40px;
                font-size: 21px;
                outline: none;
            }

            .mabolo-input-row input::placeholder {
                color: #808080;
                opacity: 1;
            }

            .mabolo-input-row input:focus {
                border-color: #228b22;
                box-shadow: none;
            }

            .mabolo-input-row.error input {
                border-color: #ff0000;
            }

            .mabolo-input-icon {
                position: absolute;
                left: 6px;
                top: 8px;
                color: #111111;
                z-index: 2;
            }

            .mabolo-eye {
                position: absolute;
                right: 0;
                top: 1px;
                width: 42px;
                height: 42px;
                display: grid;
                place-items: center;
                border: 0;
                background: transparent;
                color: #111111;
                cursor: pointer;
            }

            .mabolo-eye .eye-closed {
                display: none;
            }

            .mabolo-eye.show-password .eye-open {
                display: none;
            }

            .mabolo-eye.show-password .eye-closed {
                display: block;
            }

            .mabolo-error {
                margin: -26px 0 16px;
                color: #ff0000;
                font-size: 16px;
            }

            .mabolo-message {
                width: 408px;
                margin: 18px auto 0;
                color: #228b22;
                font-size: 16px;
                text-align: center;
            }

            .mabolo-auth-actions {
                width: 408px;
                display: grid;
                gap: 12px;
                margin: 28px auto 0;
            }

            .mabolo-auth-actions form {
                margin: 0;
            }

            .mabolo-forgot {
                display: block;
                width: 160px;
                height: 32px;
                margin: -10px 0 10px;
                border: 0;
                border-radius: 5px;
                background: transparent;
                color: #808080;
                font-size: 20px;
                cursor: pointer;
            }

            .mabolo-forgot:hover {
                color: #111111;
            }

            .mabolo-login-button {
                width: 160px;
                height: 42px;
                display: grid;
                place-items: center;
                margin-left: auto;
                border: 0;
                border-radius: 10px;
                background: #24963e;
                color: #ffffff;
                font-size: 23px;
                cursor: pointer;
                text-align: center;
            }

            .mabolo-login-button:hover {
                background: #228b22;
            }

            .mabolo-dots {
                position: absolute;
                left: 131px;
                bottom: 70px;
                display: flex;
                gap: 13px;
            }

            .mabolo-dots span {
                width: 17px;
                height: 17px;
                border-radius: 999px;
                background: #24963e;
            }

            .mabolo-dots span:nth-child(2) {
                background: #74e485;
            }

            .mabolo-dots span:nth-child(3) {
                background: #abedb5;
            }

            .mabolo-visual {
                position: relative;
                min-height: calc(100vh - 69px);
                background: #f9f9f9;
            }

            .mabolo-illustration {
                position: absolute;
                inset: 24px 36px 62px 20px;
                overflow: hidden;
            }

            .mabolo-illustration .sun {
                position: absolute;
                right: 126px;
                top: 64px;
                width: 94px;
                height: 94px;
                border-radius: 50%;
                background: #abedb5;
                box-shadow: 0 0 0 28px rgba(171, 237, 181, .22);
            }

            .building {
                position: absolute;
                display: grid;
                gap: 20px 24px;
                border-radius: 16px 16px 4px 4px;
                background: #ffffff;
                box-shadow: 0 18px 34px rgba(36, 150, 62, .12);
            }

            .building span {
                border-radius: 4px;
                background: #74e485;
            }

            .main-building {
                right: 190px;
                bottom: 96px;
                width: 330px;
                height: 390px;
                grid-template-columns: repeat(2, 72px);
                grid-auto-rows: 52px;
                justify-content: center;
                align-content: center;
                border: 8px solid #24963e;
            }

            .side-building {
                right: 520px;
                bottom: 96px;
                width: 210px;
                height: 292px;
                grid-template-columns: repeat(2, 52px);
                grid-auto-rows: 44px;
                justify-content: center;
                align-content: center;
                border: 7px solid #74e485;
            }

            .tree {
                position: absolute;
                bottom: 104px;
                width: 70px;
                height: 120px;
                background:
                    linear-gradient(#24963e, #24963e) center bottom/16px 64px no-repeat,
                    radial-gradient(circle at center 30px, #74e485 0 35px, transparent 36px);
            }

            .tree-one {
                right: 100px;
            }

            .tree-two {
                right: 740px;
                transform: scale(.86);
            }

            .ground {
                position: absolute;
                right: 52px;
                bottom: 72px;
                width: 760px;
                height: 18px;
                border-radius: 999px;
                background: #24963e;
            }

            .mabolo-dots.right {
                left: auto;
                right: 86px;
                bottom: 71px;
            }

            @media (max-width: 1000px) {
                .mabolo-title {
                    margin-left: 0;
                }

                .mabolo-main {
                    grid-template-columns: 1fr;
                }

                .mabolo-panel {
                    min-height: calc(100vh - 69px);
                }

                .mabolo-visual {
                    display: none;
                }
            }

            @media (max-width: 560px) {
                .mabolo-titlebar {
                    justify-content: flex-start;
                    padding-left: 18px;
                }

                .mabolo-title {
                    max-width: 250px;
                    font-size: 14px;
                }

                .mabolo-window-actions {
                    right: 8px;
                }

                .mabolo-window-button {
                    width: 42px;
                }

                .mabolo-form,
                .mabolo-message,
                .mabolo-auth-actions {
                    width: min(100% - 44px, 408px);
                }

                .mabolo-panel h1 {
                    padding-inline: 22px;
                }

                .mabolo-panel h2 {
                    font-size: 36px;
                }
            }

            .compact-login {
                min-height: 100vh;
                display: grid;
                place-items: center;
                background: #f9f9f9;
                padding: 22px;
            }

            .compact-login .mabolo-panel {
                width: min(100%, 380px);
                min-height: auto;
                border: 1px solid rgba(36, 150, 62, .12);
                border-radius: 14px;
                background: #ffffff;
                padding: 34px 34px 32px;
                box-shadow: 0 18px 48px rgba(0, 0, 0, .08);
            }

            .compact-login .mabolo-logo-wrap {
                width: 72px;
                height: 72px;
                margin: 0 auto 18px;
            }

            .compact-login .mabolo-logo-back {
                display: none;
            }

            .compact-login .mabolo-logo-front {
                position: static;
                width: 72px;
                height: 72px;
                border: 5px solid #24963e;
                border-radius: 16px;
                color: #24963e;
                font-size: 38px;
                box-shadow: none;
            }

            .compact-login .mabolo-panel h1 {
                margin: 0;
                color: #111111;
                text-align: center;
                font-size: 18px;
                font-weight: 700;
            }

            .compact-login .mabolo-panel h2 {
                margin-top: 30px;
                color: #111111;
                text-align: center;
                font-size: 32px;
                font-weight: 700;
            }

            .compact-login .mabolo-subtitle {
                margin-top: 3px;
                color: #111111;
                text-align: center;
                font-size: 16px;
            }

            .compact-login .mabolo-form,
            .compact-login .mabolo-message,
            .compact-login .mabolo-auth-actions {
                width: 100%;
                margin-top: 26px;
            }

            .compact-login .mabolo-input-row {
                height: 44px;
                margin-bottom: 26px;
            }

            .compact-login .mabolo-input-row input {
                height: 42px;
                padding: 0 42px 0 36px;
                font-size: 16px;
            }

            .compact-login .mabolo-input-icon {
                left: 4px;
                top: 9px;
            }

            .compact-login .mabolo-eye {
                top: 0;
                right: -2px;
                width: 40px;
                height: 40px;
            }

            .compact-login .mabolo-error {
                margin: -18px 0 14px;
                color: #ff0000;
                font-size: 13px;
            }

            .compact-login .mabolo-login-button,
            .compact-login .mabolo-secondary-button {
                width: 150px;
                height: 40px;
                display: grid;
                place-items: center;
                margin-left: auto;
                border: 0;
                border-radius: 10px;
                background: #24963e;
                color: #ffffff;
                font-size: 18px;
                cursor: pointer;
                text-align: center;
            }

            .compact-login .mabolo-login-button:hover {
                background: #228b22;
            }

            .compact-login .mabolo-secondary-button {
                width: 100%;
                margin: 0;
                border: 1px solid #24963e;
                background: transparent;
                color: #24963e;
            }

            .compact-login .mabolo-auth-actions {
                display: grid;
                gap: 12px;
            }

            .compact-login .mabolo-auth-actions .mabolo-login-button {
                width: 100%;
                margin: 0;
            }

            @media (max-width: 480px) {
                .compact-login {
                    padding: 16px;
                }

                .compact-login .mabolo-panel {
                    padding: 28px 24px 26px;
                }
            }

            .gui-role-shell {
                min-height: 100vh;
                display: grid;
                grid-template-rows: 70px 1fr;
                background: #f9f9f9;
                color: #111111;
                direction: ltr;
            }

            .gui-role-top {
                display: grid;
                grid-template-columns: 280px 1fr;
                align-items: center;
                gap: 18px;
                border-bottom: 1px solid rgba(0, 0, 0, .05);
                background: #f9f9f9;
                padding: 0 18px;
                direction: rtl;
                position: relative;
            }

            .gui-brand {
                display: grid;
                grid-template-columns: 44px 1fr;
                align-items: center;
                gap: 10px;
            }

            .gui-mark {
                width: 44px;
                height: 44px;
                display: grid;
                place-items: center;
                border: 4px solid #24963e;
                border-radius: 10px;
                background: #ffffff;
                color: #24963e;
                font-size: 22px;
                font-weight: 700;
            }

            .gui-brand strong,
            .gui-top-title {
                display: block;
                color: #111111;
                font-weight: 700;
            }

            .gui-brand span span {
                display: block;
                color: #6f6f6f;
                font-size: 13px;
            }

            .gui-top-title {
                text-align: center;
                font-size: 16px;
            }

            .gui-role-body {
                display: grid;
                grid-template-columns: 180px 1fr;
                direction: ltr;
            }

            .gui-sidebar {
                min-height: calc(100vh - 70px);
                border-left: 1px solid rgba(242, 201, 76, .18);
                background:
                    linear-gradient(180deg, rgba(18, 63, 50, .98), rgba(8, 33, 26, .98)),
                    #123f32;
                color: #fffdf7;
                padding: 22px 14px;
                direction: rtl;
                box-shadow: 10px 0 34px rgba(18, 63, 50, .12);
            }

            .sidebar-logout {
                position: fixed;
                top: 14px;
                left: 18px;
                z-index: 80;
                display: flex;
                justify-content: flex-start;
                margin: 0;
                direction: ltr;
            }

            .gui-profile {
                display: grid;
                place-items: center;
                gap: 6px;
                margin-bottom: 22px;
                border: 1px solid rgba(242, 201, 76, .2);
                border-radius: 14px;
                background: rgba(255, 255, 255, .08);
                padding: 18px 10px;
                text-align: center;
            }

            .gui-avatar {
                width: 72px;
                height: 72px;
                display: grid;
                place-items: center;
                border-radius: 999px;
                background: #f2c94c;
                color: #123f32;
                font-size: 28px;
                font-weight: 700;
                box-shadow: 0 12px 28px rgba(0, 0, 0, .22);
            }

            .gui-profile strong {
                color: #ffffff;
                font-size: 14px;
            }

            .gui-profile span {
                color: rgba(255, 253, 247, .7);
                font-size: 12px;
            }

            .gui-nav {
                display: grid;
                gap: 7px;
            }

            .gui-nav a,
            .gui-btn {
                border-radius: 8px;
                font-weight: 700;
            }

            .gui-nav a {
                display: block;
                border: 1px solid rgba(255, 255, 255, .08);
                border-radius: 9px;
                background: rgba(255, 255, 255, .06);
                padding: 11px 12px;
                color: rgba(255, 253, 247, .88);
                transition: background .18s ease, color .18s ease, border-color .18s ease, transform .18s ease;
            }

            .gui-nav a:hover {
                border-color: rgba(242, 201, 76, .36);
                background: rgba(242, 201, 76, .14);
                color: #ffffff;
                transform: translateX(-2px);
            }

            .gui-main {
                min-width: 0;
                background: #ffffff;
                padding: 28px;
                direction: rtl;
            }

            .gui-page-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 18px;
                margin-bottom: 22px;
            }

            .gui-page-head h1 {
                margin: 0;
                color: #111111;
                font-size: 30px;
                line-height: 1.35;
            }

            .gui-page-head p {
                margin: 4px 0 0;
                color: #6f6f6f;
            }

            .gui-btn {
                min-height: 40px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border: 1px solid rgba(36, 150, 62, .28);
                background: #ffffff;
                color: #24963e;
                padding: 9px 14px;
                cursor: pointer;
                transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease, background .18s ease, color .18s ease;
                white-space: nowrap;
            }

            .gui-btn:hover,
            .gui-btn:focus-visible {
                border-color: rgba(36, 150, 62, .45);
                background: #f1fbf3;
                box-shadow: 0 10px 22px rgba(36, 150, 62, .12);
                transform: translateY(-1px);
            }

            .gui-btn.primary {
                border-color: #24963e;
                background: #24963e;
                color: #ffffff;
            }

            .gui-btn.primary:hover,
            .gui-btn.primary:focus-visible {
                background: #1f8436;
                color: #ffffff;
            }

            .gui-status {
                margin-bottom: 18px;
                border-right: 4px solid #24963e;
                border-radius: 8px;
                background: #f1fbf3;
                padding: 12px 14px;
                font-weight: 700;
            }

            .gui-stat-grid {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 16px;
                margin-bottom: 20px;
            }

            .gui-card,
            .gui-stat,
            .gui-panel {
                border: 1px solid rgba(0, 0, 0, .06);
                border-radius: 12px;
                background: #ffffff;
                box-shadow: 0 16px 38px rgba(0, 0, 0, .06);
            }

            .gui-card,
            .gui-stat,
            .gui-panel,
            .room-card {
                transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
            }

            .gui-card:hover,
            .gui-stat:hover,
            .gui-panel:hover,
            .room-card:hover {
                border-color: rgba(36, 150, 62, .16);
                box-shadow: 0 20px 46px rgba(18, 63, 50, .09);
            }

            .gui-stat {
                padding: 18px;
            }

            .gui-stat.featured {
                background: #24963e;
                color: #ffffff;
            }

            .gui-stat span {
                display: block;
                color: #6f6f6f;
                font-size: 13px;
                font-weight: 700;
            }

            .gui-stat.featured span,
            .gui-stat.featured p {
                color: rgba(255, 255, 255, .78);
            }

            .gui-stat strong {
                display: block;
                margin-block: 8px;
                font-size: 34px;
                line-height: 1;
            }

            .gui-stat p {
                margin: 0;
                color: #6f6f6f;
                font-size: 13px;
            }

            .gui-grid {
                display: grid;
                grid-template-columns: 1fr 360px;
                gap: 18px;
                align-items: start;
            }

            .gui-grid.equal {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .gui-panel,
            .gui-card {
                padding: 22px;
            }

            .gui-panel-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
                margin-bottom: 16px;
            }

            .gui-panel h2,
            .gui-panel h3,
            .gui-card h2 {
                margin: 0;
                color: #111111;
                font-size: 22px;
            }

            .gui-panel p,
            .gui-card p {
                margin: 4px 0 0;
                color: #6f6f6f;
            }

            .gui-form-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 16px;
            }

            .gui-field {
                display: grid;
                gap: 7px;
            }

            .gui-field.full,
            .gui-actions.full {
                grid-column: 1 / -1;
            }

            .gui-field label {
                color: #111111;
                font-weight: 700;
            }

            .gui-field input,
            .gui-field select,
            .gui-field textarea {
                width: 100%;
                border: 1px solid rgba(0, 0, 0, .12);
                border-radius: 9px;
                background: #ffffff;
                color: #111111;
                padding: 11px 13px;
                outline: none;
                transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
            }

            .gui-field textarea {
                min-height: 110px;
                resize: vertical;
            }

            .gui-field input:focus,
            .gui-field select:focus,
            .gui-field textarea:focus {
                border-color: #24963e;
                box-shadow: 0 0 0 3px rgba(36, 150, 62, .12);
            }

            .gui-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }

            .gui-list {
                display: grid;
                gap: 12px;
            }

            .gui-item {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
                border: 1px solid rgba(0, 0, 0, .06);
                border-radius: 10px;
                background: #fbfbfb;
                padding: 13px;
            }

            .gui-item span {
                display: block;
                color: #6f6f6f;
                font-size: 13px;
            }

            .gui-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 999px;
                background: #eaf8ed;
                color: #24963e;
                padding: 6px 10px;
                font-size: 13px;
                font-weight: 700;
                white-space: nowrap;
            }

            .gui-empty {
                border: 1px dashed rgba(36, 150, 62, .34);
                border-radius: 10px;
                padding: 20px;
                color: #6f6f6f;
                text-align: center;
            }

            .gui-section-gap {
                margin-top: 18px;
            }

            .dorm-toolbar {
                display: grid;
                grid-template-columns: 1fr auto auto;
                align-items: end;
                gap: 14px;
                margin-bottom: 18px;
            }

            .dorm-toolbar-title h2 {
                margin: 0;
                color: #111111;
                font-size: 23px;
            }

            .dorm-toolbar-title p {
                margin: 3px 0 0;
                color: #6f6f6f;
            }

            .dorm-filter {
                display: grid;
                grid-template-columns: minmax(230px, 1fr) 180px auto auto;
                gap: 10px;
            }

            .dorm-table-wrap {
                overflow-x: auto;
                border: 1px solid #bbeed0;
                border-radius: 12px;
                background: #ffffff;
                box-shadow: 0 16px 38px rgba(0, 0, 0, .05);
            }

            .dorm-table {
                width: 100%;
                min-width: 980px;
                border-collapse: collapse;
                direction: rtl;
            }

            .dorm-table th {
                background: #2ecc71;
                color: #ffffff;
                padding: 15px 14px;
                text-align: right;
                font-weight: 700;
                position: sticky;
                top: 0;
                z-index: 2;
            }

            .dorm-table td {
                border-bottom: 1px solid #bbeed0;
                background: #d5f4e2;
                color: #111111;
                padding: 13px 14px;
                vertical-align: top;
            }

            .dorm-table tr:nth-child(even) td {
                background: #c0efd4;
            }

            .dorm-table tr:hover td {
                background: #a5ebc2;
            }

            .dorm-inline-meta {
                display: block;
                color: #315241;
                font-size: 13px;
                margin-top: 3px;
            }

            .dorm-form-card {
                max-width: 980px;
                margin-inline: auto;
            }

            .dorm-form-section {
                margin-top: 22px;
                border-top: 1px solid rgba(0, 0, 0, .08);
                padding-top: 18px;
            }

            .dorm-form-section.full {
                grid-column: 1 / -1;
            }

            .dorm-form-section:first-child {
                margin-top: 0;
                border-top: 0;
                padding-top: 0;
            }

            .dorm-section-title {
                margin: 0 0 14px;
                color: #111111;
                font-size: 20px;
            }

            .room-card-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 16px;
            }

            .room-card {
                border: 1px solid #bbeed0;
                border-radius: 12px;
                background: linear-gradient(180deg, #ffffff, #f3fbf6);
                padding: 18px;
                box-shadow: 0 14px 32px rgba(0, 0, 0, .05);
            }

            .room-card-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                margin-bottom: 14px;
            }

            .room-card h2 {
                margin: 0;
                color: #111111;
                font-size: 22px;
            }

            .room-progress {
                height: 10px;
                overflow: hidden;
                border-radius: 999px;
                background: #d5f4e2;
                margin: 14px 0;
            }

            .room-progress span {
                display: block;
                height: 100%;
                border-radius: inherit;
                background: #2ecc71;
            }

            @media (max-width: 980px) {
                .gui-role-top {
                    grid-template-columns: 1fr auto;
                }

                .gui-top-title {
                    display: none;
                }

                .gui-role-body {
                    grid-template-columns: 1fr;
                }

                .gui-sidebar {
                    min-height: auto;
                }

                .gui-nav {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .gui-stat-grid,
                .gui-grid,
                .gui-grid.equal,
                .gui-form-grid,
                .dorm-filter,
                .dorm-toolbar,
                .room-card-grid {
                    grid-template-columns: 1fr;
                }

                .gui-page-head,
                .gui-panel-head,
                .gui-item {
                    align-items: stretch;
                    flex-direction: column;
                }
            }
        </style>
        <link rel="stylesheet" href="{{ asset('css/fanous-modern.css') }}">
    </head>
    <body class="theme-{{ auth()->user()?->theme ?? 'light' }} locale-{{ \App\Support\Locale::current() }}">
        <div class="page">
            @if (trim($__env->yieldContent('bare_page')) !== 'true')
            <header class="site-header">
                <div class="container header-inner">
                    <a class="brand" href="{{ route('home') }}" aria-label="Fanous">
                        <span class="brand-mark">ف</span>
                        <span>فانوس</span>
                    </a>

                    <nav class="nav" aria-label="اصلی">
                        <a href="{{ route('home') }}">خانه</a>
                        <a href="{{ route('transparency') }}">شفافیت</a>
                        @auth
                            <a href="{{ route('dashboard') }}">داشبورد</a>
                            @if (in_array(auth()->user()->role, \App\Models\User::dormRecordViewerRoles(), true))
                                <a href="{{ route('dorm.students.index') }}">محصلین</a>
                            @endif
                            @if (in_array(auth()->user()->role, \App\Models\User::studentRepresentativeRoles(), true))
                                <a href="{{ route('representative.index') }}">نماینده</a>
                            @endif
                            @if (in_array(auth()->user()->role, \App\Models\User::purchaserRoles(), true))
                                <a href="{{ route('purchaser.index') }}">خرج‌آور</a>
                            @endif
                            @if (in_array(auth()->user()->role, \App\Models\User::libraryViewerRoles(), true))
                                <a href="{{ route('library.index') }}">کتاب‌خانه</a>
                            @endif
                            @if (auth()->user()->canAccessAdmin())
                                <a href="{{ route('admin.dashboard') }}">مدیریت</a>
                            @endif
                        @endauth
                    </nav>

                    @auth
                        <div class="header-actions">
                            <button class="language-switch" type="button" data-theme-toggle><span data-theme-label>Dark mode</span></button>
                            <a class="login-button" href="{{ route('dashboard') }}">داشبورد</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="login-button" type="submit">خروج</button>
                            </form>
                        </div>
                    @else
                        <div class="header-actions">
                            <button class="language-switch" type="button" data-theme-toggle><span data-theme-label>Dark mode</span></button>
                            <a class="login-button" href="{{ route('login') }}">ورود</a>
                        </div>
                    @endauth
                </div>
            </header>
            @endif

            <main>
                @yield('content')
            </main>

            @if (trim($__env->yieldContent('bare_page')) !== 'true')
            <footer class="site-footer">
                <div class="container">
                    سیستم تمرینی مدیریت لیلیه و کتاب‌خانه فانوس
                </div>
            </footer>
            @endif
        </div>
        <script src="{{ asset('js/fanous-i18n.js') }}"></script>
    </body>
</html>
