@php
  $tileId          = $tileId ?? null;
  $poseLabel       = $poseLabel ?? '';
  $placeholderPath = $placeholderPath ?? '';
  $sectionType     = $sectionType ?? 'photograph';
@endphp

<div class="media-tile"
     :class="{
       'media-tile--filled':    tiles['{{ $tileId }}'].filled,
       'media-tile--drag-over': tiles['{{ $tileId }}'].isDragOver
     }"
     :draggable="tiles['{{ $tileId }}'].filled ? 'true' : 'false'"
     @click="onTileClick('{{ $tileId }}', $event)"
     @dragstart="onTileDragStart($event, '{{ $tileId }}')"
     @dragend="dragSourceTileId = null"
     @dragover.prevent="onTileDragOver($event, '{{ $tileId }}')"
     @dragleave="onTileDragLeave('{{ $tileId }}')"
     @drop.prevent="onTileDrop($event, '{{ $tileId }}')"
     tabindex="0"
     role="button"
     aria-label="{{ $poseLabel }}">

  {{-- Placeholder shown when empty --}}
  <img x-show="!tiles['{{ $tileId }}'].filled"
       src="{{ $placeholderPath }}"
       class="media-tile__placeholder"
       alt="{{ $poseLabel }}"
       style="display:none;">

  {{-- Preview shown when filled --}}
  <img x-show="tiles['{{ $tileId }}'].filled"
       :src="tiles['{{ $tileId }}'].previewUrl || ''"
       class="media-tile__preview"
       alt="{{ $poseLabel }}"
       style="display:none;">

  <div class="media-tile__label">{{ $poseLabel }}</div>

  @if($sectionType === 'photograph')
    {{-- AI QC badge — only visible for photograph tiles with an AI signal. --}}
    <div class="media-tile__ai-badge"
         x-show="tiles['{{ $tileId }}'].filled && (tiles['{{ $tileId }}'].aiState === 'checking' || tiles['{{ $tileId }}'].aiWarning)"
         :class="{
           'media-tile__ai-badge--checking': tiles['{{ $tileId }}'].aiState === 'checking',
           'media-tile__ai-badge--warn':     tiles['{{ $tileId }}'].aiState === 'warn'
         }"
         :title="tiles['{{ $tileId }}'].aiWarning || 'Checking with AI…'"
         @click.stop
         style="display:none;">
      <template x-if="tiles['{{ $tileId }}'].aiState === 'checking'">
        <span class="media-tile__ai-badge-text">
          <i data-feather="loader"></i> AI
        </span>
      </template>
      <template x-if="tiles['{{ $tileId }}'].aiState === 'warn'">
        <span class="media-tile__ai-badge-text">
          <i data-feather="alert-triangle"></i>
          <span x-text="tiles['{{ $tileId }}'].aiWarning"></span>
        </span>
      </template>
    </div>
  @endif

  @if($sectionType === 'photograph')
    {{-- Camera entry — only when empty AND device exposes a camera --}}
    <button type="button"
            class="media-tile__camera-btn"
            x-show="!tiles['{{ $tileId }}'].filled && cameraSupported"
            @click.stop="onTileCameraClick('{{ $tileId }}')"
            aria-label="Take photo for {{ $poseLabel }}"
            title="Use camera"
            style="display:none;">
      <i data-feather="camera"></i>
    </button>
  @endif

  {{-- Hover-to-remove overlay — only when tile has content --}}
  <button type="button"
          class="media-tile__remove-btn"
          x-show="tiles['{{ $tileId }}'].filled"
          @click.stop="removeTile('{{ $tileId }}')"
          aria-label="Remove {{ $poseLabel }}"
          title="Remove {{ $poseLabel }}"
          style="display:none;">
    <i data-feather="x"></i>
  </button>

  {{-- Hidden file input — triggered programmatically on empty-tile click.
       @click.stop is critical: input.click() dispatches a synthetic click
       that bubbles up to the .media-tile div's @click="onTileClick(...)"
       and would re-fire _openFilePicker, queuing a second OS file dialog.
       (display:none used to suppress this because the element was out of
       the event tree; the visually-hidden replacement keeps it in.) --}}
  <input type="file"
         id="tile-file-{{ $tileId }}"
         class="media-tile__file-input"
         :accept="acceptAttribute"
         @click.stop
         @change="onFileInputChange('{{ $tileId }}')">
</div>
