@extends('layouts.app')

@section('title', 'Budgets')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Budgets</h4>
        <p class="text-muted small mb-0">Manage financial budgets</p>
    </div>
    <x-button href="{{ route('budgets.create') }}" variant="primary" icon="plus-lg">
        Create Budget
    </x-button>
</div>

<x-card>
    <x-datatable
        id="budgets-table"
        :ajaxUrl="route('budgets.datatable')"
        :columns="[
            ['data' => 'name', 'title' => 'Name'],
            ['data' => 'type', 'title' => 'Type', 'render' => 'function(data) { return data.charAt(0).toUpperCase() + data.slice(1); }'],
            ['data' => 'budget_year', 'title' => 'Year'],
            ['data' => 'category', 'title' => 'Category'],
            ['data' => 'allocated_amount', 'title' => 'Allocated', 'render' => 'function(data) { return window.formatCurrency(data); }'],
            ['data' => 'spent_amount', 'title' => 'Spent', 'render' => 'function(data) { return window.formatCurrency(data); }'],
            ['data' => 'remaining_amount', 'title' => 'Remaining', 'render' => 'function(data, type, row) {
                const pct = row.allocated_amount > 0 ? (row.spent_amount / row.allocated_amount * 100) : 0;
                const color = pct > 100 ? \"danger\" : (pct > 80 ? \"warning\" : \"success\");
                return `<span class=\"text-${color} fw-semibold\">${window.formatCurrency(data)}</span>`;
            }'],
            ['data' => 'status', 'title' => 'Status', 'orderable' => false, 'render' => 'function(data) { return data == 1 ? \'<span class=\"badge bg-success\">Active</span>\' : \'<span class=\"badge bg-secondary\">Inactive</span>\'; }'],
            ['data' => 'actions', 'title' => 'Actions', 'orderable' => false, 'searchable' => false, 'render' => 'function(data, type, row) { return `<a href=\"/budgets/${row.id}/edit\" class=\"btn btn-sm btn-outline-primary me-1\"><i class=\"bi bi-pencil\"></i></a><button onclick=\"deleteBudget(${row.id})\" class=\"btn btn-sm btn-outline-danger\"><i class=\"bi bi-trash\"></i></button>`; }']
        ]"
        :orderColumn="0"
        orderDirection="asc"
    />
</x-card>

<x-modal id="deleteModal" title="Confirm Delete" size="sm">
    <p>Are you sure you want to delete this budget?</p>
    <x-slot:footer>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <form id="deleteForm" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Delete</button>
        </form>
    </x-slot:footer>
</x-modal>
@endsection

@push('scripts')
<script>
function deleteBudget(id) {
    $('#deleteForm').attr('action', `/budgets/${id}`);
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush
