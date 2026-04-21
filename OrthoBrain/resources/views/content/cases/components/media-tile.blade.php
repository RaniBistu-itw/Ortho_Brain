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
     @click="onTileClick('{{ $tileId }}')"
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

  {{-- Hidden file input — triggered programmatically on empty-tile click --}}
  <input type="file"
         id="tile-file-{{ $tileId }}"
         class="media-tile__file-input"
         :accept="acceptAttribute"
         @change="onFileInputChange('{{ $tileId }}')">
</div>
