{@--
  Komponen Form Select — Dropdown standar
  Params:
    - label: string — Label select
    - name: string — Name attribute
    - options: array — ['value' => 'Label', ...] atau koleksi
    - value: mixed — Nilai terpilih
    - placeholder: string — Teks placeholder opsi kosong
    - required: boolean (default: false)
    - disabled: boolean (default: false)
    - error: string|null — Pesan validasi error
    - help: string|null — Teks bantuan
    - icon: string — FontAwesome icon (opsional)
    - class: string — Kelas CSS tambahan
--@}
@props([
    'label' => null,
    'name' => null,
    'options' => [],
    'value' => null,
    'placeholder' => 'Pilih salah satu',
    'required' => false,
    'disabled' => false,
    'error' => null,
    'help' => null,
    'icon' => null,
    'class' => null,
])

<?php
$inputId = $name;
$hasError = !empty($error);
$selectClass = 'form-select ' . ($class ?? '');
if ($hasError) $selectClass .= ' is-invalid';
?>

<div class="form-group mb-3" id="group-{{ $inputId }}">
    @if($label)
        <label for="{{ $inputId }}" class="form-label fw-medium {{ $hasError ? 'text-rose-600' : 'text-slate-700' }}">
            {{ $label }}
            @if($required)<span class="text-rose-500 ms-1">*</span>@endif
        </label>
    @endif

    <div class="input-wrapper position-relative">
        @if($icon)
            <span class="input-icon absolute left-0 top-0 d-flex align-items-center h-100 pl-3 text-slate-400">
                <i class="{{ $icon }}"></i>
            </span>
        @endif

        <select name="{{ $name }}" id="{{ $inputId }}" class="{{ $selectClass }}"
            @if($required) required @endif
            @if($disabled) disabled @endif>
            @if($placeholder)
                <option value="" {{ old($name, $value) === '' ? 'selected' : '' }} disabled>{{ $placeholder }}</option>
            @endif
            @foreach($options as $optValue => $optLabel)
                <option value="{{ $optValue }}" {{ old($name, $value) == $optValue ? 'selected' : '' }}>
                    {{ $optLabel }}
                </option>
            @endforeach
        </select>
    </div>

    @if($hasError)
        <div class="invalid-feedback d-block">
            <i class="fas fa-exclamation-circle me-1"></i>{{ $error }}
        </div>
    @endif

    @if($help)
        <div class="form-text">{{ $help }}</div>
    @endif
</div>

@push('styles')
<style>
.form-select {
  width: 100%;
  padding: 0.5rem 0.75rem;
  font-size: 0.875rem;
  font-family: var(--font-base);
  color: var(--color-text-primary);
  background-color: var(--color-surface);
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-sm);
  transition: border-color 0.2s, box-shadow 0.2s;
  appearance: none;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  background-size: 16px 12px;
}

.form-select:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.1);
  outline: none;
}

.form-select.is-invalid {
  border-color: var(--color-danger);
}
</style>
@endpushOnce
