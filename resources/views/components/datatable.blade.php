@props([
    'id',
    'ajaxUrl',
    'columns' => [],
    'orderColumn' => 0,
    'orderDirection' => 'desc',
    'pageLength' => 10,
    'searching' => true,
    'exportButtons' => false
])

<div class="table-responsive">
    <table id="{{ $id }}" class="table table-hover align-middle" style="width:100%">
        <thead class="table-light">
            <tr>
                @foreach($columns as $column)
                    <th class="fw-semibold text-uppercase small">{{ $column['title'] ?? $column }}</th>
                @endforeach
            </tr>
        </thead>
    </table>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    const table = $('#{{ $id }}').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ $ajaxUrl }}',
            type: 'GET',
            data: function(d) {
                @if(request()->filled('status'))
                    d.status = '{{ request('status') }}';
                @endif
                @if(request()->filled('type'))
                    d.type = '{{ request('type') }}';
                @endif
                @if(request()->filled('year'))
                    d.year = '{{ request('year') }}';
                @endif
            }
        },
        columns: [
            @foreach($columns as $column)
            {
                data: '{{ $column['data'] ?? strtolower(str_replace(' ', '_', $column)) }}',
                name: '{{ $column['name'] ?? ($column['data'] ?? strtolower(str_replace(' ', '_', $column))) }}',
                orderable: {{ isset($column['orderable']) ? ($column['orderable'] ? 'true' : 'false') : 'true' }},
                searchable: {{ isset($column['searchable']) ? ($column['searchable'] ? 'true' : 'false') : 'true' }},
                @if(isset($column['render']))
                render: {!! $column['render'] !!}
                @endif
            },
            @endforeach
        ],
        order: [[{{ $orderColumn }}, '{{ $orderDirection }}']],
        pageLength: {{ $pageLength }},
        searching: {{ $searching ? 'true' : 'false' }},
        language: {
            processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            emptyTable: 'No data available',
            zeroRecords: 'No matching records found'
        },
        @if($exportButtons)
        dom: 'Bfrtip',
        buttons: [
            { extend: 'copy', className: 'btn btn-sm btn-outline-secondary' },
            { extend: 'excel', className: 'btn btn-sm btn-outline-success' },
            { extend: 'pdf', className: 'btn btn-sm btn-outline-danger' },
            { extend: 'print', className: 'btn btn-sm btn-outline-primary' }
        ],
        @endif
        responsive: true,
        autoWidth: false
    });

    // Refresh table on filter change
    window.refresh{{ Str::studly($id) }} = function() {
        table.ajax.reload();
    };
});
</script>
@endpush
