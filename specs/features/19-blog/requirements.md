# F-01: Módulo de Blog Completo (CMS + Vistas Públicas)

> **Estado:** Lista para implementar  
> **ID:** F-01 · **Slug:** `19-blog`  
> **Prerequisitos:** Ninguno (módulo de contenido independiente)  
> **Desbloquea:** Posicionamiento orgánico (SEO), storytelling de marca y marketing de contenidos  
> **Presupuesto / Estimación:** 23 horas (184 €)

---

## 1. Fuentes canónicas (no duplicar)

| Tema | Fuente |
|------|--------|
| Reporte unificado de cliente | [`docs/cliente/Reporte Unificado Retroalimentacion y Scope.md`](../../../docs/cliente/Reporte%20Unificado%20Retroalimentacion%20y%20Scope.md) |
| Convenciones de arquitectura | `AGENTS.md` / `.ai/project-conventions rules` |
| Brief UI de Blog | [`specs/ui-briefs/19-blog.md`](../../ui-briefs/19-blog.md) |
| Tokens de Diseño Globales | [`specs/ui-briefs/00-design-tokens.md`](../../ui-briefs/00-design-tokens.md) |
| Layout Storefront | `resources/views/layouts/storefront.blade.php` |

---

## 2. User Stories

1. **Como** creador de contenido / administrador de Leen, **quiero** redactar artículos con editor enriquecido (texto con formato, imágenes, listas), organizados por categorías bilingües y con control de estado (borrador/publicado), **para** publicar historias de marca y guías de estilo.
2. **Como** administrador, **quiero** configurar metadatos SEO (`meta_title`, `meta_description`) por artículo, **para** mejorar el posicionamiento en motores de búsqueda (Google).
3. **Como** visitante, **quiero** explorar la lista de artículos en `/blog` con estética editorial de alta gama, paginación limpia y filtrado por categorías, **para** encontrar lecturas que me interesen.
4. **Como** visitante, **quiero** leer un artículo completo en `/blog/{slug}` con tipografía cuidada (Chillax / Montserrat), imagen de portada de alta resolución, tiempo estimado de lectura y artículos recomendados al final, **para** disfrutar de una experiencia de lectura fluida.
5. **Como** visitante, **quiero** poder cambiar de idioma entre español e inglés y ver el contenido del artículo en el idioma seleccionado, con fallback automático si no ha sido traducido aún.
6. **Como** visitante, **quiero** acceder al blog directamente desde la barra de navegación principal y el pie de página de la tienda.

---

## 3. Criterios de Aceptación (EARS: R1 – R9)

- **R1 (Publicación y Estados):**  
  *Cuando* un visitante ingrese a `/blog`,  
  *el sistema deberá* listar únicamente los artículos con estado `PostStatusEnum::Published` (`published`) y `published_at <= now()`, ordenados de forma descendente por `published_at`.

- **R2 (Acceso por Slug Único y Seguridad de Borradores):**  
  *Cuando* se solicite la ruta `/blog/{slug}`,  
  *el sistema deberá* resolver el artículo correspondiente si está publicado.  
  *Si* el artículo está en estado borrador (`draft`) o fecha futura y el usuario no es un administrador autenticado,  
  *el sistema deberá* retornar un error HTTP 404.

- **R3 (Soporte Bilingüe i18n y Fallback Automático):**  
  *Cuando* se renderice el listado o el detalle,  
  *el sistema deberá* mostrar `title`, `excerpt` y `content` en el idioma activo (`es` o `en`).  
  *Si* el campo en el idioma activo estuviera vacío,  
  *el sistema deberá* hacer fallback al idioma por defecto del proyecto (`es`).

- **R4 (Filtrado por Categorías y Ordenamiento):**  
  *Cuando* el visitante seleccione una categoría en `/blog?category={slug}`,  
  *el sistema deberá* filtrar los artículos publicados pertenecientes a esa categoría manteniendo la paginación.  
  *Las categorías* deberán mostrarse ordenadas ascendentemente por `sort_order` y sólo listar categorías activas (`is_active = true`).

- **R5 (Artículos Relacionados con Fallback Editorial):**  
  *Al* final de cada artículo en `/blog/{slug}`,  
  *el sistema deberá* mostrar hasta 3 artículos relacionados publicados de la misma categoría (excluyendo el artículo actual).  
  *Si* la categoría cuenta con menos de 3 artículos,  
  *el sistema deberá* completar los cupos restantes con los artículos publicados más recientes del blog.

- **R6 (Cálculo Estimado de Tiempo de Lectura):**  
  *En* cada tarjeta de artículo y en la cabecera del detalle,  
  *el sistema deberá* mostrar los minutos estimados de lectura calculados a partir de las palabras del contenido (`ceil(palabras / 200)` min).

- **R7 (Panel Filament v5 y Gestión de Medios):**  
  *Cuando* un administrador ingrese al panel,  
  *el sistema deberá* ofrecer los recursos `PostResource` y `PostCategoryResource` con control de permisos, pestañas de traducción por `LocaleEnum`, slugging automático reactivo, RichEditor, selección de estado con `PostStatusEnum` y purga física de archivos de imagen en disco (`Storage::disk('public')`) al eliminar un post.

- **R8 (SEO Tags y OpenGraph):**  
  *En* la cabecera HTML del detalle del artículo,  
  *el sistema deberá* inyectar las etiquetas OpenGraph y metatags configurados en el panel (`og:title`, `og:description`, `og:image`), usando como fallback el título del artículo, el extracto y la portada principal o imagen institucional de Leen.

- **R9 (Integración en Navegación Storefront):**  
  *En* el layout general de la tienda (`storefront.blade.php`),  
  *el sistema deberá* incluir el enlace "Blog" en el menú de navegación principal (Desktop y Móvil) y en el Footer, utilizando claves de traducción localizadas (`storefront.nav.blog` y `storefront.footer.blog`).

---

## 4. Alcance Excluido / Módulos Evolutivos (Por Cotizar)

Los siguientes ítems **no forman parte del alcance de esta entrega** y quedan documentados como siguientes pasos lógicos para cotización futura:

- **Auto-Traducción con API Externa (DeepL / Gemini / OpenAI):** Integración de un servicio de traducción automática que permita al administrador traducir artículos completos de español a inglés en 1 solo clic desde Filament. *(El módulo actual entrega la arquitectura JSON Translatable lista para traducción manual y fallback automático)*.
- **Comentarios públicos de lectores en artículos:** Sistema de comentarios y moderación de discusiones.
- **Multi-autor con perfiles públicos de blog:** Páginas de biografía de autores individuales.
