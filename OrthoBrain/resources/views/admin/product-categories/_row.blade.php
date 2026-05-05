@php
    $hasDeps = $cat->subcategories_count > 0 || $cat->products_count > 0;
    $isActive = $cat->status === 'ACTIVE';
@endphp
<tr data-id="{{ $cat->id }}" data-row-href="{{ route('admin.product-categories.show', $cat) }}">
    <td class="fw-bolder pc-cell-name">{{ $cat->name }}</td>
    <td>
        @if ($cat->subcategories_count > 0)
            {{ $cat->subcategories_count }}
        @else
            <span class="text-muted">—</span>
        @endif
    </td>
    <td>
        @if ($cat->products_count > 0)
            {{ $cat->products_count }}
        @else
            <span class="text-muted">—</span>
        @endif
    </td>
    <td>
        <select class="ob-status-select" data-inline-status
                data-url="{{ route('admin.product-categories.status', $cat) }}"
                data-status="{{ $cat->status }}"
                aria-label="Update status for {{ $cat->name }}">
            <option value="ACTIVE"   @selected($cat->status === 'ACTIVE')>Active</option>
            <option value="INACTIVE" @selected($cat->status === 'INACTIVE')>Inactive</option>
        </select>
    </td>
    <td class="text-end">
        <div class="ob-row-actions">
            <a href="{{ route('admin.product-categories.show', $cat) }}" class="ob-icon-btn ob-icon-btn--view" title="View"><i data-feather="eye"></i></a>
            <button type="button" class="ob-icon-btn ob-icon-btn--edit pc-edit" title="Edit"
                    data-id="{{ $cat->id }}"
                    data-name="{{ $cat->name }}"
                    data-status="{{ $cat->status }}"
                    data-url="{{ route('admin.product-categories.ajax.update', $cat) }}">
                <i data-feather="edit-2"></i>
            </button>
            @if (! $hasDeps)
                <form method="POST" action="{{ route('admin.product-categories.destroy', $cat) }}" class="d-inline js-delete-form" data-confirm="Delete category '{{ $cat->name }}'?">
                    @csrf @method('DELETE')
                    <button type="submit" class="ob-icon-btn ob-icon-btn--delete" title="Delete"><i data-feather="trash-2"></i></button>
                </form>
            @else
                <button type="button" class="ob-icon-btn ob-icon-btn--disabled js-delete-blocked" aria-disabled="true" title="Cannot delete"
                        data-reason="Cannot delete '{{ $cat->name }}' — it has linked sub-categories or products. Remove them first.">
                    <i data-feather="trash-2"></i>
                </button>
            @endif
        </div>
    </td>
</tr>
