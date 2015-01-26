#Bularcama

Un generador de paginas estaticas escrito en php.
La idea del proyecto es hacer un framework para generar paginas estaticas que se sirvan como archivos, pero permitir tambien que esas paginas sean cargadas de manera dinamica por ajax.

Estado alpha, se sube solo para tener un backup personal.


## Como usar

Ver `bularcama-cli` para ejemplo.

1. Definir constante `__INCLUDE_PATH__`.

2. Instanciar clase `Bularcama`.
TODO: Explicar parametros de configuracion

3. Llamar a metodo `build_site`
TODO: salidas de error, etc

## Estructura de proyecto
    .
    ├── data
    │   └── datos.json
    ├── layout
    │   └── base.html
    ├── _out
    │   └── Aca es el sitio generado
    ├── site
    │   ├── index.html
    │   └── pagina.html
    └── static
        ├── css
        │   └── archivos.css
        ├── fonts
        │   ├── fonts__-webfont.eot
        │   ├── fonts__-webfont.ttf
        │   └── fonts__-webfont.woff
        ├── img
        │   └── imagenes
        └── js
            ├── ajax.js
            ├── bularcama.js
            └── handlebars-v1.3.0.js

* `data`: Carpeta no obligatoria, se usa para dar un orden. Aca se ponen los archivos json que se usaran para generar las paginas
* `layout`: Aca estan los layouts a utilizar
* `_out`: Carpeta donde se pone el sitio generado
* `site`: Carpeta de la estructura de la pagina, todos los archivos que esten aca seran parseados por bularcama. Para la salida se respeta la estructura de carpetas especificada.
* `static`: Archivos estaticos que se copian a la pagina generada

## Layout

Los archivos que se contengan en la carpeta layout, son archivos `html` (usando template `Handlebars`).

Se agrega el Helper `include` para agregar archivos. Se definen por defecto las siguientes variables para especificar rutas:
* `__base_dir__`: `$this->static_dir`
* `__site_dir__` : `$this->site_dir`
* `__layout_dir__`: `$this->layout_dir`
* `__static_dir__`: `$this->static_dir`
* `__out_dir__`: `$this->out_dir`

Para unir cadenas de caracteres se tiene que hacer como en php, teniendo lei cuidado de siempre agregar espacios entre las variables, el punto (`.`) y las cadenas literales.

Ejemplo:
    {{include __static_dir__ . "/js/ajax.js"}}


""Nota"": este helper solo se puede usar en los lauyouts, ya que incluye de manera literal a los achivos, no se define este helper en el `Handlebars` que utiliza el navegador.

TODO: explicar como se usa la clase Bularcama en javascript y como maneja las cargas de links automaticas (`data-click="link"`)

## Site

TODO:


## Ejemplo

TODO: explicar ejemplo

Ver: `example-site`

# Licencia

El projecto utiliza otros projectos, ver los archivos para ver la licencia especifica de cada uno.
El resto GPLv3: http://www.gnu.org/licenses/gpl-3.0.html.
