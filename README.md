# Laboratorio Seguro Jomby

## Análisis de SQL Injection

Proyecto desarrollado para realizar una práctica controlada de análisis, demostración y mitigación de vulnerabilidades de **SQL Injection** utilizando PHP, MySQL/MariaDB y XAMPP.

---

## Objetivo

El objetivo del laboratorio es comprender:

* Qué es SQL Injection.
* Cómo detectar una posible vulnerabilidad.
* Cómo una entrada manipulada puede modificar una consulta SQL.
* Qué comportamiento puede presentar una aplicación vulnerable.
* Cómo prevenir SQL Injection mediante técnicas de programación segura.

Las pruebas se realizan únicamente sobre el entorno local del laboratorio.

---

## Tecnologías utilizadas

* PHP 8.2
* MySQL / MariaDB
* phpMyAdmin
* XAMPP
* Bootstrap 5.3.3
* Bootstrap Icons 1.11.3
* Git
* GitHub
* Visual Studio Code

---

## Estructura del proyecto

```text
laboratorio_seguro_jomby/
│
├── config/
│   └── config.php
│
├── includes/
│   └── funciones.php
│
├── acciones.php
├── editar.php
├── index.php
├── login.php
├── login_vulnerable.php
├── logout.php
├── registro.php
│
├── laboratorio_seguro_jomby.sql
└── README.md
```

---

## Base de datos

El proyecto incluye un respaldo de la base de datos:

```text
laboratorio_seguro_jomby.sql
```

Este archivo permite restaurar la base de datos del laboratorio sin tener que crear nuevamente las tablas y los registros.

### Restauración mediante phpMyAdmin

1. Abrir XAMPP.
2. Iniciar Apache y MySQL.
3. Entrar a phpMyAdmin.
4. Crear la base de datos `laboratorio_seguro_jomby`.
5. Seleccionar la base de datos.
6. Entrar en **Importar**.
7. Seleccionar `laboratorio_seguro_jomby.sql`.
8. Ejecutar la importación.

---

## Configuración

La conexión a la base de datos se encuentra en:

```text
config/config.php
```

Antes de ejecutar el proyecto, verificar que los datos de conexión correspondan con la configuración local de XAMPP.

---

## Ejecución del proyecto

Colocar el proyecto dentro de:

```text
C:\xampp\htdocs\
```

La aplicación puede ejecutarse desde:

```text
http://localhost/laboratorio_seguro_jomby/
```

---

## Análisis de SQL Injection

El proyecto contiene dos versiones del inicio de sesión para comparar el comportamiento.

### Login vulnerable

Archivo:

```text
login_vulnerable.php
```

Este archivo está diseñado intencionalmente para demostrar una vulnerabilidad de SQL Injection en un entorno controlado.

La consulta utiliza directamente el valor introducido por el usuario:

```php
$sql = "SELECT * FROM usuarios WHERE email = '$email'";
```

Esto permite observar cómo una entrada manipulada puede modificar la lógica de la consulta SQL.

> Este archivo existe exclusivamente con fines educativos y de laboratorio.

---

## Login seguro

Archivo:

```text
login.php
```

La versión segura utiliza:

* Consultas preparadas mediante `PDO`.
* Parámetros enlazados.
* `password_verify()` para verificar contraseñas.
* Sesiones para controlar el acceso.
* Validación de los datos recibidos.

Ejemplo de consulta preparada:

```php
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
$stmt->execute(['email' => $email]);
```

De esta forma, los datos introducidos por el usuario no se incorporan directamente a la estructura de la consulta SQL.

---

## Mitigación de SQL Injection

Las principales medidas utilizadas en este proyecto son:

1. Utilizar **prepared statements**.
2. Utilizar parámetros enlazados.
3. Validar los datos recibidos.
4. Evitar concatenar directamente datos del usuario en consultas SQL.
5. Utilizar funciones apropiadas para el manejo de contraseñas.
6. No mostrar errores SQL internos al usuario en un sistema real.

---

## Práctica controlada

Las pruebas de SQL Injection deben realizarse únicamente dentro del entorno local preparado para este laboratorio.

El archivo `login_vulnerable.php` se mantiene separado del login seguro para poder comparar:

**Aplicación vulnerable → comportamiento observado → aplicación protegida.**

---

## Repositorio

[GitHub — laboratorio_seguro_jomby](https://github.com/jevangelistadeplata-hue/laboratorio_seguro_jomby)

---

## Autor

**José Angel Evangelista De Plata**

Proyecto académico desarrollado para fines educativos y de práctica de seguridad web.
