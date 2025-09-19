<table class="table">
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
    <tbody>
        @foreach ($products as $product)
            <tr>
                <td>{{$product->id}}</td>
                <td>{{$product->name}}</td>
                <td>
                    @if($product->category)
                        <span class="badge badge-info">{{ $product->category->name }}</span>
                    @else
                        <span class="badge badge-secondary">{{ __('product.No_Category') }}</span>
                    @endif
                </td>
                <td><img class="product-img" src="{{ Storage::url($product->image) }}" alt=""></td>
                <td>{{$product->barcode}}</td>
                <td>{{$product->price}}</td>
                <td>
                    <span class="right badge badge-{{ $product->has_quantity ? 'success' : 'danger' }}">
                        {{$product->has_quantity ? __('product.has_quantity') : __('product.not_has_quantity') }}
                    </span>
                </td>
                <td>{{$product->quantity}}</td>
                <td>
                    <span class="right badge badge-{{ $product->status ? 'success' : 'danger' }}">
                        {{$product->status ? __('common.Active') : __('common.Inactive') }}
                    </span>
                </td>
                <td>{{$product->created_at}}</td>
                <td>{{$product->updated_at}}</td>
                <td>
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-primary"><i class="fas fa-edit"></i></a>
                    <button class="btn btn-danger btn-delete" data-url="{{route('products.destroy', $product)}}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

{{ $products->links() }}
