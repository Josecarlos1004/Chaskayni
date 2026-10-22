<article class="product-card">
<div class="product-image">
@if ($product->image_url)<img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.hidden=false"><div class="image-placeholder" hidden>🌱<span>Producto local</span></div>
@else<div class="image-placeholder">🌱<span>Producto local</span></div>@endif
<span class="pill">{{ $product->category ?: 'Cosecha local' }}</span></div>
<div class="product-content"><div class="product-meta">{{ $product->community?->name ?: 'Comunidad de Cusco' }} · {{ $product->seller->name }}</div><h3>{{ $product->name }}</h3><p class="product-description">{{ $product->description ?: 'Producto publicado directamente por un productor de la comunidad.' }}</p>
<div class="product-price">S/ {{ number_format((float) $product->unit_price, 2) }} <small>/ {{ $product->unit }}</small></div><div class="stock-line">{{ number_format((float) $product->quantity, 2) }} {{ $product->unit }} disponibles</div>
@auth
@if (auth()->user()->isProducer() && (auth()->id() === $product->user_id || auth()->user()->isAdministrator()))<div class="card-actions"><a class="button button-outline button-small" href="{{ route('products.edit', $product) }}">Editar publicación</a><form action="{{ route('products.destroy', $product) }}" method="post" onsubmit="return confirm('¿Dejar de mostrar esta publicación?')">@csrf @method('delete')<button class="button button-quiet button-small" type="submit">Ocultar</button></form></div>
@elseif (auth()->user()->role !== 'productor' && auth()->id() !== $product->user_id)<form class="order-form" action="{{ route('orders.store', $product) }}" method="post">@csrf<label>Cantidad ({{ $product->unit }})<input name="quantity" type="number" min="0.01" max="{{ $product->quantity }}" step="0.01" value="1" required></label><button class="button button-small" type="submit">Solicitar pedido</button></form>@endif
@else<a class="button button-small button-full" href="{{ route('login') }}">Ingresar para pedir</a>@endauth
</div></article>