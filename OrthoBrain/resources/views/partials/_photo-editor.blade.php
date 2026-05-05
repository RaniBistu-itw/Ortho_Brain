{{--
    Reusable photo editor modal (Cropper.js + optional webcam capture).

    Two modes:
      1) Direct-submit  — the modal owns a <form> that POSTs the cropped file
                           to the given `action` endpoint.
      2) Callback        — pass `callback` (name of a global JS function) and
                           the modal hands the cropped File to that function
                           instead of submitting. Use this when the cropper
                           feeds a parent form (e.g. multi-image product form).

    Direct-submit usage:
        @include('partials._photo-editor', [
            'id'         => 'avatar-editor',
            'title'      => 'Edit Profile Photo',
            'action'     => route('doctor.profile.photo'),
            'fieldName'  => 'avatar',
            'aspect'     => 1,
            'enableCam'  => true,
        ])

    Callback usage:
        @include('partials._photo-editor', [
            'id'         => 'product-image-editor',
            'title'      => 'Edit Product Image',
            'callback'   => 'onProductImageCropped', // window.onProductImageCropped(file, editorId)
            'aspect'     => null,
            'enableCam'  => false,
        ])

    Open from anywhere:   openPhotoEditor('avatar-editor')
--}}

@once
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.css">
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.js" defer></script>
<style>
    .photo-editor-backdrop {
        position: fixed; inset: 0; background: rgba(0,0,0,.55);
        display: none; align-items: center; justify-content: center; z-index: 1055;
    }
    .photo-editor-backdrop.open { display: flex; }
    .photo-editor-dialog {
        background: #fff; border-radius: .5rem; width: min(640px, 94vw);
        max-height: 92vh; overflow: auto; box-shadow: 0 8px 32px rgba(0,0,0,.25);
    }
    .photo-editor-header {
        padding: .75rem 1rem; border-bottom: 1px solid #ebe9f1;
        display: flex; justify-content: space-between; align-items: center;
    }
    .photo-editor-header h5 { margin: 0; font-size: 1rem; font-weight: 600; color: #5e5873; }
    .photo-editor-close { border: 0; background: transparent; font-size: 1.25rem; cursor: pointer; color: #6e6b7b; }
    .photo-editor-body { padding: 1rem; }
    .photo-editor-stage {
        background: #f4f4f5; border-radius: .358rem; min-height: 280px;
        display: flex; align-items: center; justify-content: center; position: relative;
    }
    .photo-editor-stage img { max-width: 100%; display: block; }
    .photo-editor-stage video { max-width: 100%; border-radius: .358rem; transform: scaleX(-1); }
    .photo-editor-picker { text-align: center; padding: 2rem; color: #6e6b7b; }
    .photo-editor-picker .icon { font-size: 2.5rem; color: #b9b9c3; margin-bottom: .5rem; }
    .photo-editor-picker button { margin: .25rem; }
    .photo-editor-toolbar {
        display: flex; flex-wrap: wrap; gap: .25rem; margin-top: .75rem; justify-content: center;
    }
    .photo-editor-toolbar button {
        border: 1px solid #d8d6de; background: #fff; color: #5e5873;
        padding: .35rem .6rem; border-radius: .358rem; cursor: pointer; font-size: .85rem;
        display: inline-flex; align-items: center; gap: .3rem;
    }
    .photo-editor-toolbar button:hover { background: #f8f8f8; }
    .photo-editor-footer {
        padding: .75rem 1rem; border-top: 1px solid #ebe9f1;
        display: flex; justify-content: flex-end; gap: .5rem;
    }
    .btn-pe-primary { background: #5bc0de; color: #fff; border: 0; padding: .5rem 1rem; border-radius: .358rem; cursor: pointer; }
    .btn-pe-primary:hover { background: #46b8da; }
    .btn-pe-primary:disabled { opacity: .5; cursor: not-allowed; }
    .btn-pe-secondary { background: #fff; color: #5e5873; border: 1px solid #d8d6de; padding: .5rem 1rem; border-radius: .358rem; cursor: pointer; }
    .photo-editor-error { color: #ea5455; font-size: .85rem; margin-top: .5rem; text-align: center; }
</style>

<script>
    // Global registry + launcher for all photo editors on the page.
    window.__photoEditors = window.__photoEditors || {};
    window.openPhotoEditor = function (id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.add('open');
        // Reset to picker state
        const ed = window.__photoEditors[id];
        if (ed) ed.reset();
    };
    window.closePhotoEditor = function (id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.remove('open');
        const ed = window.__photoEditors[id];
        if (ed) ed.teardown();
    };
</script>
@endonce

@php $peCallback = $callback ?? null; @endphp
<div id="{{ $id }}" class="photo-editor-backdrop"
     data-id="{{ $id }}"
     data-aspect="{{ $aspect ?? 'null' }}"
     data-cam="{{ !empty($enableCam) ? '1' : '0' }}"
     data-callback="{{ $peCallback ?? '' }}">
    <div class="photo-editor-dialog">
        <div class="photo-editor-header">
            <h5>{{ $title ?? 'Edit Photo' }}</h5>
            <button type="button" class="photo-editor-close" onclick="closePhotoEditor('{{ $id }}')">&times;</button>
        </div>

        @if($peCallback)
            <div id="{{ $id }}-form" class="pe-shell">
                <input type="file" class="pe-file" accept="image/jpeg,image/png,image/webp" hidden>
        @else
            <form id="{{ $id }}-form" method="POST" action="{{ $action }}" enctype="multipart/form-data" class="pe-shell">
                @csrf
                <input type="file" class="pe-file" name="{{ $fieldName }}" accept="image/jpeg,image/png,image/webp" hidden>
        @endif
            <div class="photo-editor-body">
                <div class="photo-editor-stage pe-stage">
                    <div class="photo-editor-picker pe-picker">
                        <div class="icon"><i class="bi bi-image"></i></div>
                        <div style="margin-bottom:.75rem">Choose a file{{ !empty($enableCam) ? ' or use your webcam' : '' }}</div>
                        <button type="button" class="btn-pe-primary pe-choose"><i class="bi bi-folder2-open"></i> Choose file</button>
                        @if(!empty($enableCam))
                            <button type="button" class="btn-pe-secondary pe-webcam"><i class="bi bi-camera-video"></i> Use webcam</button>
                        @endif
                    </div>
                    <img class="pe-img" alt="" hidden>
                    <video class="pe-video" autoplay playsinline hidden></video>
                </div>

                <div class="photo-editor-toolbar pe-toolbar" hidden>
                    <button type="button" class="pe-rot-l" title="Rotate left"><i class="bi bi-arrow-counterclockwise"></i></button>
                    <button type="button" class="pe-rot-r" title="Rotate right"><i class="bi bi-arrow-clockwise"></i></button>
                    <button type="button" class="pe-flip-h" title="Flip horizontal"><i class="bi bi-symmetry-vertical"></i></button>
                    <button type="button" class="pe-flip-v" title="Flip vertical"><i class="bi bi-symmetry-horizontal"></i></button>
                    <button type="button" class="pe-zoom-in" title="Zoom in"><i class="bi bi-zoom-in"></i></button>
                    <button type="button" class="pe-zoom-out" title="Zoom out"><i class="bi bi-zoom-out"></i></button>
                    <button type="button" class="pe-reset" title="Reset"><i class="bi bi-arrow-clockwise"></i> Reset</button>
                    <button type="button" class="pe-change" title="Choose another file"><i class="bi bi-folder2-open"></i> Change</button>
                </div>

                <div class="photo-editor-toolbar pe-cam-tools" hidden>
                    <button type="button" class="pe-snap btn-pe-primary"><i class="bi bi-camera"></i> Capture</button>
                    <button type="button" class="pe-switch-cam"><i class="bi bi-arrow-repeat"></i> Switch camera</button>
                    <button type="button" class="pe-cancel-cam">Cancel</button>
                </div>

                <div class="photo-editor-error pe-error" hidden></div>
            </div>

            <div class="photo-editor-footer">
                <button type="button" class="btn-pe-secondary" onclick="closePhotoEditor('{{ $id }}')">Cancel</button>
                <button type="button" class="btn-pe-primary pe-save" disabled>Save photo</button>
            </div>
        @if($peCallback)
            </div>
        @else
            </form>
        @endif
    </div>
</div>

@once
<script>
    (function () {
        function initEditor(root) {
            const id       = root.dataset.id;
            const aspect   = root.dataset.aspect === 'null' || root.dataset.aspect === '' ? NaN : parseFloat(root.dataset.aspect);
            const camOn    = root.dataset.cam === '1';
            const cbName   = root.dataset.callback || '';
            const picker   = root.querySelector('.pe-picker');
            const img      = root.querySelector('.pe-img');
            const video    = root.querySelector('.pe-video');
            const tools    = root.querySelector('.pe-toolbar');
            const camTools = root.querySelector('.pe-cam-tools');
            const fileIn   = root.querySelector('.pe-file');
            const saveBtn  = root.querySelector('.pe-save');
            const err      = root.querySelector('.pe-error');
            const form     = root.querySelector('form');

            let cropper = null;
            let stream  = null;
            let facing  = 'user';

            function showErr(msg) { err.textContent = msg; err.hidden = !msg; }
            function reset() {
                destroyCropper();
                stopStream();
                picker.hidden = false;
                img.hidden = true; img.removeAttribute('src');
                video.hidden = true;
                tools.hidden = true; camTools.hidden = true;
                saveBtn.disabled = true;
                showErr('');
            }
            function teardown() { stopStream(); destroyCropper(); }
            function destroyCropper() { if (cropper) { cropper.destroy(); cropper = null; } }
            function stopStream() { if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; } }

            function loadImage(src) {
                destroyCropper();
                picker.hidden = true;
                video.hidden = true; stopStream(); camTools.hidden = true;
                img.src = src;
                img.hidden = false;
                tools.hidden = false;
                saveBtn.disabled = false;
                cropper = new Cropper(img, {
                    aspectRatio: isNaN(aspect) ? NaN : aspect,
                    viewMode: 1, autoCropArea: 0.9, background: false,
                    movable: true, zoomable: true, rotatable: true, scalable: true,
                });
            }

            // File picker flow
            root.querySelector('.pe-choose').addEventListener('click', () => fileIn.click());
            fileIn.addEventListener('change', e => {
                const f = e.target.files?.[0]; if (!f) return;
                if (f.size > 2 * 1024 * 1024) return showErr('File is larger than 2 MB.');
                const reader = new FileReader();
                reader.onload = ev => loadImage(ev.target.result);
                reader.readAsDataURL(f);
            });
            root.querySelector('.pe-change').addEventListener('click', () => fileIn.click());

            // Cropper toolbar
            root.querySelector('.pe-rot-l').addEventListener('click', () => cropper?.rotate(-90));
            root.querySelector('.pe-rot-r').addEventListener('click', () => cropper?.rotate(90));
            root.querySelector('.pe-flip-h').addEventListener('click', () => cropper && cropper.scaleX(-cropper.getData().scaleX || -1));
            root.querySelector('.pe-flip-v').addEventListener('click', () => cropper && cropper.scaleY(-cropper.getData().scaleY || -1));
            root.querySelector('.pe-zoom-in').addEventListener('click', () => cropper?.zoom(0.1));
            root.querySelector('.pe-zoom-out').addEventListener('click', () => cropper?.zoom(-0.1));
            root.querySelector('.pe-reset').addEventListener('click', () => cropper?.reset());

            // Webcam flow
            const webcamBtn = root.querySelector('.pe-webcam');
            if (webcamBtn && camOn && navigator.mediaDevices?.getUserMedia) {
                webcamBtn.addEventListener('click', startCam);
                root.querySelector('.pe-snap').addEventListener('click', snap);
                root.querySelector('.pe-switch-cam').addEventListener('click', () => { facing = facing === 'user' ? 'environment' : 'user'; startCam(); });
                root.querySelector('.pe-cancel-cam').addEventListener('click', reset);
            } else if (webcamBtn) {
                webcamBtn.hidden = true;
            }

            async function startCam() {
                showErr('');
                stopStream();
                try {
                    stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: facing, width: 1280, height: 720 } });
                    video.srcObject = stream;
                    picker.hidden = true; img.hidden = true; tools.hidden = true;
                    video.hidden = false; camTools.hidden = false;
                } catch (e) {
                    showErr('Camera blocked or unavailable — choose a file instead.');
                    reset();
                }
            }
            function snap() {
                const canvas = document.createElement('canvas');
                canvas.width  = video.videoWidth;
                canvas.height = video.videoHeight;
                const ctx = canvas.getContext('2d');
                // Un-mirror the captured frame (preview is mirrored via CSS)
                ctx.translate(canvas.width, 0); ctx.scale(-1, 1);
                ctx.drawImage(video, 0, 0);
                const dataUrl = canvas.toDataURL('image/jpeg', 0.92);
                stopStream();
                loadImage(dataUrl);
            }

            // Save — crop/rotate → blob. Either hand to callback or POST via inner form.
            saveBtn.addEventListener('click', async () => {
                if (!cropper) return;
                saveBtn.disabled = true;
                cropper.getCroppedCanvas({ maxWidth: 1600, maxHeight: 1600 }).toBlob(blob => {
                    if (!blob) { showErr('Crop failed — try again.'); saveBtn.disabled = false; return; }
                    const file = new File([blob], 'upload.jpg', { type: 'image/jpeg' });
                    if (cbName && typeof window[cbName] === 'function') {
                        try { window[cbName](file, id); } finally { closePhotoEditor(id); }
                        return;
                    }
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    fileIn.files = dt.files;
                    form.submit();
                }, 'image/jpeg', 0.9);
            });

            window.__photoEditors[id] = { reset, teardown };
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.photo-editor-backdrop').forEach(initEditor);
        });
    })();
</script>
@endonce
