# MesaQR

Plantilla web **modular** de pedidos para restaurantes con **detección de contexto por código QR**.

Una sola página web reconoce desde dónde llega el cliente:

- Si escanea el **QR de una mesa** → la URL trae `?mesa=N` → **vista presencial** (pedido asociado a la mesa).
- Si entra **sin QR** → **vista de domicilio** (pide la dirección al confirmar).

Detrás hay un **panel del restaurante** (autenticado) donde el personal administra el menú y gestiona los pedidos entrantes.

## Stack

- **Laravel 13** + **Blade** (un único proyecto y despliegue)
- **Alpine.js** para la interactividad del carrito y los totales
- **Tailwind CSS** (compilado con Vite)
- **Laravel Breeze** (Blade) para la autenticación del panel
- **SQLite** como base de datos (cero configuración)

> Se eligió Laravel + Blade + Alpine (no React) tal como define el documento de
> requerimientos: un solo framework, un solo despliegue, sin CORS ni doble auth.

## Arquitectura modular

| Módulo | Controlador | Rutas | Requerimientos |
|--------|-------------|-------|----------------|
| **1. Gestión de Menú** | `DishController` | `/dishes` (resource) + `/dishes/{dish}/toggle` | RF-01 … RF-05, RNF-01 (imágenes) |
| **2. Flujo de Cliente** | `OrderController` | `/pedido`, `POST /pedido`, `/pedido/{order}/pago`, `/pedido/{order}/confirmacion` | RF-05 … RF-17, RNF-08 (pago) |
| **3. Panel del Restaurante** | `Admin\OrderPanelController` | `/panel/pedidos`, `/panel/pedidos/{order}`, `PATCH …/estado` | RF-19, RF-20 |
| **Comanda de cocina** | `Admin\ComandaController` | `/panel/pedidos/{order}/comanda` (txt) | RNF-07 · flag `MODULE_COMANDA` |
| **Reserva de mesas** | `Admin\ReservationController` | `/reservas` (resource) | flag `MODULE_RESERVAS` |
| **Export CSV** | `Admin\ExportController` | `/panel/export/*.csv` | RNF-16 · flag `MODULE_EXPORT` |
| **Auth (panel)** | Laravel Breeze | `/login`, `/logout`, … | RF-18 |

### Módulos activables (feature flags · RNF-10)

`config/modules.php` lee interruptores `MODULE_*` del `.env`. Un módulo apagado
desaparece en **3 capas** (ruta 404 + navegación oculta + lógica inactiva), sin
tocar el código: la misma plantilla sirve a comercios distintos según su `.env`.
Los módulos básicos (menú, pedidos, panel, QR) siempre están activos. El **cobro
del pedido (RNF-08) es obligatorio**, por eso no es un flag.

> El pago usa `App\Services\Payments\PaymentGateway` (interfaz) con
> `FakePaymentGateway` (simulado) por defecto; integrar una pasarela real es
> cambiar el binding en `AppServiceProvider`.

### Modelos

- `Dish` — platos del menú (`name`, `description`, `image_path`, `price`, `is_available`).
- `Order` — pedidos (`type`, `table_number`, `address`, `total`, `status`, `payment_status`, `payment_method`, `payment_reference`, `paid_at`).
- `OrderItem` — líneas del pedido (cantidad + precio congelado).
- `Reservation` — reservas de mesa (`customer_name`, `reserved_at`, `party_size`, `status`, …).

## Puesta en marcha

```bash
# 1. Dependencias PHP y JS
composer install
npm install

# 2. Entorno (si aún no existe el .env)
cp .env.example .env
php artisan key:generate

# 3. Base de datos + datos de ejemplo
php artisan migrate:fresh --seed

# 4. Compilar assets y levantar
npm run dev                 # en una terminal (Vite)
php artisan serve           # en otra terminal
```

App en **http://127.0.0.1:8000**.

### Credenciales del panel (sembradas)

- **Email:** `admin@mesaqr.test`
- **Password:** `password`

