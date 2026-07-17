# Guía de estilo — MesaQR / Balcoa's Café

> Sistema de diseño del frontend. **Toda vista nueva o modificada debe seguir esta guía.**
> Fuente de la paleta: `public/img/logo.png`. Tokens definidos en `tailwind.config.js`; recetas en `resources/css/app.css` (`@layer components`).

## 1. Paleta (derivada del logo)

| Token | Origen en el logo | Uso semántico |
|---|---|---|
| `cocoa-*` (marrón chocolate, base `#6b3d2e`) | Listón y borde | Texto principal (`cocoa-950`), texto secundario (`cocoa-600`), navbar admin (`cocoa-900`), botón primario (`cocoa-800`) |
| `caramel-*` (naranja caramelo, base `#e39f4e`) | Pastel y aro | CTA del cliente (`caramel-600`), enlaces (`caramel-700`), badges (`caramel-100/800`), focos/acentos (`ring-caramel-400`) |
| `cream-*` (crema, base `#f5e7cb`) | Fondo del círculo | Fondo de página (`cream-100` cliente, `cream-50` admin), bordes de tarjeta (`cream-200`) |

**Prohibido**: `indigo-*`, `blue-*`, `purple-*` (paleta Breeze de fábrica). Verde/rojo/ámbar solo con significado semántico (éxito/error/en curso).

## 2. Tipografía (self-hosted vía @fontsource, sin CDN — decisión SRI)

- `font-sans` → **Figtree Variable**: cuerpo, formularios, tablas.
- `font-display` → **Fraunces Variable** (serif cálida): títulos h1/h2, nombre del comercio, precios destacados. Usar con `tracking-tight`.

## 3. Recetas (clases en `resources/css/app.css`)

| Clase | Para qué |
|---|---|
| `.btn-brand` | Acción primaria del panel (guardar, crear) — chocolate, hover se aclara + sombra |
| `.btn-accent` | CTA del cliente (agregar, realizar pedido) — caramelo; incluye estado `disabled` |
| `.btn-ghost` | Acción secundaria (volver, cancelar) — borde suave, hover crema |
| `.card-brand` | Tarjeta blanca `rounded-xl` sobre fondo crema |
| `.badge-brand` | Insignia caramelo (mesa N, contadores) |

Botones destructivos: `bg-red-700 hover:bg-red-600` + mismo patrón de focus.

## 4. Identidad

- Logo: `config('comercio.logo')` (`/img/logo.png`); nombre: `config('comercio.nombre')` — RNF-24, editable por `.env` sin tocar código.
- Favicon: `<link rel="icon" type="image/png" href="/img/logo.png">` en todos los layouts.

## 5. Criterios de calidad aplicados (UI de restaurantes)

1. **Paleta café/crema con fondo claro** — la convención de cafés-restaurante modernos; cálida y "apetitosa", consistente en header y footer.
2. **Mobile-first**: >75 % de pedidos online son móviles; targets táctiles ≥ 44 px (`w-11 h-11`, ya RNF-12), navegación alcanzable con el pulgar.
3. **Tipografía simple y legible** con jerarquía clara: serif solo en titulares, sans en todo lo demás; precios siempre visibles y alineados.
4. **Feedback en cada interacción**: todo elemento clickeable tiene `hover:` (iluminarse = aclarar un paso el color + `shadow-md`), `focus-visible:ring-2 ring-caramel-400`, `transition duration-150`, y `active:scale-[0.98]` en botones.
5. **Contraste WCAG AA**: texto normal sobre crema usa `cocoa-700` o más oscuro; blanco solo sobre `caramel-600`+ o `cocoa-700`+.
6. **Espacio generoso**: tarjetas `p-6/p-8`, secciones `py-8`, `rounded-xl`, sombras suaves (`shadow-sm`, `hover:shadow-md`) — nunca sombras duras.
7. Futuro (RNF-01): fotografías de platos — el elemento de mayor conversión en menús online; reservar `aspect-video rounded-lg` en las cards de plato.

## 6. Patrones por zona

- **Cliente** (`components/cliente-layout`): fondo `cream-100`, header blanco con logo + nombre, badge de mesa `.badge-brand`, footer `cocoa-600` con enlaces `hover:text-caramel-600`.
- **Admin** (`layouts/app` + `navigation`): navbar `cocoa-900` con texto `cream-100`, enlace activo con `border-b-2 border-caramel-400`, fondo de contenido `cream-50`.
- **Auth** (`layouts/guest`): fondo `cream-100`, logo centrado, card `.card-brand` con `p-8`.
- **Estados de pedido**: recibido `caramel-100/800` · en_preparacion `amber-100/800` · listo `green-100/800` · entregado `cocoa-100/700`.
