# BDJR Dashboard App

BDJR Dashboard es un **HUB web de exploración interna** para la empresa BDJR. Actúa como un punto central para:

- **Catálogo y venta de software** (productos BDJR).
- **Gestión de tickets de soporte**.
- **Panel de administración de productos** (CRUD completo sobre `bdjr_products`).

Actualmente es un **prototipo funcional** orientado a demostrar capacidades técnicas y de arquitectura. Esta documentación está pensada para **desarrolladores** del equipo (no es material comercial).

---

## 1. Arquitectura General

- **Frontend**
  - HTML5 semántico.
  - CSS3 modular (variables, layout, componentes, utilidades).
  - JavaScript ES Modules (sin frameworks, sin bundlers).

- **Backend (PHP)**
  - Endpoints ubicados en `backend/` (ej. `auth.php`, `product_manage.php`, `products.php`, etc.).
  - Integración con Supabase vía REST (`db.php`, `auth.php`).
  - Dependencias PHP gestionadas mediante `vendor/` (por ejemplo SDK de Mercado Pago para pruebas de pago).

- **Base de datos**
  - Supabase Project: **BDJR**.
  - Todas las tablas del proyecto usan el prefijo `bdjr_` (p.ej. `bdjr_products`, `bdjr_orders`, `bdjr_order_items`).
  - Esquema inicial documentado en `supabase_schema.sql` y extendido en `import_products.sql`.

- **Integraciones externas**
  - **Supabase Auth** para registro/login por email y password.
  - **Supabase REST** para CRUD sobre tablas.
  - **Mercado Pago** (vía SDK PHP en `vendor/`) para pruebas de flujos de pago.

---

## 2. Estructura del Proyecto

Estructura simplificada relevante para desarrollo:

```bash
bdjr-web/
├── assets/
│   ├── css/
│   │   ├── variables.css
│   │   ├── reset.css
│   │   ├── layout.css
│   │   ├── components.css
│   │   └── utilities.css
│   ├── js/
│   │   ├── api.js          # Helper genérico para llamadas al backend
│   │   ├── auth.js         # Login/register con Supabase Auth
│   │   ├── app.js          # Lógica compartida (layout, sidebar, etc.)
│   │   ├── admin.js        # Lógica del panel admin de productos
│   │   └── ...
│   └── images/
│       └── products/       # Imágenes de productos (convención de nombres)
├── backend/
│   ├── config.php          # Configuración de Supabase (URL + service_role key)
│   ├── db.php              # Cliente HTTP simple hacia REST de Supabase
│   ├── auth.php            # Login/register contra Supabase Auth
│   ├── product_manage.php  # CRUD de productos (panel admin)
│   ├── products.php        # Endpoint público de catálogo de productos
│   └── ...
├── vendor/                 # Dependencias PHP (incl. Mercado Pago SDK)
├── supabase_schema.sql     # Esquema base de BD (productos, órdenes, ítems)
├── import_products.sql     # Script para poblar `bdjr_products` con datos demo
├── index.html              # Dashboard & métricas
├── catalog.html            # Catálogo de software
├── product.html            # Detalle de producto (zoom, pestañas, etc.)
├── cart.html               # Carrito y flujo de pago simulado
├── tickets.html            # Módulo de tickets de soporte
├── admin_products.html     # Panel de administración de productos (CRUD)
└── ...
```

---

## 3. Setup Local

- **Requisitos**
  - PHP (XAMPP en Windows).
  - Servidor web apuntando al directorio `c:\xampp\htdocs\bdjr-web`.
  - Extensión **cURL** habilitada en PHP (`php.ini`).