## Cómo probar cada flujo

| Acción | URL / paso |
|--------|------------|
| Pedido **presencial** (QR) | `http://127.0.0.1:8000/pedido?mesa=12` |
| Pedido **a domicilio** | `http://127.0.0.1:8000/pedido` |
| **Panel** (menú + pedidos) | Inicia sesión y ve a **Menú** / **Pedidos** |

### QR de mesa

El QR sólo codifica una URL con el parámetro de mesa, por ejemplo:

```
http://127.0.0.1:8000/pedido?mesa=12
```

Genera el QR de cada mesa con cualquier generador apuntando a esa URL.

## Thunder Client

La carpeta `thunder-tests/` trae una colección lista para la extensión
**Thunder Client** de VS Code:

- `thunderclient.json` — colección con todos los endpoints, agrupados por módulo
  y anotados con su RF.
- `thunderEnvironment.json` — entorno **MesaQR Local** con `base_url` y variables.

Ábrela desde el panel de Thunder Client (detecta la carpeta automáticamente) o
**Import** → selecciona `thunderclient.json`.

> Los `POST`/`PUT`/`PATCH` de Laravel requieren cookie de sesión + token CSRF;
> los GET públicos (`/pedido`) se prueban directamente. Cada request documenta su
> contrato.

## Trazabilidad de requerimientos

| RF | Dónde se implementa |
|----|---------------------|
| RF-01 crear plato | `DishController@store` + `dishes/create` |
| RF-02 editar plato | `DishController@update` + `dishes/edit` |
| RF-03 eliminar plato | `DishController@destroy` |
| RF-04 disponibilidad | `DishController@toggle` / `update` |
| RF-05 sólo disponibles | `Dish::scopeAvailable` en las vistas de cliente |
| RF-06 vista presencial | `OrderController@create` (lee `?mesa=`) |
| RF-07 mostrar mesa | `orders/presencial` (badge "Mesa N") |
| RF-08 asociar mesa | `OrderController@store` (`table_number`) |
| RF-09/11 agregar al carrito | `orders/_cart` (Alpine `addToCart`) |
| RF-10 vista domicilio | `OrderController@create` (sin `mesa`) |
| RF-12 pedir dirección | `orders/_cart` + validación en `store` |
| RF-13 cambiar cantidades | `orders/_cart` (`increment`/`decrement`) |
| RF-14 calcular total | total reactivo (Alpine) + recálculo en servidor |
| RF-15 registrar pedido | `OrderController@store` (transacción) |
| RF-16 carrito vacío | validación `items min:1` + botón deshabilitado |
| RF-17 estado "recibido" | `Order::STATUS_RECIBIDO` al crear |
| RF-18 login del panel | Laravel Breeze + middleware `auth` |
| RF-19 listar pedidos | `OrderPanelController@index` |
| RF-20 actualizar estado | `OrderPanelController@updateStatus` |

### RNF destacados

| RNF | Dónde se implementa |
|----|---------------------|
| RNF-01 imágenes de platos | `image_path` + disco `public`; `Dish::imageUrl()`, fallback en vistas |
| RNF-04 caché de catálogo | `Dish::availableCached` (`Cache::remember`) + invalidación por eventos |
| RNF-06 QR por mesa | `Admin\TableQrController` (SVG, `bacon/bacon-qr-code`) |
| RNF-07 comanda de cocina | `Admin\ComandaController` (texto plano) · flag `MODULE_COMANDA` |
| RNF-08 cobro del pedido | `App\Services\Payments\*` + `OrderController@showPayment/processPayment` (pasarela simulada) |
| RNF-10 módulos activables | `config/modules.php` + `if(config('modules.*'))` en rutas y `@if` en vistas |
| RNF-16 export CSV | `Admin\ExportController` (streaming + `chunk`) · flag `MODULE_EXPORT` |
| RNF-24 branding por `.env` | `config/comercio.php` (nombre + logo) |

> Ejecuta la suite con `php artisan test` — **85 tests / 246 aserciones** en verde.
