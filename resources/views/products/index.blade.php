@extends('layouts.admin')

@section('title', __('product.Product_List'))
@section('content-header', __('product.Product_List'))
@section('content-actions')
<a href="{{route('products.create')}}" class="btn btn-primary">{{ __('product.Create_Product') }}</a>
@endsection
@section('css')
<link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endsection
@section('content')
    <div class="card product-list">
       

        <div class="card-body">
                 <table id="products-table" class="table table-bordered table-striped"> 
                    <thead>
                        <tr>
                            <th>{{ __('product.ID') }}</th>
                            <th>{{ __('product.Name') }}</th>
                            <th>{{ __('product.Category') }}</th>
                            <th>{{ __('product.Image') }}</th>
                            <th>{{ __('product.Barcode') }}</th>
                            <th>{{ __('product.Price') }}</th>
                            <th>{{ __('product.Has_Quantity') }}</th>
                            <th>{{ __('product.Quantity') }}</th>
                            <th>{{ __('product.Status') }}</th>
                            <th>{{ __('product.Created_At') }}</th>
                            <th>{{ __('product.Updated_At') }}</th>
                            <th>{{ __('product.Actions') }}</th>
                        </tr>
                    </thead>
                 </table>
        </div>
    </div>
@endsection

@section('js')


    <script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>

    

    <script type="module">
        $(document).ready(function() {

            $('#products-table').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: '{{ route('products.index') }}',
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' ,searchable: true },
                    { data: 'category.name', name: 'category.name' ,searchable: true },
                    { data: 'image', name: 'image' },
                    { data: 'barcode', name: 'barcode' ,searchable: true},
                    { data: 'price', name: 'price' ,searchable: true},
                    { data: 'has_quantity', name: 'has_quantity' },
                    { data: 'quantity', name: 'quantity' ,searchable: true},
                    { data: 'status', name: 'status' ,searchable: true},
                    { data: 'created_at', name: 'created_at'},
                    { data: 'updated_at', name: 'updated_at' },
                    { data: 'action', name: 'action' }
                ]
            });


            let currentCategoryId = '';
            let currentSearch = '';

            // Handle category tab clicks
            $(document).on('click', '#categoryTabs .nav-link', function () {
                currentCategoryId = $(this).data('category-id');
                loadProducts(currentSearch, currentCategoryId);
            });

            $(document).on('click', '.btn-delete', function () {
                var $this = $(this);
                const swalWithBootstrapButtons = Swal.mixin({
                    customClass: {
                        confirmButton: 'btn btn-success',
                        cancelButton: 'btn btn-danger'
                    },
                    buttonsStyling: false
                })

                swalWithBootstrapButtons.fire({
                    title: '{{ __('product.sure') }}',
                    text: '{{ __('product.really_delete') }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '{{ __('product.yes_delete') }}',
                    cancelButtonText: '{{ __('product.No') }}',
                    reverseButtons: true
                }).then((result) => {
                    if (result.value) {
                        $.post($this.data('url'), {
                            _method: 'DELETE',
                            _token: '{{ csrf_token() }}' // Wrap in quotes
                        }, function (res) {
                            $this.closest('tr').fadeOut(500, function () {
                                $(this).remove();
                            });
                        });
                    }
                });
            });

            let timer = null;

            $("#search").on("keyup", function () {
                clearTimeout(timer);
                let query = $(this).val();
                currentSearch = query;

                if (query.length >= 3 || query.length === 0) {
                    timer = setTimeout(() => {
                        loadProducts(query, currentCategoryId);
                    }, 500);
                }
            });

            $(document).on('click', '#close-search', function () {
                $('#search').val('');
                currentSearch = '';
                loadProducts('', currentCategoryId);
            });

            // Function to load products with search and category filter
            function loadProducts(search, categoryId) {
                const params = new URLSearchParams();
                if (search) params.append('search', search);
                if (categoryId) params.append('category_id', categoryId);

                $.ajax({
                    url: "{{ route('products.index') }}",
                    type: "GET",
                    data: params.toString(),
                    success: function (res) {
                        $("#products-table").html(res);
                    }
                });
            }
        });
    </script>
@endsection
