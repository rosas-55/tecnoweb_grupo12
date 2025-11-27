## Sistema de Inventario y Producción — Restaurante Las Brazas

Aplicación web MVC (Laravel) + MVVM (Inertia + Vue 3) conectada a PostgreSQL del docente.

### Requisitos
- PHP 8.2+
- Composer
- Node 18+ y npm

### 1) Clonar e instalar dependencias
```bash
composer install
npm install
```

### 2) Configurar variables de entorno
Edita el archivo `.env` (NO `.env.example`) con las credenciales del servidor del docente:
```env
APP_NAME="LasBrazas"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_HOST=tecnoweb.org.bo
DB_PORT=5432
DB_DATABASE=db_grupo12sc
DB_USERNAME=grupo12sc
DB_PASSWORD="grup012grup012*"

SESSION_DRIVER=file
CACHE_DRIVER=file
```
Luego:
```bash
php artisan key:generate
php artisan config:clear
```

Si tu proveedor exige SSL:
```env
DB_SSLMODE=require
```

### 3) Compilar assets
Desarrollo (HMR):
```bash
npm run dev
```
Producción (manifest):
```bash
npm run build
```

### 4) Levantar el servidor Laravel
En otra terminal (en la carpeta del proyecto):
```bash
php artisan serve
```

### 5) Abrir la aplicación
- Desarrollo con HMR: `http://localhost:8000`
- Con build: `http://localhost:8000` (después de `npm run build`)

### 6) Rutas principales (CRUD)
- Usuarios: `/users`
- Roles: `/roles`
- Insumos: `/insumos`
- Recetas: `/recetas`
- Producción: `/produccion`
- Inventario (movimientos): `/inventario`
- Ventas: `/ventas`
- Pagos: `/pagos`

Todas las secciones exponen crear/editar/eliminar desde la tabla (botones de acción).

### 7) Notas de conexión a BD
- Este proyecto NO crea tablas en la BD remota; mapea modelos a tablas existentes.
- Si ves “relation ... does not exist”, verifica que los nombres de tabla en los modelos coincidan con la BD.
- Si cambias el `.env`, corre:
```bash
php artisan config:clear
```
Y reinicia `php artisan serve`.

### 8) Errores comunes
- “Could not open input file: artisan”: ejecuta los comandos dentro de la carpeta del proyecto.
- “no password supplied” o error de conexión: revisa `.env` y comillas en DB_PASSWORD, limpia config y reinicia.

### 9) Flujo funcional
- Producción descuenta insumos (próximo paso: aplicar descuento automático según `receta_insumo`).
- Ventas registran cabecera (y se puede agregar detalle_venta para calcular total).
- Pagos se asocian a una venta y su suma debe cubrir el total.

### 10) Soporte
Si algo no levanta, comparte el error exacto de la consola del navegador o el traceback de Laravel. Se corrige al instante. 
