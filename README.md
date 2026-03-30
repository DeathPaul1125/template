# Laravel Premium Admin Panel

Una solución administrativa de alta gama basada en Laravel, diseñada con una estética "Modern SaaS" que combina Glassmorphism, tipografía premium y configuraciones dinámicas.

## 🚀 Tecnologías Core
El proyecto utiliza lo último en el ecosistema Laravel para garantizar rendimiento, seguridad y una experiencia de usuario superior:

-   **Backend**: [Laravel 13.0](https://laravel.com) / PHP 8.3
-   **Frontend Interactivo**: [Livewire 3.6](https://livewire.laravel.com) & [Jetstream 5.5](https://jetstream.laravel.com)
-   **Estilos**: [Tailwind CSS 3.4](https://tailwindcss.com) & [Preline UI 2.4](https://preline.co)
-   **Bundler**: [Vite 8.0](https://vitejs.dev)
-   **Base de Datos**: Soporte multinivel (MySQL/SQLite/PostgreSQL)

## ✨ Características Premium
-   **Rediseño Visual SaaS**: Estética basada en Glassmorphism con efectos de desenfoque (`backdrop-blur`), sombras "Glow" reactivas y animaciones de entrada suaves.
-   **Tipografía de Élite**: Integración de la fuente **Plus Jakarta Sans** para una apariencia tecnológica y moderna.
-   **Gestión de RBAC**: Control de acceso basado en roles y permisos mediante el paquete oficial de [Spatie](https://spatie.be/docs/laravel-permission).
-   **Configuración Dinámica del Sistema**:
    -   Personalización en tiempo real del **Color de Marca**.
    -   Gestión de LOGO, FAVICON e Imágenes de Login desde el panel.
    -   Inyección dinámica de variables CSS para consistencia visual.
-   **Reportes PDF**: Generación de documentos profesionales y listados de usuarios mediante [DOMPDF](https://github.com/barryvdh/laravel-dompdf).
-   **Modo Oscuro Permanente en Navegación**: Sidebar y Topbar optimizados para un modo oscuro elegante y de alto contraste.

## 📦 Instalación y Configuración

1.  **Clonar el repositorio y entrar al directorio**:
    ```bash
    git clone [url-del-repositorio]
    cd template
    ```
2.  **Instalar dependencias de PHP**:
    ```bash
    composer install
    ```
3.  **Configurar el entorno**:
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
4.  **Ejecutar migraciones y seeders (Datos Iniciales)**:
    ```bash
    php artisan migrate:fresh --seed
    ```
5.  **Instalar y compilar assets**:
    ```bash
    npm install
    npm run build
    ```
6.  **Enlazar almacenamiento**:
    ```bash
    php artisan storage:link
    ```

## 🔐 Datos de Acceso (Entorno de Desarrollo)
Una vez ejecutados los seeders, puedes acceder con las siguientes credenciales:

| Email | Contraseña | Rol |
| :--- | :--- | :--- |
| `admin@admin.com` | `password` | **Super Admin** |
| `administrador@admin.com` | `password` | **Admin** |
| `editor@admin.com` | `password` | **Editor** |

---

Diseñado con ❤️ para ofrecer una experiencia administrativa premium.
