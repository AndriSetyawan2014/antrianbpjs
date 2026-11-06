{{--
  Komponen Tabel Standar — Sistem Pemantauan Bridging BPJS
  Params:
    - id: string — ID tabel (untuk DataTables)
    - columns: array — ['field' => 'Label', ...]
    - rows: array — Data baris (kumpulan associative array)
    - actions: array — Daftar aksi per baris ['label' => 'Edit', 'url' => '/edit/{id}', 'class' => 'btn-primary', 'icon' => 'fas fa-edit']
    - actionField: string — Field yang digunakan untuk mengganti {id} di URL aksi (default: 'id')
    - pageLength: int — Jumlah baris per halaman (default: 25)
    - searchable: boolean (default: true)
    - orderable: boolean (default: true)
    - responsive: boolean (default: true)
    - emptyMessage: string — Pesan saat tidak ada data (default: 'Tidak ada data')
    - striped: boolean (default: true)
    - hover: boolean (default: true)
    - bordered: boolean (default: true)
    - small: boolean (default: false)
    - stickyHeader: boolean (default: true)
--}}
@props([
    'id' => 'dataTable',
    'columns' => [],
    'rows' => [],
    'actions' => [],
    'actionField' => 'id',
    'pageLength' => 25,
    'searchable' => true,
    'orderable' => true,
    'responsive' => true,
    'emptyMessage' => 'Tidak ada data',
    'striped' => true,
    'hover' => true,
    'bordered' => true,
    'small' => false,
    'stickyHeader' => true,
])

<?php
$tableClasses = 'table';
if ($striped) $tableClasses .= ' table-striped';
if ($hover) $tableClasses .= ' table-hover';
if ($bordered) $tableClasses .= ' table-bordered';
if ($small) $tableClasses .= ' table-sm';
?>

<div class="table-responsive {{ $responsive ? '' : '' }}">
    @if(count($rows) > 0)
        <table id="{{ $id }}" class="{{ $tableClasses }}">
            <thead>
                <tr>
                    @foreach($columns as $field => $label)
                        <th {{ $stickyHeader ? 'style="position: sticky; top: 0; z-index: 10;"' : '' }}>
                            {{ $label }}
                        </th>
                    @endforeach
                    @if(count($actions) > 0)
                        <th class="text-center" style="width: 120px;">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr>
                        @foreach($columns as $field => $label)
                            <td>
                                {{ $row[$field] ?? '-' }}
                            </td>
                        @endforeach
                        @if(count($actions) > 0)
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    @foreach($actions as $action)
                                        <?php
                                        $url = str_replace('{' . $actionField . '}', $row[$actionField] ?? '', $action['url'] ?? '#');
                                        $btnClass = $action['class'] ?? 'btn-outline-secondary';
                                        $icon = $action['icon'] ?? 'fas fa-eye';
                                        ?>
                                        <a href="{{ $url }}" class="btn {{ $btnClass }}" title="{{ $action['label'] }}">
                                            <i class="{{ $icon }}"></i>
                                        </a>
                                    @endforeach
                                </div>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty-state text-center py-5">
            <i class="fas fa-inbox text-slate-300" style="font-size: 3rem;"></i>
            <p class="mt-3 text-slate-500">{{ $emptyMessage }}</p>
        </div>
    @endif
</div>

@push('scripts')
<script>
$(document).ready(function() {
    var tableId = '{{ $id }}';
    var $table = $('#' + tableId);
    
    if ($table.length > 0 && typeof $.fn.DataTable !== 'undefined') {
        $table.DataTable({
            paging: true,
            lengthChange: true,
            searching: {{ $searchable ? 'true' : 'false' }},
            ordering: {{ $orderable ? 'true' : 'false' }},
            info: true,
            autoWidth: false,
            pageLength: {{ $pageLength }},
            responsive: {{ $responsive ? 'true' : 'false' }},
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            },
            columnDefs: [
                {{ $stickyHeader ? '{ orderable: false, targets: [-1] }' : '' }}
            ]
        });
    }
});
</script>
@endpush