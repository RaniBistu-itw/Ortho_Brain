@extends('layouts.app')

@section('title', 'Help')

@section('content')
<style>
    .doc-help {
        max-width: 960px;
        margin: 0 auto;
        padding: 1.25rem 0.5rem 3rem;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        color: #1f2937;
    }
    .doc-help__hero {
        background: linear-gradient(135deg, #e3f4fa 0%, #eafbe0 100%);
        border-radius: 1rem;
        padding: 1.75rem 1.75rem 1.5rem;
        margin-bottom: 1.5rem;
    }
    .doc-help__eyebrow {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #5bc0de;
        margin-bottom: 0.35rem;
    }
    .doc-help__title { font-size: 1.55rem; font-weight: 700; margin: 0 0 0.35rem; color: #111827; }
    .doc-help__sub   { font-size: 0.95rem; color: #4b5563; margin: 0; max-width: 640px; }

    .doc-help__grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 1rem;
    }
    .doc-help__card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 0.85rem;
        padding: 1.2rem 1.25rem;
        box-shadow: 0 1px 2px rgba(24, 28, 40, 0.04);
        display: flex; flex-direction: column;
    }
    .doc-help__card-icon {
        width: 40px; height: 40px;
        border-radius: 0.65rem;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.1rem;
        margin-bottom: 0.9rem;
    }
    .doc-help__card--a .doc-help__card-icon { background: rgba(91, 192, 222, 0.14); color: #1e6c85; }
    .doc-help__card--b .doc-help__card-icon { background: rgba(140, 198, 63, 0.14); color: #5a8f21; }
    .doc-help__card--c .doc-help__card-icon { background: rgba(255, 159, 67, 0.14); color: #b9681a; }
    .doc-help__card-title { font-size: 1rem; font-weight: 700; margin: 0 0 0.4rem; color: #111827; }
    .doc-help__card p { font-size: 0.9rem; line-height: 1.45; color: #4b5563; margin: 0 0 0.6rem; }
    .doc-help__card ol { padding-left: 1.15rem; margin: 0 0 0.2rem; }
    .doc-help__card ol li { font-size: 0.9rem; line-height: 1.5; color: #374151; margin-bottom: 0.25rem; }

    .doc-help__contact {
        margin-top: 1.75rem;
        padding: 1rem 1.25rem;
        background: #f8f8fb;
        border: 1px solid #e5e7eb;
        border-radius: 0.85rem;
        display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;
    }
    .doc-help__contact-text { font-size: 0.9rem; color: #4b5563; margin: 0; }
    .doc-help__contact-link {
        font-size: 0.9rem; font-weight: 600;
        color: #1e6c85; text-decoration: none;
        padding: 0.45rem 0.9rem; border-radius: 0.45rem;
        background: rgba(91, 192, 222, 0.12);
    }
    .doc-help__contact-link:hover { background: rgba(91, 192, 222, 0.22); }
</style>

<section class="doc-help">
    <div class="doc-help__hero">
        <div class="doc-help__eyebrow">Help &amp; FAQ</div>
        <h1 class="doc-help__title">Getting started with orthoBrain</h1>
        <p class="doc-help__sub">Short answers to the most common questions about working with cases, practices, and your account.</p>
    </div>

    <div class="doc-help__grid">
        <div class="doc-help__card doc-help__card--a">
            <span class="doc-help__card-icon"><i class="bi bi-folder-plus"></i></span>
            <h3 class="doc-help__card-title">Creating a case</h3>
            <p>Cases are the core of your workflow &mdash; each one represents a patient treatment plan.</p>
            <ol>
                <li>Go to <strong>Cases</strong> from the left sidebar.</li>
                <li>Click <strong>New Case</strong> in the top-right.</li>
                <li>Fill in the prescription details and submit when ready.</li>
            </ol>
        </div>

        <div class="doc-help__card doc-help__card--b">
            <span class="doc-help__card-icon"><i class="bi bi-arrow-left-right"></i></span>
            <h3 class="doc-help__card-title">Switching practices</h3>
            <p>If you work at more than one approved practice, you can switch between them anytime.</p>
            <ol>
                <li>Click the practice name in the top bar (e.g. <em>Working at XYZ Dentals</em>).</li>
                <li>Pick another practice from the dropdown.</li>
                <li>Your case list updates to show only that practice&rsquo;s cases.</li>
            </ol>
        </div>

        <div class="doc-help__card doc-help__card--c">
            <span class="doc-help__card-icon"><i class="bi bi-building-add"></i></span>
            <h3 class="doc-help__card-title">Joining a new practice</h3>
            <p>You can request to join additional practices at any time. An admin reviews every request.</p>
            <ol>
                <li>Open <strong>My Practices</strong> from your profile menu.</li>
                <li>Use <em>Request another practice</em> and search by name.</li>
                <li>Wait for admin approval &mdash; you&rsquo;ll get an email and a bell notification.</li>
            </ol>
        </div>
    </div>

    <div class="doc-help__contact">
        <p class="doc-help__contact-text">Can&rsquo;t find what you&rsquo;re looking for? Reach out to the orthoBrain team.</p>
        <a class="doc-help__contact-link" href="mailto:support@orthobrain.local">support@orthobrain.local</a>
    </div>
</section>
@endsection