- **Pasos básicos**
  1. Clonar/copiar el proyecto dentro de `c:\xampp\htdocs\bdjr-web`.
  2. Crear el esquema base en Supabase usando `supabase_schema.sql` y luego ampliar con `import_products.sql`.
  3. Configurar en `backend/config.php`:
     - `SUPABASE_URL`: URL del proyecto BDJR en Supabase.
     - `SUPABASE_KEY`: **service_role key** (no la anon key).
  4. Ajustar `assets/js/api.js` para entorno local:

     ```js
     // Conexion para local
     const API_BASE_URL = "backend";

     // Conexion para despliegue
     // const API_BASE_URL = "/backend";
     ```

  5. Acceder vía navegador a `http://localhost/bdjr-web/index.html`.

---

## 4. Despliegue en Producción

- **Hosting**: ProPHP.

<img width="1896" height="913" alt="image" src="https://github.com/user-attachments/assets/dbf542fb-2e59-4e2b-bb28-4720ed19e2d1" />

- **URL oficial**: `https://bdjr-web.66ghz.com`.

- El backend PHP vive bajo la ruta `/backend` (ej. `https://bdjr-web.66ghz.com/backend/product_manage.php`).

<img width="1916" height="988" alt="image" src="https://github.com/user-attachments/assets/a7156c81-0000-47ea-9659-e09e05fe0499" />

En producción, `assets/js/api.js` debe apuntar a `/backend`:

```js
// Conexion para local
// const API_BASE_URL = "backend";

// Conexion para despliegue
const API_BASE_URL = "/backend";
```

> Nota: cualquier cambio de dominio o ruta del backend debe reflejarse en `api.js`.

---

## 5. Integración con Supabase

### 5.1. Esquema de base de datos

<img width="710" height="696" alt="image" src="https://github.com/user-attachments/assets/9f4d511e-29d7-451d-ba47-f0e819e42efd" />

El archivo `supabase_schema.sql` define las tablas base:

- **`public.bdjr_products`**
  - `id uuid default uuid_generate_v4() primary key`
  - `name text not null`
  - `description text`
  - `price numeric not null`
  - `image_url text`
  - `category text`
  - `stock integer default 0`
  - `created_at timestamp with time zone default timezone('utc', now()) not null`

- **`public.bdjr_orders`**
  - Encabezado de órdenes (usuario, monto total, estado, dirección, timestamps).

- **`public.bdjr_order_items`**
  - Ítems de cada orden (producto, cantidad, precio a la fecha de compra).

El archivo `import_products.sql` amplía el esquema de `bdjr_products` con columnas adicionales orientadas a contenido de marketing (vendor, long_description, compare_price, features, benefits, images) y realiza una carga inicial de productos demo.

### 5.2. Cliente REST (`db.php`)

`backend/db.php` implementa un helper `supabase_request($endpoint, $method, $data, $token)` que:

- Construye la URL: `SUPABASE_URL/rest/v1/{endpoint}`.
- Envía headers:
  - `apikey: SUPABASE_KEY`
  - `Authorization: Bearer {token}` (o `SUPABASE_KEY` por defecto).
  - `Content-Type: application/json`.
  - `Prefer: return=representation` para obtener los registros creados/actualizados.
- Devuelve siempre arrays decodificados desde JSON, con manejo simple de errores HTTP.

### 5.3. Autenticación (`auth.php` + `auth.js`)

- `backend/auth.php` expone endpoints:
  - `auth.php?action=register` → alta de usuario en Supabase Auth.
  - `auth.php?action=login` → login por email/password vía `grant_type=password`.
- `assets/js/auth.js` provee funciones:
  - `login(email, password)`: llama a `auth.php?action=login`, extrae `access_token` y lo guarda como `supabase_token` en `localStorage`.
  - `register(email, password)`: proxy hacia `auth.php?action=register`.
  - `logout()`: borra token y usuario de `localStorage`.
  - `isAuthenticated()`: valida existencia de `supabase_token`.

El módulo `admin.js` utiliza `isAuthenticated()` al cargar para redirigir a `login.html` si no hay sesión.

---

## 6. Panel de Administración de Productos

