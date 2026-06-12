# Edición de tema y plantillas (Astra + Elementor)

## Qué quedó accesible desde el repo

- `wp-content/themes/` está bind-mounteado al contenedor `wordpress`. El child theme
  `astra-child/` vive ahí y es lo único versionado en git (el resto de `themes/`
  está en `.gitignore` — son temas de terceros, se reinstalan solos).
- `wp-content/mu-plugins/` también está bind-mounteado, para snippets/registro de
  plantillas Elementor vía PHP sin tocar plugins de terceros.
- `plugins/` y `uploads/` siguen en el volumen Docker `wp_data` (no versionados,
  se gestionan desde wp-admin / WP-CLI).

## WP-CLI

Nuevo servicio `wp-cli` (imagen `wordpress:cli`), comparte BD, `wp-content` y todo
el repo (montado en `/repo`). Uso:

```bash
docker compose run --rm wp-cli <comando wp-cli>
# ej:
docker compose run --rm wp-cli theme list
docker compose run --rm wp-cli post list --post_type=page
```

## Child theme

`wp-content/themes/astra-child/` — child theme estándar de Astra (ya activado).
Acá van customizaciones de CSS/PHP, hooks, plantillas de tema (`header.php`,
`single.php`, etc. siguiendo la jerarquía de WordPress/Astra).

## Plantillas de Elementor (export/import JSON)

`_elementor_data` (el contenido visual de Elementor) vive en la BD como JSON en
postmeta. Dos scripts en `scripts/` permiten traerlo al repo, editarlo y devolverlo:

```bash
# Exportar el contenido de Elementor de un post a un JSON versionable
docker compose run --rm wp-cli eval-file /repo/scripts/export-elementor-template.php \
  <ID-post> /repo/elementor-templates/<nombre>.json

# Importar (sobrescribe) el contenido de Elementor de un post desde un JSON
docker compose run --rm wp-cli eval-file /repo/scripts/import-elementor-template.php \
  <ID-post> /repo/elementor-templates/<nombre>.json [template-type]
```

`template-type` por defecto es `wp-page`. Para plantillas de la librería de Elementor
(headers, footers, secciones reutilizables) se usa el post type `elementor_library`,
ej:

```bash
docker compose run --rm wp-cli post create --post_type=elementor_library \
  --post_title="Header Cliente X" --post_status=publish --porcelain

docker compose run --rm wp-cli eval-file /repo/scripts/import-elementor-template.php \
  <ID-nuevo> /repo/elementor-templates/header-cliente-x.json header
```

## Flujo típico para una página nueva de cliente

1. `docker compose run --rm wp-cli post create --post_type=page --post_title="..." --post_status=draft --porcelain`
   → devuelve el ID.
2. Escribir/editar el JSON de Elementor en `elementor-templates/<algo>.json`
   (a mano, o partiendo de un export de una página/sección existente).
3. Importar con `import-elementor-template.php`.
4. Revisar en `http://localhost:8081/?page_id=<ID>` y/o abrir en el editor de
   Elementor para ajustes finos.
5. Publicar: `docker compose run --rm wp-cli post update <ID> --post_status=publish`.
