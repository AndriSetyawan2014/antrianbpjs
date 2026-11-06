{{--
  Komponen Form Group — Label + Input + Error Message + Help Text
  Params:
    - label: string — Label input
    - name: string — Name attribute
    - type: text|email|password|number|date|datetime-local|file|hidden (default: text)
    - value: mixed — Nilai input
    - placeholder: string — Placeholder
    - required: boolean (default: false)
    - disabled: boolean (default: false)
    - readonly: boolean (default: false)
    - error: string|null — Pesan validasi error
    - help: string|null — Teks bantuan di bawah input
    - icon: string — FontAwesome icon di sebelah kiri input (opsional)
    - class: string — Kelas CSS tambahan untuk input
    - wrapperClass: string — Kelas CSS tambahan untuk wrapper
    - id: string — ID custom (default: otomatis dari name)
--}}
@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'error' => null,
    'help' => null,
    'icon' => null,
    'class' => null,
    'wrapperClass' => null,
    'id' => null,
])

<?php
$inputId = $id ?? $name;
$hasError = !empty($error);
$inputClass = 'form-control ' . ($class ?? '');
if ($hasError) $inputClass .= ' is-invalid';
if ($type === 'file') $inputClass = 'form-control ' . ($class ?? '');
?>

<div class="form-group mb-3 {{ $wrapperClass ?? '' }}" @if($inputId) id="group-{{ $inputId }}" @endif>
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

        @if($type === 'textarea')
            <textarea name="{{ $name }}" id="{{ $inputId }}" class="{{ $inputClass }}" 
                @if($placeholder) placeholder="{{ $placeholder }}" @endif
                @if($required) required @endif
                @if($disabled) disabled @endif
                @if($readonly) readonly @endif
                rows="3">{{ $value ?? '' }}</textarea>
        @elseif($type === 'file')
            <input type="file" name="{{ $name }}" id="{{ $inputId }}" class="{{ $inputClass }}"
                @if($required) required @endif
                @if($disabled) disabled @endif
                @if($readonly) readonly @endif>
        @else
            <input type="{{ $type }}" name="{{ $name }}" id="{{ $inputId }}" class="{{ $inputClass }}"
                value="{{ $value ?? '' }}"
                @if($placeholder) placeholder="{{ $placeholder }}" @endif
                @if($required) required @endif
                @if($disabled) disabled @endif
                @if($readonly) readonly @endif>
        @endif
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
.form-group {
  margin-bottom: 1rem;
}

.form-label {
  font-size: 0.875rem;
  font-weight: 600;
  margin-bottom: 0.375rem;
  display: block;
}

.form-control {
  width: 100%;
  padding: 0.5rem 0.75rem;
  font-size: 0.875rem;
  font-family: var(--font-base);
  color: var(--color-text-primary);
  background-color: var(--color-surface);
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-sm);
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-control:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.1);
  outline: none;
}

.form-control.is-invalid {
  border-color: var(--color-danger);
}

.form-control.is-invalid:focus {
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: stretch;
}

.input-icon {
  pointer-events: none;
}

.form-text {
  font-size: 0.75rem;
  color: var(--color-text-muted);
  margin-top: 0.25rem;
}

.invalid-feedback {
  font-size: 0.75rem;
  color: var(--color-danger);
  margin-top: 0.25rem;
}

textarea.form-control {
  resize: vertical;
  min-height: 80px;
}
</style>
@endpush
