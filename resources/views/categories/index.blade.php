@extends('layouts.admin')

@section('title', __('category.Category_List'))
@section('content-header', __('category.Category_List'))
@section('content-actions')
<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#categoryModal"
    onclick="openCreateModal()">
    {{ __('category.Create_Category') }}
</button>
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped" id="categoriesTable">
                <thead>
                    <tr>
                        <th>{{ __('category.ID') }}</th>
                        <th>{{ __('category.Name') }}</th>
                        <th>{{ __('category.Description') }}</th>
                        <th>{{ __('category.Image') }}</th>
                        <th>{{ __('category.Status') }}</th>
                        <th>{{ __('category.Actions') }}</th>
                    </tr>
                </thead>
               
            </table>

        </div>
    </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" role="dialog" aria-labelledby="categoryModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalLabel">
                    {{ __('category.Create_Category') }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="categoryForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">{{ __('category.Name') }}
                                    <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control"
                                    id="name" name="name"
                                    required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">{{ __('category.Status') }}
                                    <span
                                        class="text-danger">*</span></label>
                                <select class="form-control" id="status"
                                    name="status" required>
                                    <option value="1">
                                        {{ __('common.Active') }}
                                    </option>
                                    <option value="0">
                                        {{ __('common.Inactive') }}
                                    </option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label
                            for="description">{{ __('category.Description') }}</label>
                        <textarea class="form-control" id="description"
                            name="description" rows="3"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="form-group">
                        <label for="image">{{ __('category.Image') }}</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input"
                                id="image" name="image"
                                accept="image/*">
                            <label class="custom-file-label"
                                for="image">{{ __('common.Choose_file') }}</label>
                        </div>
                        <div class="invalid-feedback"></div>
                        <div id="imagePreview" class="mt-2" style="display: none;">
                            <img id="previewImg" src="" alt="Preview"
                                class="img-thumbnail"
                                style="max-width: 200px; max-height: 200px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-dismiss="modal">{{ __('common.Close') }}</button>
                    <button type="submit" class="btn btn-primary"
                        id="submitBtn">{{ __('common.Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">{{ __('common.Confirm_Delete') }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{ __('common.Are_you_sure_delete') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-dismiss="modal">{{ __('common.Cancel') }}</button>
                <button type="button" class="btn btn-danger"
                    id="confirmDeleteBtn">{{ __('common.Delete') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- jQuery (required by DataTables) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<script>
    let currentCategoryId = null;
    let isEditMode = false;

    $(document).ready(function() {

        $('#categoriesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("categories.index") }}',
            columns: [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'description', name: 'description' },
                { data: 'image', name: 'image' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action' }
            ]
        });
    });

    // Open create modal
    function openCreateModal() {
        isEditMode = false;
        currentCategoryId = null;
        $('#categoryModalLabel').text('{{ __("category.Create_Category") }}');
        $('#categoryForm')[0].reset();
        $('#imagePreview').hide();
        $('#categoryForm').attr('action', '{{ route("categories.store") }}');
        $('#categoryForm').attr('method', 'POST');
        $('#submitBtn').text('{{ __("common.Save") }}');
        clearValidationErrors();
        $('#categoryModal').modal('show');
    }

    // Open edit modal
    function openEditModal(categoryId) {
        isEditMode = true;
        currentCategoryId = categoryId;
        $('#categoryModalLabel').text('{{ __("category.Edit_Category") }}');
        $('#categoryForm')[0].reset();
        $('#imagePreview').hide();
        $('#categoryForm').attr('action', '{{ route("categories.update", ":id") }}'.replace(':id', categoryId));
        $('#categoryForm').attr('method', 'POST');
        $('#categoryForm').append('<input type="hidden" name="_method" value="PUT">');
        $('#submitBtn').text('{{ __("common.Update") }}');
        clearValidationErrors();

        // Load category data
        $.get('{{ route("categories.show", ":id") }}'.replace(':id', categoryId))
            .done(function(response) {
                if (response.category) {
                    $('#name').val(response.category.name);
                    $('#description').val(response.category.description);
                    $('#status').val(response.category.status);
                    if (response.category.image_url) {
                        $('#previewImg').attr('src', response.category.image_url);
                        $('#imagePreview').show();
                    }
                }
            })
            .fail(function() {
                showAlert('{{ __("common.Error_loading_data") }}', 'error');
            });

        $('#categoryModal').modal('show');
    }

    // Delete category
    function deleteCategory(categoryId) {
        currentCategoryId = categoryId;
        $('#deleteModal').modal('show');
    }

    // Confirm delete
    $('#confirmDeleteBtn').click(function() {
        if (currentCategoryId) {
            $.ajax({
                url: '{{ route("categories.destroy", ":id") }}'
                    .replace(':id', currentCategoryId),
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        $('#category-row-' +
                                currentCategoryId
                            )
                            .remove();
                        showAlert(response.message,
                            'success'
                        );
                    }
                },
                error: function(xhr) {
                    showAlert('{{ __("common.Error_deleting_category") }}',
                        'error');
                }
            });
        }
        $('#deleteModal').modal('hide');
    });

    // Handle form submission
    $('#categoryForm').submit(function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const url = $(this).attr('action');
        const method = $(this).attr('method');

        $.ajax({
            url: url,
            type: method,
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#categoryModal').modal(
                        'hide');
                    if (isEditMode) {
                        // Update existing row
                        updateCategoryRow(response
                            .category
                        );
                    } else {
                        // Add new row
                        addCategoryRow(response
                            .category
                        );
                    }
                    showAlert(response.message,
                        'success');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    displayValidationErrors(xhr
                        .responseJSON
                        .errors);
                } else {
                    showAlert('{{ __("common.Error_saving_category") }}',
                        'error');
                }
            }
        });
    });

    // Image preview
    $('#image').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#previewImg').attr('src', e.target.result);
                $('#imagePreview').show();
            };
            reader.readAsDataURL(file);
        }
    });

    // Add new category row
    function addCategoryRow(category) {
        const row = `
                        <tr id="category-row-${category.id}">
                            <td>${category.id}</td>
                            <td>${category.name}</td>
                            <td>${category.description ? category.description.substring(0, 50) + (category.description.length > 50 ? '...' : '') : ''}</td>
                            <td>
                                <img width="50" height="50" src="${category.image_url || '{{ asset("images/img-placeholder.jpg") }}'}" alt="${category.name}" class="img-thumbnail">
                            </td>
                            <td>
                                <span class="badge badge-${category.status ? 'success' : 'danger'}">
                                    ${category.status ? '{{ __("common.Active") }}' : '{{ __("common.Inactive") }}'}
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-primary btn-sm" onclick="openEditModal(${category.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteCategory(${category.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
        $('#categoriesTable tbody').prepend(row);
    }

    // Update existing category row
    function updateCategoryRow(category) {
        const row = $(`#category-row-${category.id}`);
        row.find('td:eq(1)').text(category.name);
        row.find('td:eq(2)').text(category.description ? category.description.substring(0, 50) + (category.description
            .length > 50 ? '...' : '') : '');
        row.find('td:eq(3) img').attr('src', category.image_url || '{{ asset("images/img-placeholder.jpg") }}');
        row.find('td:eq(4) span').removeClass('badge-success badge-danger').addClass(category.status ?
            'badge-success' : 'badge-danger').text(category.status ? '{{ __("common.Active") }}' :
            '{{ __("common.Inactive") }}');
    }

    // Display validation errors
    function displayValidationErrors(errors) {
        clearValidationErrors();
        $.each(errors, function(field, messages) {
            const input = $(`[name="${field}"]`);
            input.addClass('is-invalid');
            input.siblings('.invalid-feedback').text(messages[0]);
        });
    }

    // Clear validation errors
    function clearValidationErrors() {
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }

    // Show alert
    function showAlert(message, type) {
        // You can customize this based on your alert system
        if (type === 'success') {
            toastr.success(message);
        } else {
            toastr.error(message);
        }
    }
</script>
@endpush