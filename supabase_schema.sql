-- Enable UUID extension
create extension if not exists "uuid-ossp";

-- PRODUCTS TABLE
create table public.bdjr_products (
  id uuid default uuid_generate_v4() primary key,
  name text not null,
  description text,
  price numeric not null,
  image_url text,
  category text,
  stock integer default 0,
  created_at timestamp with time zone default timezone('utc'::text, now()) not null
);

-- ORDERS TABLE
create table public.bdjr_orders (
  id uuid default uuid_generate_v4() primary key,
  user_id uuid references auth.users(id),
  total_amount numeric not null,
  status text default 'pending',
  shipping_address text,
  created_at timestamp with time zone default timezone('utc'::text, now()) not null
);

-- ORDER ITEMS TABLE
create table public.bdjr_order_items (
  id uuid default uuid_generate_v4() primary key,
  order_id uuid references public.bdjr_orders(id),
  product_id uuid references public.bdjr_products(id),
  quantity integer not null,
  price_at_purchase numeric not null
);

-- RLS POLICIES

-- Products: Everyone can read, only admins can insert/update (for now we just allow read for public)
alter table public.bdjr_products enable row level security;
create policy "Public products are viewable by everyone" on public.bdjr_products for select using (true);

-- Orders: Users can see their own orders. Users can insert their own orders.
alter table public.bdjr_orders enable row level security;
create policy "Users can view own orders" on public.bdjr_orders for select using (auth.uid() = user_id);
create policy "Users can insert own orders" on public.bdjr_orders for insert with check (auth.uid() = user_id);

-- Order Items: Same as orders
alter table public.bdjr_order_items enable row level security;
create policy "Users can view own order items" on public.bdjr_order_items for select using (
  exists ( select 1 from public.bdjr_orders where bdjr_orders.id = bdjr_order_items.order_id and bdjr_orders.user_id = auth.uid() )
);
create policy "Users can insert own order items" on public.bdjr_order_items for insert with check (
  exists ( select 1 from public.bdjr_orders where bdjr_orders.id = bdjr_order_items.order_id and bdjr_orders.user_id = auth.uid() )
);

-- SEED DATA (Optional)
insert into public.bdjr_products (name, description, price, image_url, category, stock) values
('Camiseta Básica', 'Camiseta de algodón 100%', 25.00, 'assets/images/product-1.jpg', 'Ropa', 100),
('Pantalón Jean', 'Jean clásico azul', 45.50, 'assets/images/product-2.jpg', 'Ropa', 50),
('Zapatillas Urbanas', 'Zapatillas cómodas para el día a día', 60.00, 'assets/images/product-3.jpg', 'Calzado', 30);
