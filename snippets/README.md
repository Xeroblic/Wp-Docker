# Snippets del sitio (plugin Code Snippets)

Copias versionables de los snippets que viven en la tabla `wp_snippets` de la BD.
La BD es la fuente de verdad: WordPress ejecuta lo que está en la tabla, no estos archivos.

Exportar: `docker compose run --rm wp-cli eval-file /repo/scripts/export-snippets.php`

Importar un snippet editado (reemplaza el código en la BD por el del archivo):
`docker compose run --rm wp-cli eval-file /repo/scripts/import-snippet.php <id> <archivo>`

| ID | Nombre | Archivo | Scope | Activo | Modificado |
|----|--------|---------|-------|--------|------------|
| 1 | Hacer que los nombres de archivos subidos estén en minúsculas | `hacer-que-los-nombres-de-archivos-subidos-esten-en-minusculas.php` | global | no | 2026-04-30 05:01:52 |
| 2 | Desactivar barra de administración | `desactivar-barra-de-administracion.php` | front-end | no | 2026-04-30 05:01:52 |
| 3 | Permitir emotíconos | `permitir-emoticonos.php` | global | no | 2026-04-30 05:01:52 |
| 4 | Año actual | `ano-actual.php` | content | no | 2026-04-30 05:01:52 |
| 5 | Menu Dinamico | `menu-dinamico.php` | global | sí | 2026-06-04 04:54:18 |
| 6 | menu_dinamico_categorias | `menu_dinamico_categorias.php` | global | sí | 2026-05-17 22:28:02 |
| 7 | grilla_pcfactory_ajax | `grilla_pcfactory_ajax.php` | global | sí | 2026-06-13 03:07:55 |
| 8 | buscador_mundo_planet | `buscador_mundo_planet.php` | global | sí | 2026-05-17 22:26:51 |
| 9 | indice_categorias_mp | `indice_categorias_mp.php` | global | sí | 2026-05-17 22:25:37 |
| 10 | astra_breadcrumb_trail_items | `astra_breadcrumb_trail_items.php` | global | sí | 2026-05-17 22:24:38 |
| 11 | woocommerce_shop_loop_item | `woocommerce_shop_loop_item.php` | global | sí | 2026-06-13 01:05:48 |
| 12 | Comparador Global - Barra Flotante | `comparador-global-barra-flotante.php` | global | sí | 2026-05-17 22:22:53 |
| 13 | Página de Comparación | `pagina-de-comparacion.php` | global | sí | 2026-05-20 02:30:26 |
| 14 | woocommerce_archive_description | `woocommerce_archive_description.php` | global | sí | 2026-05-17 22:32:08 |
| 15 | zentria_myaccount_login_layout_css | `zentria_myaccount_login_layout_css.php` | global | sí | 2026-06-12 05:48:37 |
| 16 | mp_filtros_tienda_css | `mp_filtros_tienda_css.php` | front-end | sí | 2026-06-13 03:09:15 |
