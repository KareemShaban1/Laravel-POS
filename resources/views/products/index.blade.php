@extends('layouts.admin')

@section('title', __('product.Product_List'))
@section('content-header', __('product.Product_List'))
@section('content-actions')
<a href="{{route('products.create')}}" class="btn btn-primary">{{ __('product.Create_Product') }}</a>
@endsection
@section('css')
<link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
@endsection
@section('content')
<div class="card product-list">
<div class="card-header">
    <form method="GET" action="{{ route('products.index') }}" class="d-flex">
        <input type="text"
               name="search"
               id="search"
               value="{{ request('search') }}"
               class="form-control me-2"
               placeholder="{{ __('product.Search_Product') }}">
               <!-- close button -->
               <button type="button" class="btn btn-outline-secondary" id="close-search">
    <i class="fas fa-times"></i>
</button>
    </form>
</div>

    <div class="card-body">
    <div id="products-table">
            @include('products.partials.table', ['products' => $products])
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script type="module">
    $(document).ready(function() {

        $(document).on('click', '.btn-delete', function() {
            var $this = $(this);
            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-danger'
                },
                buttonsStyling: false
            })

            swalWithBootstrapButtons.fire({
                title: '{{ __('product.sure ') }}', // Wrap in quotes
                text: '{{ __('product.really_delete ') }}', // Wrap in quotes
                icon: 'warning', // Fix the icon string
                showCancelButton: true,
                confirmButtonText: '{{ __('product.yes_delete ') }}', // Wrap in quotes
                cancelButtonText: '{{ __('product.No ') }}', // Wrap in quotes
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.post($this.data('url'), {
                        _method: 'DELETE',
                        _token: '{{ csrf_token() }}' // Wrap in quotes
                    }, function(res) {
                        $this.closest('tr').fadeOut(500, function() {
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

    if (query.length >= 3 || query.length === 0) {
        timer = setTimeout(() => {
            $.ajax({
                url: "{{ route('products.index') }}",
                type: "GET",
                data: { search: query },
                success: function (res) {
                    // نحط الجدول كامل
                    $("#products-table").html(res);
                }
            });
        }, 500);
    }
});

$(document).on('click', '#close-search', function() {
            $('#search').val('');
            $.ajax({
                url: "{{ route('products.index') }}",
                type: "GET",
                success: function (res) {
                    $("#products-table").html(res);
                }
            });
        });
    });
</script>
@endsection