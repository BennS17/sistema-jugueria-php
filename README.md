# 🥤 Sistema de Ventas de Juguería (Proyecto INSO)

Este es un sistema de ventas web desarrollado para el curso de Ingeniería de Software (INSO). El proyecto simula el flujo de trabajo completo de una juguería, desde la toma de pedidos hasta la gestión de inventario, implementando los roles de Mozo, Cajero y Gerente.

## 🚀 Características

El sistema está dividido en tres módulos principales, uno para cada rol de usuario:

### 👤 Mozo
* **Iniciar Sesión:** Acceso seguro al panel del mozo.
* **Consultar Productos:** Ver la lista de productos disponibles con su precio y stock en tiempo real.
* **Realizar Pedido:** Tomar el pedido del cliente, seleccionar productos y cantidades, y enviarlo al sistema.

### 💰 Cajero
* **Iniciar Sesión:** Acceso seguro al panel de caja.
* **Verificar Pedidos:** Revisar los pedidos `pendientes` enviados por los mozos y validarlos.
* **Generar Cobro:** Procesar el pago de los pedidos `validados` y generar el comprobante (Boleta/Factura).

### 📊 Gerente
* **Iniciar Sesión:** Acceso seguro al panel de administración.
* **Reporte de Ventas:** Consultar el historial de todos los comprobantes emitidos para analizar las ventas.
* **Generar Reposición:** Registrar órdenes de reposición de inventario y actualizar el stock de los productos.

---

## 🛠️ Stack Tecnológico

* **Backend:** PHP 8.2
* **Base de Datos:** MySQL (MariaDB)
* **Servidor:** Apache (distribuido a través de XAMPP)
* **Frontend:** HTML5, CSS3, y JavaScript (vainilla)
* **Gestión de Versiones:** Git y GitHub

---

## 🏁 Guía de Instalación Local

Sigue estos pasos para ejecutar el proyecto en tu propia máquina:

1.  **Instalar Entorno:**
    * Asegúrate de tener **XAMPP** (Apache + MySQL) y **Git** instalados.

2.  **Clonar el Repositorio:**
    * Abre tu terminal (CMD o Git Bash) y navega a la carpeta `htdocs` de XAMPP:
        ```bash
        cd C:\xampp\htdocs
        ```
    * Clona este repositorio:
        ```bash
        git clone [https://github.com/BennS17/sistema-jugueria-php.git](https://github.com/BennS17/sistema-jugueria-php.git)
        ```

3.  **Configurar la Base de Datos:**
    * Inicia Apache y MySQL en XAMPP.
    * Ve a `http://localhost/phpmyadmin/`.
    * Crea una nueva base de datos llamada `db_jugueria`.
    * Ve a la pestaña **Importar**, selecciona el archivo `db_jugueria.sql` (incluido en este repositorio) y haz clic en "Continuar".

4.  **¡Ejecutar!**
    * (El archivo `conexion.php` ya está incluido y configurado para XAMPP por defecto).
    * Abre tu navegador y ve a:
        `http://localhost/sistema-jugueria-php/login.php`
        *(Si clonaste el proyecto con un nombre de carpeta diferente, usa ese nombre en la URL)*.

---

## 👥 Autores

Este proyecto fue desarrollado por:
* **Barreto Houghton, Gabriel** (C)
* **Chavez Vargas, John Marlon**
* **Sinacay Nuñez, Ahrat Benjamin**
* **Goicochea Vargas, Stefano Rosel**