### 6.1. Vista (`admin_products.html`)

<img width="1891" height="913" alt="image" src="https://github.com/user-attachments/assets/7a6b7352-2036-476e-8498-7ac3a31b437d" />
<img width="1915" height="918" alt="image" src="https://github.com/user-attachments/assets/e77a610c-e39c-4e9e-80b9-561e8382f0c2" />

- Página protegida por `isAuthenticated()` (redirección a `login.html` si no hay token).
- Presenta:
  - Tabla de productos (`#products-table-body`).
  - Botón `+ Agregar Producto`.
  - Modal de creación/edición con todos los campos relevantes:
    - Nombre, vendor, categoría, precio, precio de comparación.
    - Descripción corta y larga.
    - Listas de características y beneficios (texto multilinea, una por línea).

### 6.2. Lógica (`assets/js/admin.js`)

- **Carga inicial de productos**
  - `loadProducts()` llama a `apiRequest('products.php')` (endpoint público) para leer `bdjr_products` y renderizarlos en la tabla.

- **Crear / Editar producto**
  - El botón `+ Agregar Producto` abre el modal con formulario vacío.
  - El botón de edición (`.btn-edit`) rellena el formulario con los datos del producto seleccionado.
  - En el submit del formulario:
    - Se construye un `data` JSON coherente con las columnas de `bdjr_products` (y campos extendidos manejados vía JSONB).
    - Se envía a `product_manage.php` con método:
      - `POST` si no hay `id` → crear.
      - `PUT` si hay `id` → actualizar.
    - Tras éxito: se cierra el modal, se recarga la tabla y se muestra un mensaje al usuario.

- **Eliminar producto**
  - El botón de borrar (`.btn-delete`) dispara una confirmación y luego llama a `product_manage.php?id={id}` con método `DELETE`.

### 6.3. Backend del CRUD (`backend/product_manage.php`)

- Usa siempre la **service_role key** configurada en `config.php`:

  ```php
  $token = SUPABASE_KEY; // service_role
  ```

- Acciones soportadas:
  - `POST` → insert en `bdjr_products`.
  - `PUT`  → update parcial vía `PATCH` sobre `bdjr_products?id=eq.{id}`.
  - `DELETE` → borrado de `bdjr_products?id=eq.{id}`.

- Construye automáticamente la ruta de imagen principal a partir del nombre del producto:

  ```php
  $imageBasicName = $name; // se asume que coincide con el nombre del archivo
  $imagePath = "assets/images/products/{$imageBasicName}.png";
  ```

  El frontend debe asegurar que el archivo exista bajo `assets/images/products/` con ese nombre.

### 6.4. Advertencias de seguridad

- `SUPABASE_KEY` es la **service_role key**:
  - Permite omitir las políticas de Row Level Security (RLS) en las tablas.
  - **Nunca** debe exponerse en código de frontend ni repositorios públicos.
- Este panel de administración está pensado para un entorno controlado (prototipo interno). Para un entorno de producción público se recomienda:
  - Definir políticas RLS apropiadas.
  - Usar tokens de usuario (`authenticated`) en lugar de `service_role` para la mayoría de operaciones.
  - Añadir controles adicionales de rol/permiso en PHP.

---

## 7. Guía Rápida de Uso del Panel Admin

1. **Iniciar sesión**
   - Ir a `login.html`.
   - Registrarse o iniciar sesión con email/password.
   - Tras login exitoso se guarda `supabase_token` en `localStorage`.

2. **Acceder al panel**
   - Navegar a `admin_products.html`.
   - Si no hay token, la página redirige a `login.html`.

3. **Crear un nuevo producto**
   - Clic en `+ Agregar Producto`.
   - Completar:
     - Nombre (debe corresponderse con el nombre del archivo `.png` en `assets/images/products/`).
     - Vendor (por defecto `BDJR`).
     - Categoría (`Software`, `Curso`, `Ebook`, `Mentoría`, `General`).
     - Precio y precio de comparación.
     - Descripciones.
     - Características y beneficios (una por línea).
   - Guardar. El producto se inserta en `bdjr_products` y se recarga la tabla.

