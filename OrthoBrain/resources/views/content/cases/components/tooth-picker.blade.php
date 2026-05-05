{{-- Reusable tooth picker — use with @include, passing instanceId and selectedInitial.
     instanceId      — unique string ('tmr' or 'ar') used in tooth-picker-change events
     selectedInitial — JSON-encoded array of pre-selected tooth IDs, default '[]'
--}}
@php
$selectedInitial = $selectedInitial ?? '[]';
$upperPrimary = ['A','B','C','D','E', null, 'F','G','H','I','J'];
$upperAdult   = ['1','2','3','4','5','6','7','8', null, '9','10','11','12','13','14','15','16'];
$lowerAdult   = ['32','31','30','29','28','27','26','25', null, '24','23','22','21','20','19','18','17'];
$lowerPrimary = ['T','S','R','Q','P', null, 'O','N','M','L','K'];
@endphp

<div x-data="toothPicker({{ $selectedInitial }})"
     @tooth-picker-hydrate.window="if ($event.detail.instance === $root.dataset.instance) selected = ($event.detail.selected || []).slice()"
     data-instance="{{ $instanceId }}"
     class="tooth-picker mt-75">

  <div class="tooth-picker__arch-label text-muted text-center mb-25">Upper (Maxillary)</div>

  {{-- Upper primary --}}
  <div class="tooth-picker__row tooth-picker__row--primary">
    @foreach($upperPrimary as $tooth)
      @if($tooth === null)
        <div class="tooth-picker__midline"></div>
      @else
        <button type="button" class="tooth-picker__tooth"
                :class="{ 'tooth-picker__tooth--selected': isSelected('{{ $tooth }}') }"
                @click="toggle('{{ $tooth }}')">{{ $tooth }}</button>
      @endif
    @endforeach
  </div>

  {{-- Upper adult --}}
  <div class="tooth-picker__row">
    @foreach($upperAdult as $tooth)
      @if($tooth === null)
        <div class="tooth-picker__midline"></div>
      @else
        <button type="button" class="tooth-picker__tooth"
                :class="{ 'tooth-picker__tooth--selected': isSelected('{{ $tooth }}') }"
                @click="toggle('{{ $tooth }}')">{{ $tooth }}</button>
      @endif
    @endforeach
  </div>

  <div class="tooth-picker__arch-divider"></div>

  {{-- Lower adult --}}
  <div class="tooth-picker__row">
    @foreach($lowerAdult as $tooth)
      @if($tooth === null)
        <div class="tooth-picker__midline"></div>
      @else
        <button type="button" class="tooth-picker__tooth"
                :class="{ 'tooth-picker__tooth--selected': isSelected('{{ $tooth }}') }"
                @click="toggle('{{ $tooth }}')">{{ $tooth }}</button>
      @endif
    @endforeach
  </div>

  {{-- Lower primary --}}
  <div class="tooth-picker__row tooth-picker__row--primary">
    @foreach($lowerPrimary as $tooth)
      @if($tooth === null)
        <div class="tooth-picker__midline"></div>
      @else
        <button type="button" class="tooth-picker__tooth"
                :class="{ 'tooth-picker__tooth--selected': isSelected('{{ $tooth }}') }"
                @click="toggle('{{ $tooth }}')">{{ $tooth }}</button>
      @endif
    @endforeach
  </div>

  <div class="tooth-picker__arch-label text-muted text-center mt-25">Lower (Mandibular)</div>

</div>