4. **Editar un producto**
   - Clic en el ícono de edición (✏️) en la fila correspondiente.
   - Modificar los campos necesarios.
   - Guardar.

5. **Eliminar un producto**
   - Clic en el ícono de papelera (🗑️).
   - Confirmar en el diálogo.

---

## 8. Ejemplos de Requests/Responses del CRUD

Los siguientes ejemplos asumen que el frontend ya llama a `apiRequest` y que `product_manage.php` está correctamente configurado con la service_role key.

### 8.1. Crear producto (`POST /backend/product_manage.php`)

**Request JSON (body)**

```json
{
  "name": "FinanzApp1",
  "vendor": "BDJR",
  "category": "Software",
  "price": 50000,
  "compare_price": 100000,
  "description": "Disponible en Android (apk), Web y Windows (exe).",
  "long_description": "Suite de finanzas personales para controlar gastos y presupuestos.",
  "features": [
    "Seguimiento de Gastos",
    "Presupuestos Personalizados"
  ],
  "benefits": [
    "Ahorra más dinero mes a mes",
    "Control total de tus finanzas"
  ],
  "stock": 100
}
```

**Response JSON (éxito)**

```json
{
  "message": "Product created",
  "data": [
    {
      "id": "c1a2b3c4-d5e6-7890-abcd-ef0123456789",
      "name": "FinanzApp1",
      "vendor": "BDJR",
      "description": "Disponible en Android (apk), Web y Windows (exe).",
      "long_description": "Suite de finanzas personales para controlar gastos y presupuestos.",
      "price": 50000,
      "compare_price": 100000,
      "stock": 100,
      "category": "Software",
      "image_url": "assets/images/products/FinanzApp1.png",
      "images": "[\"assets/images/products/FinanzApp1.png\",\"assets/images/products/FinanzApp1.png\"]",
      "features": "[\"Seguimiento de Gastos\",\"Presupuestos Personalizados\"]",
      "benefits": "[\"Ahorra más dinero mes a mes\",\"Control total de tus finanzas\"]",
      "created_at": "2025-01-01T12:00:00+00:00"
    }
  ]
}
```

### 8.2. Actualizar producto (`PUT /backend/product_manage.php?id={id}`)

**Request JSON (body)**

```json
{
  "price": 60000,
  "compare_price": 120000,
  "stock": 80
}
```

**Response JSON (éxito)**

```json
{
  "message": "Product updated",
  "data": [
    {
      "id": "c1a2b3c4-d5e6-7890-abcd-ef0123456789",
      "price": 60000,
      "compare_price": 120000,
      "stock": 80
    }
  ]
}
```

### 8.3. Eliminar producto (`DELETE /backend/product_manage.php?id={id}`)

**Response JSON (éxito)**

```json
{
  "message": "Product deleted"
}
```

### 8.4. Listar productos (`GET /backend/products.php`)

`products.php` expone un endpoint de solo lectura sobre `bdjr_products` (usado por catálogo y admin).

**Response JSON (ejemplo)**

```json
[
  {
    "id": "c1a2b3c4-d5e6-7890-abcd-ef0123456789",
    "name": "FinanzApp1",
    "vendor": "BDJR",
    "description": "Disponible en Android (apk), Web y Windows (exe).",
    "long_description": "Suite de finanzas personales para controlar gastos y presupuestos.",
    "price": 50000,
    "compare_price": 100000,
    "stock": 100,
    "category": "Software",
    "image_url": "assets/images/products/FinanzApp1.png",
    "features": "[\"Seguimiento de Gastos\",\"Presupuestos Personalizados\"]",
    "benefits": "[\"Ahorra más dinero mes a mes\",\"Control total de tus finanzas\"]"
  }
]
```

---

## 9. Diagramas y Flujo de Arquitectura

### 9.1. Arquitectura de alto nivel

```text
┌─────────────────────────┐       ┌──────────────────────────────┐
│  Navegador (Cliente)    │       │   Backend PHP (ProPHP/XAMPP) │
│                         │  HTTP │                              │
│  - HTML/CSS/JS (ESM)    ├──────▶│  - auth.php                  │
│  - admin.js / api.js    │       │  - product_manage.php        │
│  - auth.js              │       │  - products.php              │
└──────────┬──────────────┘       │  - db.php (Supabase client)  │
           │                      └───────────┬──────────────────┘
           │                                   │
           │                                   │ HTTP (REST)
           │                                   ▼
           │                      ┌──────────────────────────────┐
           │                      │      Supabase (BDJR)         │
           │                      │  - Auth (usuarios)           │
           │                      │  - rest/v1/bdjr_products     │
           │                      │  - rest/v1/bdjr_orders       │
           │                      └──────────────────────────────┘
           │
           │  Opcional / pruebas
           ▼
┌─────────────────────────┐
│  Pasarela de pago (MP) │
└─────────────────────────┘
```

### 9.2. Flujo del panel admin (CRUD productos)

```text
[1] Usuario abre admin_products.html
    └─ admin.js importa auth.js y verifica isAuthenticated()
         └─ Si no hay supabase_token → redirige a login.html

[2] admin.js ejecuta loadProducts()
    └─ apiRequest('products.php')
          └─ GET /backend/products.php
                └─ db.php → Supabase REST (bdjr_products)

[3] Usuario hace clic en "+ Agregar Producto" o ✏️ (editar)
    └─ Se abre el modal y se llama a openModal(product?)

[4] Enviar formulario
    └─ admin.js captura submit
        ├─ Construye JSON con campos del formulario
        ├─ Determina método:
        │     - POST si no hay id
        │     - PUT  si hay id
        └─ apiRequest('product_manage.php', method, data)
              └─ fetch(`${API_BASE_URL}/product_manage.php`)
                     └─ PHP product_manage.php
                           ├─ Lee JSON de entrada
                           ├─ Usa SUPABASE_KEY (service_role)
                           └─ Llama a supabase_request(...)
                                   └─ Supabase REST (insert/patch/delete)

[5] Respuesta
    └─ PHP devuelve JSON (message + data)
    └─ admin.js:
         - Cierra modal
         - Llama a loadProducts() de nuevo
         - Muestra alert de éxito o error
```

---

## 10. Módulos Principales de la App

- **Dashboard (`index.html`)**
  - Resumen de métricas y accesos rápidos a las secciones clave.

- **Catálogo (`catalog.html`)**
  - Listado de productos con tarjetas.
  - Uso de datos provenientes de `bdjr_products` vía endpoint público.

- **Detalle de producto (`product.html`)**
  - Vista individual con zoom interactivo, galería y pestañas (descripción, características, beneficios).

- **Carrito (`cart.html`)**
  - Gestión de items, cálculo de totales, integración de prueba con pasarelas de pago.

- **Tickets (`tickets.html`)**
  - Alta y listado de tickets de soporte (estructura preparada para integrarse con backend / Supabase en futuras iteraciones).

- **Admin productos (`admin_products.html` + `admin.js`)**
  - CRUD completo sobre `bdjr_products`.

---

## 9. Roadmap (alto nivel)

- Definir y documentar políticas RLS por rol (`authenticated` vs `service_role`).
- Incorporar roles de usuario (admin, soporte, solo lectura, etc.).
- Añadir filtros/búsquedas/paginación en el panel de productos.
- Completar integración persistente de tickets con Supabase.
- Incorporar panel de reportes (órdenes, ventas, uso de productos).

---

Desarrollado por **BDJR Team**.
